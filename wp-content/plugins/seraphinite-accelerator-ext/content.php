<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

require( __DIR__ . '/htmlparser.php' );
require( __DIR__ . '/content_img.php' );
require( __DIR__ . '/content_js.php' );
require( __DIR__ . '/content_css.php' );
require( __DIR__ . '/content_frm.php' );

spl_autoload_register(
	function( $class )
	{
		if( strpos( $class, 'seraph_accel\\CssToXPathNormalizedAttributeMatchingExtension' ) === 0 || strpos( $class, 'seraph_accel\\CssToXPathHtmlExtension' ) === 0 )
			require_once( __DIR__ . '/content_css_ex.php' );
		if( strpos( $class, 'seraph_accel\\Symfony\\Component\\CssSelector\\' ) === 0 )
			require_once( __DIR__ . '/Cmn/Ext/PHP/symfony-css-selector/' . str_replace( '\\', '/', substr( $class, 43 ) ) . '.php' );

		if( strpos( $class, 'seraph_accel\\tubalmartin\\CssMin' ) === 0 )
			require_once( __DIR__ . '/Cmn/Ext/PHP/YUI-CSS-compressor-PHP-port/' . str_replace( '\\', '/', substr( $class, 32 ) ) . '.php' );
		if( strpos( $class, 'seraph_accel\\Sabberworm\\CSS' ) === 0 )
			require_once( __DIR__ . '/Cmn/Ext/PHP/php-css-parser/' . str_replace( '\\', '/', substr( $class, 28 ) ) . '.php' );

		if( strpos( $class, 'seraph_accel\\JSMin\\' ) === 0 )
			require_once( __DIR__ . '/Cmn/Ext/PHP/jsmin-php/' . str_replace( '\\', '/', substr( $class, 19 ) ) . '.php' );
		if( strpos( $class, 'seraph_accel\\JShrink\\' ) === 0 )
			require_once( __DIR__ . '/Cmn/Ext/PHP/JShrink/' . str_replace( '\\', '/', substr( $class, 21 ) ) . '.php' );
	}
);

function ContentDisableIndexingEx( $buffer )
{
	$posHead = Ui::TagBeginGetPos( array( 'head', 'HEAD' ), $buffer );
	if( $posHead )
		$buffer = substr( $buffer, 0, $posHead[ 1 ] ) . Ui::TagOpen( 'meta', array( 'name' => 'robots', 'content' => 'noindex' ) ) . substr( $buffer, $posHead[ 1 ] );
	return( $buffer );
}

function ContentDisableIndexing()
{
	ob_start( 'seraph_accel\\ContentDisableIndexingEx' );
}

function InitContentProcessor( $sett )
{

	add_action( 'init', 'seraph_accel\\_InitContentProcessor' , 0 );
}

function _ContentProcessor_TmpCont_SettImg_Adjust( &$settImg )
{

	Gen::SetArrField( $settImg, array( 'inlSml' ), false );

}

function _InitContentProcessor()
{
	global $seraph_accel_g_prepPrms;
	global $seraph_accel_g_prepCont;
	global $seraph_accel_g_prepContIsTmp;

	$siteId = GetSiteId();
	$sett = Plugin::SettGet();
	$settContPr = Gen::GetArrField( $sett, array( 'contPr' ), array() );
	$tmCur = Gen::GetCurRequestTime();

	$seraph_accel_g_prepCont = false;
	$seraph_accel_g_prepContIsTmp = false;

	if( !GetContentProcessorForce( $sett ) )
	{
		if( $cacheSkipData = GetContCacheEarlySkipData( $path, $pathIsDir, $args ) )
		{
			if( $cacheSkipData[ 0 ] == 'revalidating-begin' )
				$seraph_accel_g_prepContIsTmp = true;

			else
				return;

			unset( $cacheSkipData );
		}

		if( $seraph_accel_g_prepPrms !== null && (isset($seraph_accel_g_prepPrms[ 'tmp' ])?$seraph_accel_g_prepPrms[ 'tmp' ]:null) )
			$seraph_accel_g_prepContIsTmp = true;

		$settCache = Gen::GetArrField( $sett, array( 'cache' ), array() );

		if( (isset($settCache[ 'enable' ])?$settCache[ 'enable' ]:null) && !function_exists( 'seraph_accel_siteSettInlineDetach' ) )
			return;

		if( ContProcGetExclStatus( $siteId, $settCache, $path, $pathIsDir, $_GET, $varsOut, false, !(isset($settCache[ 'enable' ])?$settCache[ 'enable' ]:null) ) )
			return;

		if( Gen::GetArrField( $settContPr, array( 'enable' ), false ) && lfjikztqjqji( $siteId, $tmCur, false ) )
			return;

	}
	else
	{
		ApplyContentProcessorForceSett( $sett );
		Plugin::SettSet( $sett, true );
	}

	if( $seraph_accel_g_prepPrms !== null )
		Wp::RemoveFilters( 'init', 'wp_cron' );

	$seraph_accel_g_prepCont = true;

	{
		if( defined( 'EZOIC__PLUGIN_NAME' ) )
			Wp::RemoveFilters( 'shutdown', array( 'Ezoic_Namespace\\Ezoic_Integration_Public', 'ez_buffer_end' ) );

		if( defined( 'HMWP_VERSION' ) )
		{
			$model = Gen::GetArrField( Wp::GetFilters( 'plugins_url', array( 'HMWP_Models_Rewrite', 'plugin_url' ) ), array( 0, 'f', 0 ) );
			if( $model && Gen::DoesFuncExist( 'HMWP_Models_Rewrite::getBuffer' ) )
				add_filter( 'seraph_accel_content', array( $model, 'getBuffer' ) );
			unset( $model );
		}

		if( Gen::DoesFuncExist( '\\WPH::proces_html_buffer' ) )
		{
			add_filter( 'seraph_accel_content_pre',
				function( $buffer )
				{
					global $wph;

					if( !$wph || (isset($wph -> ob_callback_late)?$wph -> ob_callback_late:null) )
						return( $buffer );

					$buffer = $wph -> proces_html_buffer( $buffer );
					$wph -> ob_callback_late = true;
					return( $buffer );
				}
			);
		}
	}

	{

		if( Gen::GetArrField( $settContPr, array( 'img', 'lazy', 'load' ), false, '/' ) )
			add_filter( 'wp_lazy_loading_enabled', function( $default, $tag_name ) { return( ( $tag_name == 'img' || $tag_name == 'picture' ) ? false : $default ); }, 10, 2 );
		if( Gen::GetArrField( $settContPr, array( 'frm', 'lazy', 'enable' ), false, '/' ) )
			add_filter( 'wp_lazy_loading_enabled', function( $default, $tag_name ) { return( ( $tag_name == 'iframe' ) ? false : $default ); }, 10, 2 );
	}

	$settImg = Gen::GetArrField( $settContPr, array( 'img' ), array() );
	$settCdn = Gen::GetArrField( $settContPr, array( 'cdn' ), array() );

	if( Gen::GetArrField( $settImg, array( 'sysFlt' ), false ) && ( Gen::GetArrField( $settImg, array( 'srcAddLm' ), false ) || Gen::GetArrField( $settCdn, array( 'enable' ), false ) ) )
	{

		add_filter( 'wp_get_attachment_url',
			function( $url, $post_id )
			{
				if( !$url )
					return( $url );

				$sett = Plugin::SettGet();
				$settCache = Gen::GetArrField( $sett, array( 'cache' ), array() );
				$settContPr = Gen::GetArrField( $sett, array( 'contPr' ), array() );
				$settImg = Gen::GetArrField( $settContPr, array( 'img' ), array() );
				$settCdn = Gen::GetArrField( $settContPr, array( 'cdn' ), array() );

				_ContentProcessor_TmpCont_SettImg_Adjust( $settImg );

				$ctxProcess = &GetContentProcessCtx( $_SERVER, $sett );

				$url = new ImgSrc( $url );
				Images_ProcessSrc( $ctxProcess, $url, $settCache, $settImg, $settCdn );
				return( $url -> src );
			}
		, 9999, 2 );

	    add_filter( 'wp_get_attachment_image_src',
	        function( $image, $attachment_id, $size, $icon )
	        {
	            if( !is_array( $image ) )
					return( $image );

				$src = (isset($image[ 0 ])?$image[ 0 ]:null);
	            if( !$src )
					return( $image );

				$sett = Plugin::SettGet();
				$settCache = Gen::GetArrField( $sett, array( 'cache' ), array() );
				$settContPr = Gen::GetArrField( $sett, array( 'contPr' ), array() );
				$settImg = Gen::GetArrField( $settContPr, array( 'img' ), array() );
				$settCdn = Gen::GetArrField( $settContPr, array( 'cdn' ), array() );

				_ContentProcessor_TmpCont_SettImg_Adjust( $settImg );

				$ctxProcess = &GetContentProcessCtx( $_SERVER, $sett );

				$src = new ImgSrc( $src );
	            if( Images_ProcessSrc( $ctxProcess, $src, $settCache, $settImg, $settCdn ) )
	                $image[ 0 ] = $src -> src;

	            return( $image );
	        }
	    , 9999, 4 );

	    add_filter( 'wp_calculate_image_srcset',
	        function( $sources, $size_array, $image_src, $image_meta, $attachment_id )
	        {
	            if( !is_array( $sources ) )
	                return( $sources );

				$sett = Plugin::SettGet();
				$settCache = Gen::GetArrField( $sett, array( 'cache' ), array() );
				$settContPr = Gen::GetArrField( $sett, array( 'contPr' ), array() );
				$settImg = Gen::GetArrField( $settContPr, array( 'img' ), array() );
				$settCdn = Gen::GetArrField( $settContPr, array( 'cdn' ), array() );

				_ContentProcessor_TmpCont_SettImg_Adjust( $settImg );

				$ctxProcess = &GetContentProcessCtx( $_SERVER, $sett );

	            foreach( $sources as &$source )
	            {
	                if( !is_array( $source ) )
	                    continue;

					$src = (isset($source[ 'url' ])?$source[ 'url' ]:null);
	                if( !$src )
	                    continue;

					$src = new ImgSrc( $src );
	                if( Images_ProcessSrc( $ctxProcess, $src, $settCache, $settImg, $settCdn ) )
	                    $source[ 'url' ] = $src -> src;
	            }

	            return( $sources );
	        }
	    , 9999, 5 );
	}

	if( function_exists( 'flatsome_text_box'  ) )
	{
		add_action( 'wp_loaded',
			function()
			{
				$dir = dirname( ( string )Gen::GetFuncFile( 'flatsome_text_box' ) );
				if( !$dir )
					return;

				global $shortcode_tags;

				$data = new AnyObj();
				$data -> idxs = array();

				foreach( $shortcode_tags as $tag => $cb )
				{
					if( !is_string( $cb ) )
						continue;

					if( !Gen::StrStartsWith( ( string )Gen::GetFuncFile( $cb ), $dir ) )
						continue;

					$hook = new AnyObj();
					$hook -> data = $data;
					$hook -> cbPrev = $cb;
					$hook -> cb =
						function( $hook, $attrs, $content, $tag )
						{
							$content = call_user_func( $hook -> cbPrev, $attrs, $content, $tag );

							if( preg_match( '@\\sid\\s*=\\s*["\']([\\w\\-]+-)(\\d+)@', $content, $m ) )
							{
								$idx = &$hook -> data -> idxs[ $m[ 1 ] ];

								$id = $m[ 1 ] . 'a' . ( ++$idx );
								$content = str_replace( $m[ 1 ] . $m[ 2 ], $id, $content );

							}

							return( $content );
						}
					;

					$shortcode_tags[ $tag ] = array( $hook, 'cb' );
				}
			}
		);
	}
}

