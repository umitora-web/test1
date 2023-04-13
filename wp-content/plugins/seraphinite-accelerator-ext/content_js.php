<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

function _Scripts_EncodeBodyAsSrc( $cont )
{

	$cont = str_replace( "%", '%25', $cont );

	$cont = str_replace( "\n", '%0A', $cont );
	$cont = str_replace( "#", '%23', $cont );
	$cont = str_replace( "\"", '%22', $cont );

	return( $cont );
}

function IsScriptTypeJs( $type )
{
	return( !$type || $type == 'application/javascript' || $type == 'text/javascript' || $type == 'module' );
}

function Script_SrcAddPreloading( $item, $src, $head, $doc )
{
	if( !$src )
		return;

	$itemPr = $doc -> createElement( 'link' );
	$itemPr -> setAttribute( 'rel', 'preload' );
	$itemPr -> setAttribute( 'as', $item -> tagName == 'IFRAME' ? 'document' : 'script' );
	$itemPr -> setAttribute( 'href', $src );
	if( $item -> hasAttribute( 'integrity' ) )
		$itemPr -> setAttribute( "integrity", $item -> getAttribute( "integrity" ) );
	if( $item -> hasAttribute( "crossorigin" ) )
		$itemPr -> setAttribute( "crossorigin", $item -> getAttribute( "crossorigin" ) );
	$head -> appendChild( $itemPr );
}

