<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

function _CssExtractImports_GetPosRange( &$aCommentRange, $pos )
{
	foreach( $aCommentRange as &$commentRange )
		if( $commentRange[ 0 ] <= $pos && $pos < $commentRange[ 1 ] )
			return( $commentRange );

	return( false );
}

function _CssExtractImports( &$cont )
{

	$res = array();

	$m = array(); preg_match_all( '#@import\\s+(?:url\\([^()]*\\))?[^@{}]*;\\s*\\n?#', $cont, $m, PREG_OFFSET_CAPTURE );
	if( !$m )
		return( $res );

	$aCommentRange = array();
	for( $offs = 0; ; )
	{
		$posCommentBegin = strpos( $cont, '/*', $offs );
		if( $posCommentBegin === false )
			break;

		$posCommentEnd = strpos( $cont, '*/', $posCommentBegin + 2 );
		if( $posCommentEnd === false )
			$posCommentEnd = strlen( $cont );
		else
			$posCommentEnd += 2;

		$aCommentRange[] = array( $posCommentBegin, $posCommentEnd );
		$offs = $posCommentEnd;
	}
	unset( $posCommentBegin, $posCommentEnd );

	for( $offs = 0; ; )
	{
		$posFirstBlock = strpos( $cont, '{', $offs );
		if( $posFirstBlock === false )
		{
			$posFirstBlock = strlen( $cont );
			break;
		}

		$range = _CssExtractImports_GetPosRange( $aCommentRange, $posFirstBlock );
		if( !$range )
			break;

		$offs = $range[ 1 ];
	}

	for( $i = count( $m[ 0 ] ); $i > 0; $i-- )
	{
		$mi = $m[ 0 ][ $i - 1 ];

		$offs = $mi[ 1 ];
		$len = strlen( $mi[ 0 ] );

		if( $offs > $posFirstBlock )
			continue;

		if( _CssExtractImports_GetPosRange( $aCommentRange, $offs ) )
			continue;

		array_splice( $res, 0, 0, array( substr( $cont, $offs, $len ) ) );
		$cont = substr_replace( $cont, '', $offs, $len );
	}

	return( $res );
}

function _CssInsertImports( &$cont, $imports )
{
	$contHead = implode( '', array_merge( _CssExtractImports( $cont ), $imports ) );
	if( $contHead )
		$cont = $contHead . $cont;

}