function OnEarlyContentComplete( $buffer, $tmpUpdate = false )
{
	global $seraph_accel_g_prepCont;
	global $seraph_accel_g_prepPrms;
	global $seraph_accel_g_contProcGetSkipStatus;

	if( !$seraph_accel_g_prepCont )
	{
		if( $seraph_accel_g_prepCont === null && $seraph_accel_g_prepPrms !== null )
		{
			$seraph_accel_g_contProcGetSkipStatus = null;
			ContProcGetSkipStatus( $buffer );
			if( !$seraph_accel_g_contProcGetSkipStatus || $seraph_accel_g_contProcGetSkipStatus == 'noHdrOrBody' )
				$seraph_accel_g_contProcGetSkipStatus = 'err:contTermEarly:' . rawurlencode( Gen::GetCallStack() );
		}

		return( $buffer );
	}

	if( !IsHtml( $buffer ) )
	{

		return( $buffer );
	}

	$skipStatus = ContProcGetSkipStatus( $buffer );
	if( $skipStatus )
		return( $buffer );

	$buffer = apply_filters( 'seraph_accel_content_pre', $buffer );

	global $seraph_accel_g_dataPath;
	global $seraph_accel_g_prepContIsTmp;
	global $seraph_accel_g_prepOrigContHashPrev;
	global $seraph_accel_g_prepOrigContHash;

	$sett = Plugin::SettGet();
	if( is_multisite() )
	{
		$settCacheGlobal = Gen::GetArrField( Plugin::SettGetGlobal(), array( 'cache' ), array() );
		foreach( array( array( 'cache', 'procWorkInt' ), array( 'cache', 'procPauseInt' ) ) as $fldPath )
			Gen::SetArrField( $sett, $fldPath, Gen::GetArrField( $settCacheGlobal, $fldPath ) );
		unset( $fldPath, $settCacheGlobal );
	}

	$settCache = Gen::GetArrField( $sett, array( 'cache' ), array() );
	$settContPr = Gen::GetArrField( $sett, array( 'contPr' ), array() );

	{
		$dataForChecksum = $buffer;
		foreach( GetCurHdrsToStoreInCache( $settCache ) as $hdr )
			$dataForChecksum .= $hdr;

		$seraph_accel_g_prepOrigContHash = md5( $dataForChecksum, true );
		if( $seraph_accel_g_prepOrigContHash === $seraph_accel_g_prepOrigContHashPrev && !( $seraph_accel_g_prepPrms !== null && isset( $seraph_accel_g_prepPrms[ 'lrn' ] ) ) )
		{
			$seraph_accel_g_contProcGetSkipStatus = 'notChanged';
			return( $buffer );
		}

		unset( $dataForChecksum );
	}

	if( $seraph_accel_g_prepContIsTmp )
		return( $buffer );

	if( $tmpUpdate )
	{
		if( $seraph_accel_g_prepPrms !== null && (isset($seraph_accel_g_prepPrms[ 'lazyInvTmp' ])?$seraph_accel_g_prepPrms[ 'lazyInvTmp' ]:null) )
		{
			$lock = new Lock( 'dl', GetCacheDir() );
			CacheDscUpdate( $lock, $settCache, $buffer, null, null, $seraph_accel_g_dataPath, 'u' );
			unset( $lock );

			if( Gen::GetArrField( $settCache, array( 'srvClr' ), false ) && function_exists( 'seraph_accel\\CacheExt_Clear' ) )
				CacheExt_Clear( GetCurRequestUrl() );
		}
	}

	{
		$memLim = Gen::GetArrField( $settCache, array( 'procMemLim' ), 0 );

		$memLimCur = wp_convert_hr_to_bytes( @ini_get( 'memory_limit' ) ) / 1024 / 1024;

		if( $memLimCur < $memLim )
		{

			@ini_set( 'memory_limit', ( string )$memLim . 'M' );

		}

		unset( $memLim );
		unset( $memLimCur );
	}

	$ctxProcess = &GetContentProcessCtx( $_SERVER, $sett );

	if( (isset($settCache[ 'enable' ])?$settCache[ 'enable' ]:null) && Gen::GetArrField( $settCache, array( 'chunks', 'enable' ), false ) )
		$ctxProcess[ 'chunksEnabled' ] = true;

	$skipStatus = null;

	$encPrev = ContentParseStrIntEncodingCorrect();
	$buffer = ContentProcess( $ctxProcess, $sett, $settCache, $settContPr, $buffer, $skipStatus );
	ContentParseStrIntEncodingRestore( $encPrev );

	if( $skipStatus )
	{
		$seraph_accel_g_contProcGetSkipStatus = $skipStatus;
		if( Gen::LastErrDsc_Is() )
			$seraph_accel_g_contProcGetSkipStatus .= ':' . rawurlencode( Gen::LastErrDsc_Get() );
	}
	else
		$skipStatus = 'ok';

	if( (isset($sett[ 'hdrTrace' ])?$sett[ 'hdrTrace' ]:null) )
	{
		if( headers_sent() )
			$buffer .= Ui::Comment( 'Seraphinite Accelerator/2.19.7; status=' . $skipStatus );
		else
			header( 'X-Seraph-Accel-Content: 2.19.7; status=' . $skipStatus );
	}

	$buffer = apply_filters( 'seraph_accel_content', $buffer );
	return( $buffer );
}

function ContentProcess_GetCurRelatedUri( $ctxProcess, $args )
{
	return( add_query_arg( $args, $ctxProcess[ 'ndHeadBase' ] ? (isset($ctxProcess[ 'serverArgs' ][ 'REQUEST_URI' ])?$ctxProcess[ 'serverArgs' ][ 'REQUEST_URI' ]:'') : ( '?' . (isset($ctxProcess[ 'serverArgs' ][ 'QUERY_STRING' ])?$ctxProcess[ 'serverArgs' ][ 'QUERY_STRING' ]:null) ) ) );
}

function ContentProcess_GetGetPartUri( $ctxProcess, $id )
{
	return( ContentProcess_GetCurRelatedUri( $ctxProcess, array( 'seraph_accel_gp' => ( string )Gen::GetCurRequestTime( $ctxProcess[ 'serverArgs' ] ) . '_' . str_replace( '.', '_', $id ) ) ) );
}