function Scripts_Process( &$ctxProcess, $sett, $settCache, $settContPr, $settJs, $settCdn, $doc )
{
	if( (isset($ctxProcess[ 'isAMP' ])?$ctxProcess[ 'isAMP' ]:null) )
	    return( true );

	$optLoad = Gen::GetArrField( $settJs, array( 'optLoad' ), false );
	$skips = Gen::GetArrField( $settJs, array( 'skips' ), array() );

	if( !( $optLoad || Gen::GetArrField( $settJs, array( 'groupNonCrit' ), false ) || Gen::GetArrField( $settJs, array( 'min' ), false ) || Gen::GetArrField( $settCdn, array( 'enable' ), false ) || $skips ) )
		return( true );

	if( (isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) )
		$optLoad = false;

	$aGrpExcl = Gen::GetArrField( $settJs, array( 'groupExcls' ), array() );
	$notCritsDelayTimeout = Gen::GetArrField( $settJs, array( 'nonCrit', 'timeout', 'enable' ), false ) ? Gen::GetArrField( $settJs, array( 'nonCrit', 'timeout', 'v' ), 0 ) : null;

	$specsDelayTimeout = Gen::GetArrField( $settJs, array( 'spec', 'timeout', 'enable' ), false ) ? Gen::GetArrField( $settJs, array( 'spec', 'timeout', 'v' ), 0 ) : null;
	$specs = ( ( $notCritsDelayTimeout !== null && $specsDelayTimeout ) || ( $notCritsDelayTimeout === null && $specsDelayTimeout !== null ) ) ? Gen::GetArrField( $settJs, array( 'spec', 'items' ), array() ) : array();

	$head = $ctxProcess[ 'ndHead' ];
	$body = $ctxProcess[ 'ndBody' ];

	$settNonCrit = Gen::GetArrField( $settJs, array( 'nonCrit' ), array() );

	$delayNotCritNeeded = false;
	$delaySpecNeeded = false;

	$items = HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'script' ) );

	$contGroups = array( 'crit' => array( array( 0, 0 ), array( '' ) ), '' => array( array( 0, 0 ), array( '' ) ), 'spec' => array( array( 0, 0 ), array( '' ) ) );

	foreach( $items as $item )
	{
		if( ContentProcess_IsAborted( $settCache ) ) return( true );

		$type = HtmlNd::GetAttrVal( $item, 'type' );
		if( !IsScriptTypeJs( $type ) )
			continue;

		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
		{
			if( !$type )
				$item -> setAttribute( 'type', $type = 'text/javascript' );
		}
		else if( $type && (isset($settContPr[ 'min' ])?$settContPr[ 'min' ]:null) && $type != 'module' )
		{
			$item -> removeAttribute( 'type' );
			$type = null;
		}

		$src = HtmlNd::GetAttrVal( $item, 'src' );
		$id = HtmlNd::GetAttrVal( $item, 'id' );
		$cont = $item -> nodeValue;

		{

		}

		$detectedPattern = null;
		if( IsObjInRegexpList( $skips, array( 'src' => $src, 'id' => $id, 'body' => $cont ), $detectedPattern ) )
		{
			if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
			{
				$item -> setAttribute( 'type', 'o/js-inactive' );
				$item -> setAttribute( 'seraph-accel-debug', 'status=skipped;' . ( $detectedPattern ? ' detectedPattern="' . $detectedPattern . '"' : '' ) );
			}
			else
				$item -> parentNode -> removeChild( $item );
			continue;
		}

		$detectedPattern = null;
		if( $src )
		{
			$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, $src );

			if( (isset($srcInfo[ 'filePath' ])?$srcInfo[ 'filePath' ]:null) && Gen::GetFileExt( $srcInfo[ 'filePath' ] ) == 'js' )
				$cont = @file_get_contents( $srcInfo[ 'filePath' ] );
			if( !$cont )
			{
				$cont = GetExtContents( (isset($srcInfo[ 'url' ])?$srcInfo[ 'url' ]:null), $contMimeType );
				if( $cont !== false && !in_array( $contMimeType, array( 'text/javascript', 'application/x-javascript', 'application/javascript' ) ) )
				{
					$cont = false;
					LastWarnDscs_Add( LocId::Pack( 'JsUrlWrongType_%1$s%2$s', null, array( $srcInfo[ 'url' ], $contMimeType ) ) );
				}
			}

			$isCrit = $item -> hasAttribute( 'seraph-accel-crit' ) ? true : GetObjSrcCritStatus( $settNonCrit, $specs, $srcInfo, $src, $id, $cont, $detectedPattern );

			if( Script_AdjustCont( $ctxProcess, $settCache, $settJs, $srcInfo, $src, $id, $cont ) )
			{
				if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
					$cont = '// ################################################################################################################################################' . "\r\n" . '// DEBUG: seraph-accel JS src="' . $src . '"' . "\r\n\r\n" . $cont;

				if( !adkxsshiujqtfk( $ctxProcess, $settCache, 'js', $cont, $src ) )
					return( false );
			}

			Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, 'js' );
			Fullness_AdjustUrl( $ctxProcess, $src, (isset($srcInfo[ 'srcUrlFullness' ])?$srcInfo[ 'srcUrlFullness' ]:null) );

			$item -> setAttribute( 'src', $src );
		}
		else
		{
			if( !$cont )
				continue;

			$isCrit = $item -> hasAttribute( 'seraph-accel-crit' ) ? true : GetObjSrcCritStatus( $settNonCrit, $specs, null, null, $id, $cont, $detectedPattern );

			if( Script_AdjustCont( $ctxProcess, $settCache, $settJs, null, null, $id, $cont ) )
			{
				if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
					$cont = '// ################################################################################################################################################' . "\r\n" . '// DEBUG: seraph-accel JS src="inline:' . (isset($ctxProcess[ 'serverArgs' ][ 'REQUEST_SCHEME' ])?$ctxProcess[ 'serverArgs' ][ 'REQUEST_SCHEME' ]:null) . '://' . $ctxProcess[ 'host' ] . ':' . (isset($ctxProcess[ 'serverArgs' ][ 'SERVER_PORT' ])?$ctxProcess[ 'serverArgs' ][ 'SERVER_PORT' ]:null) . (isset($ctxProcess[ 'serverArgs' ][ 'REQUEST_URI' ])?$ctxProcess[ 'serverArgs' ][ 'REQUEST_URI' ]:null) . ':' . $item -> getLineNo() . '"' . "\r\n\r\n" . $cont;

				HtmlNd::SetValFromContent( $item, $cont );
			}
		}

		ContUpdateItemIntegrity( $item, $cont );

		if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
			$item -> setAttribute( 'seraph-accel-debug', 'status=' . ( $isCrit === true ? 'critical' : ( $isCrit === null ? 'special' : 'nonCritical' ) ) . ';' . ( $detectedPattern ? ' detectedPattern="' . $detectedPattern . '"' : '' ) );

		$delay = 0;
		if( $optLoad )
		{
			if( !$isCrit )
			{
				$parentNode = $item -> parentNode;
				$async = $item -> hasAttribute( 'async' );

				$delay = ( $isCrit === null ) ? $specsDelayTimeout : $notCritsDelayTimeout;

				if( $delay === 0 && ( !$async || ( $parentNode === $head || $parentNode === $body ) ) )
					$body -> appendChild( $item );
			}

		}

		if( (isset($ctxProcess[ 'chunksEnabled' ])?$ctxProcess[ 'chunksEnabled' ]:null) )
			ContentMarkSeparate( $item, false );

		if( $delay )
		{
			if( $type )
				$item -> setAttribute( 'data-type', $type );

			if( $isCrit === null )
			{

				$item -> setAttribute( 'type', 'o/js-lzls' );
				$delaySpecNeeded = true;
			}
			else
			{

				$item -> setAttribute( 'type', 'o/js-lzl' );
				$delayNotCritNeeded = true;
			}
		}

		if( !(isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) && (isset($settJs[ $isCrit ? 'group' : ( $isCrit === null ? 'groupSpec' : 'groupNonCrit' ) ])?$settJs[ $isCrit ? 'group' : ( $isCrit === null ? 'groupSpec' : 'groupNonCrit' ) ]:null) )
		{
			if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) && is_string( $cont ) )
				$cont = '/* ################################################################################################################################################ */' . "\r\n" . '/* DEBUG: seraph-accel JS src="' . $src . '" */' . "\r\n\r\n" . $cont;

			$bGrpExcl = ( Gen::GetArrField( $settJs, array( 'groupExclMdls' ) ) && $type == 'module' ) || IsObjInRegexpList( $aGrpExcl, array( 'src' => $src, 'id' => $id, 'body' => $cont ) );

			if( $cont === false || $bGrpExcl )
				$cont = '';

			if( substr( $cont, -1, 1 ) == ';' )
				$cont .= "\r\n";
			else
				$cont .= ";\r\n";

			if( (isset($ctxProcess[ 'chunksEnabled' ])?$ctxProcess[ 'chunksEnabled' ]:null) && Gen::GetArrField( $settCache, array( 'chunks', 'js' ) ) )
				$cont .= ContentMarkGetSep();

			if( $optLoad && $isCrit === false && $delayNotCritNeeded )
				$cont .= 'seraph_accel_gzjydy();';

			$contGroup = &$contGroups[ $isCrit ? 'crit' : ( $isCrit === null ? 'spec' : '' ) ];

			if( ( $item -> hasAttribute( 'defer' ) && $item -> getAttribute( 'defer' ) !== false ) && !( $item -> hasAttribute( 'async' ) && $item -> getAttribute( 'async' ) !== false ) && $src )
			{
				if( $bGrpExcl )
					array_splice( $contGroup[ 1 ], count( $contGroup[ 1 ] ), 0, array( $item, '' ) );

				$contGroup[ 1 ][ count( $contGroup[ 1 ] ) - 1 ] .= $cont;
			}
			else
			{
				if( $bGrpExcl )
				{
					array_splice( $contGroup[ 1 ], $contGroup[ 0 ][ 0 ], 1, array( substr( $contGroup[ 1 ][ $contGroup[ 0 ][ 0 ] ], 0, $contGroup[ 0 ][ 1 ] ), $item, substr( $contGroup[ 1 ][ $contGroup[ 0 ][ 0 ] ], $contGroup[ 0 ][ 1 ] ) ) );
					$contGroup[ 0 ][ 0 ] += 2;
					$contGroup[ 0 ][ 1 ] = 0;
				}

				$contGroup[ 1 ][ $contGroup[ 0 ][ 0 ] ] = substr_replace( $contGroup[ 1 ][ $contGroup[ 0 ][ 0 ] ], $cont, $contGroup[ 0 ][ 1 ], 0 );
				$contGroup[ 0 ][ 1 ] += strlen( $cont );
			}

			unset( $contGroup );

			$item -> parentNode -> removeChild( $item );
		}
		else if( $delay && (isset($settJs[ 'preLoadEarly' ])?$settJs[ 'preLoadEarly' ]:null) )
			Script_SrcAddPreloading( $item, $src, $head, $doc );
	}

	if( $optLoad )
	{
		foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'iframe' ) ) as $item )
		{
			if( ContentProcess_IsAborted( $settCache ) ) return( true );

			if( HtmlNd::FindUpByTag( $item, 'noscript' ) )
				continue;

			if( !Scripts_IsElemAs( $ctxProcess, $doc, $settJs, $item ) )
				continue;

			$src = HtmlNd::GetAttrVal( $item, 'src' );
			$id = HtmlNd::GetAttrVal( $item, 'id' );
			$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, $src );

			$detectedPattern = null;
			$isCrit = GetObjSrcCritStatus( $settNonCrit, $specs, $srcInfo, $src, $id, null, $detectedPattern );

			Fullness_AdjustUrl( $ctxProcess, $src, (isset($srcInfo[ 'srcUrlFullness' ])?$srcInfo[ 'srcUrlFullness' ]:null) );
			$item -> setAttribute( 'src', $src );
			$item -> setAttribute( 'async', '' );

			if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
				$item -> setAttribute( 'seraph-accel-debug', 'status=' . ( $isCrit === true ? 'critical' : ( $isCrit === null ? 'special' : 'nonCritical' ) ) . ';' . ( $detectedPattern ? ' detectedPattern="' . $detectedPattern . '"' : '' ) );

			if( $isCrit )
				continue;

			$delay = ( $isCrit === null ) ? $specsDelayTimeout : $notCritsDelayTimeout;
			if( !$delay )
				continue;

			HtmlNd::RenameAttr( $item, 'src', 'data-src' );
			if( $isCrit === null )
			{
				$item -> setAttribute( 'type', 'o/js-lzls' );
				$delaySpecNeeded = true;
			}
			else
			{
				$item -> setAttribute( 'type', 'o/js-lzl' );
				$delayNotCritNeeded = true;
			}
		}
	}

	foreach( $contGroups as $contGroupId => $contGroup )
	{
		foreach( $contGroup[ 1 ] as $cont )
		{
			if( !$cont )
				continue;

			if( is_string( $cont ) )
			{
				$item = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$item -> setAttribute( $item, 'type', 'text/javascript' );

				if( !GetContentProcessorForce( $sett ) && (isset($ctxProcess[ 'chunksEnabled' ])?$ctxProcess[ 'chunksEnabled' ]:null) && Gen::GetArrField( $settCache, array( 'chunks', 'js' ) ) )
				{
					$idSub = ( string )( $ctxProcess[ 'subCurIdx' ]++ ) . '.js';
					$ctxProcess[ 'subs' ][ $idSub ] = $cont;
					$src = ContentProcess_GetGetPartUri( $ctxProcess, $idSub );
				}
				else
				{
					$cont = str_replace( ContentMarkGetSep(), '', $cont );
					if( !adkxsshiujqtfk( $ctxProcess, $settCache, 'js', $cont, $src ) )
						return( false );
				}

				Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, 'js' );
				Fullness_AdjustUrl( $ctxProcess, $src );
				$item -> setAttribute( 'src', $src );
			}
			else
				$item = $cont;

			if( $contGroupId === 'crit' )
			{
				$head -> insertBefore( $item, $head -> firstChild );
				continue;
			}

			if( is_string( $cont ) && $optLoad )
			{
				$delay = ( $contGroupId === 'spec' ) ? $specsDelayTimeout : $notCritsDelayTimeout;
				if( $delay )
				{

					if( $contGroupId === 'spec' )
					{
						$item -> setAttribute( 'type', 'o/js-lzls' );
						$delaySpecNeeded = true;

						$delay = $specsDelayTimeout;
					}
					else
					{
						$item -> setAttribute( 'type', 'o/js-lzl' );
						$delayNotCritNeeded = true;

						$delay = $notCritsDelayTimeout;
					}

					if( (isset($settJs[ 'preLoadEarly' ])?$settJs[ 'preLoadEarly' ]:null) )
						Script_SrcAddPreloading( $item, $src, $head, $doc );
				}
			}

			$body -> appendChild( $item );
		}
	}

	if( $delayNotCritNeeded || $delaySpecNeeded )
	{

		{

			$item = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$item -> setAttribute( 'type', 'text/javascript' );
			$item -> nodeValue = htmlspecialchars( 'document.seraph_accel_bpb_ce=document.createElement;' );

			$head -> insertBefore( $item, $head -> firstChild );
		}

		{
			$item = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$item -> setAttribute( 'type', 'text/javascript' );
			$item -> nodeValue = htmlspecialchars( '
				(
					function( d )
					{
						function SetSize( e )
						{
							e.style.setProperty("--seraph-accel-client-width", "" + e.clientWidth + "px");
							e.style.setProperty("--seraph-accel-client-width-px", "" + e.clientWidth);
							e.style.setProperty("--seraph-accel-client-height", "" + e.clientHeight + "px");
						}

						d.addEventListener( "seraph_accel_calcSizes", function( evt ) { SetSize( d.documentElement ); }, { capture: true, passive: true } );
						SetSize( d.documentElement );
					}
				)( document );
			' );

			$body -> insertBefore( $item, $body -> firstChild );
		}

		$delayCss = false;
		if( $notCritsDelayTimeout && (isset($ctxProcess[ 'lazyloadStyles' ][ 'nonCrit' ])?$ctxProcess[ 'lazyloadStyles' ][ 'nonCrit' ]:null) === 'withScripts' )
		{
			$delayCss = true;
			unset( $ctxProcess[ 'lazyloadStyles' ][ 'nonCrit' ] );
		}

		if( !adkxsshiujqtfk( $ctxProcess, $settCache, 'js', str_replace( array( '_E_A1_', '_E_A2_', '_E_TM1_', '_E_TM2_', '_E_CSS_', '_E_CJSD_', '_E_FCD_', '_E_PRL_', '_E_LF_' ), array( '"o/js-lzl"', '"o/js-lzls"', $notCritsDelayTimeout ? $notCritsDelayTimeout : 0, $specsDelayTimeout ? $specsDelayTimeout : 0, $delayCss ? $delayCss : 0, (isset($settJs[ 'cplxDelay' ])?$settJs[ 'cplxDelay' ]:null) ? 1 : 0, Gen::GetArrField( $settJs, array( 'clk', 'delay' ), 250 ), (isset($settJs[ 'preLoadEarly' ])?$settJs[ 'preLoadEarly' ]:null) ? 0 : 1, (isset($settJs[ 'loadFast' ])?$settJs[ 'loadFast' ]:null) ? 1 : 0 ),

			"(function(p,e,z,w,r,A,E,F,G,H,I){function B(){if(f){var d=p[function(b){var a=\"\";b.forEach(function(b){a+=String.fromCharCode(b+3)});return a}([103,78,114,98,111,118])];!f.dkhjihyvjed&&d?f=void 0:(f.dkhjihyvjed=!0,f.jydy(d))}}function t(d,b,a){function h(){if(!d)return[];for(var u=[].slice.call(e.querySelectorAll('[type=\"'+d+'\"]')),a=0,m=u.length;a<m;a++){var c=u[a];!c.hasAttribute(\"defer\")||!1===c.defer||c.hasAttribute(\"async\")&&!1!==c.async||!c.hasAttribute(\"src\")||(u.splice(a,1),u.push(c),a--,\nm--)}return u}function g(a){a=void 0===a?!1:a;B();I||a?k():setTimeout(k,b)}function c(a){a=a.ownerDocument;var c=a.seraph_accel_njsujyhmaeex={hujvqjdes:\"\",wyheujyhm:a[function(a){var c=\"\";a.forEach(function(a){c+=String.fromCharCode(a+3)});return c}([116,111,102,113,98])],wyhedbujyhm:a[function(a){var c=\"\";a.forEach(function(a){c+=String.fromCharCode(a+3)});return c}([116,111,102,113,98,105,107])],ujyhm:function(a){this.seraph_accel_njsujyhmaeex.hujvqjdes+=a},dbujyhm:function(a){this.write(a+\"\\n\")}};\na[function(a){var c=\"\";a.forEach(function(a){c+=String.fromCharCode(a+3)});return c}([116,111,102,113,98])]=c.ujyhm;a[function(a){var c=\"\";a.forEach(function(a){c+=String.fromCharCode(a+3)});return c}([116,111,102,113,98,105,107])]=c.dbujyhm}function v(a){var c=a.ownerDocument,b=c.seraph_accel_njsujyhmaeex;if(b){if(b.hujvqjdes){var h=c.createElement(\"span\");a.parentNode.insertBefore(h,a.nextSibling);h.outerHTML=b.hujvqjdes}c[function(a){var c=\"\";a.forEach(function(a){c+=String.fromCharCode(a+3)});\nreturn c}([116,111,102,113,98])]=b.wyheujyhm;c[function(a){var c=\"\";a.forEach(function(a){c+=String.fromCharCode(a+3)});return c}([116,111,102,113,98,105,107])]=b.wyhedbujyhm;delete c.seraph_accel_njsujyhmaeex}}function k(){var b=l.shift();if(b)if(b.parentNode){var d=e.seraph_accel_bpb_ce(b.tagName),m=b.attributes;if(m)for(var f=0;f<m.length;f++){var n=m[f],p=n.value;n=n.name;\"type\"!=n&&(\"data-type\"==n&&(n=\"type\"),\"data-src\"==n&&(n=\"src\"),d.setAttribute(n,p))}d.textContent=b.textContent;m=!d.hasAttribute(\"async\");\nf=d.hasAttribute(\"src\");n=d.hasAttribute(\"nomodule\");m&&c(d);if(f=m&&f&&!n)d.onload=d.onerror=function(){v(d);g()};b.parentNode.replaceChild(d,b);f||(m&&v(d),g(!m))}else l=h(),k();else a&&a()}b=void 0===b?0:b;var l=h();if(H){var f=e.createDocumentFragment();l.forEach(function(a){var c=a?a.getAttribute(\"src\"):void 0;if(c){var b=e.createElement(\"link\");b.setAttribute(\"rel\",\"preload\");b.setAttribute(\"as\",\"IFRAME\"==a.tagName?\"document\":\"script\");b.setAttribute(\"href\",c);a.hasAttribute(\"integrity\")&&b.setAttribute(\"integrity\",\na.getAttribute(\"integrity\"));a.hasAttribute(\"crossorigin\")&&b.setAttribute(\"crossorigin\",a.getAttribute(\"crossorigin\"));f.appendChild(b)}});e.head.appendChild(f)}g()}function l(d,b,a){var h=e.createEvent(\"Events\");h.initEvent(b,!0,!1);if(a)for(var g in a)h[g]=a[g];d.dispatchEvent(h)}function x(d,b){function a(a){try{Object.defineProperty(e,\"readyState\",{configurable:!0,enumerable:!0,value:a})}catch(J){}}function h(c){r?(e.body.className=e.body.className.replace(/(?:^|\\s)seraph-accel-js-lzl-ing(?:\\s|$)/,\n\" \"),f&&(f.jydyut(),f=void 0),a(\"interactive\"),l(e,\"readystatechange\"),l(e,\"DOMContentLoaded\"),delete e.readyState,l(e,\"readystatechange\"),setTimeout(function(){l(p,\"load\");l(p,\"scroll\");b&&b();c()})):c()}if(q){if(3==q){E&&function(a,c){a.querySelectorAll(c).forEach(function(a){var c=a.cloneNode();c.rel=\"stylesheet\";a.parentNode.replaceChild(c,a)})}(e,'link[rel=\"stylesheet/lzl-nc\"]');l(e,\"seraph_accel_beforeJsDelayLoad\");for(var g=e.querySelectorAll(\"noscript[data-lzl-bjs]\"),c=0;c<g.length;c++){var v=\ng[c];v.outerHTML=v.textContent}r&&a(\"loading\");d?t(r?z:0,10,function(){h(function(){2==q?(q=1,1E6!=A&&setTimeout(function(){x(!0)},A)):t(w)})}):t(r?z:0,0,function(){h(function(){t(w)})})}else 1==q&&t(w);d?q--:q=0}}function C(d){function b(a){return\"click\"==a||\"touchend\"==a}function a(c){if(b(c.type)){if(void 0!==g){var d=!0;if(\"click\"==c.type)for(var k=c.target;k;k=k.parentNode)if(k.getAttribute&&(k.getAttribute(\"data-lzl-clk-no\")&&(d=!1),k.getAttribute(\"data-lzl-clk-nodef\"))){c.preventDefault();\nbreak}if(d){d=!1;for(k=0;k<g.length;k++)if(g[k].type==c.type){d=!0;break}d||g.push(c)}}}else e.removeEventListener(c.type,a,{passive:!0});x(!1,h)}function h(){D.forEach(function(c){e.removeEventListener(c,a,{passive:!b(c)})});setTimeout(function(){g.forEach(function(a){if(\"touchend\"==a.type){var c=a.changedTouches&&a.changedTouches.length?a.changedTouches[0]:void 0,b=c?e.elementFromPoint(c.clientX,c.clientY):void 0;b&&(l(b,\"touchstart\",{touches:[{clientX:c.clientX,clientY:c.clientY}],changedTouches:a.changedTouches}),\nl(b,\"touchend\",{touches:[{clientX:c.clientX,clientY:c.clientY}],changedTouches:a.changedTouches}))}else\"click\"==a.type&&(b=e.elementFromPoint(a.clientX,a.clientY))&&l(b,\"click\",{clientX:a.clientX,clientY:a.clientY})});g=void 0},G)}d.currentTarget&&d.currentTarget.removeEventListener(d.type,C);var g=[];1E6!=r&&setTimeout(function(){x(!0,h)},r);D.forEach(function(c){e.addEventListener(c,a,{passive:!b(c)})})}function y(){l(e,\"seraph_accel_calcSizes\")}e.seraph_accel_bpb_ce||(e.seraph_accel_bpb_ce=e.createElement);\nvar D=\"scroll wheel mouseenter mousemove mouseover keydown click touchstart touchmove touchend\".split(\" \"),f=F?{a:[],jydy:function(d){if(d&&d.fn&&!d.seraph_accel_bpb){this.a.push(d);d.seraph_accel_bpb={otquhdv:d.fn[function(b){var a=\"\";b.forEach(function(b){a+=String.fromCharCode(b+3)});return a}([111,98,94,97,118])]};if(d[function(b){var a=\"\";b.forEach(function(b){a+=String.fromCharCode(b+3)});return a}([101,108,105,97,79,98,94,97,118])])d[function(b){var a=\"\";b.forEach(function(b){a+=String.fromCharCode(b+\n3)});return a}([101,108,105,97,79,98,94,97,118])](!0);d.fn[function(b){var a=\"\";b.forEach(function(b){a+=String.fromCharCode(b+3)});return a}([111,98,94,97,118])]=function(b){e.addEventListener(\"DOMContentLoaded\",function(a){b.bind(e)(d,a)});return this}}},jydyut:function(){for(var d=0;d<this.a.length;d++){var b=this.a[d];b.fn[function(a){var b=\"\";a.forEach(function(a){b+=String.fromCharCode(a+3)});return b}([111,98,94,97,118])]=b.seraph_accel_bpb.otquhdv;delete b.seraph_accel_bpb;if(b[function(a){var b=\n\"\";a.forEach(function(a){b+=String.fromCharCode(a+3)});return b}([101,108,105,97,79,98,94,97,118])])b[function(a){var b=\"\";a.forEach(function(a){b+=String.fromCharCode(a+3)});return b}([101,108,105,97,79,98,94,97,118])](!1)}}}:void 0;p.seraph_accel_gzjydy=B;var q=3;p.addEventListener(\"load\",C);p.addEventListener(\"resize\",y,!1);e.addEventListener(\"DOMContentLoaded\",y,!1);p.addEventListener(\"load\",y)})(window,document,_E_A1_,_E_A2_,_E_TM1_,_E_TM2_,_E_CSS_,_E_CJSD_,_E_FCD_,_E_PRL_,_E_LF_);\n"
		), $src ) )
			return( false );

		$item = $doc -> createElement( 'script' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$item -> setAttribute( 'type', 'text/javascript' );
		$item -> setAttribute( 'id', 'seraph-accel-js-lzl' );
	    $item -> setAttribute( 'defer', '' );

		Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, 'js' );
		Fullness_AdjustUrl( $ctxProcess, $src );
		$item -> setAttribute( 'src', $src );

		$body -> appendChild( $item );

	}

	return( true );
}