function Style_ProcessCont( &$ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $head, &$item, $srcInfo, $src, $id,  $cont, $contAdjusted, $isInlined, $status, $isNoScript, &$contGroups )
{
	$contPrefix = RemoveZeroSpace( $cont, '' );
	if( $contPrefix )
		$contPrefix = '@charset "' . $contPrefix . '";';

	$m = array();
	if( substr( $cont, 0, 8 ) == '@charset' && preg_match( '/^@charset\\s+"([\\w-]+)"\\s*;/i', $cont, $m, PREG_OFFSET_CAPTURE ) )
	{
		if( $m[ 0 ][ 1 ] == 0 )
		{
			$contPrefix = '@charset "' . strtoupper( $m[ 1 ][ 0 ] ) . '";';
			$cont = substr( $cont, strlen( $m[ 0 ][ 0 ] ) );
		}
	}

	$group = null;
	if( !$isNoScript )
	{
		if( $status == 'crit' )
		{
			if( (isset($settCss[ 'group' ])?$settCss[ 'group' ]:null) )
				$group = !!(isset($settCss[ 'groupCombine' ])?$settCss[ 'groupCombine' ]:null);
		}
		else if( $status == 'fonts' )
		{
			if( (isset($settCss[ 'groupFont' ])?$settCss[ 'groupFont' ]:null) )
				$group = !!(isset($settCss[ 'groupFontCombine' ])?$settCss[ 'groupFontCombine' ]:null);
		}
		else
		{
			if( (isset($settCss[ 'groupNonCrit' ])?$settCss[ 'groupNonCrit' ]:null) )
				$group = !!(isset($settCss[ 'groupNonCritCombine' ])?$settCss[ 'groupNonCritCombine' ]:null);
		}
	}

	$contImports = array();
	if( $group )
	{
		$contImports = _CssExtractImports( $cont );

		$media = HtmlNd::GetAttrVal( $item, 'media' );
		if( $media && $media != 'all' && !$item -> hasAttribute( 'onload' ) )
			$cont = '@media ' . $media . "{\r\n" . $cont . "\r\n}";
	}

	if( ( $contAdjusted || $group ) && (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
		$cont = '/* ################################################################################################################################################ */' . "\r\n" . '/* DEBUG: seraph-accel CSS src="' . $src . '" */' . "\r\n\r\n" . $cont;

	if( $group )
	{
		$contGroup = &$contGroups[ $status ];
		_CssInsertImports( $contGroup, $contImports );
		$contGroup .= $cont . "\r\n";

		if( (isset($ctxProcess[ 'chunksEnabled' ])?$ctxProcess[ 'chunksEnabled' ]:null) && Gen::GetArrField( $settCache, array( 'chunks', 'css' ) ) )
			$contGroup .= ContentMarkGetSep();

		if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
			$contGroup .= "\r\n";

		if( $item -> parentNode )
			$item -> parentNode -> removeChild( $item );
	}
	else
	{
		$cont = $contPrefix . $cont;

		if( !Style_ProcessCont_ItemApply( $ctxProcess, $sett, $settCache, $settCss, $settCdn, $head, $item, $srcInfo, $src, $id,  $cont, $contAdjusted, $isInlined, $status, $isNoScript, $group !== null, false ) )
			return( false );
	}

	return( true );
}

function Style_ProcessCont_ItemApply( &$ctxProcess, $sett, $settCache, $settCss, $settCdn, $head, &$item, $srcInfo, $src, $id,  $cont, $contAdjusted, $isInlined, $status, $isNoScript, $repos, $composite = false )
{
	$itemsAfter = array();
	$optLoad = (isset($settCss[ 'optLoad' ])?$settCss[ 'optLoad' ]:null);
	$inlineAsSrc = (isset($settCss[ 'inlAsSrc' ])?$settCss[ 'inlAsSrc' ]:null);

	if( (isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) )
		$inlineAsSrc = false;

	$inline = $isInlined;
	if( $optLoad && !$isNoScript )
	{
		$inline = ( (isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) !== 'cm' ) && !!(isset($settCss[ $status == 'crit' ? 'inlCrit' : 'inlNonCrit' ])?$settCss[ $status == 'crit' ? 'inlCrit' : 'inlNonCrit' ]:null);
		if( HtmlNd::FindUpByTag( $item, 'svg' ) && $isInlined )
			$inline = true;
	}

	$media = $item -> getAttribute( 'media' );

	$cont = str_replace( '::bhkdyqcetujyi::', (

		( $inlineAsSrc && $inline && !$isInlined  ) ) ? $ctxProcess[ 'siteDomainUrl' ] : '', $cont );

	ContUpdateItemIntegrity( $item, $cont );

	if( $inline )
	{

		if( $composite )
		    $cont = str_replace( ContentMarkGetSep(), '', $cont );

		if( !$isInlined )
		{
			if( $inlineAsSrc )
			    $item -> setAttribute( 'href', 'data:text/css,' . _Scripts_EncodeBodyAsSrc( $cont ) );
			else
			{
				$item = HtmlNd::SetTag( $item, 'style', array( 'rel', 'as', 'href' ) );
				HtmlNd::SetValFromContent( $item, $cont );
			}
		}
		else if( $contAdjusted )
			HtmlNd::SetValFromContent( $item, $cont );
	}
	else
	{
		if( $isInlined )
		{
			$item = HtmlNd::SetTag( $item, 'link', true );
			$item -> setAttribute( 'rel', 'stylesheet' );

		}

		if( $contAdjusted || $isInlined )
		{
			if( $composite && !GetContentProcessorForce( $sett ) && (isset($ctxProcess[ 'chunksEnabled' ])?$ctxProcess[ 'chunksEnabled' ]:null) && Gen::GetArrField( $settCache, array( 'chunks', 'css' ) ) )
			{
				$idSub = ( string )( $ctxProcess[ 'subCurIdx' ]++ ) . '.css';
				$ctxProcess[ 'subs' ][ $idSub ] = $cont;
				$src = ContentProcess_GetGetPartUri( $ctxProcess, $idSub );
			}
			else
			{
				if( $composite )
					$cont = str_replace( ContentMarkGetSep(), '', $cont );
				if( !adkxsshiujqtfk( $ctxProcess, $settCache, 'css', $cont, $src ) )
					return( false );
			}
		}

		Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, 'css' );
		Fullness_AdjustUrl( $ctxProcess, $src, $srcInfo ? (isset($srcInfo[ 'srcUrlFullness' ])?$srcInfo[ 'srcUrlFullness' ]:null) : null );

		$item -> nodeValue = '';
		$item -> setAttribute( 'href', $src );

		if( !(isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) && $optLoad && !$isNoScript && $status != 'crit' )
		{

			{
				$itemCopy = $item -> cloneNode( true );
				$itemNoScript = $item -> ownerDocument -> createElement( 'noscript' );
				if( !$itemCopy || !$itemNoScript )
					return( false );

				$itemNoScript -> setAttribute( 'lzl', '' );
				$itemNoScript -> appendChild( $itemCopy );

				$itemsAfter[] = $itemNoScript;
			}

			if( $status == 'fonts' )
			{
				$itemPreLoad = $item -> cloneNode( true );
				$itemPreLoad -> setAttribute( 'rel', 'preload' );
				$itemPreLoad -> setAttribute( 'as', 'style' );
				$itemsAfter[] = $itemPreLoad;
			}

			$item -> setAttribute( 'rel', 'stylesheet/lzl' . ( $status == 'nonCrit' ? '-nc' : '' ) );
			$ctxProcess[ 'lazyloadStyles' ][ $status ] = (isset($settCss[ 'delayNonCritWithJs' ])?$settCss[ 'delayNonCritWithJs' ]:null) ? 'withScripts' : '';

		}
	}

	if( $repos )
	{
		if( $item -> parentNode )
			$item -> parentNode -> removeChild( $item );

		if( $status == 'crit' )
		{
			$head -> appendChild( $item );
		}
		else
		{
			if( $item -> nodeName != 'style' )
				$head -> appendChild( $item );
			else
				$ctxProcess[ 'ndBody' ] -> appendChild( $item );
		}
	}

	$itemInsertAfter = $item;
	foreach( $itemsAfter as $itemAfter )
	{
		HtmlNd::InsertAfter( $item -> parentNode, $itemAfter, $itemInsertAfter );
		$itemInsertAfter = $itemAfter;
	}

	if( (isset($ctxProcess[ 'chunksEnabled' ])?$ctxProcess[ 'chunksEnabled' ]:null) )
	{
		ContentMarkSeparate( $item, false, 1 );
		ContentMarkSeparate( $itemsAfter ? $itemsAfter[ count( $itemsAfter ) - 1 ] : $item, false, 2 );
	}

	return( true );
}

function _Styles_TmpNormalizeElemClasses( $settCache, $ndRoot, $norm = true )
{
	for( $item = null; $item = HtmlNd::GetNextTreeChild( $ndRoot, $item, true );  )
	{
	    if( ContentProcess_IsAborted( $settCache ) ) return;

	    if( $item -> nodeType != XML_ELEMENT_NODE )
	        continue;

	    $attrClass = $item -> getAttribute( 'class' );
	    if( $attrClass )
	        if( $norm )
				$item -> setAttribute( 'class', '| ' . str_replace( array( "\t", "\v", "\f", "\r", "\n" ), array( ' ', ' ', ' ', ' ', ' ' ), $attrClass ) . ' |' );
			else
				$item -> setAttribute( 'class', str_replace( array( '| ', ' |' ), array( '', '' ), $attrClass ) );
	}
}

function _EmbedStyles_Process( &$ctxProcess, $settCache, $settContPr, $settCss, $settImg, $settCdn, $doc, &$aCritFonts, &$aImgSzAlternativesBlocksGlobal = null )
{
	$itemClassIdx = 0;
	for( $item = null; $item = HtmlNd::GetNextTreeChild( $ctxProcess[ 'ndBody' ], $item );  )
	{
		if( ContentProcess_IsAborted( $settCache ) ) return;

		if( $item -> nodeType != XML_ELEMENT_NODE )
			continue;

		$skip = false;
		switch( $item -> nodeName )
		{
		case 'script':
		case 'noscript':
		case 'style':
		case 'img':
		case 'picture':
		case 'source':
			$skip = true;
			break;
		}

		if( $skip )
			continue;

		$style = $item -> getAttribute( 'style' );
		if( !$style )
			continue;

		$ruleSet = StyleProcessor::ParseRuleSet( $style );
		if( !$ruleSet )
			continue;

		$imgSzAlternatives = null;
		if( $aImgSzAlternativesBlocksGlobal !== null && !Images_CheckSzAdaptExcl( $ctxProcess, $doc, $settImg, $item ) )
			$imgSzAlternatives = new ImgSzAlternatives();

		$ctxItems = new AnyObj( array( 'item' => $item ) );

		$r = StyleProcessor::AdjustRuleSet( $ruleSet, $aCritFonts, $ctxItems, $doc, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, null, null, true, $imgSzAlternatives );
		if( $r === false )
			return( false );

		if( $r )
		{
			if( (isset($ctxItems -> isLazyStyleBg)?$ctxItems -> isLazyStyleBg:null) )
				StyleProcessor::AdjustItemLazyBg( $ctxProcess, $settImg, $doc, $item );

			$style = $ruleSet -> render( StyleProcessor::GetRenderFormat() );
			$style = str_replace( '::bhkdyqcetujyi::', '', $style );
			$item -> setAttribute( 'style', $style );

			if( $imgSzAlternatives && $imgSzAlternatives -> a )
			{
				$itemCssClass = 'seraph-accel-bg-' . $itemClassIdx++;
				HtmlNd::AddRemoveAttrClass( $item, array( $itemCssClass ) );

				foreach( $imgSzAlternatives -> a as $imgSzAlternativeScope => $imgSzAlternative )
					Gen::SetArrField( $aImgSzAlternativesBlocksGlobal, array( $imgSzAlternativeScope, '+' ), array_merge( array( 'sels' => '.' . $itemCssClass . ':not(.lzl)' ), $imgSzAlternative ) );
			}
		}
	}

	return( true );
}

function Styles_Process( &$ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $doc )
{
	if( (isset($ctxProcess[ 'isAMP' ])?$ctxProcess[ 'isAMP' ]:null) )
	    return( true );

	$adjustCont = (isset($settCss[ 'optLoad' ])?$settCss[ 'optLoad' ]:null)
		|| (isset($settCss[ 'min' ])?$settCss[ 'min' ]:null)
		|| (isset($settCss[ 'fontOptLoad' ])?$settCss[ 'fontOptLoad' ]:null)
		|| !(isset($settCss[ 'fontCrit' ])?$settCss[ 'fontCrit' ]:null)
		|| ( (isset($settCss[ 'group' ])?$settCss[ 'group' ]:null) && (isset($settCss[ 'groupCombine' ])?$settCss[ 'groupCombine' ]:null) )
		|| ( (isset($settCss[ 'groupNonCrit' ])?$settCss[ 'groupNonCrit' ]:null) && (isset($settCss[ 'groupNonCritCombine' ])?$settCss[ 'groupNonCritCombine' ]:null) )
		|| (isset($settImg[ 'szAdaptBg' ])?$settImg[ 'szAdaptBg' ]:null)
		|| Gen::GetArrField( $settCdn, array( 'enable' ), false );

	$skips = Gen::GetArrField( $settCss, array( 'skips' ), array() );
	if( !( $adjustCont || (isset($settCss[ 'group' ])?$settCss[ 'group' ]:null) || (isset($settCss[ 'groupNonCrit' ])?$settCss[ 'groupNonCrit' ]:null) || (isset($settCss[ 'sepImp' ])?$settCss[ 'sepImp' ]:null) || $skips ) )
		return( true );

	$head = $ctxProcess[ 'ndHead' ];

	_Styles_TmpNormalizeElemClasses( $settCache, $ctxProcess[ 'ndHtml' ], true );

	$aCritFonts = (isset($settCss[ 'fontCritAuto' ])?$settCss[ 'fontCritAuto' ]:null) ? array() : null;

	$aImgSzAlternativesBlocksGlobal = (isset($settImg[ 'szAdaptBg' ])?$settImg[ 'szAdaptBg' ]:null) ? array() : null;
	if( $adjustCont && !_EmbedStyles_Process( $ctxProcess, $settCache, $settContPr, $settCss, $settImg, $settCdn, $doc, $aCritFonts, $aImgSzAlternativesBlocksGlobal ) )
		return( false );

	if( ContentProcess_IsAborted( $settCache ) ) return( true );

	$processor = new StyleProcessor( $doc, $ctxProcess[ 'ndHtml' ], (isset($ctxProcess[ 'docSkeleton' ])?$ctxProcess[ 'docSkeleton' ]:null), (isset($ctxProcess[ 'sklCssSelExcl' ])?$ctxProcess[ 'sklCssSelExcl' ]:null) );

	if( !isset( $ctxProcess[ 'lrn' ] ) && isset( $ctxProcess[ 'lrnDsc' ] ) )
		$processor -> readLrnData( $ctxProcess[ 'lrnDsc' ], $ctxProcess[ 'lrnDataPath' ] );

	$settNonCrit = Gen::GetArrField( $settCss, array( 'nonCrit' ), array() );
	$contGroups = array( 'crit' => '', 'fonts' => '', 'nonCrit' => '' );

	$items = array();
	for( $item = null; $item = HtmlNd::GetNextTreeChild( $doc, $item );  )
	{
		if( $item -> nodeType != XML_ELEMENT_NODE )
			continue;

		switch( $item -> nodeName )
		{
		case 'link':
		case 'style':
			$itemData = array( 'item' => $item );

			$items[] = $itemData;
			break;
		}
	}

	$hrefs = array();

	for( $i = 0; $i < count( $items ); $i++ )
	{
		$itemData = &$items[ $i ];
		$item = $itemData[ 'item' ];
		$cont = (isset($itemData[ 'cont' ])?$itemData[ 'cont' ]:null);

		if( ContentProcess_IsAborted( $settCache ) ) return( true );

		$isInlined = ( $item -> nodeName == 'style' );

		if( !$isInlined )
		{
			$rel = HtmlNd::GetAttrVal( $item, 'rel' );
			if( $cont === null )
				if( !( $rel == 'stylesheet' || ( $rel == 'preload' && HtmlNd::GetAttrVal( $item, 'as' ) == 'style' ) ) )
					continue;
		}

		$type = HtmlNd::GetAttrVal( $item, 'type' );
		if( $cont === null )
		{
			if( $type && $type != 'text/css' )
				continue;

			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			{
				if( !$type )
					$item -> setAttribute( 'type', 'text/css' );
			}
			else if( $type && (isset($settContPr[ 'min' ])?$settContPr[ 'min' ]:null) )
				$item -> removeAttribute( 'type' );
		}
		else
			unset( $itemData[ 'cont' ] );

		$src = null;
		if( !$isInlined )
		{
			$src = HtmlNd::GetAttrVal( $item, 'href' );
			if( !$src )
				continue;
		}

		$id = HtmlNd::GetAttrVal( $item, 'id' );

		$detectedPattern = null;
		if( ( $cont === null ) && IsObjInRegexpList( $skips, array( 'src' => $src, 'id' => $id ), $detectedPattern ) )
		{
			if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
			{
				$item -> setAttribute( 'type', 'o/css-inactive' );
				$item -> setAttribute( 'seraph-accel-debug', 'status=skipped;' . ( $detectedPattern ? ' detectedPattern="' . $detectedPattern . '"' : '' ) );
			}
			else if( $item -> parentNode )
				$item -> parentNode -> removeChild( $item );

			continue;
		}

		$isNoScript = HtmlNd::FindUpByTag( $item, 'noscript' );
		if( $isNoScript && !$isInlined )
			continue;

		$srcInfo = null;
		if( !$isInlined )
		{
			if( $cont === null )
			{
				$itemPrevHref = (isset($hrefs[ $src ])?$hrefs[ $src ]:null);
				if( $itemPrevHref )
				{
					if( $rel == 'stylesheet' && $itemPrevHref -> getAttribute( 'rel' ) != $rel )
					{
						if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
						{
							$itemPrevHref -> setAttribute( 'href', $src );
							$itemPrevHref -> setAttribute( 'as', 'style-inactive' );
							$itemPrevHref -> setAttribute( 'seraph-accel-debug', 'status=skipped; reason=alreadyUsed;' );
						}
						else if( $itemPrevHref -> parentNode )
							$itemPrevHref -> parentNode -> removeChild( $itemPrevHref );
					}
					else
					{
						if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
						{
							if( $rel == 'stylesheet' )
								$item -> setAttribute( 'type', 'o/css-inactive' );
							else
								$item -> setAttribute( 'as', 'style-inactive' );
							$item -> setAttribute( 'seraph-accel-debug', 'status=skipped; reason=alreadyUsed;' );
						}
						else if( $item -> parentNode )
							$item -> parentNode -> removeChild( $item );

						continue;
					}
				}

				$hrefs[ $src ] = $item;
			}

			$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, $src );
		}

		if( $cont === null )
		{
			$cont = false;
			if( !$isInlined )
			{
				if( (isset($srcInfo[ 'filePath' ])?$srcInfo[ 'filePath' ]:null) && Gen::GetFileExt( $srcInfo[ 'filePath' ] ) == 'css' )
					$cont = @file_get_contents( (isset($srcInfo[ 'filePath' ])?$srcInfo[ 'filePath' ]:null) );
				if( $cont === false )
				{
					$cont = GetExtContents( (isset($srcInfo[ 'url' ])?$srcInfo[ 'url' ]:null), $contMimeType );
					if( $cont !== false && !in_array( $contMimeType, array( 'text/css' ) ) )
					{
						$cont = false;
						LastWarnDscs_Add( LocId::Pack( 'CssUrlWrongType_%1$s%2$s', null, array( $srcInfo[ 'url' ], $contMimeType ) ) );
					}
				}
			}
			else
				$cont = $item -> nodeValue;

			if( $cont === false || ( !$cont && $isInlined ) )
			{

				continue;
			}

			if( (isset($settCss[ 'sepImp' ])?$settCss[ 'sepImp' ]:null) )
			{
				$contWoImports = $cont;
				$imports = _CssExtractImports( $contWoImports );
				if( $imports )
				{
					$media = $item -> getAttribute( 'media' );

					foreach( $imports as &$import )
					{
						$import = StyleProcessor::GetFirstImportSimpleAttrs( $ctxProcess, $import, $src );
						if( !$import || ( (isset($import[ 'media' ])?$import[ 'media' ]:null) && (isset($import[ 'media' ])?$import[ 'media' ]:null) != 'all' && $media && $media != 'all' && (isset($import[ 'media' ])?$import[ 'media' ]:null) != $media ) )
						{
							$imports = false;
							break;
						}
					}
					unset( $import );

					if( $imports )
					{
						$j = 0;
						foreach( $imports as $import )
						{

							$itemImp = $doc -> createElement( 'link' );
							HtmlNd::CopyAllAttrs( $item, $itemImp, array( 'id', 'type', 'rel' ) );
							$itemImp -> setAttribute( 'rel', 'stylesheet' );
							if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
								$itemImp -> setAttribute( 'type', 'text/css' );
							if( $id )
								$itemImp -> setAttribute( 'id', $id . '-i' . $j );

							$itemImp -> setAttribute( 'href', $import[ 'url' ] );
							if( (isset($import[ 'media' ])?$import[ 'media' ]:null) && ( !$media || $media == 'all' ) )
								$itemImp -> setAttribute( 'media', $import[ 'media' ] );

							$item -> parentNode -> insertBefore( $itemImp, $item );

							$itemDataImp = array( 'item' => $itemImp );

							array_splice( $items, $i + $j, 0, array( $itemDataImp ) );
							$j++;
						}

						$i--;
						$itemData[ 'cont' ] = $contWoImports;
						unset( $contWoImports );

						continue;
					}
				}

				unset( $contWoImports );
			}
		}

			if( $adjustCont )
			{
				$extract = !(isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) && (isset($settNonCrit[ 'auto' ])?$settNonCrit[ 'auto' ]:null);
				$contsExtracted = $processor -> AdjustCont( $extract, $aCritFonts, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $cont, $isInlined );
				if( $contsExtracted === false )
					return( false );
			}
			else
			{
				$extract = false;
				$contsExtracted = null;
			}

			if( ContentProcess_IsAborted( $settCache ) ) return( true );

		$ps = array();

		if( $extract )
		{
			$contsExtracted[ 'nonCrit' ] = $cont;
			unset( $cont );

			$contExtractedIdDef = 'nonCrit';
			if( $isInlined && ( (isset($settCss[ 'optLoad' ])?$settCss[ 'optLoad' ]:null) && (isset($settCss[ 'inlCrit' ])?$settCss[ 'inlCrit' ]:null) ) && HtmlNd::HasAttrs( $item, array( 'type', 'media' ) ) )
				$contExtractedIdDef = 'crit';

			$itemInsertAfter = null;
			foreach( $contsExtracted as $contExtractedId => $contExtracted )
			{

				if( $contExtractedIdDef == $contExtractedId )
				{
					$itemExtracted = $item;
					$idExtracted = $id;
					$itemInsertAfter = $item;
				}
				else
				{
					if( !$contExtracted )
						continue;

					$itemExtracted = $doc -> createElement( $item -> nodeName );
					if( $itemInsertAfter )
					{
						HtmlNd::InsertAfter( $item -> parentNode, $itemExtracted, $itemInsertAfter );
						$itemInsertAfter = $itemExtracted;
					}
					else
						$item -> parentNode -> insertBefore( $itemExtracted, $item );

					if( $id )
					{
						$idExtracted = $id . '-' . $contExtractedId;
						$itemExtracted -> setAttribute( 'id', $idExtracted );
					}
					else
						$idExtracted = null;

					HtmlNd::CopyAllAttrs( $item, $itemExtracted, array( 'id' ) );
				}

				$ps[] = array( 'item' => $itemExtracted, 'id' => $idExtracted,  'cont' => $contExtracted, 'contAdjusted' => true, 'status' => $contExtractedId );
			}

			unset( $contExtracted );
			unset( $itemInsertAfter );
		}
		else
		{
			$detectedPattern = null;
			$isCrit = GetObjSrcCritStatus( $settNonCrit, null, $srcInfo, $src, $id, $cont, $detectedPattern );
			$ps[] = array( 'item' => $item, 'id' => $id,  'cont' => $cont, 'contAdjusted' => $contsExtracted !== null, 'status' => $isCrit ? 'crit' : 'nonCrit', 'detectedPattern' => $detectedPattern );
		}

		if( $isInlined )
		{
			if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
				$src = 'inline:' . (isset($ctxProcess[ 'serverArgs' ][ 'REQUEST_SCHEME' ])?$ctxProcess[ 'serverArgs' ][ 'REQUEST_SCHEME' ]:null) . '://' . $ctxProcess[ 'host' ] . ':' . (isset($ctxProcess[ 'serverArgs' ][ 'SERVER_PORT' ])?$ctxProcess[ 'serverArgs' ][ 'SERVER_PORT' ]:null) . (isset($ctxProcess[ 'serverArgs' ][ 'REQUEST_URI' ])?$ctxProcess[ 'serverArgs' ][ 'REQUEST_URI' ]:null) . ':' . $item -> getLineNo();
		}

		foreach( $ps as $psi )
		{
			if( (isset($ctxProcess[ 'debug' ])?$ctxProcess[ 'debug' ]:null) )
				$psi[ 'item' ] -> setAttribute( 'seraph-accel-debug', 'status=' . $psi[ 'status' ] . ';' . ( (isset($psi[ 'detectedPattern' ])?$psi[ 'detectedPattern' ]:null) ? ' detectedPattern="' . $psi[ 'detectedPattern' ] . '"' : '' ) );

			if( !Style_ProcessCont( $ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $head, $psi[ 'item' ], $srcInfo, $src, $psi[ 'id' ],  $psi[ 'cont' ], (isset($psi[ 'contAdjusted' ])?$psi[ 'contAdjusted' ]:null), $isInlined, $psi[ 'status' ], $isNoScript, $contGroups ) )
				return( false );
		}
	}

	if( $adjustCont )
		$processor -> ApplyItems( $ctxProcess, $settImg );

	if( $aImgSzAlternativesBlocksGlobal )
	{
		$cssCritDoc = new Sabberworm\CSS\CSSList\Document();
		StyleProcessor::ImgSzAlternativesBlockAddToStyle( $cssCritDoc, $aImgSzAlternativesBlocksGlobal, true );
		$cont = StyleProcessor::RenderData( $cssCritDoc, $settCss );
		unset( $cssCritDoc );

		$item = $doc -> createElement( 'style' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$item -> setAttribute( 'type', 'text/css' );
		HtmlNd::SetValFromContent( $item, $cont );
		$head -> appendChild( $item );

		if( !Style_ProcessCont( $ctxProcess, $sett, $settCache, $settContPr, $settCss, $settImg, $settCdn, $head, $item, null, null, null,  $cont, true, true, 'crit', false, $contGroups ) )
			return( false );

		unset( $cont );
		unset( $item );
	}

	unset( $itemData );
	unset( $hrefs );

	if( ContentProcess_IsAborted( $settCache ) ) return( true );

	foreach( $contGroups as $contGroupId => $contGroup )
	{
		if( !$contGroup )
			continue;

		if( $contGroupId == 'crit' )
		{
			$item = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$item -> setAttribute( 'type', 'text/css' );

			if( !Style_ProcessCont_ItemApply( $ctxProcess, $sett, $settCache, $settCss, $settCdn, $head, $item, null, null, null,  $contGroup, true, true, $contGroupId, false, true, true ) )
				return( false );
		}
		else
		{
			$item = $doc -> createElement( 'link' );
			$item -> setAttribute( 'rel', 'stylesheet' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$item -> setAttribute( 'type', 'text/css' );

			if( !Style_ProcessCont_ItemApply( $ctxProcess, $sett, $settCache, $settCss, $settCdn, $head, $item, null, null, null,  $contGroup, true, false, $contGroupId, false, true, true ) )
				return( false );
		}
	}

	_Styles_TmpNormalizeElemClasses( $settCache, $ctxProcess[ 'ndHtml' ], false );

	if( (isset($settCss[ 'fontPreload' ])?$settCss[ 'fontPreload' ]:null) )
	{
		$itemInsBefore = $head -> firstChild;
		foreach( array_unique( $processor -> aFonts ) as $font )
		{
			$itemFont = $doc -> createElement( 'link' );
			$itemFont -> setAttribute( 'rel', 'preload' );
			$itemFont -> setAttribute( 'as', 'font' );

			$itemFont -> setAttribute( 'crossorigin', '' );
			$itemFont -> setAttribute( 'href', str_replace( '::bhkdyqcetujyi::', '', trim( $font -> getURL() -> getString() ) ) );
			$head -> insertBefore( $itemFont, $itemInsBefore );

			if( (isset($ctxProcess[ 'chunksEnabled' ])?$ctxProcess[ 'chunksEnabled' ]:null) )
				ContentMarkSeparate( $itemFont, false );
		}
	}

	if( isset( $ctxProcess[ 'lrn' ] ) && !$processor -> writeLrnData( $ctxProcess[ 'lrnDsc' ], $ctxProcess[ 'lrnDataPath' ] ) )
		return( false );

	return( true );
}

class StyleProcessor
{
	protected $doc;
	protected $rootElem;
	protected $xpath;
	protected $cnvCssSel2Xpath;

	protected $minifier;

	protected $_aCssSelIsCritCache;
	protected $_xpathCssSelCache;

	public $aFonts;

	function __construct( $doc, $rootElem, $docSkeleton = null, $sklCssSelExcls = null )
	{
		$this -> doc = $doc;
		$this -> rootElem = $rootElem;
		$this -> xpath = new \DOMXPath( $doc );
		$this -> xpathSkeleton = $docSkeleton ? new \DOMXPath( $docSkeleton ) : null;
		$this -> minifier = new tubalmartin\CssMin\Minifier();
		$this -> sklCssSelExcls = is_array( $sklCssSelExcls ) ? $sklCssSelExcls : array();

		$this -> cnvCssSel2Xpath = new Symfony\Component\CssSelector\XPath\Translator();
		$this -> cnvCssSel2Xpath -> registerExtension( new CssToXPathHtmlExtension( $this -> cnvCssSel2Xpath ) );

		$this -> cnvCssSel2Xpath -> registerExtension( new CssToXPathNormalizedAttributeMatchingExtension() );

		$this -> cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\EmptyStringParser() );
		$this -> cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\ElementParser() );
		$this -> cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\ClassParser() );
		$this -> cnvCssSel2Xpath -> registerParserShortcut( new Symfony\Component\CssSelector\Parser\Shortcut\HashParser() );

		$this -> _aCssSelIsCritCache = array();
		$this -> _aAdjustContCache = array();
		$this -> _aXpathSelLazyBgCache = array();
	}

	function __destruct()
	{

	}

	static private function _EscapeNonStdParts( $cont, $escape )
	{
		if( $escape )
		{
			$cont = preg_replace_callback( '@{{(\\w+)}}@',
				function( array $matches )
				{
					return( 'TMPSYM293654_DBLSCOPEOPEN' . $matches[ 1 ] . 'TMPSYM293654_DBLSCOPECLOSE' );
				}
			, $cont );

			$cont = str_replace( '&gt;', 'TMPSYM293654_GT', $cont );

			return( $cont );
		}

		$cont = str_replace( array( 'TMPSYM293654_DBLSCOPEOPEN', 'TMPSYM293654_DBLSCOPECLOSE', 'TMPSYM293654_GT' ), array( '{{', '}}', '&gt;' ), $cont );
		return( $cont );
	}

	function AdjustCont( $extract, &$aCritFonts, &$ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, &$cont, $isInlined )
	{
		if( isset( $ctxProcess[ 'lrnDsc' ] ) )
		{
			$contHash = md5( $cont, true );

			$res = (isset($this -> _aAdjustContCache[ $contHash ])?$this -> _aAdjustContCache[ $contHash ]:null);
			if( is_array( $res ) )
			{
				$ok = true;
				foreach( $res as $contPartId => &$contPart )
				{
					if( $contPart === '' )
						continue;

					$contPart = adkxsshitquh( $ctxProcess, $settCache, $contPart, 'css' );
					if( $contPart === null )
					{
						$ok = false;
						break;
					}
				}

				if( $ok )
				{
					$cont = $res[ 'nonCrit' ];
					unset( $res[ 'nonCrit' ] );
					return( $res );
				}
			}
			else if( $res === false )
			{
				return( null );
			}
			else if( $res === '' )
			{
				$cont = '';
				return( true );
			}
			else if( $res )
			{
				$contPart = adkxsshitquh( $ctxProcess, $settCache, $res, 'css' );
				if( $contPart !== null )
				{
					$cont = $contPart;
					return( true );
				}
			}
		}

		$res = $this -> _AdjustCont( $extract, $aCritFonts, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $cont, $isInlined );
		if( $res === false )
			return( false );

		if( isset( $ctxProcess[ 'lrn' ] ) )
		{
			if( is_array( $res ) )
			{
				$resLrn = array();
				foreach( array_merge( array( 'nonCrit' => $cont ), $res ) as $contPartId => $contPart )
				{
					$oiCi = ( $contPart !== '' ) ? adkxsshiujqtfk( $ctxProcess, $settCache, 'css', $contPart ) : '';
					if( $oiCi === false )
						return( false );

					$resLrn[ $contPartId ] = $oiCi;
				}

				$this -> _aAdjustContCache[ $contHash ] = $resLrn;
			}
			else if( $res === null )
				$this -> _aAdjustContCache[ $contHash ] = false;
			else
			{
				$oiCi = ( $cont !== '' ) ? adkxsshiujqtfk( $ctxProcess, $settCache, 'css', $cont ) : '';
				if( $oiCi === false )
					return( false );

				$this -> _aAdjustContCache[ $contHash ] = $oiCi;
			}
		}

		return( $res );
	}

	function _AdjustCont( $extract, &$aCritFonts, &$ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, &$cont, $isInlined )
	{
		RemoveZeroSpace( $cont );

		$cont = self::_EscapeNonStdParts( $cont, true );

		$corrErr = (isset($settCss[ 'corrErr' ])?$settCss[ 'corrErr' ]:null);
		$cssParser = new Sabberworm\CSS\Parsing\ParserState( $cont, self::_GetCssParserSettings( $corrErr ) );
		$cssDoc = new Sabberworm\CSS\CSSList\Document( $cssParser -> currentLine() );

		try
		{
			$cssDoc -> parseEx( $cssParser );
		}
		catch( \Exception $e )
		{

			if( $corrErr )
			{
				if( !$extract )
					return( null );

				$contExtracted = self::_EscapeNonStdParts( $cont, false );
				$cont = '';
				return( array( 'crit' => $contExtracted ) );
			}
		}

		unset( $cssParser, $corrErr );

		$cssCritDoc = new Sabberworm\CSS\CSSList\Document();
		$isCritDocAdjusted = false;
		$cssFontsDoc = new Sabberworm\CSS\CSSList\Document();
		$isFontsDocAdjusted = false;
		$isAdjusted = false;

		$blockParents = array( $cssDoc );
		$blockParentsCrit = array( $cssCritDoc );
		$blockParentsFonts = array( $cssFontsDoc );

		$imgSzAlternativesBlocks = null;
		if( (isset($settImg[ 'szAdaptBg' ])?$settImg[ 'szAdaptBg' ]:null) )
		{
			$imgSzAlternativesBlocks = new AnyObj( array( 'a' => array() ) );
			$imgSzAlternativesBlocks -> cxMin = ImgSzAlternatives::MIN;
			$imgSzAlternativesBlocks -> cxMax = ImgSzAlternatives::MAX;
		}

		foreach( ( $aCritFonts !== null ? array( 'main', 'fonts' ) : array( '' ) ) as $stage )
		{
			foreach( $cssDoc -> getContents() as $i )
			{
				if( $i instanceof Sabberworm\CSS\Property\Charset )
				{
					if( !$stage || $stage == 'main' )
					{
						$cssCritDoc -> append( $i );
						$cssFontsDoc -> append( $i );
					}
				}
				else
				{
					$r = $this -> _AdjustContBlock( $stage, $aCritFonts, $i, $blockParents, $blockParentsCrit, $blockParentsFonts, $isCritDocAdjusted, $isFontsDocAdjusted, $extract, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined, $imgSzAlternativesBlocks );
					if( $r === false )
						return( false );
					if( $r )
						$isAdjusted = true;
				}

				if( ContentProcess_IsAborted( $settCache ) ) return( null );
			}
		}

		if( $imgSzAlternativesBlocks )
			foreach( $imgSzAlternativesBlocks -> a as $aImgSzAlternativesBlockId => $aImgSzAlternativesBlock )
				if( StyleProcessor::ImgSzAlternativesBlockAddToStyle( $aImgSzAlternativesBlockId == 'crit' ? $cssCritDoc : $cssDoc, $aImgSzAlternativesBlock ) )
					$isAdjusted = true;

		$min = (isset($settCss[ 'min' ])?$settCss[ 'min' ]:null);

		if( !$isAdjusted && !$isCritDocAdjusted && !$isFontsDocAdjusted && !$min )
		{
			if( !$extract )
				return( null );
			return( array( 'crit' => '' ) );
		}

		$format = self::GetRenderFormat( $min === true );
		$cont = self::_EscapeNonStdParts( trim( $cssDoc -> render( $format ) ), false );

		if( !$extract )
			return( ( $min || $isAdjusted ) ? true : null );

		$res = array();
		$res[ 'crit' ] = $isCritDocAdjusted ? self::_EscapeNonStdParts( trim( $cssCritDoc -> render( $format ) ), false ) : '';
		if( $isFontsDocAdjusted )
			$res[ 'fonts' ] = self::_EscapeNonStdParts( trim( $cssFontsDoc -> render( $format ) ), false );

		return( $res );
	}

	static function RenderData( $cssDoc, $settCss )
	{
		$min = (isset($settCss[ 'min' ])?$settCss[ 'min' ]:null);
		$format = self::GetRenderFormat( $min === true );
		return( self::_EscapeNonStdParts( trim( $cssDoc -> render( $format ) ), false ) );
	}

	static function ImgSzAlternativesBlockAddToStyle( $blockList, $aImgSzAlternativesBlock, $important = false )
	{
		$isAdjusted = false;

		if( !$blockList )
			return( false );

		foreach( $aImgSzAlternativesBlock as $aImgSzAlternativesId => $imgSzAlternatives )
		{
			$ruleAt = new Sabberworm\CSS\CSSList\AtRuleBlockList( 'media', '(max-width: ' . $aImgSzAlternativesId . 'px)' );
			$blockList -> append( $ruleAt );

			foreach( $imgSzAlternatives as $imgSzAlternative )
			{
				if( ulyjqbuhdyqcetbhkiy( $imgSzAlternative[ 'img' ] ) )
					$imgSzAlternative[ 'img' ] = '::bhkdyqcetujyi::' . $imgSzAlternative[ 'img' ];

				$rule = new Sabberworm\CSS\Rule\Rule( 'background-image' );
				$rule -> setValue( new Sabberworm\CSS\Value\URL( new Sabberworm\CSS\Value\CSSString( $imgSzAlternative[ 'img' ] ) ) );
				$rule -> setIsImportant( $important || $imgSzAlternative[ 'isImportant' ] );

				$block = new Sabberworm\CSS\RuleSet\DeclarationBlock();
				$block -> setSelectors( $imgSzAlternative[ 'sels' ] );
				$block -> addRule( $rule );

				$ruleAt -> append( $block );

				$isAdjusted = true;
			}
		}

		return( $isAdjusted );
	}

	private function _AdjustContBlock( $stage, &$aCritFonts, $block, &$blockParents, &$blockParentsCrit, &$blockParentsFonts, &$isCritDocAdjusted, &$isFontsDocAdjusted, $extract, &$ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined, $imgSzAlternativesBlocks = null )
	{
		$isAdjusted = null;
		$canMoveTo = false;

		$imgSzAlternatives = null;

		if( $block instanceof Sabberworm\CSS\Property\Import )
		{
			if( !$stage || $stage == 'main' )
			{
				$r = self::_AdjustUrls( array( $block -> getLocation() ), false, $ctxProcess, $settCache, $settImg, $settCdn, $src, $isInlined );
				if( $r === false )
					return( false );
				if( $r )
					$isAdjusted = true;
				$canMoveTo = true;
			}
		}
		else if( $block instanceof Sabberworm\CSS\CSSList\AtRuleBlockList )
		{
			$blockParents[] = $block;
			$blockParentsCrit[] = null;
			$blockParentsFonts[] = null;

			if( !$stage || $stage == 'main' )
			{
				if( (isset($settCss[ 'min' ])?$settCss[ 'min' ]:null) === true )
					$block -> setAtRuleArgs( $this -> _SelectorMinify( $block -> atRuleArgs() ) );
			}

			$imgSzAlternativesBlocksSub = null;
			if( (isset($settImg[ 'szAdaptBg' ])?$settImg[ 'szAdaptBg' ]:null) )
			{
				$imgSzAlternativesBlocksSub = new AnyObj( array( 'a' => array() ) );
				$imgSzAlternativesBlocksSub -> cxMin = ImgSzAlternatives::MAX;
				$imgSzAlternativesBlocksSub -> cxMax = ImgSzAlternatives::MIN;

				if( $block -> atRuleName() == 'media' )
				{
					$m = array();
					if( preg_match( '@\\(\\s*min-width:\\s*(\\d+)px\\s*\\)@', $block -> atRuleArgs(), $m ) )
					{
						$v = intval( $m[ 1 ] );
						if( $imgSzAlternativesBlocksSub -> cxMin > $v )
							$imgSzAlternativesBlocksSub -> cxMin = $v;
					}

					$m = array();
					if( preg_match( '@\\(\\s*max-width:\\s*(\\d+)px\\s*\\)@', $block -> atRuleArgs(), $m ) )
					{
						$v = intval( $m[ 1 ] );
						if( $imgSzAlternativesBlocksSub -> cxMax < $v )
							$imgSzAlternativesBlocksSub -> cxMax = $v;
					}

					unset( $m );
					unset( $atRuleCond );
				}

				if( $imgSzAlternativesBlocksSub -> cxMin == ImgSzAlternatives::MAX )
					$imgSzAlternativesBlocksSub -> cxMin = ImgSzAlternatives::MIN;
				if( $imgSzAlternativesBlocksSub -> cxMax == ImgSzAlternatives::MIN )
					$imgSzAlternativesBlocksSub -> cxMax = ImgSzAlternatives::MAX;
			}

			foreach( $block -> getContents() as $i )
			{
				$r = $this -> _AdjustContBlock( $stage, $aCritFonts, $i, $blockParents, $blockParentsCrit, $blockParentsFonts, $isCritDocAdjusted, $isFontsDocAdjusted, $extract, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined, $imgSzAlternativesBlocksSub );
				if( $r === false )
					return( false );
				if( $r )
					$isAdjusted = true;
				if( ContentProcess_IsAborted( $settCache ) ) return( null );
			}

			if( $imgSzAlternativesBlocksSub )
				foreach( $imgSzAlternativesBlocksSub -> a as $aImgSzAlternativesBlockId => $aImgSzAlternativesBlock )
					if( StyleProcessor::ImgSzAlternativesBlockAddToStyle( $aImgSzAlternativesBlockId == 'crit' ? $blockParentsCrit[ count( $blockParentsCrit ) - 1 ] : $blockParents[ count( $blockParents ) - 1 ], $aImgSzAlternativesBlock ) )
						$isAdjusted = true;

			array_pop( $blockParents );
			array_pop( $blockParentsCrit );
			array_pop( $blockParentsFonts );

			if( (isset($settCss[ 'min' ])?$settCss[ 'min' ]:null) === true && !$block -> getContents() )
			{
				$blockParents[ count( $blockParents ) - 1 ] -> remove( $block );
				$isAdjusted = true;
			}
		}
		else if( $block instanceof Sabberworm\CSS\RuleSet\DeclarationBlock )
		{
			if( !$stage || $stage == 'main' )
			{
				$isCrit = false;

				if( !$block -> isEmpty() )
				{
					if( $imgSzAlternativesBlocks && !Images_CheckSzAdaptExcl( $ctxProcess, $this -> doc, $settImg, ( string )$block ) )
						$imgSzAlternatives = new ImgSzAlternatives( $imgSzAlternativesBlocks -> cxMin, $imgSzAlternativesBlocks -> cxMax );

					if( $this -> _AdjustBlock( $block, $extract, $isCrit, $aCritFonts, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined, $imgSzAlternatives ) )
						$isAdjusted = true;
				}

				if( (isset($settCss[ 'min' ])?$settCss[ 'min' ]:null) === true && $block -> isEmpty() )
				{
					$blockParents[ count( $blockParents ) - 1 ] -> remove( $block );
					$isAdjusted = true;
				}

				if( $isCrit )
					$canMoveTo = true;
			}
		}
		else if( $block instanceof Sabberworm\CSS\RuleSet\AtRuleSet && $block -> atRuleName() == 'font-face' )
		{
			if( !$stage || $stage == 'fonts' )
			{

				if( (isset($settCss[ 'fontOptLoad' ])?$settCss[ 'fontOptLoad' ]:null) )
				{
					$rule = new Sabberworm\CSS\Rule\Rule( 'font-display' );
					$rule -> setValue( (isset($settCss[ 'fontOptLoadDisp' ])?$settCss[ 'fontOptLoadDisp' ]:null) ? $settCss[ 'fontOptLoadDisp' ] : 'swap' );
					$block -> removeRule( $rule -> getRule() );
					$block -> addRule( $rule );

					$isAdjusted = true;
				}

				if( (isset($settCss[ 'fontPreload' ])?$settCss[ 'fontPreload' ]:null) )
				{
					foreach( $block -> getRules( 'src' ) as $rule )
					{
						self::_GetCssRuleValUrlObjs( $rule -> getValue(), $this -> aFonts );

					}
				}

				$aDepFonts = null;

				$r = self::AdjustRuleSet( $block, $aDepFonts, new AnyObj(), $this -> doc, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined );
				if( $r === false )
					return( false );

				if( $r )
					$isAdjusted = true;

				if( (isset($settCss[ 'fontCrit' ])?$settCss[ 'fontCrit' ]:null) )
					$canMoveTo = true;
				else
				{
					if( (isset($settCss[ 'delayNonCritWithJs' ])?$settCss[ 'delayNonCritWithJs' ]:null) )
					{
						if( $aCritFonts !== null )
						{
							$isFontCrit = false;

							$names = array();
							foreach( $block -> getRules( 'font-family' ) as $rule )
								self::_GetCssRuleValFontNames( $rule -> getValue(), $names );

							foreach( $names as $name => $namesVal )
								if( (isset($aCritFonts[ $name ])?$aCritFonts[ $name ]:null) )
									$isFontCrit = true;

							if( $isFontCrit )
								$canMoveTo = 'fonts';
						}
						else
							$canMoveTo = 'fonts';
					}
				}
			}
		}
		else
		{
			if( !$stage || $stage == 'main' )
			{
				$canMoveTo = true;
			}
		}

		$isCrit = $extract && $canMoveTo;
		if( $isCrit )
		{
			if( $canMoveTo === 'fonts' )
			{
				$blockParentsMoveTo = &$blockParentsFonts;
				$isFontsDocAdjusted = true;
			}
			else
			{
				$blockParentsMoveTo = &$blockParentsCrit;
				$isCritDocAdjusted = true;
			}

			for( $iParent = 0; $iParent < count( $blockParentsMoveTo ); $iParent++ )
			{
				if( $blockParentsMoveTo[ $iParent ] )
					continue;

				$blockParentsMoveTo[ $iParent ] = new Sabberworm\CSS\CSSList\AtRuleBlockList( $blockParents[ $iParent ] -> atRuleName(), $blockParents[ $iParent ] -> atRuleArgs() );
				$blockParentsMoveTo[ $iParent - 1 ] -> append( $blockParentsMoveTo[ $iParent ] );
			}

			$blockParents[ count( $blockParents ) - 1 ] -> remove( $block );
			$blockParentsMoveTo[ count( $blockParentsMoveTo ) - 1 ] -> append( $block );
		}

		if( $imgSzAlternatives )
			foreach( $imgSzAlternatives -> a as $imgSzAlternativeScope => $imgSzAlternative )
				Gen::SetArrField( $imgSzAlternativesBlocks -> a, array( $isCrit ? 'crit' : 'nonCrit', $imgSzAlternativeScope, '+' ), array_merge( array( 'sels' => $block -> getSelectors() ), $imgSzAlternative ) );

		return( $isAdjusted );
	}

	static function GetRenderFormat( $min = true )
	{
		if( $min )
		{
			$format = Sabberworm\CSS\OutputFormat::createCompact();
			$format -> setSemicolonAfterLastRule( false );
			$format -> setSpaceAfterRuleName( '' );
			$format -> setSpaceBeforeImportant( '' );
		}
		else
			$format = Sabberworm\CSS\OutputFormat::createPretty() -> set( 'Space*Rules', "\r\n" ) -> set( 'Space*Blocks', "\r\n" ) -> setSpaceBetweenBlocks( "\r\n\r\n" );

		return( $format );
	}

	private static function _GetCssParserSettings( $bCorrectErrors = true )
	{
		return( Sabberworm\CSS\Settings::create() -> withMultibyteSupport( false ) -> withLenientParsing( Sabberworm\CSS\Settings::ParseErrMed | ( $bCorrectErrors ? Sabberworm\CSS\Settings::ParseErrHigh : 0 ) ) );

	}

	private function _AdjustBlock( $ruleSet, $extract, &$isCrit, &$aCritFonts, &$ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined, $imgSzAlternatives = null )
	{
		if( !$extract )
			$isCrit = true;

		$selectors = $ruleSet -> getSelectors();
		foreach( $selectors as $sel )
		{

			if( (isset($settCss[ 'min' ])?$settCss[ 'min' ]:null) === true )
				$sel -> setSelector( $this -> _SelectorMinify( $sel -> getSelector() ) );

			if( !$isCrit )
			{
				foreach( Gen::GetArrField( $settCss, array( 'nonCrit', 'autoExcls' ), array() ) as $excl )
				{
					if( @preg_match( $excl, $sel ) )
					{
						$isCrit = true;
						break;
					}
				}
			}
		}

		$ctxItems = new AnyObj();
		$isAdjusted = self::AdjustRuleSet( $ruleSet, $aCritFonts, $ctxItems, $this -> doc, $ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined, $imgSzAlternatives );

		if( (isset($ctxItems -> isLazyStyleBg)?$ctxItems -> isLazyStyleBg:null) )
			foreach( $selectors as $sel )
				if( $sel = $this -> cssSelToXPath( ( string )$sel ) )
					$this -> _aXpathSelLazyBgCache[ $sel ] = true;

		if( !$isCrit )
			foreach( $selectors as $sel )
			{
				if( $this -> xpathIsCssSelCrit( $ctxProcess, ( string )$sel ) )
				{
					$isCrit = true;
					break;
				}
			}

		return( $isAdjusted );
	}

	function ApplyItems( &$ctxProcess, $settImg )
	{
		foreach( $this -> _aXpathSelLazyBgCache as $xpathSel => $selTrue )
			if( $items = $this -> xpathEvaluate( $xpathSel ) )
				foreach( $items as $item )
					StyleProcessor::AdjustItemLazyBg( $ctxProcess, $settImg, $this -> doc, $item );
	}

	static function AdjustItemLazyBg( &$ctxProcess, $settImg, $doc, $item )
	{
		if( Images_CheckLazyExcl( $ctxProcess, $doc, $settImg, $item ) )
			return;

		$ctxProcess[ 'lazyload' ] = true;
		HtmlNd::AddRemoveAttrClass( $item, array( 'lzl' ) );
	}

	static function AdjustRuleSet( $ruleSet, &$aDepFonts, $ctxItems, $doc, &$ctxProcess, $settCache, $settCss, $settImg, $settCdn, $srcInfo, $src, $isInlined, $imgSzAlternatives = null )
	{
		$isAdjusted = null;

		$urlDomainUrl = $isInlined ? null : Net::GetSiteAddrFromUrl( $src, true );
		$urlPath = $isInlined ? $ctxProcess[ 'requestUriPath' ] : Gen::GetFileDir( Net::Url2Uri( $src ) );

		{
			$urls = array();
			foreach( $ruleSet -> getRules() as $rule )
			{
				if( (isset($settCss[ 'min' ])?$settCss[ 'min' ]:null) === true )
					self::_RuleMinify( $rule );

				$skip = false;

				{
					switch( $rule -> getRule() )
					{
					case 'background-image':
					case 'background':
						$skip = true;
						break;
					}
				}

				if( $aDepFonts !== null )
				{
					switch( $rule -> getRule() )
					{

					case 'font-family':
					case 'font':
						self::_GetCssRuleValFontNames( $rule -> getValue(), $aDepFonts );
						break;
					}
				}

				if( !$skip )
					self::_GetCssRuleValUrlObjs( $rule -> getValue(), $urls );
			}

			$r = self::_AdjustUrls( $urls, $ruleSet instanceof Sabberworm\CSS\RuleSet\AtRuleSet && $ruleSet -> atRuleName() == 'font-face', $ctxProcess, $settCache, $settImg, $settCdn, $src, $isInlined );
			if( $r === false )
				return( false );
			if( $r )
				$isAdjusted = true;
		}

		$isLazy = Gen::GetArrField( $settImg, array( 'lazy', 'load' ), false );
		$itemsLazyAdjusted = false;
		$ctxItems -> isLazyStyleBg = false;
		$bgImgSzAlternativesProcessed = false;

		foreach( $ruleSet -> getRules( 'background-image' ) as $rule )
		{

			$urls = array(); self::_GetCssRuleValUrlObjs( $rule -> getValue(), $urls );
			foreach( $urls as $url )
			{
				$bgImgSrcOrig = html_entity_decode( trim( $url -> getURL() -> getString() ) );
				if( !$bgImgSrcOrig )
					continue;

				$adjustedItem = false;

				$bgImgSrc = new ImgSrc( $bgImgSrcOrig );
				$bgImgSrc -> Init( $ctxProcess, $urlDomainUrl, $urlPath );
				if( $bgImgSrc -> src != $bgImgSrcOrig )
					$adjustedItem = true;

				if( !is_a( $rule -> getValue(), 'seraph_accel\\Sabberworm\\CSS\\Value\\RuleValueList' ) && $imgSzAlternatives && !$bgImgSzAlternativesProcessed )
				{
					if( Images_ProcessSrcSizeAlternatives( $imgSzAlternatives, $ctxProcess, $bgImgSrc, $settCache, $settImg, $settCdn, $rule -> getIsImportant() ) === false )
						return( false );
					$bgImgSzAlternativesProcessed = true;
				}

				$r = Images_ProcessSrc( $ctxProcess, $bgImgSrc, $settCache, $settImg, $settCdn );
				if( $r === false )
					return( false );

				if( $r )
					$adjustedItem = true;

				if( $isLazy && !$itemsLazyAdjusted && !Ui::IsSrcAttrData( $bgImgSrc -> src ) )
				{
					if( isset( $ctxItems -> item ) )
					{
						if( Images_ProcessItemLazyBg( $ctxProcess, $doc, $settImg, $ctxItems -> item, $bgImgSrc ) )
						{
							$adjustedItem = true;
							$itemsLazyAdjusted = true;
						}
					}
					else
					{
						$ctxItems -> isLazyStyleBg = true;
						$itemsLazyAdjusted = true;
					}
				}

				if( ulyjqbuhdyqcetbhkiy( $bgImgSrc -> src ) )
				{
					$bgImgSrc -> src = '::bhkdyqcetujyi::' . $bgImgSrc -> src;
					$adjustedItem = true;
				}

				if( $adjustedItem )
				{
					$isAdjusted = true;
					$url -> setURL( new Sabberworm\CSS\Value\CSSString( $bgImgSrc -> src ) );
				}

				unset( $bgImgSrc );
			}
		}

		foreach( $ruleSet -> getRules( 'background' ) as $rule )
		{
			$urls = array(); self::_GetCssRuleValUrlObjs( $rule -> getValue(), $urls );
			foreach( $urls as $url )
			{
				$bgImgSrcOrig = html_entity_decode( trim( $url -> getURL() -> getString() ) );
				if( !$bgImgSrcOrig )
					continue;

				$adjustedItem = false;

				$bgImgSrc = new ImgSrc( $bgImgSrcOrig );
				$bgImgSrc -> Init( $ctxProcess, $urlDomainUrl, $urlPath );
				if( $bgImgSrc -> src != $bgImgSrcOrig )
					$adjustedItem = true;

				if( !self::_IsRuleValCompound( $rule -> getValue() ) && $imgSzAlternatives && !$bgImgSzAlternativesProcessed )
				{
					if( Images_ProcessSrcSizeAlternatives( $imgSzAlternatives, $ctxProcess, $bgImgSrc, $settCache, $settImg, $settCdn, $rule -> getIsImportant() ) === false )
						return( false );
					$bgImgSzAlternativesProcessed = true;
				}

				$r = Images_ProcessSrc( $ctxProcess, $bgImgSrc, $settCache, $settImg, $settCdn );
				if( $r === false )
					return( false );

				if( $r )
					$adjustedItem = true;

				if( $isLazy && !$itemsLazyAdjusted && !Ui::IsSrcAttrData( $bgImgSrc -> src ) )
				{
					$ctxItems -> isLazyStyleBg = true;
					$itemsLazyAdjusted = true;
				}

				if( ulyjqbuhdyqcetbhkiy( $bgImgSrc -> src ) )
				{
					$bgImgSrc -> src = '::bhkdyqcetujyi::' . $bgImgSrc -> src;
					$adjustedItem = true;
				}

				if( $adjustedItem )
				{
					$isAdjusted = true;
					$url -> setURL( new Sabberworm\CSS\Value\CSSString( $bgImgSrc -> src ) );
				}

				unset( $bgImgSrc );
			}
		}

		return( $isAdjusted );
	}

	static function _IsRuleValCompound( $v )
	{
		if( !is_a( $v, 'seraph_accel\\Sabberworm\\CSS\\Value\\RuleValueList' ) )
			return( false );

		if( $v -> getListSeparator() == ',' )
			return( true );

		foreach( $v -> getListComponents() as $component )
			if( is_a( $component, 'seraph_accel\\Sabberworm\\CSS\\Value\\RuleValueList' ) && $component -> getListSeparator() == ',' )
				return( true );

		return( false );
	}

	static function _AdjustUrls( $urls, $isFont, &$ctxProcess, $settCache, $settImg, $settCdn, $src, $isInlined )
	{

		$isAdjusted = null;

		$urlDomainUrl = $isInlined ? null : Net::GetSiteAddrFromUrl( $src, true );
		$urlPath = $isInlined ? $ctxProcess[ 'requestUriPath' ] : Gen::GetFileDir( Net::Url2Uri( $src ) );

		foreach( $urls as $oUrl )
		{
			$url = trim( $oUrl -> getURL() -> getString() );

			if( Ui::IsSrcAttrData( $url ) )
				continue;

			$urlNew = $url;
			$urlAdjusted = false;

			$srcInfo = GetSrcAttrInfo( $ctxProcess, $urlDomainUrl, $urlPath, $urlNew );
			if( $urlNew != $url )
				$urlAdjusted = true;

			$fileType = strtolower( Gen::GetFileExt( (isset($srcInfo[ 'srcWoArgs' ])?$srcInfo[ 'srcWoArgs' ]:null) ) );

			$isImg = false;
			switch( $fileType )
			{
			case 'jpeg':
			case 'jpg':
			case 'gif':
			case 'png':
			case 'webp':
			case 'bmp':
			case 'svg':
				$isImg = !$isFont;
				break;
			}

			if( $isImg )
			{

				$imgSrc = new ImgSrc( $urlNew, $srcInfo );

				$r = Images_ProcessSrcEx( $ctxProcess, $imgSrc, $settCache, $settImg );
				if( $r === false )
					return( false );

				if( $r )
					$urlAdjusted = true;

				$urlNew = $imgSrc -> src;
				unset( $imgSrc );
			}

			if( Cdn_AdjustUrl( $ctxProcess, $settCdn, $urlNew, $fileType ) )
				$urlAdjusted = true;
			if( Fullness_AdjustUrl( $ctxProcess, $urlNew, (isset($srcInfo[ 'srcUrlFullness' ])?$srcInfo[ 'srcUrlFullness' ]:null) ) )
				$urlAdjusted = true;

			if( $urlAdjusted )
				$isAdjusted = true;

			if( ulyjqbuhdyqcetbhkiy( $urlNew ) )
			{
				$urlNew = '::bhkdyqcetujyi::' . $urlNew;
				$urlAdjusted = true;
			}

			if( $urlAdjusted )
				$oUrl -> setURL( new Sabberworm\CSS\Value\CSSString( $urlNew ) );
		}

		return( $isAdjusted );
	}

	static function ParseRuleSet( $data, $bCorrectErrors = true )
	{
		try
		{
			$parserState = new Sabberworm\CSS\Parsing\ParserState( $data, self::_GetCssParserSettings( $bCorrectErrors ) );
			$ruleSet = new Sabberworm\CSS\RuleSet\RuleSet();
			Sabberworm\CSS\RuleSet\RuleSet::parseRuleSet( $parserState, $ruleSet );
		}
		catch( \Exception $e )
		{
			$ruleSet = null;
		}

		return( $ruleSet );
	}

	static function GetFirstImportSimpleAttrs( $ctxProcess, $import, $src )
	{
		if( preg_match( '@\\ssupports\\s*\\(@', $import ) )
			return( null );

		try
		{
			$cssParser = new Sabberworm\CSS\Parser( $import, Sabberworm\CSS\Settings::create() -> withMultibyteSupport( false ) );
			$cssDoc = $cssParser -> parse();
			unset( $cssParser );
		}
		catch( \Exception $e )
		{
			return( null );
		}

		foreach( $cssDoc -> getContents() as $block )
		{
			if( $block instanceof Sabberworm\CSS\Property\Import )
			{
				$args = $block -> atRuleArgs();

				$url = $args[ 0 ];
				if( $url instanceof Sabberworm\CSS\Value\URL )
					$url = $url -> getURL();
				if( $url instanceof Sabberworm\CSS\Value\CSSString )
					$url = $url -> getString();

				if( gettype( $url ) !== 'string' )
					return( null );

				{
					$urlDomainUrl = $src ? Net::GetSiteAddrFromUrl( $src, true ) : null;
					$urlPath = $src ? Gen::GetFileDir( Net::Url2Uri( $src ) ) : $ctxProcess[ 'requestUriPath' ];
					$srcInfo = GetSrcAttrInfo( $ctxProcess, $urlDomainUrl, $urlPath, $url );
					Fullness_AdjustUrl( $ctxProcess, $url, (isset($srcInfo[ 'srcUrlFullness' ])?$srcInfo[ 'srcUrlFullness' ]:null) );
				}

				$res = array( 'url' => $url );
				if( count( $args ) > 1 )
					$res[ 'media' ] = ( string )$args[ 1 ];

				return( $res );
			}
		}

		return( null );
	}

	function cssSelToXPath( string $sel )
	{

		$pos = strpos( $sel, '::' );
		if( $pos !== false )
			$sel = substr( $sel, 0, $pos );

		$xpathQ = null; try { $xpathQ = $this -> cnvCssSel2Xpath -> cssToXPath( $sel ); } catch( \Exception $e ) {}
		return( $xpathQ );
	}

	function xpathEvaluate( $query )
	{
		return( $this -> xpath -> evaluate( $query, $this -> rootElem ) );
	}

	function xpathIsCssSelCrit( $ctxProcess, string $sel )
	{

		if( isset( $ctxProcess[ 'lrnDsc' ] ) || $this -> xpathSkeleton )
		{

			if( Gen::StrPosArr( $sel, array( ':nth-child', ':nth-last-child', ':first-child', ':last-child', ':only-child', ':nth-of-type', ':nth-last-of-type', ':first-of-type', ':last-of-type', ':only-of-type', '[', ']', '+', '~' ) ) !== false )
				return( true );
		}

		if( isset( $this -> _aCssSelIsCritCache[ $sel ] ) )
		{

			return( $this -> _aCssSelIsCritCache[ $sel ] );
		}

		$selFiltered = trim( ContSkeleton_FltName( $this -> sklCssSelExcls, $sel, true ) );

		$items = false;
		if( $xpathQ = $this -> cssSelToXPath( $selFiltered ) )
		{
			$xpathQ = '(' . $xpathQ . ')[1]';
			$items = $this -> xpathSkeleton ? $this -> xpathSkeleton -> evaluate( $xpathQ ) : $this -> xpathEvaluate( $xpathQ );
		}

		return( $this -> _aCssSelIsCritCache[ $sel ] = ( $items === false || HtmlNd::FirstOfChildren( $items ) ) );
	}

	static function keepLrnNeededData( &$datasDel, &$lrnsGlobDel, $dsc, $dataPath )
	{
		if( $id = Gen::GetArrField( $dsc, array( 'css', 'c' ) ) )
		{
			unset( $lrnsGlobDel[ 'css/c/' . $id . '.dat.gz' ] );

			$data = Tof_GetFileData( $dataPath . '/css/c', 'dat.gz', 1, true, $id );

			foreach( Gen::GetArrField( $data, array( 'ac' ), array() ) as $contHash => $contParts )
			{
				if( is_array( $contParts ) )
				{
					foreach( $contParts as $partId => $oiCi )
						if( is_string( $oiCi ) && strlen( $oiCi ) )
							unset( $datasDel[ 'css' ][ $oiCi ] );
				}
				else if( is_string( $contParts ) && strlen( $contParts ) )
					unset( $datasDel[ 'css' ][ $contParts ] );
			}
		}

		if( $id = Gen::GetArrField( $dsc, array( 'css', 'xslb' ) ) )
		{
			unset( $lrnsGlobDel[ 'css/xslb/' . $id . '.dat.gz' ] );
		}
	}

	function readLrnData( $dsc, $dataPath )
	{
		if( $id = Gen::GetArrField( $dsc, array( 'css', 'c' ) ) )
		{
			$data = Tof_GetFileData( $dataPath . '/css/c', 'dat.gz', 1, true, $id );

			$this -> _aAdjustContCache = Gen::GetArrField( $data, array( 'ac' ), array() );
		}

		if( $id = Gen::GetArrField( $dsc, array( 'css', 'xslb' ) ) )
		{
			$data = Tof_GetFileData( $dataPath . '/css/xslb', 'dat.gz', 1, true, $id );

			$this -> _aXpathSelLazyBgCache = Gen::GetArrField( $data, array( 'd' ), array() );
		}
	}

	function writeLrnData( &$dsc, $dataPath )
	{
		if( $this -> _aAdjustContCache )
		{
			$data = array();

			if( $this -> _aAdjustContCache )
				$data[ 'ac' ] = $this -> _aAdjustContCache;

			$dsc[ 'css' ][ 'c' ] = '';
			if( Gen::HrFail( @Tof_SetFileData( $dataPath . '/css/c', 'dat.gz', $data, 1, false, TOF_COMPR_MAX, $dsc[ 'css' ][ 'c' ] ) ) )
				return( false );
		}

		if( $this -> _aXpathSelLazyBgCache )
		{
			$dsc[ 'css' ][ 'xslb' ] = '';
			if( Gen::HrFail( @Tof_SetFileData( $dataPath . '/css/xslb', 'dat.gz', array( 'd' => $this -> _aXpathSelLazyBgCache ), 1, false, TOF_COMPR_MAX, $dsc[ 'css' ][ 'xslb' ] ) ) )
				return( false );
		}

		return( true );
	}

	private static function _GetCssRuleValUrlObjs( $v, &$urls )
	{
		if( $v instanceof Sabberworm\CSS\Value\URL )
			$urls[] = $v;
		else if( $v instanceof Sabberworm\CSS\Value\RuleValueList )
			foreach( $v -> getListComponents() as $vComp )
				self::_GetCssRuleValUrlObjs( $vComp, $urls );
	}

	private static function _GetCssRuleValFontNames( $v, &$names )
	{
		if( gettype( $v ) === 'string' )
		{
			if( !in_array( $v, array( 'normal', 'inherit', 'italic', 'oblique', 'small-caps', 'bold', 'bolder', 'lighter' ) ) )
				$names[ $v ] = true;
		}
		else if( $v instanceof Sabberworm\CSS\Value\CSSString )
			$names[ $v -> getString() ] = true;
		else if( $v instanceof Sabberworm\CSS\Value\RuleValueList )
			foreach( $v -> getListComponents() as $vComp )
				self::_GetCssRuleValFontNames( $vComp, $names );
	}

	private static function _DoesCSSRuleValContainFileURL( $v )
	{
		if( $v instanceof Sabberworm\CSS\Value\URL )
			return( !Ui::IsSrcAttrData( trim( $v -> getURL() -> getString() ) ) );

		if( !( $v instanceof Sabberworm\CSS\Value\RuleValueList ) )
			return( false );

		foreach( $v -> getListComponents() as $vComp )
			if( self::_DoesCSSRuleValContainFileURL( $vComp ) )
				return( true );

		return( false );
	}

	private static function _RuleMinify( $rule )
	{
		$aShorters = array(
			'font-weight'		=> array( 'normal' => 400, 'bold' => 700, ),
			'background'		=> array( 'transparent' => '0 0', 'none' => '0 0', 'black' => '#000', 'white' => '#fff', 'fuchsia' => '#f0f', 'magenta' => '#f0f', 'yellow' => '#ff0' ),

			'margin'			=> __CLASS__ . '::_RuleMinifySizes',
			'padding'			=> __CLASS__ . '::_RuleMinifySizes',
			'border-width'		=> __CLASS__ . '::_RuleMinifySizes',

			'left'				=> __CLASS__ . '::_RuleMinifySizes',
			'top'				=> __CLASS__ . '::_RuleMinifySizes',
			'right'				=> __CLASS__ . '::_RuleMinifySizes',
			'bottom'			=> __CLASS__ . '::_RuleMinifySizes',

			'margin-left'		=> __CLASS__ . '::_RuleMinifySizes',
			'margin-top'		=> __CLASS__ . '::_RuleMinifySizes',
			'margin-right'		=> __CLASS__ . '::_RuleMinifySizes',
			'margin-bottom'		=> __CLASS__ . '::_RuleMinifySizes',

			'padding-left'		=> __CLASS__ . '::_RuleMinifySizes',
			'padding-top'		=> __CLASS__ . '::_RuleMinifySizes',
			'padding-right'		=> __CLASS__ . '::_RuleMinifySizes',
			'padding-bottom'	=> __CLASS__ . '::_RuleMinifySizes',
		);

		$shorter = (isset($aShorters[ $rule -> getRule() ])?$aShorters[ $rule -> getRule() ]:null);
		if( !$shorter )
			return;

		if( is_array( $shorter ) )
		{
			$val = $rule -> getValue();
			if( !is_object( $val ) )
			{
				$valShort = (isset($shorter[ $val ])?$shorter[ $val ]:null);
				if( $valShort !== null )
					$rule -> setValue( $valShort );
			}
		}
		else
			@call_user_func( $shorter, $rule );
	}

	static function _SizeMin( $v )
	{
		if( $v instanceof Sabberworm\CSS\Value\Size && !$v -> getSize() )
			$v -> setUnit( null );
		return( $v );
	}

	static function _RuleMinifySizes( $rule )
	{

		$v = $rule -> getValue();
		if( $v instanceof Sabberworm\CSS\Value\RuleValueList )
		{
			$comps = $v -> getListComponents();
			foreach( $comps as &$vComp )
				$vComp = self::_SizeMin( $vComp );

			if( count( $comps ) == 4 && ( string )$comps[ 1 ] === ( string )$comps[ 3 ] )
				array_pop( $comps );
			if( count( $comps ) == 3 && ( string )$comps[ 0 ] === ( string )$comps[ 2 ] )
				array_pop( $comps );
			if( count( $comps ) == 2 && ( string )$comps[ 0 ] === ( string )$comps[ 1 ] )
				array_pop( $comps );

			$v -> setListComponents( $comps );
		}
		else
			$v = self::_SizeMin( $v );

		$rule -> setValue( $v );
	}

	private function _SelectorMinify( $sel )
	{
		$selWrongSuffix = '';

		{
			$posWrongSel = strpos( $sel, '{' );
			if( $posWrongSel !== false )
			{
				$selWrongSuffix = substr( $sel, $posWrongSel );
				$sel = substr( $sel, 0, $posWrongSel );
			}
		}

		if( $selNew = $this -> minifier -> run( $sel ) )
			$sel = $selNew;

		return( $sel . $selWrongSuffix );
	}
}