function ContentProcess( &$ctxProcess, $sett, $settCache, $settContPr, $buffer, &$skipStatus )
{
	@set_time_limit( Gen::GetArrField( $settCache, array( 'procTmLim' ), 570 ) );

	global $seraph_accel_g_prepPrms;
	global $seraph_accel_g_viewPath;
	global $seraph_accel_g_prepContIsUserCtx;
	global $seraph_accel_g_prepLearnId;

	$stage = 'parse'; if( $seraph_accel_g_prepPrms && !( $resUpd = ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'stage' => $stage ) ) ) ) { $skipStatus = ( $resUpd === null ) ? 'aborted' : 'err:internal'; return( $buffer ); }

	$norm = Gen::GetArrField( $settContPr, array( 'normalize' ), 0 );
	$doc = GetHtmlDoc( $buffer, $norm, Gen::GetArrField( $settContPr, array( 'min' ), false ), Gen::GetArrField( $settContPr, array( 'cln', 'cmts' ), false ) ? Gen::GetArrField( $settContPr, array( 'cln', 'cmtsExcl' ), array() ) : true );

	if( !$doc )
	{
		$skipStatus = 'err:' . $stage;
		return( $buffer );
	}

	if( ContentProcess_IsAborted( $settCache ) ) { $skipStatus = 'aborted'; return( $buffer ); }

	$ctxProcess[ 'ndHtml' ] = HtmlNd::FindByTag( $doc, 'html', false );
	$ctxProcess[ 'ndHead' ] = HtmlNd::FindByTag( $ctxProcess[ 'ndHtml' ], 'head', false );
	$ctxProcess[ 'ndHeadBase' ] = HtmlNd::FindByTag( $ctxProcess[ 'ndHead' ], 'base', false );
	$ctxProcess[ 'ndBody' ] = HtmlNd::FindByTag( $ctxProcess[ 'ndHtml' ], 'body', false );

	if( !$ctxProcess[ 'ndHead' ] || !$ctxProcess[ 'ndBody' ] )
	{
		$skipStatus = 'err:noHdrOrBody';
		return( $buffer );
	}

	$ctxProcess[ 'isAMP' ] = $ctxProcess[ 'ndHtml' ] -> hasAttribute( 'amp' );

	if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
	{
		$item = $doc -> createElement( 'script' );
		$item -> setAttribute( 'type', 'text/javascript' );
		$item -> setAttribute( 'id', 'seraph-accel-testLoad' );
		$item -> nodeValue = htmlspecialchars( '
			(function()
			{
				var callsCheck = {};

				function cr( k, fromFunc )
				{
					console.log( "seraph_accel: \\"" + k + "\\" just triggered" + ( fromFunc ? " from \\"" + fromFunc + "\\"" : "" ) );
					if( !callsCheck[ k ] )
						callsCheck[ k ] = { n: 0 };
					return( callsCheck[ k ] );
				}

				document.addEventListener( "DOMContentLoaded",
					function( e )
					{
						cr( "document.DOMContentLoaded" ).n++;
					}
				);
				window.addEventListener( "DOMContentLoaded",
					function( e )
					{
						cr( "window.DOMContentLoaded" ).n++;
					}
				);

				window.addEventListener( "load",
					function( e )
					{
						cr( "window.load" ).n++;
					}
				);

				/*window.onload =
					function( e )
					{
						cr( "window.onload", arguments.callee.caller.name ).n++;
					}
				;

				window.onload =
					function( e )
					{
						cr( "window.onload-2", arguments.callee.caller.name ).n++;
					}
				;*/

				jQuery(
					function()
					{
						cr( "jQuery( func... )" ).n++;
					}
				);

				if( parseInt( jQuery.fn.jquery.split( "." )[ 0 ], 10 ) < 3 )
					jQuery( window ).load(
						function( e )
						{
							var o = cr( "jQuery( window ).load()" );

							o.n++;
							if( cr( "jQuery( func... )" ).n < 1 )
								o.err = "too early";
						}
					);
				else
					cr( "jQuery( window ).load()" ).n++;

				var JQCheck = 0;
				jQuery( document ).ready(
					function( $ )
					{
						var o = cr( "jQuery( document ).ready()" );
						o.n++;
						if( !JQCheck )
							o.err = "not async";
					}
				);
				JQCheck = 1;

				jQuery( document ).on( "ready",
					function( $ )
					{
						var o = cr( "jQuery( document ).on( \\"ready\\" )" );
						o.n++;
					}
				);

				setTimeout(
					function()
					{
						var ak =
						[
							"document.DOMContentLoaded",
							"window.DOMContentLoaded",
							"window.load",
							//"window.onload",
							//"window.onload-2",
							"jQuery( func... )",
							"jQuery( window ).load()",
							"jQuery( document ).ready()",
							"jQuery( document ).on( \\"ready\\" )",
						];

						for( var k in ak )
						{
							cr( ak[ k ] );
						}

						for( var k in callsCheck )
						{
							var o = callsCheck[ k ];
							console.log( "seraph_accel: \\"" + k + "\\": " + ( ( o.n == 1 && !o.err ) ? "OK" : ( "ERROR: fired " + o.n + " times" + ( o.err ? ( ", " + o.err ) : "" ) ) ) );
						}
					}
				, 5 * 1000 );
			})();
		' );
		$ctxProcess[ 'ndBody' ] -> appendChild( $item );
	}

	{
		$xpath = null;
		foreach( Gen::GetArrField( $settCache, array( 'exclConts' ), array() ) as $pattern )
		{
			if( !$xpath )
				$xpath = new \DOMXPath( $doc );

			if( !HtmlNd::FirstOfChildren( @$xpath -> query( $pattern, $doc ) ) )
				continue;

			$skipStatus = 'exclConts:' . $pattern;
			return( $buffer );
		}
		unset( $xpath );
	}

	{
		$xpath = null;
		foreach( Gen::GetArrField( $settContPr, array( 'cln', 'items' ), array() ) as $pattern )
		{
			if( !$xpath )
				$xpath = new \DOMXPath( $doc );

			foreach( HtmlNd::ChildrenAsArr( @$xpath -> query( $pattern, $doc ) ) as $item )
				$item -> parentNode -> removeChild( $item );
		}
		unset( $xpath );
	}

	$viewId = 'cmn';
	if( $viewsDeviceGrp = GetCacheViewDeviceGrp( $settCache, $ctxProcess[ 'userAgent' ] ) )
		$viewId = (isset($viewsDeviceGrp[ 'id' ])?$viewsDeviceGrp[ 'id' ]:null);

	$contGrpRes = isset( $_REQUEST[ 'seraph_accel_proc' ] ) ? array() : ContGrpsGet( $contGrpResPagePath, $ctxProcess, Gen::GetArrField( $settContPr, array( 'grps' ), array() ), $doc, $viewId );

	if( $seraph_accel_g_prepPrms !== null && isset( $seraph_accel_g_prepPrms[ 'lrn' ] ) && !isset( $contGrpRes[ 2 ] ) )
	{
		$skipStatus = 'grpLrnOff';
		return( $buffer );
	}

	if( isset( $contGrpRes[ 1 ] ) )
	{
		$contGrp = $contGrpRes[ 1 ][ 0 ];

		if( !Gen::GetArrField( $contGrp, array( 'contPr', 'enable' ), false ) )
		{

			return( $buffer );
		}

		if( Gen::GetArrField( $contGrp, array( 'contPr', 'cssOvr' ), false ) )
			Gen::ArrSet( $settContPr[ 'css' ], Gen::GetArrField( $contGrp, array( 'contPr', 'css' ), array() ) );

		if( Gen::GetArrField( $contGrp, array( 'contPr', 'jsOvr' ), false ) )
			Gen::ArrSet( $settContPr[ 'js' ], Gen::GetArrField( $contGrp, array( 'contPr', 'js' ), array() ) );
	}

	if( $seraph_accel_g_prepContIsUserCtx )
	{
		Gen::SetArrField( $settContPr, array( 'css', 'nonCrit', 'auto' ), false );
		Gen::SetArrField( $settContPr, array( 'js', 'optLoad' ), false );
	}

	$settCss = Gen::GetArrField( $settContPr, array( 'css' ), array() );
	$settJs = Gen::GetArrField( $settContPr, array( 'js' ), array() );
	$settCdn = Gen::GetArrField( $settContPr, array( 'cdn' ), array() );
	$settImg = Gen::GetArrField( $settContPr, array( 'img' ), array() );
	$settFrm = Gen::GetArrField( $settContPr, array( 'frm' ), array() );
	$settCp = Gen::GetArrField( $settContPr, array( 'cp' ), array() );

	$jsNotCritsDelayTimeout = ( Gen::GetArrField( $settJs, array( 'optLoad' ), false ) && Gen::GetArrField( $settJs, array( 'nonCrit', 'timeout', 'enable' ), false ) ) ? Gen::GetArrField( $settJs, array( 'nonCrit', 'timeout', 'v' ), 0 ) : null;

	$bSpecStuffForJsDelayLoad = false;
	if(

		!(isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) && !$ctxProcess[ 'isAMP' ] && $jsNotCritsDelayTimeout )
	{
		$bSpecStuffForJsDelayLoad = true;

		$aBodyClasses = array( 'seraph-accel-js-lzl-ing' );
		if( (isset($settCache[ 'views' ])?$settCache[ 'views' ]:null) )
			$aBodyClasses[] = 'seraph-accel-view-' . $viewId;

		HtmlNd::AddRemoveAttrClass( $ctxProcess[ 'ndBody' ], $aBodyClasses );
		unset( $aBodyClasses );
	}

	if( Gen::LastErrDsc_Is() )
	{
		$skipStatus = 'err:prepare';
		return( $buffer );
	}

	$stage = 'contParts'; if( $seraph_accel_g_prepPrms && !( $resUpd = ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'stage' => $stage ) ) ) ) { $skipStatus = ( $resUpd === null ) ? 'aborted' : 'err:internal'; return( $buffer ); }

	if( !ContParts_Process( $ctxProcess, $doc, $settCache, $settCp, $settImg, $settCdn, $jsNotCritsDelayTimeout ) )
	{
		$skipStatus = 'err:' . $stage;
		return( $buffer );
	}

	{
		$settHash = GetContProcSettHash( $settContPr );

		if( GetContentProcessorForce( $sett ) )
		{
			$contGrpResTmp = ContGrpsGet( $contGrpResPagePath, $ctxProcess, Gen::GetArrField( $settContPr, array( 'grps' ), array() ), $doc, $viewId );
			if( isset( $contGrpResTmp[ 2 ] ) )
			{
				$contGrpTmp = $contGrpResTmp[ 2 ][ 0 ];

				$sklCssSelExcl = (isset($contGrpTmp[ 'sklSrch' ])?$contGrpTmp[ 'sklSrch' ]:null) ? (isset($contGrpTmp[ 'sklCssSelExcl' ])?$contGrpTmp[ 'sklCssSelExcl' ]:null) : null;
				$contSkeletonHash = GetContSkeletonHash( $ctxProcess[ 'ndBody' ], Gen::GetArrField( $contGrpTmp, array( 'sklExcl' ), array() ), $sklCssSelExcl );

				$item = $doc -> createElement( 'script' );
				$item -> setAttribute( 'type', 'text/seraph-accel-learnComparingStructure' );
				$item -> nodeValue = htmlspecialchars( "/* THE SKELETON OF COMPARISON IN LEARNING\n\nHASH: " . $contSkeletonHash . "\n\nDETAILS:\n" . GetContSkeletonHash( $ctxProcess[ 'ndBody' ], Gen::GetArrField( $contGrpTmp, array( 'sklExcl' ), array() ), $sklCssSelExcl, true ) . "*/" );
				$ctxProcess[ 'ndHead' ] -> insertBefore( $item, $ctxProcess[ 'ndHead' ] -> firstChild );
			}
		}

		if( ( $seraph_accel_g_prepPrms !== null  ) && isset( $contGrpRes[ 2 ] ) )
		{
			$contGrp = $contGrpRes[ 2 ][ 0 ];

			if( (isset($contGrp[ 'sklSrch' ])?$contGrp[ 'sklSrch' ]:null) )
			{
				$ctxProcess[ 'docSkeleton' ] = new \DOMDocument();
				$ctxProcess[ 'sklCssSelExcl' ] = (isset($contGrp[ 'sklCssSelExcl' ])?$contGrp[ 'sklCssSelExcl' ]:null);
			}

			$contSkeletonHash = GetContSkeletonHash( $ctxProcess[ 'ndBody' ], Gen::GetArrField( $contGrp, array( 'sklExcl' ), array() ), (isset($ctxProcess[ 'sklCssSelExcl' ])?$ctxProcess[ 'sklCssSelExcl' ]:null), false, (isset($ctxProcess[ 'docSkeleton' ])?$ctxProcess[ 'docSkeleton' ]:null) );

			$ctxProcess[ 'lrnFile' ] = $seraph_accel_g_viewPath . '/l/' . $contGrpRes[ 2 ][ 1 ] . '/' . $contSkeletonHash . '.dat.gz';
			$ctxProcess[ 'lrnDataPath' ] = Gen::GetFileDir( $ctxProcess[ 'dataPath' ] ) . '/l';
			$seraph_accel_g_prepLearnId = $contGrpRes[ 2 ][ 1 ] . '/' . hex2bin( $contSkeletonHash );

			if( isset( $seraph_accel_g_prepPrms[ 'lrn' ] ) )
			{
				$ctxProcess[ 'lrn' ] = $seraph_accel_g_prepPrms[ 'lrn' ];
				$ctxProcess[ 'lrnDsc' ] = array();
			}
			else if( !Learn_Init( $ctxProcess, $settHash ) )
			{
				$lrnStart = false;

				$tmLearnStart = Learn_IsStarted( $ctxProcess );
				if( $tmLearnStart === false )
				{
					$lrnStart = true;
				}
				else if( ( time() - $tmLearnStart > 60 ) && !Queue_IsPriorFirst( $ctxProcess[ 'siteId' ], -480 ) )
				{

					Learn_Clear( $ctxProcess[ 'lrnFile' ] );
					$lrnStart = true;
				}
				else
				{
					$skipStatus = 'lrnNeed';
					return( $buffer );
				}

				if( $lrnStart )
				{
					if( !Learn_Start( $ctxProcess ) )
						$skipStatus = 'err:writeLrnPending';
					else
						$skipStatus = 'lrnNeed:' . substr( $ctxProcess[ 'lrnFile' ], strlen( GetCacheDir() ) );

					return( $buffer );
				}
			}

		}

		unset( $contGrpRes );
	}

	$stage = 'images'; if( $seraph_accel_g_prepPrms && !( $resUpd = ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'stage' => $stage ) ) ) ) { $skipStatus = ( $resUpd === null ) ? 'aborted' : 'err:internal'; return( $buffer ); }

	if( !Images_Process( $ctxProcess, $doc, $settCache, $settImg, $settCdn ) )
	{
		$skipStatus = 'err:' . $stage;
		return( $buffer );
	}

	$stage = 'frames'; if( $seraph_accel_g_prepPrms && !( $resUpd = ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'stage' => $stage ) ) ) ) { $skipStatus = ( $resUpd === null ) ? 'aborted' : 'err:internal'; return( $buffer ); }

	if( !Frames_Process( $ctxProcess, $doc, $settCache, $settFrm, $settImg, $settCdn, $settJs ) )
	{
		$skipStatus = 'err:' . $stage;
		return( $buffer );
	}

	{
		$itemhuddqr = HtmlNd::Parse(
			Ui::Tag( 'a',
				Ui::TagOpen( 'img', array( 'src' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAMAAAAM7l6QAAAABGdBTUEAALGPC/xhBQAAAAFzUkdCAK7OHOkAAAAJcEhZcwAAFxIAABcSAWef0lIAAABOUExURUdwTAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAANyQSi4AAAAZdFJOUwAU7Y9RHUj2hSgEu6fGceGYMw17PLDWXGe4ORhvAAAAz0lEQVQoz42S2RaDIAxEQUBkEXCX///RYtVY28TTPHJhEmbC2J+lFuHmGNtpqO0PlC5fFYW5wUWXQ/1xIScOkDvtpel7NYYWeCUPOjpxNes8XBj2g+beSEKTZlOuv6c0FXDLFPJF6G9RC+pTvMctmgpsFQuBcDDnQlzmhMONYrLKho7A7x/Da3M5kuFto5HS69s3QTzdoyWkw+FYh0FzJpowOoDbCOTXMowInoF6hMr8JM0E4BqN4Um6LN1BNb4jLD1Iv+WL2bMkY7RrEve3L/wzFnTO5UlaAAAAAElFTkSuQmCC', 'alt' => Wp::GetLocString( 'Seraphinite Accelerator', null, 'seraphinite-accelerator' ), 'style' => array_map( $ctxProcess[ 'isAMP' ] ? function( $v ) { return( $v ); } : function( $v ) { return( '' . $v . '!important' ); }, array( 'display' => 'inline-block', 'vertical-align' => 'top', 'position' => 'absolute' ) ) ) ) .
				Ui::Tag( 'span', sprintf( __( 'BannerText_%s', 'seraphinite-accelerator' ), Wp::GetLocString( 'Seraphinite Accelerator', null, 'seraphinite-accelerator' ) ) . Ui::TagOpen( 'br' ) . Ui::Tag( 'span', Wp::GetLocString( 'Turns on site high speed to be attractive for people and search engines.', null, 'seraphinite-accelerator' ), array( 'style' => array_map( $ctxProcess[ 'isAMP' ] ? function( $v ) { return( $v ); } : function( $v ) { return( '' . $v . '!important' ); }, array( 'font-size' => '0.7em' ) ) ) ), array( 'style' => array_map( $ctxProcess[ 'isAMP' ] ? function( $v ) { return( $v ); } : function( $v ) { return( '' . $v . '!important' ); }, array( 'display' => 'inline-block', 'text-align' => 'left', 'vertical-align' => 'top', 'font-size' => '16px', 'padding-left' => '36px' ) ) ) )
			, array( 'href' => Plugin::RmtCfgFld_GetLoc( PluginRmtCfg::Get(), 'Links.FrontendBannerUrl' ), 'target' => '_blank', 'style' => array_map( $ctxProcess[ 'isAMP' ] ? function( $v ) { return( $v ); } : function( $v ) { return( '' . $v . '!important' ); }, array( 'display' => 'block', 'clear' => 'both', 'text-align' => 'center', 'position' => 'relative', 'padding' => '0.5em', 'background-color' => 'transparent', 'color' => '#000', 'line-height' => 1 ) ) ) ) );
		if( $itemhuddqr && $itemhuddqr -> firstChild )
			if( $item = $doc -> importNode( $itemhuddqr -> firstChild, true ) )
				$ctxProcess[ 'ndBody' ] -> appendChild( $item );
	}

	$stage = 'styles'; if( $seraph_accel_g_prepPrms && !( $resUpd = ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'stage' => $stage ) ) ) ) { $skipStatus = ( $resUpd === null ) ? 'aborted' : 'err:internal'; return( $buffer ); }

	$lastBodyChild = $ctxProcess[ 'ndBody' ] -> lastChild;

	if( $bSpecStuffForJsDelayLoad )
	{

		$xpath = new \DOMXPath( $doc );

		foreach( array( 'excl' => 'data-lzl-clk-no', 'exclDef' => 'data-lzl-clk-nodef' ) as $settItem => $prop )
		{
			foreach( Gen::GetArrField( $settJs, array( 'clk', $settItem ), array() ) as $e )
			{
				$items = @$xpath -> query( $e, $ctxProcess[ 'ndHtml' ] );
				if( $items )
					foreach( $items as $item )
						$item -> setAttribute( $prop, '1' );
			}
		}

		unset( $xpath );
	}

	if( $bSpecStuffForJsDelayLoad && ( $aCustStyles = Gen::GetArrField( $settCss, array( 'custom' ), array() ) ) )
	{
		$contCustStyles = '';

		foreach( $aCustStyles as $custStyle )
		{
			if( !(isset($custStyle[ 'enable' ])?$custStyle[ 'enable' ]:null) )
				continue;

			if( $contCustStyles )
				$contCustStyles .= "\n\n";

			$descr = (isset($custStyle[ 'descr' ])?$custStyle[ 'descr' ]:null);
			if( $descr )
				$contCustStyles .= "/*" . $descr . "*/\n";

			$contCustStyles .= (isset($custStyle[ 'data' ])?$custStyle[ 'data' ]:null);
		}

		unset( $aCustStyles );

		if( $contCustStyles )
		{
			$item = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$item -> setAttribute( 'type', 'text/css' );
			$item -> setAttribute( 'id', 'seraph-accel-css-custom' );
			HtmlNd::SetValFromContent( $item, $contCustStyles );
			unset( $contCustStyles );

			$ctxProcess[ 'ndHead' ] -> appendChild( $item );
		}
	}

	$ctxProcess[ 'lazyloadStyles' ] = array();

	if( !Styles_Process( $ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $doc ) )
	{
		$skipStatus = 'err:' . $stage;
		return( $buffer );
	}

	$stage = 'scripts'; if( $seraph_accel_g_prepPrms && !( $resUpd = ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'stage' => $stage ) ) ) ) { $skipStatus = ( $resUpd === null ) ? 'aborted' : 'err:internal'; return( $buffer ); }

	if( !Scripts_Process( $ctxProcess, $sett, $settCache, $settContPr, $settJs, $settCdn, $doc ) )
	{
		$skipStatus = 'err:' . $stage;
		return( $buffer );
	}

	if( ContentProcess_IsAborted( $settCache ) ) { $skipStatus = 'aborted'; return( $buffer ); }

	{
		$cssLzlItems = array();
		foreach( $ctxProcess[ 'lazyloadStyles' ] as $lazyloadStyleStatus => $lazyloadStyle )
			$cssLzlItems[] = 'link[rel=\\"stylesheet/lzl' . ( $lazyloadStyleStatus == 'nonCrit' ? '-nc' : '' ) . '\\"]';

		if( $cssLzlItems )
		{
			$item = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$item -> setAttribute( 'type', 'text/javascript' );
			$item -> setAttribute( 'id', 'seraph-accel-css-lzl' );
			$item -> nodeValue = htmlspecialchars( '(function(d,s){d.querySelectorAll(s).forEach(function(i){var iS=i.cloneNode();iS.rel="stylesheet";i.parentNode.replaceChild(iS,i)})})(document,"' . implode( ',', $cssLzlItems ) . '")' );

			$ctxProcess[ 'ndBody' ] -> appendChild( $item );
		}
	}

	$stage = 'lazyCont'; if( $seraph_accel_g_prepPrms && !( $resUpd = ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'stage' => $stage ) ) ) ) { $skipStatus = ( $resUpd === null ) ? 'aborted' : 'err:internal'; return( $buffer ); }

	$lazyCont = false;
	{

		$itemsPathes = Gen::GetArrField( $settContPr, array( 'lazy', 'items' ), array() );
		$isItemsLazyBeforeJsDelayLoad = Gen::GetArrField( $settContPr, array( 'lazy', 'bjs' ), false );

		if( $isItemsLazyBeforeJsDelayLoad && !$jsNotCritsDelayTimeout )
			$itemsPathes = array();

		if( $itemsPathes )
		{
			$xpath = new \DOMXPath( $doc );

			$items = array();
			foreach( $itemsPathes as $itemPath )
			{
				foreach( $xpath -> query( $itemPath, $ctxProcess[ 'ndHtml' ] ) as $item )
				{
					for( $i = 0; $i < count( $items ); $i++ )
					{
						if( HtmlNd::DoesContain( $items[ $i ], $item ) )
							break;

						if( HtmlNd::DoesContain( $item, $items[ $i ] ) )
						{
							array_splice( $items, $i, 1 );
							continue;
						}
					}

					if( $i === count( $items ) )
						$items[] = $item;
				}
			}

			unset( $xpath );

			$idSubPart = 1;

			$itemsGroupSize = 0;
			$itemGroupFirst = $itemGroupLast = null;
			for( $i = 0; $i < count( $items ) + 1; $i++ )
			{
				$item = $i < count( $items ) ? $items[ $i ] : null;

				if( $item && $itemGroupLast && HtmlNd::GetNextTypeSibling( $itemGroupLast ) === $item && $itemsGroupSize < 8192 )
				{
					$itemsGroupSize += HtmlNd::GetOuterSize( $item );
					$itemGroupLast = $item;
					continue;
				}

				if( $itemGroupFirst )
				{
					if( (isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) )
					{
						ContentMarkSeparate( $itemGroupFirst, false, 1 );
						ContentMarkSeparate( $itemGroupLast, false, 2 );
					}
					else
					{
						$itemParentCont = $doc -> createElement( 'div' );
						HtmlNd::AddRemoveAttrClass( $itemParentCont, 'lzl' . ( $isItemsLazyBeforeJsDelayLoad ? ' bjs' : '' ) );
						$itemParentCont -> setAttribute( 'data-lzl-nos', ( string )$idSubPart );
						$itemGroupFirst -> parentNode -> insertBefore( $itemParentCont, $itemGroupFirst );

						ContentMarkSeparate( $itemParentCont, false, 1 );

						{
							$itemNoScript = $doc -> createElement( 'noscript' );
							$itemNoScript -> setAttribute( 'data-lzl-nos', ( string )$idSubPart );
							HtmlNd::InsertAfter( $itemParentCont -> parentNode, $itemNoScript, $itemParentCont );

							$itemNoScript -> appendChild( _ContentMarkSeparate_CreateSepElem( $doc ) );
							for( ;; )
							{
								$itemNext = $itemGroupFirst -> nextSibling;
								$itemNoScript -> appendChild( $itemGroupFirst );

								if( $itemGroupFirst === $itemGroupLast )
									break;
								$itemGroupFirst = $itemNext;
							}
							$itemNoScript -> appendChild( _ContentMarkSeparate_CreateSepElem( $doc ) );

							ContNoScriptItemClear( $itemNoScript );
						}

						ContentMarkSeparate( $itemNoScript, false, 2 );

						$idSubPart++;
						$ctxProcess[ 'lazyload' ] = true;
					}
				}

				$itemGroupFirst = $itemGroupLast = $item;
				$itemsGroupSize = HtmlNd::GetOuterSize( $item );
			}

			if( $items )
				$lazyCont = true;
		}
	}

	$stage = 'final'; if( $seraph_accel_g_prepPrms && !( $resUpd = ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'stage' => $stage ) ) ) ) { $skipStatus = ( $resUpd === null ) ? 'aborted' : 'err:internal'; return( $buffer ); }

	if( (isset($ctxProcess[ 'lazyload' ])?$ctxProcess[ 'lazyload' ]:null) )
	{
		{
			$itemInsertBefore = null;
			foreach( $ctxProcess[ 'ndHead' ] -> childNodes as $item )
			{
				if( $item -> nodeName == 'style' || ( $item -> nodeName == 'link' && strpos( $item -> getAttribute( 'rel' ), 'stylesheet' ) === 0 ) )
				{
					$itemInsertBefore = $item;
					break;
				}
			}

			{
				$item = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$item -> setAttribute( 'type', 'text/css' );
				$item -> nodeValue = htmlspecialchars( '.lzl{display:none!important;}' );

				$itemParentCont = $doc -> createElement( 'noscript' );
				$itemParentCont -> appendChild( $item );

				$ctxProcess[ 'ndHead' ] -> insertBefore( $itemParentCont, $itemInsertBefore );
				$itemInsertBefore = $itemParentCont -> nextSibling;

				ContentMarkSeparate( $itemParentCont );
			}

			{
				$item = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$item -> setAttribute( 'type', 'text/css' );
				$item -> nodeValue = htmlspecialchars( ( Gen::GetArrField( $settImg, array( 'lazy', 'smoothAppear' ), false ) ? 'img.lzl,img.lzl-ing{opacity:0.01;}img.lzl-ed{transition:opacity .25s ease-in-out;}' : '' ) . '.lzl{background-image:none!important;}' . ( $lazyCont ? 'div[data-lzl-nos]{height:10em}' : '' ) );

				$ctxProcess[ 'ndHead' ] -> insertBefore( $item, $itemInsertBefore );
				$itemInsertBefore = $item -> nextSibling;

				ContentMarkSeparate( $item );
			}

			unset( $itemInsertBefore );
		}

		{
			{
				$cont = '';

				$cont .= '(function(d){var a=d.querySelectorAll("noscript[lzl]");for(var i=0;i<a.length;i++){var c=a[i];c.parentNode.removeChild(c)}})(document)';

				if( $lazyCont )
					$cont .=

					";(function(b){function f(a,d,b){var c=d.getAttribute(\"data-lzl-nos\");c&&(a=a.querySelector('noscript[data-lzl-nos=\"'+c+'\"]'))&&(d.outerHTML=a.textContent,a.parentNode.removeChild(a),b&&b.fire(window,\"resize\",{},!1,!0))}function e(a,d,b){if(void 0!==d){if(\"string\"!==typeof d)return;var c=d.indexOf(\"#\");if(-1==c)return;c=d.substr(c+1)}if(void 0===c||!a.querySelector('[id=\"'+c+'\"]')){d=window.lzl_lazySizes;b=a.querySelectorAll(\"div\"+(b?\".bjs\":\"\")+\"[data-lzl-nos]\");for(var e=0;e<b.length&&(f(a,b[e],d),\nvoid 0===c||!a.querySelector('[id=\"'+c+'\"]'));e++);}}window.lzl_lazysizesConfig={beforeUnveil:function(a,b){f(a.ownerDocument,a,b)}};e(b,location.href);b.addEventListener(\"click\",function(a){e(b,a.target.getAttribute(\"href\"))},{capture:!0,passive:!0});b.addEventListener(\"keydown\",function(a){(70==a.keyCode&&(a.ctrlKey||a.metaKey)||191==a.keyCode)&&e(b)},{capture:!0,passive:!0});b.addEventListener(\"seraph_accel_beforeJsDelayLoad\",function(a){e(b,void 0,!0)},{capture:!0,passive:!0})})(document);\n"
					;

				$item = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$item -> setAttribute( 'type', 'text/javascript' );
				$item -> nodeValue = htmlspecialchars( $cont );

				$ctxProcess[ 'ndBody' ] -> appendChild( $item );

				ContentMarkSeparate( $item );
			}

			$cont =
			    @file_get_contents( __DIR__ . '/Cmn/Ext/JS/lazysizes/lazysizes' . $ctxProcess[ 'jsMinSuffix' ] . '.js' ) .
			    @file_get_contents( __DIR__ . '/Cmn/Ext/JS/lazysizes/plugins/unveilhooks/ls.unveilhooks' . $ctxProcess[ 'jsMinSuffix' ] . '.js' ) .
				'';

			$item = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$item -> setAttribute( 'type', 'text/javascript' );
			$item -> setAttribute( 'id', 'seraph-accel-lzl' );

			$item -> setAttribute( 'async', '' );

			if( !adkxsshiujqtfk( $ctxProcess, $settCache, 'js', $cont, $src ) )
			{
				$skipStatus = 'err:lazyLoadScr';
				return( false );
			}
			Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, 'js' );
			Fullness_AdjustUrl( $ctxProcess, $src );
			$item -> setAttribute( 'src', $src );

			$ctxProcess[ 'ndBody' ] -> appendChild( $item );

			ContentMarkSeparate( $item );
		}
	}

	if( (isset($settContPr[ 'earlyPaint' ])?$settContPr[ 'earlyPaint' ]:null) && !(isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) && !$ctxProcess[ 'isAMP' ] )
	{

	    {
	        $item = $doc -> createElement( 'img' );

	        $item -> setAttribute( 'style', Ui::GetStyleAttr( array( 'z-index' => -99999, 'position' => 'fixed', 'top' => 0, 'left' => 0, 'margin' => '1px', 'max-width' => 'none!important', 'max-height' => 'none!important', 'width' => '100vw!important', 'height' => '100vh!important' ) ) );
	        $item -> setAttribute( 'onload', 'var i=this,d=document;function c(e){d.removeEventListener(e.type,c);setTimeout(function(){i.parentNode.removeChild(i)},250)}d.addEventListener("DOMContentLoaded",c)' );
	        $item -> setAttribute( 'src',
	             LazyLoad_SrcSubst( 10, 10 )

	        );
	        $item -> setAttribute( 'alt', '...' );

			HtmlNd::InsertChild( $ctxProcess[ 'ndBody' ], 0, $item );

	    }

	}

	if( ( (isset($settCache[ 'cron' ])?$settCache[ 'cron' ]:null) && CacheDoesCronDelayPageLoad() )  )
	{
		$urlCron = $ctxProcess[ 'siteRootUri' ] . $ctxProcess[ 'wpRootSubPath' ] . 'wp-cron.php';
		if( $ctxProcess[ 'isAMP' ] )
			$urlCron = $ctxProcess[ 'siteDomainUrl' ] . $urlCron;

		$cont = 'setTimeout(function(){var x=new window.XMLHttpRequest();x.open("GET","' . $urlCron . '",true);x.send()},0)';

		if( $ctxProcess[ 'isAMP' ] )
		{
			$itemAmpScriptMjsTpl = null;
			$itemAmpScriptJsTpl = null;
			$itemAmpMetaScriptSrc = null;

			foreach( $ctxProcess[ 'ndHead' ] -> childNodes as $item )
			{
				if( $item -> nodeType != XML_ELEMENT_NODE )
					continue;

				if( $item -> nodeName == 'script' )
				{
					$m = array();
					if( preg_match( '@//cdn\\.ampproject\\.org/v\\d+/([a-z-]+)-(?:[\\d+\\.]+|latest)\\.(m?)js@', $item -> getAttribute( 'src' ), $m, PREG_OFFSET_CAPTURE ) )
					{
						if( $m[ 2 ][ 0 ] === 'm' )
						{
							if( $itemAmpScriptMjsTpl !== false )
								$itemAmpScriptMjsTpl = ( $m[ 1 ][ 0 ] === 'amp-script' ) ? false : array( 'item' => $item, 'm' => $m );
						}
						else
						{
							if( $itemAmpScriptJsTpl !== false )
								$itemAmpScriptJsTpl = ( $m[ 1 ][ 0 ] === 'amp-script' ) ? false : array( 'item' => $item, 'm' => $m );
						}
					}
				}

				if( !$itemAmpMetaScriptSrc && $item -> nodeName == 'meta' && $item -> getAttribute( 'name' ) == 'amp-script-src' )
					$itemAmpMetaScriptSrc = $item;
			}

			foreach( array( $itemAmpScriptMjsTpl, $itemAmpScriptJsTpl ) as $itemAmpScriptTpl )
			{
				if( !$itemAmpScriptTpl )
					continue;

				$item = $itemAmpScriptTpl[ 'item' ] -> cloneNode( true );
				$item -> setAttribute( 'custom-element', 'amp-script' );
				$src = $item -> getAttribute( 'src' );
				$item -> setAttribute( 'src', substr_replace( $src, 'amp-script', $itemAmpScriptTpl[ 'm' ][ 1 ][ 1 ], strlen( $itemAmpScriptTpl[ 'm' ][ 1 ][ 0 ] ) ) );

				$ctxProcess[ 'ndHead' ] -> appendChild( $item );
			}

			if( !$itemAmpMetaScriptSrc )
			{
				$itemAmpMetaScriptSrc = $doc -> createElement( 'meta' );
				$itemAmpMetaScriptSrc -> setAttribute( 'name', 'amp-script-src' );
				$ctxProcess[ 'ndHead' ] -> appendChild( $itemAmpMetaScriptSrc );
			}

			if( function_exists( 'hash' ) )
				$itemAmpMetaScriptSrc -> setAttribute( 'content', $itemAmpMetaScriptSrc -> getAttribute( 'content' ) . ' sha384-' . str_replace( array( '=', '+', '/' ), array( '', '-', '_' ), base64_encode( hash( 'sha384', $cont, true ) ) ) );

			$item = HtmlNd::Parse( Ui::Tag( 'amp-script', null, array( 'script' => 'seraph-accel-cron', 'layout' => 'fixed', 'height' => '1', 'width' => '1', 'style' => array( 'position' => 'fixed', 'top' => '0', 'left' => '0', 'visibility' => 'hidden' ) ) ) );
			if( $item && $item -> firstChild && ( $item = $doc -> importNode( $item -> firstChild, true ) ) )
				$ctxProcess[ 'ndBody' ] -> appendChild( $item );
		}

		$item = $doc -> createElement( 'script' );
		$item -> setAttribute( 'id', 'seraph-accel-cron' );

		if( $ctxProcess[ 'isAMP' ] )
		{
			$item -> setAttribute( 'type', 'text/plain' );
			$item -> setAttribute( 'target', 'amp-script' );
		}
		else
		{
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$item -> setAttribute( 'type', 'text/javascript' );
		}

		$item -> nodeValue = htmlspecialchars( $cont );
		$ctxProcess[ 'ndBody' ] -> appendChild( $item );
	}

	if( (isset($ctxProcess[ 'chunksEnabled' ])?$ctxProcess[ 'chunksEnabled' ]:null) )
	{
		$settChunks = Gen::GetArrField( $settCache, array( 'chunks' ), array() );

		$xpath = new \DOMXPath( $doc );

		foreach( Gen::GetArrField( $settChunks, array( 'seps' ), array() ) as $sep )
		{
			if( !(isset($sep[ 'enable' ])?$sep[ 'enable' ]:null) )
				continue;

			$xpathQ = (isset($sep[ 'sel' ])?$sep[ 'sel' ]:null);
			foreach( HtmlNd::ChildrenAsArr( $xpath -> query( $xpathQ, $ctxProcess[ 'ndHtml' ] ) ) as $item )
				ContentMarkSeparate( $item, false, $sep[ 'side' ] );
		}
	}

	if( ContentProcess_IsAborted( $settCache ) ) { $skipStatus = 'aborted'; return( $buffer ); }

	global $seraph_accel_g_cacheObjChildren;
	global $seraph_accel_g_cacheObjSubs;
	$seraph_accel_g_cacheObjChildren = $ctxProcess[ 'deps' ];
	$seraph_accel_g_cacheObjSubs = $ctxProcess[ 'subs' ];

	$buffer = HtmlDocDeParse( $doc, $norm );

	if( isset( $ctxProcess[ 'lrn' ] ) && !Learn_Finish( $ctxProcess, $settHash, $ctxProcess[ 'lrn' ] ) )
	{
		$skipStatus = 'err:writeLrnDone';
		return( $buffer );
	}

	return( $buffer );
}