function Scripts_IsElemAs( &$ctxProcess, $doc, $settJs, $item )
{
	$items = &$ctxProcess[ 'scriptsInclItems' ];
	if( $items === null )
	{
		$items = array();

		$incls = Gen::GetArrField( $settJs, array( 'other', 'incl' ), array() );
		if( $incls )
		{
			$xpath = new \DOMXPath( $doc );

			foreach( $incls as $inclItemPath )
				foreach( HtmlNd::ChildrenAsArr( $xpath -> query( $inclItemPath, $ctxProcess[ 'ndHtml' ] ) ) as $itemIncl )
					$items[] = $itemIncl;
		}
	}

	return( in_array( $item, $items, true ) );
}

function JsMinify( $cont, $method, $removeFlaggedComments = false )
{
	try
	{
		switch( $method )
		{
		case 'jshrink':		$contNew = JShrink\Minifier::minify( $cont, array( 'flaggedComments' => !$removeFlaggedComments ) ); break;
		default:			$contNew = JSMin\JSMin::minify( $cont, array( 'removeFlaggedComments' => $removeFlaggedComments ) ); break;
		}
	}
	catch( \Exception $e )
	{
		return( $cont );
	}

	if( !$contNew )
		return( $cont );

	$cont = $contNew;

	if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
		$cont = '/* DEBUG: MINIFIED by seraph-accel */' . $cont;

	return( $cont );
}

function Script_AdjustCont( $ctxProcess, $settCache, $settJs, $srcInfo, $src, $id, &$cont )
{
	if( !$cont )
		return( false );

	$adjusted = false;
	if( ( !$srcInfo || !(isset($srcInfo[ 'ext' ])?$srcInfo[ 'ext' ]:null) ) && Gen::GetArrField( $settJs, array( 'min' ), false ) && !IsObjInRegexpList( Gen::GetArrField( $settJs, array( 'minExcls' ), array() ), array( 'src' => $src, 'id' => $id, 'body' => $cont ) ) )
	{
		$contNew = trim( JsMinify( $cont, (isset($settJs[ 'minMthd' ])?$settJs[ 'minMthd' ]:null), (isset($settJs[ 'cprRem' ])?$settJs[ 'cprRem' ]:null) ) );
		if( $cont != $contNew )
		{
			$cont = $contNew;
			$adjusted = true;
		}
	}

	return( $adjusted );
}