function ContNoScriptItemClear( $itemNoScript )
{

	foreach( HtmlNd::ChildrenAsArr( $itemNoScript -> getElementsByTagName( 'noscript' ) ) as $itemCheck )
	{
		if( $itemNoScript === $itemCheck )
			continue;

		if( $itemCheck -> hasAttribute( 'lzl' ) )
		{
			if( $itemCheck -> previousSibling )
				$itemCheck -> parentNode -> removeChild( $itemCheck -> previousSibling );
			HtmlNd::MoveChildren( $itemCheck -> parentNode, $itemCheck, $itemCheck );
		}

		$itemCheck -> parentNode -> removeChild( $itemCheck );
	}
}

class ContSkeletonHash_MatchAll extends \DOMAttr
{
	public function __construct( $aAttrs, $glob, $aArg )
	{
		parent::__construct( 'd' );

		if( $glob )
			$this -> attr = $aAttrs[ 0 ] -> nodeName;
		else
			$this -> aAttr = $aAttrs;

		$this -> aPattern = $aArg;
		array_shift( $this -> aPattern );
	}
}

function _GetContSkeletonHash_MatchEx( $v, &$aPattern )
{
	if( @is_a( $v, 'DOMNode' ) )
		$v = $v -> nodeValue;

	if( is_string( $v ) )
	{
		foreach( $aPattern as $pattern )
			if( @preg_match( $pattern, $v ) )
				return( true );
	}

	return( false );
}

function _GetContSkeletonHash_Match( $v )
{
	$aPattern = func_get_args();
	array_shift( $aPattern );

	if( is_array( $v ) )
	{
		foreach( $v as $vi )
			if( _GetContSkeletonHash_MatchEx( $vi, $aPattern ) )
				return( true );
	}
	else if( _GetContSkeletonHash_MatchEx( $v, $aPattern ) )
		return( true );

	return( null );
}

function _GetContSkeletonHash_ExclMatchAll( $v )
{
	if( is_array( $v ) )
		return( new ContSkeletonHash_MatchAll( $v, false, func_get_args() ) );

	if( is_string( $v ) )
	{
		$aPattern = func_get_args();
		array_shift( $aPattern );

		foreach( $aPattern as $pattern )
			if( @preg_match( $pattern, $v ) )
				return( true );
	}

	return( null );
}

function _GetContSkeletonHash_ExclMatchAllGlob( $v )
{
	if( is_array( $v ) )
		return( new ContSkeletonHash_MatchAll( $v, true, func_get_args() ) );

	return( null );
}

function ContSkeleton_FltName( $patterns, $s, $spaceAround = false )
{
	foreach( $patterns as $pattern )
	{
		if( $spaceAround && strlen( $s ) )
		{
			if( $s[ 0 ] !== ' ' )
				$s = ' ' . $s;
			if( $s[ strlen( $s ) - 1 ] !== ' ' )
				$s = $s . ' ';
		}

		for( $i = 0; $i < 1000; $i++ )
		{
			if( !@preg_match_all( $pattern, $s, $am, PREG_SET_ORDER | PREG_OFFSET_CAPTURE ) )
				break;

			for( $i = count( $am ); $i > 0; $i-- )
			{
				$m = $am[ $i - 1 ];

				$j = count( $m );
				$jmin = ( $j > 1 ) ? 1 : 0;

				for( ; $j > $jmin; $j-- )
				{
					$mj = $m[ $j - 1 ];
					$s = substr_replace( $s, '', $mj[ 1 ], strlen( $mj[ 0 ] ) );
				}
			}
		}
	}

	return( $s );

}

function _GetContSkeletonHash_GetAttrs( $item, $aExcl )
{
	$contItemTpl = $item -> nodeName;

	if( $item -> attributes )
	{
		foreach( array( 'class', 'id' ) as $attrName )
		{
			$attr = $item -> attributes -> getNamedItem( $attrName );
			if( !$attr || in_array( $attr, $aExcl[ 'a' ], true ) )
				continue;

			$v = $attr -> nodeValue;
			if( $attr -> nodeName == 'class' )
				$v = ' ' . implode( ' ', Ui::ParseClassAttr( $v ) ) . ' ';

			$aPattern = array();
			foreach( $aExcl[ 'as' ] as $exclAttrStr )
				if( isset( $exclAttrStr -> attr ) ? ( $attr -> nodeName == $exclAttrStr -> attr ) : in_array( $attr, $exclAttrStr -> aAttr, true ) )
					$aPattern = array_merge( $aPattern, $exclAttrStr -> aPattern );

			if( $aPattern )
				$v = ContSkeleton_FltName( $aPattern, $v, $attr -> nodeName == 'class' );

			switch( $attr -> nodeName )
			{
			case 'class':
				$v = explode( ' ', $v );
				foreach( $v as $vItem )
				{
					$vItem = trim( $vItem );
					if( strlen( $vItem ) )
						$contItemTpl .= '.' . str_replace( '.', '{{{pt}}}', $vItem );
				}
				break;

			case 'id':
				$v = trim( $v );
				if( strlen( $v ) )
					$contItemTpl .= '#' . $v;
				break;
			}
		}
	}

	$contItemTpl = trim( ContSkeleton_FltName( $aExcl[ 'sel' ], $contItemTpl, true ) );

	$contItemTplTag = _GetContSkeletonHash_GetAttrsParts( $contItemTpl, $contItemTplClasses, $contItemTplId );
	if( $contItemTplClasses )
	{
		$contItemTplClasses = array_unique( explode( '.', substr( $contItemTplClasses, 1 ) ) );
		sort( $contItemTplClasses );
		$contItemTplClasses = implode( '', array_map( function( $v ) { return( '.' . $v ); }, $contItemTplClasses ) );
		$contItemTpl = $contItemTplTag . $contItemTplClasses . $contItemTplId;
	}

	return( $contItemTpl );
}

function _GetContSkeletonHash_GetAttrsParts( $contItemTpl, &$classes, &$id )
{
	$posClasses = strpos( $contItemTpl, '.' );
	$posId = strpos( $contItemTpl, '#' );

	$classes = ( $posClasses !== false ) ? ( $posId !== false ? substr( $contItemTpl, $posClasses, $posId - $posClasses ) : substr( $contItemTpl, $posClasses ) ) : '';
	$id = ( $posId !== false ) ? substr( $contItemTpl, $posId ) : '';
	return( ( $posClasses !== false ) ? substr( $contItemTpl, 0, $posClasses ) : ( $posId !== false ? substr( $contItemTpl, 0, $posId ) : $contItemTpl ) );
}

function _GetContSkeletonHash_Enum( &$aParentUniqueItems, $itemParent, $aExcl )
{
	if( !$itemParent -> childNodes )
		return;

	foreach( $itemParent -> childNodes as $item )
	{
		if( $item -> nodeType != XML_ELEMENT_NODE || in_array( $item -> nodeName, $aExcl[ 'n' ], true ) || in_array( $item, $aExcl[ 'e' ], true ) )
			continue;

		$contItemTpl = _GetContSkeletonHash_GetAttrs( $item, $aExcl );

		$aUniqueItems = array();

			_GetContSkeletonHash_Enum( $aUniqueItems, $item, $aExcl );

		$aParentUniqueItems = array_merge_recursive( $aParentUniqueItems, array( $contItemTpl => $aUniqueItems ) );
	}
}

function _GetContSkeletonHash_EnumUniqueItems( &$contTpl, $docTpl, $itemParentTpl, &$aParentUniqueItems, $test, $level = 0 )
{
	ksort( $aParentUniqueItems );

	foreach( $aParentUniqueItems as $contItemTpl => &$aUniqueItems )
	{
		$itemTpl = null;
		if( $docTpl )
		{
			$itemTpl = $docTpl -> createElement( _GetContSkeletonHash_GetAttrsParts( $contItemTpl, $contItemTplClasses, $contItemTplId ) );
			if( $contItemTplClasses )
				$itemTpl -> setAttribute( 'class', str_replace( '{{{pt}}}', '.', str_replace( '.', ' ', $contItemTplClasses ) ) . ' ' );
			if( $contItemTplId )
				$itemTpl -> setAttribute( 'id', substr( $contItemTplId, 1 ) );
			$itemParentTpl -> appendChild( $itemTpl );
		}

		if( $level )
			$contTpl .= $test ? str_repeat( "\t", $level ) : ( string )$level;
		$contTpl .= $contItemTpl;
		if( $test )
			$contTpl .= "\n";

		_GetContSkeletonHash_EnumUniqueItems( $contTpl, $docTpl, $itemTpl, $aUniqueItems, $test, $level + 1 );
	}
}

function GetContSkeletonHash( $ndBody, $excls, $exclsCssSel, $test = false, $docTpl = null )
{
	$aExcl = array( 'n' => array(), 'e' => array(), 'a' => array(), 'as' => array(), 'sel' => is_array( $exclsCssSel ) ? $exclsCssSel : array() );
	{
		$xpath = new \DOMXPath( $ndBody -> ownerDocument );
		$xpath -> registerNamespace( 'php', 'http://php.net/xpath' );
		$xpath -> registerPhpFunctions( array( 'seraph_accel\\_GetContSkeletonHash_Match', 'seraph_accel\\_GetContSkeletonHash_ExclMatchAll', 'seraph_accel\\_GetContSkeletonHash_ExclMatchAllGlob' ) );

		foreach( $excls as $exclItemPath )
		{
			if( @preg_match( '@^\\.//([\\w\\-]+)$@', $exclItemPath, $m ) )
			{
				$aExcl[ 'n' ][] = $m[ 1 ];
				continue;
			}

			$exclItemPath = preg_replace( '@matchAll\\(\\s*\\.\\/\\/\\*\\[\\@([\\w]+)\\]/\\@([\\w]+)@', 'php:function("seraph_accel\\_GetContSkeletonHash_ExclMatchAllGlob",.//*[@${1}][1]/@${2}', $exclItemPath );
			$exclItemPath = str_replace( 'matchAll(', 'php:function("seraph_accel\\_GetContSkeletonHash_ExclMatchAll",', $exclItemPath );
			$exclItemPath = str_replace( 'match(', 'php:function("seraph_accel\\_GetContSkeletonHash_Match",', $exclItemPath );

			$items = @$xpath -> query( $exclItemPath, $ndBody -> parentNode -> parentNode );
			if( !$items )
				continue;

			foreach( $items as $item )
			{
				if( is_a( $item, 'seraph_accel\\ContSkeletonHash_MatchAll' ) )
					$aExcl[ 'as' ][] = $item;
				else if( is_a( $item, 'DOMElement' ) )
					$aExcl[ 'e' ][] = $item;
				else if( is_a( $item, 'DOMAttr' ) )
					$aExcl[ 'a' ][] = $item;
			}
		}

		unset( $xpath );
	}

	$aUniqueItems = array();
	_GetContSkeletonHash_Enum( $aUniqueItems, $ndBody -> parentNode -> parentNode, $aExcl );

	$contTpl = '';
	_GetContSkeletonHash_EnumUniqueItems( $contTpl, $docTpl, $docTpl, $aUniqueItems, $test );

	return( $test ? $contTpl : md5( $contTpl ) );
}

function GetContProcSettHash( $settContPr )
{
	$settContPr = Gen::ArrCopy( $settContPr );

	foreach( Gen::GetArrField( $settContPr, array( 'cp' ), array() ) as $k => $item )
		if( !$item )
			unset( $settContPr[ 'cp' ][ $k ] );

	foreach( Gen::GetArrField( $settContPr, array( 'css', 'custom' ), array() ) as $k => $item )
		if( !(isset($item[ 'enable' ])?$item[ 'enable' ]:null) )
			unset( $settContPr[ 'css' ][ 'custom' ][ $k ] );

	foreach( Gen::GetArrField( $settContPr, array( 'cdn', 'items' ), array() ) as $k => $item )
		if( !(isset($item[ 'enable' ])?$item[ 'enable' ]:null) )
			unset( $settContPr[ 'cdn' ][ 'items' ][ $k ] );

	foreach( Gen::GetArrField( $settContPr, array( 'grps', 'items' ), array() ) as $k => $item )
		if( !(isset($item[ 'enable' ])?$item[ 'enable' ]:null) )
			unset( $settContPr[ 'grps' ][ 'items' ][ $k ] );

	return( md5( @json_encode( $settContPr ), true ) );
}

function Learn_Id2File( $id )
{
	$pos = strpos( $id, '/' );
	if( $pos === false )
		return( null );

	$pos += 1;
	return( substr( $id, 0, $pos ) . bin2hex( substr( $id, $pos ) ) . '.dat.gz' );
}

function Learn_ReadDsc( $lrnFile )
{
	return( Tof_GetFileData( Gen::GetFileDir( $lrnFile ), Gen::GetFileName( $lrnFile ), 1, true ) );
}

function Learn_KeepNeededData( &$datasDel, &$lrnsGlobDel, $lrnDsc, $lrnDataPath )
{
	StyleProcessor::keepLrnNeededData( $datasDel, $lrnsGlobDel, $lrnDsc, $lrnDataPath );
}

function Learn_Init( &$ctxProcess, $settHash )
{
	$ctxProcess[ 'lrnDsc' ] = Learn_ReadDsc( $ctxProcess[ 'lrnFile' ] );
	if( !$ctxProcess[ 'lrnDsc' ] )
		return( false );

	if( Gen::GetArrField( $ctxProcess[ 'lrnDsc' ], array( 'sh' ) ) === $settHash )
		return( true );

	unset( $ctxProcess[ 'lrnDsc' ] );
	@unlink( $ctxProcess[ 'lrnFile' ] );

	return( false );
}

function Learn_IsStarted( &$ctxProcess )
{
	return( @filemtime( $ctxProcess[ 'lrnFile' ] . '.p' ) );
}

function Learn_Start( &$ctxProcess )
{
	Gen::MakeDir( Gen::GetFileDir( $ctxProcess[ 'lrnFile' ] ), true );
	return( @file_put_contents( $ctxProcess[ 'lrnFile' ] . '.p', '' ) !== false );
}

function Learn_Finish( &$ctxProcess, $settHash, $lrnFileInitiate = null )
{
	if( !isset( $ctxProcess[ 'lrnDsc' ] ) )
		$ctxProcess[ 'lrnDsc' ] = array();

	$ctxProcess[ 'lrnDsc' ][ 'sh' ] = $settHash;

	$ok = Gen::HrSucc( @Tof_SetFileData( Gen::GetFileDir( $ctxProcess[ 'lrnFile' ] ), Gen::GetFileName( $ctxProcess[ 'lrnFile' ] ), $ctxProcess[ 'lrnDsc' ], 1, false, true ) );
	@unlink( $ctxProcess[ 'lrnFile' ] . '.p' );
	if( $lrnFileInitiate )
		@unlink( GetCacheDir() . '/' . $lrnFileInitiate . '.p' );
	return( $ok );
}

function Learn_Clear( $lrnFile )
{
	@unlink( $lrnFile );
	@unlink( $lrnFile . '.p' );
}

function GetContentProcessCtxEx( $serverArgs, $sett, $siteId, $siteUrl, $siteRootPath, $wpRootSubPath, $cacheDir, $scriptDebug )
{
	$ctx = array(
		'siteDomainUrl' => Net::GetSiteAddrFromUrl( $siteUrl, true ),
		'siteRootUri' => Gen::SetLastSlash( Net::Url2Uri( $siteUrl ), false ),
		'siteRootPath' => Gen::SetLastSlash( $siteRootPath, false ),
		'wpRootSubPath' => $wpRootSubPath . '/',
		'siteId' => $siteId,
		'dataPath' => GetCacheDataDir( $cacheDir . '/s/' . $siteId ),
		'deps' => array(),
		'subs' => array(),
		'subCurIdx' => 0,
		'debug' => (isset($sett[ 'debugInfo' ])?$sett[ 'debugInfo' ]:null),
		'jsMinSuffix' => $scriptDebug ? '' : '.min',
		'userAgent' => strtolower( isset( $_SERVER[ 'SERAPH_ACCEL_ORIG_USER_AGENT' ] ) ? $_SERVER[ 'SERAPH_ACCEL_ORIG_USER_AGENT' ] : (isset($serverArgs[ 'HTTP_USER_AGENT' ])?$serverArgs[ 'HTTP_USER_AGENT' ]:null) ),
	);

	$ctx[ 'compatView' ] = ContProcIsCompatView( Gen::GetArrField( $sett, array( 'cache' ), array() ), $ctx[ 'userAgent' ] );

	CorrectRequestScheme( $serverArgs );

	$ctx[ 'serverArgs' ] = $serverArgs;
	$ctx[ 'requestUriPath' ] = Gen::GetFileDir( (isset($serverArgs[ 'REQUEST_URI' ])?$serverArgs[ 'REQUEST_URI' ]:null) );
	$ctx[ 'host' ] = Gen::GetArrField( Net::UrlParse( $serverArgs[ 'REQUEST_SCHEME' ] . '://' . GetRequestHost( $serverArgs ) ), array( 'host' ) );
	if( !$ctx[ 'host' ] )
		$ctx[ 'host' ] = (isset($serverArgs[ 'SERVER_NAME' ])?$serverArgs[ 'SERVER_NAME' ]:null);

	$settContPr = Gen::GetArrField( $sett, array( 'contPr' ), array() );
	if( Gen::GetArrField( $settContPr, array( 'normUrl' ), false ) )
		$ctx[ 'srcUrlFullness' ] = Gen::GetArrField( $settContPr, array( 'normUrlMode' ), 0 );
	else
		$ctx[ 'srcUrlFullness' ] = 0;

	return( $ctx );
}

function &GetContentProcessCtx( $serverArgs, $sett )
{
	global $seraph_accel_g_ctxProcess;

	if( !$seraph_accel_g_ctxProcess )
	{
		$siteRootUrl = Wp::GetSiteRootUrl();
		$siteWpRootSubPath = trim( substr( rtrim( Wp::GetSiteWpRootUrl(), '/' ), strlen( rtrim( $siteRootUrl, '/' ) ) ), '/' );
		$siteRootPath = ABSPATH;
		if( $siteWpRootSubPath )
			$siteRootPath = substr( rtrim( $siteRootPath, '\\/' ), 0, - strlen( $siteWpRootSubPath ) );

		$seraph_accel_g_ctxProcess = GetContentProcessCtxEx( $serverArgs, $sett, GetSiteId(), $siteRootUrl, $siteRootPath, $siteWpRootSubPath, GetCacheDir(), defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
	}

	return( $seraph_accel_g_ctxProcess );
}

function ContUpdateItemIntegrity( $item, $cont )
{
	if( $cont === false )
		return;

	$integrity = trim( $item -> getAttribute( 'integrity' ) );
	if( !$integrity )
		return;

	$algo = strpos( $integrity, '-' );
	if( $algo === false )
		return;
	$algo = substr( $integrity, 0, $algo );

	$hashNew = function_exists( 'hash' ) ? hash( $algo, $cont, true ) : null;
	if( $hashNew )
		$item -> setAttribute( 'integrity', $algo . '-' . base64_encode( $hashNew ) );
	else
		$item -> removeAttribute( 'integrity' );
}

function GetSrcAttrInfo( $ctxProcess, $requestDomainUrl, $requestUriPath, &$src )
{
	$src = trim( $src );

	if( Ui::IsSrcAttrData( $src ) )
		return( array( 'url' => $src, 'srcWoArgs' => $src, 'args' => array() ) );

	$urlComps = Net::UrlParse( $src, Net::URLPARSE_F_PRESERVEEMPTIES | Net::URLPARSE_F_PATH_FIXFIRSTSLASH );
	if( !$urlComps )
		return( array( 'url' => $src, 'srcWoArgs' => $src, 'args' => array() ) );

	$args = Net::UrlParseQuery( (isset($urlComps[ 'query' ])?$urlComps[ 'query' ]:null) );

	$serverArgs = $ctxProcess[ 'serverArgs' ];

	if( isset( $urlComps[ 'host' ] ) )
	{
		if( isset( $urlComps[ 'scheme' ] ) )
		{
			$srcUrlFullness = 4;
			if( $urlComps[ 'scheme' ] != (isset($serverArgs[ 'REQUEST_SCHEME' ])?$serverArgs[ 'REQUEST_SCHEME' ]:null) && (isset($serverArgs[ 'REQUEST_SCHEME' ])?$serverArgs[ 'REQUEST_SCHEME' ]:null) == 'https' )
				$urlComps[ 'scheme' ] = (isset($serverArgs[ 'REQUEST_SCHEME' ])?$serverArgs[ 'REQUEST_SCHEME' ]:null);
		}
		else
		{
			$srcUrlFullness = 3;
			$urlComps[ 'scheme' ] = (isset($serverArgs[ 'REQUEST_SCHEME' ])?$serverArgs[ 'REQUEST_SCHEME' ]:null);
		}
	}
	else
	{
		$srcUrlFullness = 2;

		$requestDomainUrlComps = $requestDomainUrl ? Net::UrlParse( $requestDomainUrl ) : null;
		if( !$requestDomainUrlComps )
		{
			$requestDomainUrlComps = array( 'scheme' => (isset($serverArgs[ 'REQUEST_SCHEME' ])?$serverArgs[ 'REQUEST_SCHEME' ]:null), 'host' => $ctxProcess[ 'host' ] );
			if( (isset($serverArgs[ 'SERVER_PORT' ])?$serverArgs[ 'SERVER_PORT' ]:null) != 80 && (isset($serverArgs[ 'SERVER_PORT' ])?$serverArgs[ 'SERVER_PORT' ]:null) != 443 )
				$requestDomainUrlComps[ 'port' ] = (isset($serverArgs[ 'SERVER_PORT' ])?$serverArgs[ 'SERVER_PORT' ]:null);
		}

		$urlComps[ 'scheme' ] = (isset($requestDomainUrlComps[ 'scheme' ])?$requestDomainUrlComps[ 'scheme' ]:null);
		$urlComps[ 'host' ] = (isset($requestDomainUrlComps[ 'host' ])?$requestDomainUrlComps[ 'host' ]:null);
		$urlComps[ 'port' ] = (isset($requestDomainUrlComps[ 'port' ])?$requestDomainUrlComps[ 'port' ]:null);

		unset( $requestDomainUrlComps );

		if( (isset($urlComps[ 'path' ][ 0 ])?$urlComps[ 'path' ][ 0 ]:null) !== '/' )
		{
			if( $requestUriPath === null )
				$requestUriPath = $ctxProcess[ 'requestUriPath' ];
			$urlComps[ 'path' ] = $requestUriPath . '/' . $urlComps[ 'path' ];
		}
	}

	if( $urlComps[ 'host' ] != $ctxProcess[ 'host' ] || ( isset( $urlComps[ 'port' ] ) && $urlComps[ 'port' ] != (isset($serverArgs[ 'SERVER_PORT' ])?$serverArgs[ 'SERVER_PORT' ]:null) ) )
	{
		$src = Net::UrlDeParse( $urlComps, Net::URLPARSE_F_PRESERVEEMPTIES );
		return( array( 'url' => $src, 'srcWoArgs' => Net::UrlDeParse( $urlComps, Net::URLPARSE_F_PRESERVEEMPTIES, array( PHP_URL_QUERY, PHP_URL_FRAGMENT ) ), 'args' => $args, '#' => (isset($urlComps[ 'fragment' ])?$urlComps[ 'fragment' ]:null), 'ext' => true ) );
	}

	if( stripos( (isset($urlComps[ 'path' ])?$urlComps[ 'path' ]:null) . '/', $ctxProcess[ 'siteRootUri' ] . '/' ) !== 0 )
	{
		$src = Net::UrlDeParse( $urlComps, Net::URLPARSE_F_PRESERVEEMPTIES );
		return( array( 'url' => $src, 'srcWoArgs' => Net::UrlDeParse( $urlComps, Net::URLPARSE_F_PRESERVEEMPTIES, array( PHP_URL_QUERY, PHP_URL_FRAGMENT ) ), 'args' => $args, '#' => (isset($urlComps[ 'fragment' ])?$urlComps[ 'fragment' ]:null), 'ext' => true ) );
	}

	$res = array( 'url' => Net::UrlDeParse( $urlComps, Net::URLPARSE_F_PRESERVEEMPTIES ), 'srcWoArgs' => Net::UrlDeParse( $urlComps, Net::URLPARSE_F_PRESERVEEMPTIES, array( PHP_URL_SCHEME, PHP_URL_USER, PHP_URL_PASS, PHP_URL_HOST, PHP_URL_PORT, PHP_URL_QUERY, PHP_URL_FRAGMENT ) ), 'args' => $args, '#' => (isset($urlComps[ 'fragment' ])?$urlComps[ 'fragment' ]:null), 'srcUrlFullness' => $srcUrlFullness );
	$src = Net::UrlDeParse( $urlComps, Net::URLPARSE_F_PRESERVEEMPTIES, array( PHP_URL_SCHEME, PHP_URL_USER, PHP_URL_PASS, PHP_URL_HOST, PHP_URL_PORT ) );

	$srcRelFile = substr( (isset($urlComps[ 'path' ])?$urlComps[ 'path' ]:null), strlen( $ctxProcess[ 'siteRootUri' ] ) );
	if( $srcRelFile )
		$res[ 'filePath' ] = $ctxProcess[ 'siteRootPath' ] . rawurldecode( $srcRelFile );

	return( $res );
}

function IsUrlInPartsList( $items, $url )
{
	if( !$url || !$items )
		return( false );

	$url = strtolower( $url );

	foreach( $items as $item )
		if( strpos( $url, $item ) !== false )
			return( true );

	return( false );
}

function IsObjInRegexpList( $list, array $scopes, &$detectedPattern = null )
{
	if( !(isset($scopes[ 'src' ])?$scopes[ 'src' ]:null) && !(isset($scopes[ 'id' ])?$scopes[ 'id' ]:null) && !(isset($scopes[ 'body' ])?$scopes[ 'body' ]:null) )
		return( false );

	foreach( $list as $item )
	{
		$isMatched = true;
		foreach( ExprConditionsSet_Parse( $item ) as $itemE )
		{
			$itemScope = array( 'src', 'id', 'body' );
			$posScopeEnd = strpos( $itemE[ 'expr' ], ':' );
			if( $posScopeEnd !== false )
			{
				$posExpBegin = false;
				foreach( array( '/', '~', '@', ';', '%', '`', '#' ) as $expQuote )
				{
					$posExpBegin2 = strpos( $itemE[ 'expr' ], $expQuote );
					if( $posExpBegin2 !== false && ( $posExpBegin === false || $posExpBegin2 < $posExpBegin ) )
						$posExpBegin = $posExpBegin2;
				}

				if( $posExpBegin !== false && $posScopeEnd < $posExpBegin )
				{
					$itemScope = explode( ',', substr( $itemE[ 'expr' ], 0, $posScopeEnd ) );
					$itemE[ 'expr' ] = substr( $itemE[ 'expr' ], $posScopeEnd + 1 );
				}
			}

			$match = false;
			foreach( $itemScope as $scopeCheck )
			{
				if( !(isset($scopes[ $scopeCheck ])?$scopes[ $scopeCheck ]:null) )
					continue;

				$m = array();
				if( ExprConditionsSet_IsItemOpFullSearch( $itemE ) )
				{
					if( !@preg_match_all( $itemE[ 'expr' ], $scopes[ $scopeCheck ], $m, PREG_SET_ORDER ) )
						$m = array( array( '' ) );
				}
				else
				{
					if( !@preg_match( $itemE[ 'expr' ], $scopes[ $scopeCheck ], $m ) )
						$m = array( '' );
					$m = array( $m );
				}

				foreach( $m as $mi )
				{
					if( count( $mi ) > 1 )
						array_shift( $mi );
					$mi = implode( '', $mi );

					if( ExprConditionsSet_ItemOp( $itemE, $mi ) )
					{
						$match = true;
						break;
					}
				}

				if( $match )
					break;
			}

			if( !$match )
			{
				$isMatched = false;
				break;
			}
		}

		if( $isMatched )
		{
			$detectedPattern = $item;
			return( true );
		}
	}

	return( false );
}

function GetObjSrcCritStatus( $settNonCrit, $specs, $srcInfo, $src, $id, $body = null, &$detectedPattern = null )
{
	if( !IsObjSrcNotCrit( $settNonCrit, $srcInfo, $src, $id, $body, $detectedPattern ) )
		return( true );
	if( $specs && IsObjInRegexpList( $specs, array( 'src' => $src, 'id' => $id, 'body' => $body ), $detectedPattern ) )
		return( null );
	return( false );
}

function IsObjSrcNotCrit( $settNonCrit, $srcInfo, $src, $id, $body = null, &$detectedPattern = null )
{
	if( $srcInfo )
	{
		if( !(isset($settNonCrit[ 'ext' ])?$settNonCrit[ 'ext' ]:null) && (isset($srcInfo[ 'ext' ])?$srcInfo[ 'ext' ]:null) )
			return( false );
		if( !(isset($settNonCrit[ 'int' ])?$settNonCrit[ 'int' ]:null) )
			return( false );
	}
	else if( !(isset($settNonCrit[ 'inl' ])?$settNonCrit[ 'inl' ]:null) )
		return( false );

	$inList = IsObjInRegexpList( Gen::GetArrField( $settNonCrit, array( 'items' ), array() ), array( 'src' => $src, 'id' => $id, 'body' => $body ), $detectedPattern );
	return( (isset($settNonCrit[ 'excl' ])?$settNonCrit[ 'excl' ]:null) ? !$inList : $inList );
}

function adkxsshiujqtfk( &$ctxProcess, $settCache, $type, $cont, &$src = null, &$filePath = null )
{
	$fileExt = null;
	if( is_array( $type ) )
	{
		$fileExt = (isset($type[ 1 ])?$type[ 1 ]:null);
		$type = (isset($type[ 0 ])?$type[ 0 ]:null);
	}

	$chunk = CacheCw( $settCache, $ctxProcess[ 'siteRootPath' ], $ctxProcess[ 'dataPath' ], false, $cont, $type, $fileExt );
	if( !$chunk )
		return( false );

	$ctxProcess[ 'deps' ][ $type ][] = $chunk[ 'id' ];
	$src = $ctxProcess[ 'siteRootUri' ] . '/' . $chunk[ 'relFilePath' ];
	$filePath = $ctxProcess[ 'siteRootPath' ] . '/' . $chunk[ 'relFilePath' ];
	return( $chunk[ 'id' ] );
}

function adkxsshitquh( $ctxProcess, $settCache, $id, $type )
{
	$dataPath = $ctxProcess[ 'dataPath' ];

	$dataComprs = Gen::GetArrField( $settCache, array( 'dataCompr' ), array() );
	if( empty( $dataComprs ) )
		$dataComprs[] = '';

	if( $type != 'html' )
	{
		if( $type != 'css' && $type != 'js' )
			$dataComprs = array( '' );
		else if( !in_array( '', $dataComprs, true ) )
			$dataComprs[] = '';
	}

	$oiCf = _GetCcf( $id, '', $dataPath, time(), $type, $dataComprs );
	if( !$oiCf )
		return( null );

	$oiCd = _GetCfc( $oiCf );
	if( $oiCd === false || !CacheCvs( strlen( $oiCd ), GetCacheCos( $id ) ) )
		return( null );

	switch( $oiCf[ 'fmt' ] )
	{
	case '.gz':				$oiCd = @gzdecode( $oiCd ); break;
	case '.deflu':		$oiCd = @gzinflate( $oiCd . "\x03\0" ); break;
	case '.br':				$oiCd = Gen::CallFunc( 'brotli_uncompress', array( $oiCd ), false ); break;
	case '.brua':		$oiCd = Gen::CallFunc( 'brotli_uncompress', array( "\x6b\x00" . $oiCd . "\x03" ), false ); break;
	}

	if( $oiCd === false )
		return( null );

	return( $oiCd );
}

function ContentParseStrIntEncodingCorrect()
{
	if( !function_exists( 'mb_strlen' ) || !( ( int )@ini_get( 'mbstring.func_overload' ) & 2 ) )
		return( null );

	$mbIntEnc = mb_internal_encoding();
	mb_internal_encoding( '8bit' );
	return( $mbIntEnc );
}

function ContentParseStrIntEncodingRestore( $mbIntEnc )
{
	if( $mbIntEnc !== null )
		mb_internal_encoding( $mbIntEnc );
}

function GetContentTestData( $size )
{
	$extra = '';

	$n = $size / 32;
	for( $i = 0; $i < $n; $i++ )
		$extra .= md5( '' . $i );

	return( $extra );
}

function GetContentsRawHead( $data )
{
	$nPos = Gen::StrPosArr( $data, array( '</head>', '</HEAD>' ) );
	if( $nPos === false )
		return( false );
	$data = substr( $data, 0, $nPos );

	$nPos = Gen::StrPosArr( $data, array( '<head>', '<HEAD>' ) );
	if( $nPos === false )
		return( false );
	return( substr( $data, $nPos + 6 ) );
}

function GetContentsMetaProps( $data )
{
	$res = array();

	$data = GetContentsRawHead( $data );
	if( !$data )
		return( $res );

	$doc = new \DOMDocument();
	if( !@$doc -> loadHTML( '<!DOCTYPE html><html><head>' . $data . '</head></html>', LIBXML_NOBLANKS | LIBXML_NONET | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_PARSEHUGE ) )
		return( $res );

	foreach( $doc -> getElementsByTagName( 'meta' ) as $item )
	{
		$k = $item -> getAttribute( 'property' );
		if( !$k )
			$k = $item -> getAttribute( 'name' );

		$v = $item -> getAttribute( 'content' );

		if( $k && $v )
			$res[ $k ] = $v;
	}

	return( $res );
}

function ContGrpsGet( &$path, $ctxProcess, $settGrps, $doc, $viewId )
{
	$res = array();

	$xpath = null;

	$path = CachePathNormalize( substr( ParseContCachePathArgs( $ctxProcess[ 'serverArgs' ], $args ), strlen( $ctxProcess[ 'siteRootUri' ] ) ), $pathIsDir );
	if( $pathIsDir )
		$path .= '/';

	foreach( Gen::GetArrField( $settGrps, array( 'items' ), array() ) as $contGrpId => $contGrp )
	{
		$mode = (isset($contGrp[ 'enable' ])?$contGrp[ 'enable' ]:null);
		if( !( $mode & ( ( isset( $res[ 1 ] ) ? 0 : 1 ) | ( isset( $res[ 2 ] ) ? 0 : 2 ) ) ) )
			continue;

		if( $a = Gen::GetArrField( $contGrp, array( 'views' ), array() ) )
			if( !in_array( $viewId, $a ) )
				continue;

		if( $a = Gen::GetArrField( $contGrp, array( 'urisIncl' ), array() ) )
			if( !CheckPathInUriList( $a, $path ) )
				continue;

		if( $doc && ( $a = Gen::GetArrField( $contGrp, array( 'patterns' ), array() ) ) )
		{
			$found = false;
			foreach( $a as $pattern )
			{
				if( !$xpath )
					$xpath = new \DOMXPath( $doc );

				if( HtmlNd::FirstOfChildren( @$xpath -> query( $pattern, $doc ) ) )
				{
					$found = true;
					break;
				}
			}

			if( !$found )
				continue;
		}

		if( !isset( $res[ 1 ] ) && ( $mode & 1 ) )
			$res[ 1 ] = array( $contGrp, $contGrpId );
		if( !isset( $res[ 2 ] ) && ( $mode & 2 ) )
			$res[ 2 ] = array( $contGrp, $contGrpId );
	}

	return( $res );
}

function ulyjqbuhdyqcetbhkiy( $url )
{
	return( (isset($url[ 0 ])?$url[ 0 ]:null) == '/' && (isset($url[ 1 ])?$url[ 1 ]:null) != '/' );
}

function Cdn_AdjustUrl( $ctxProcess, $settCdn, &$uri, $fileType )
{
	if( !ulyjqbuhdyqcetbhkiy( $uri ) )
		return( false );

	foreach( Gen::GetArrField( $settCdn, array( 'items' ), array() ) as $item )
	{
		$urlCdn = $item[ 'addr' ];
		if( !$item[ 'enable' ] || !$urlCdn )
			continue;

		{
			$types = Gen::GetArrField( $item, array( 'types' ), array() );
			if( $types && !in_array( $fileType, $types ) )
				continue;
		}

		{
			$uris = Gen::GetArrField( $item, array( 'uris' ), array() );
			if( $uris && !IsUrlInPartsList( $uris, $uri ) )
				continue;
		}

		{
			$uris = Gen::GetArrField( $item, array( 'urisExcl' ), array() );
			if( $uris && IsUrlInPartsList( $uris, $uri ) )
				continue;
		}

		$urlCdn = Net::GetUrlWithoutProtoEx( $urlCdn, $proto );
		if( $proto )
		{
			$scheme = (isset($ctxProcess[ 'serverArgs' ][ 'REQUEST_SCHEME' ])?$ctxProcess[ 'serverArgs' ][ 'REQUEST_SCHEME' ]:null);
			if( $proto == 'http' && $scheme == 'https' )
				$proto = $scheme;
			$urlCdn = $proto . '://' . $urlCdn;
		}

		$uri = $urlCdn . $uri;
		return( true );
	}

	return( false );
}

function Fullness_AdjustUrl( $ctxProcess, &$src, $srcUrlFullness = null )
{
	if( !ulyjqbuhdyqcetbhkiy( $src ) )
		return( false );

	$serverArgs = $ctxProcess[ 'serverArgs' ];
	$host = Net::GetUrlWithoutProto( $ctxProcess[ 'siteDomainUrl' ] );

	if( $ctxProcess[ 'srcUrlFullness' ] !== 0 )
		$srcUrlFullness = $ctxProcess[ 'srcUrlFullness' ];
	else if( $srcUrlFullness === null )
		return( false );

	switch( $srcUrlFullness )
	{
	case 4:		$src = (isset($serverArgs[ 'REQUEST_SCHEME' ])?$serverArgs[ 'REQUEST_SCHEME' ]:null) . '://' . $host . $src; return( true );
	case 3:			$src = '//' . $host . $src; return( true );
	}

	if( substr( $src, 0, 3 ) == '//#' )
		$src = substr( $src, 2 );

	return( false );
}

