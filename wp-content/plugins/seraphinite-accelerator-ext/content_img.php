<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

class ImgSrc
{
	public $src;
	public $srcInfo;
	public $mimeType;

	function __construct( $src, $srcInfo = null )
	{
		$this -> src = $src;
		$this -> srcInfo = $srcInfo;

	}

	function Init( $ctxProcess, $requestDomainUrl = null, $requestUriPath = null )
	{
		if( !isset( $this -> srcInfo ) )
			$this -> srcInfo = Ui::IsSrcAttrData( $this -> src ) ? false : GetSrcAttrInfo( $ctxProcess, $requestDomainUrl, $requestUriPath, $this -> src );
	}

	function GetSize()
	{
		if( !isset( $this -> cont ) && $this -> srcInfo )
		{
			$file = (isset($this -> srcInfo[ 'filePath' ])?$this -> srcInfo[ 'filePath' ]:null);
			if( $file )
				return( @filesize( $file ) );
		}

		$this -> GetCont();
		return( $this -> cont !== false ? strlen( $this -> cont ) : false );
	}

	function GetCont()
	{
		if( !isset( $this -> cont ) )
		{
			if( $this -> srcInfo )
			{
				$file = (isset($this -> srcInfo[ 'filePath' ])?$this -> srcInfo[ 'filePath' ]:null);
				if( $file )
				{
					$this -> cont = @file_get_contents( $file );

				}
				if( !(isset($this -> cont)?$this -> cont:null) )
				{
					$this -> cont = GetExtContents( $this -> srcInfo[ 'url' ], $this -> mimeType, true, 10 );

				}
			}
			else
			{
				$data = strpos( $this -> src, ',' );
				if( $data !== false )
				{
					$prms = explode( ';', substr( $this -> src, 5, $data - 5 ) );
					$data = substr( $this -> src, $data + 1 );
					$this -> mimeType = (isset($prms[ 0 ])?$prms[ 0 ]:null);
					$this -> cont = ( (isset($prms[ 1 ])?$prms[ 1 ]:null) == 'base64' ) ? base64_decode( $data ) : false;
				}
				else
					$this -> cont = false;
			}

			if( !$this -> mimeType )
			{
				$this -> GetInfo();
				if( $this -> info )
					$this -> mimeType = $this -> info[ 'mime' ];
			}
		}

		return( $this -> cont );
	}

	function GetInfo()
	{
		$this -> GetCont();

		if( !isset( $this -> info ) )
		{
			$this -> info = Img::GetInfoFromData( $this -> cont );
			if( $this -> info === null )
				$this -> info = false;
		}

		return( $this -> info );
	}
}

class ImgSzAlternatives
{
	const MIN			= 0;
	const MAX			= 999999999;

	public $cxMin;
	public $cxMax;
	public $a;

	function __construct( $cxMin = ImgSzAlternatives::MIN, $cxMax = ImgSzAlternatives::MAX )
	{
		$this -> cxMin = $cxMin;
		$this -> cxMax = $cxMax;
		$this -> a = array();
	}
}

function _Images_ProcessSrc_InlineSmallEx( $mimeType, $imgCont )
{

	return( 'data:' . $mimeType . ';base64,' . base64_encode( $imgCont ) );
}

function _Images_ProcessSrc_InlineSmall( $imgSrc, $settImg )
{
	if( !Gen::GetArrField( $settImg, array( 'inlSml' ), false ) )
		return( false );

	$fileSize = $imgSrc -> GetSize();
	if( $fileSize === false )
		return( false );

	if( !$fileSize || $fileSize > Gen::GetArrField( $settImg, array( 'inlSmlSize' ), 0 ) )
		return( false );

	$imgCont = $imgSrc -> GetCont();
	if( $imgCont === false )
		return( false );

	if( !$imgSrc -> mimeType )
		return( false );

	$imgSrc -> src = _Images_ProcessSrc_InlineSmallEx( $imgSrc -> mimeType, $imgCont );
	return( true );
}

function _FileWriteTmpAndReplace( $file, $fileTime, $data = null, $fileTmp = null )
{

	if( $fileTmp === null )
		$fileTmp = $file . '.tmp';

	$lock = new Lock( $fileTmp . '.l', false, true );
	if( $lock -> Acquire() )
	{
		if( $data === null || @file_put_contents( $fileTmp, $data ) )
		{
			if( @touch( $fileTmp, $fileTime ) )
			{

				if( @rename( $fileTmp, $file ) )
				{
					$lock -> Release();
					return( true );
				}
				else
					Gen::LastErrDsc_Set( LocId::Pack( 'FileRenameErr_%1$s%2$s', 'Common', array( $fileTmp, $file ) ) );
			}
			else
				Gen::LastErrDsc_Set( LocId::Pack( 'FileWriteErr_%1$s', 'Common', array( $fileTmp ) ) );
		}
		else
			Gen::LastErrDsc_Set( LocId::Pack( 'FileWriteErr_%1$s', 'Common', array( $fileTmp ) ) );

		$lock -> Release();
	}
	else
		Gen::LastErrDsc_Set( $lock -> GetErrDescr() );

	@unlink( $fileTmp );
	@unlink( $file );

	return( false );
}

function _Images_ProcessSrc_ConvertEx( $type, $typeIdx, $settImg, $data, $file, $fileCnv, $fileTime, $fileTimeCnv, &$sizeCheck )
{
	global $seraph_accel_g_prepPrms;

	$fileCnvStat = $fileCnv . '.json';
	$fileTimeCnvStat = @filemtime( $fileCnvStat );

	$fileExt = Gen::GetFileExt( $file );

	if( !Gen::GetArrField( $settImg, array( $type, 'enable' ), false ) || !in_array( $fileExt, array( 'jpe','jpg','jpeg','png','gif','bmp' ) ) )
		return;

	if( $fileTimeCnvStat !== false && $fileTime <= $fileTimeCnvStat )
		return;

	if( $fileTimeCnv !== false && $fileTime <= $fileTimeCnv )
	{
		$sizeCheck = @filesize( $fileCnv );
		if( $sizeCheck === false )
			LastWarnDscs_Add( LocId::Pack( 'ImgConvertFileErr_%1$s%2$s%3$s', null, array( $file, $type, LocId::Pack( 'FileReadErr_%1$s', 'Common', array( $fileCnv ) ) ) ) );
		return;
	}

	if( @is_a( $data, 'seraph_accel\\ImgSrc' ) )
		$data = $data -> GetCont();

	$status = null;
	if( ( $fileExt == 'png' && Img::IsDataPngAnimated( $data ) ) || ( $fileExt == 'gif' && Img::IsDataGifAnimated( $data ) ) )
		$status = 'aniNotSupp';

	$hr = Gen::S_FALSE;
	$fileCnvTmp = $fileCnv . '.tmp';
	if( !$status )
	{
		@unlink( $fileCnvTmp );

		if( $seraph_accel_g_prepPrms )
			ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'stageDsc' => LocId::Pack( 'ImgConvertFile_%1$s%2$s', null, array( $file, $type ) ) ) );

		$hr = Img::ConvertDataEx( $dataCnvRes, $data, 'image/' . $type, Gen::GetArrField( $settImg, array( $type, 'prms' ), array() ), $fileCnvTmp );

		if( $seraph_accel_g_prepPrms )
			ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'stageDsc' => null ) );
	}

	$fileTime += $typeIdx + 1;

	if( $hr == Gen::S_OK )
	{
		if( $sizeCheck === false )
			$sizeCheck = strlen( $data );

		$sizeCnv = @filesize( $fileCnvTmp );
		if( $sizeCnv !== false )
		{
			if( $sizeCnv < $sizeCheck )
			{
				if( _FileWriteTmpAndReplace( $fileCnv, $fileTime, null, $fileCnvTmp ) )
					$sizeCheck = $sizeCnv;
				@unlink( $fileCnvStat );
			}
			else
				$status = array( 'larger' => $sizeCnv );
		}
		else
			Gen::LastErrDsc_Set( LocId::Pack( 'FileReadErr_%1$s', 'Common', array( $fileCnvTmp ) ) );
	}
	else if( $hr == Gen::E_UNSUPPORTED )
		Gen::LastErrDsc_Set( LocId::Pack( 'ImgConvertUnsupp' ) );

	if( $hr != Gen::S_OK || $status )
	{
		@unlink( $fileCnv );
		if( $status )
			_FileWriteTmpAndReplace( $fileCnvStat, $fileTime, @json_encode( $status ) );
		else
			@unlink( $fileCnvStat );
	}

	@unlink( $fileCnvTmp );

	if( !Gen::LastErrDsc_Is() )
		return;

	LastWarnDscs_Add( LocId::Pack( 'ImgConvertFileErr_%1$s%2$s%3$s', null, array( $file, $type, Gen::LastErrDsc_Get() ) ) );
	Gen::LastErrDsc_Set( null );
}

function _Images_ProcessSrc_ConvertAll( $settImg, $imgSrcOrCont, $file, $fileTime )
{
	$sizeCheck = false;
	foreach( array( 'webp','avif' ) as $typeIdx => $type )
	{
		$fileCnv = $file . '.' . $type;
		$fileTimeCnv = _Images_ProcessSrcEx_FileMTime( $fileCnv );

		_Images_ProcessSrc_ConvertEx( $type, $typeIdx, $settImg, $imgSrcOrCont, $file, $fileCnv, $fileTime, $fileTimeCnv, $sizeCheck );
	}
}

function _Images_ProcessSrcEx_FileMTime( $file )
{
	return( @filesize( $file ) ? @filemtime( $file ) : false );
}

function Images_ProcessSrcEx( &$ctxProcess, $imgSrc, $settCache, $settImg )
{
	$args = $imgSrc -> srcInfo[ 'args' ];

	$file = (isset($imgSrc -> srcInfo[ 'filePath' ])?$imgSrc -> srcInfo[ 'filePath' ]:null);

	if( !$file )
	{

		$cache = false;
		foreach( Gen::GetArrField( $settImg, array( 'cacheExt' ), array() ) as $srcPattern )
		{
			if( !@preg_match( $srcPattern, $imgSrc -> src ) )
				continue;

			$cache = true;
			break;
		}

		if( !$cache )
			return( null );

		$imgCont = $imgSrc -> GetCont();

		if( $imgCont === false )
		{
			Gen::LastErrDsc_Set( LocId::Pack( 'CacheExtImgErr_%1$s', null, array( LocId::Pack( 'NetDownloadErr_%1$s', 'Common', array( $imgSrc -> src ) ) ) ) );
			return( false );
		}

		if( !$imgSrc -> mimeType )
		{
			Gen::LastErrDsc_Set( LocId::Pack( 'CacheExtImgErr_%1$s', null, array( LocId::Pack( 'NetMimeErr_%1$s', 'Common', array( $imgSrc -> src ) ) ) ) );
			return( false );
		}

		if( Gen::GetArrField( $settImg, array( 'inlSml' ), false ) && strlen( $imgCont ) <= Gen::GetArrField( $settImg, array( 'inlSmlSize' ), 0 ) )
			$imgSrc -> src = _Images_ProcessSrc_InlineSmallEx( $imgSrc -> mimeType, $imgCont );
		else
		{
			if( !adkxsshiujqtfk( $ctxProcess, $settCache, array( 'img', Gen::GetFileName( $imgSrc -> mimeType ) ), $imgCont, $imgSrc -> src, $file ) )
				return( false );

			_Images_ProcessSrc_ConvertAll( $settImg, $imgCont, $file, _Images_ProcessSrcEx_FileMTime( $file ) );
		}

		return( true );
	}

	$fileTime = _Images_ProcessSrcEx_FileMTime( $file );
	if( !$fileTime )
		return( null );

	if( ( (isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) !== 'cm' ) && _Images_ProcessSrc_InlineSmall( $imgSrc, $settImg ) )
		return( true );

	_Images_ProcessSrc_ConvertAll( $settImg, $imgSrc, $file, $fileTime );

	foreach( array( 'webp','avif' ) as $typeCnv )
	{
		if( !( Gen::GetArrField( $settImg, array( $typeCnv, 'redir' ), false ) ) )
			continue;

		$srcRealCnvFile = $file . '.' . $typeCnv;
		$fileTimeCnv = _Images_ProcessSrcEx_FileMTime( $srcRealCnvFile );
		if( $fileTimeCnv !== false && $fileTimeCnv > $fileTime )
			$fileTime = $fileTimeCnv;
	}

	if( Gen::GetArrField( $settImg, array( 'srcAddLm' ), false ) )
	{
		$args[ 'lm' ] = sprintf( '%X', $fileTime );
		$imgSrc -> src = Net::UrlDeParse( array( 'path' => $imgSrc -> srcInfo[ 'srcWoArgs' ], 'query' => $args, 'fragment' => (isset($imgSrc -> srcInfo[ '#' ])?$imgSrc -> srcInfo[ '#' ]:null) ), Net::URLPARSE_F_PRESERVEEMPTIES );

	}

	return( true );
}

function Images_ProcessSrcSizeAlternatives( $imgSzAlternatives, &$ctxProcess, $imgSrc, $settCache, $settImg, $settCdn, $isImportant = false )
{
	$imgSrc -> Init( $ctxProcess );

	$info = $imgSrc -> GetInfo();

	if( !$info || !Img::IsMimeRaster( $info[ 'mime' ] ) )
		return( null );

	$img = null;

	foreach( array( array( 1366, 1.2 ), array( 768, 1.1 ), array( 480, 1.0 ), array( 360, 1.0 ) ) as $cx )
	{
		$scale = ( float )$info[ 'cx' ] / $cx[ 0 ];
		if( $scale < $cx[ 1 ] || !( $imgSzAlternatives -> cxMin <= $cx[ 0 ] && $cx[ 0 ] <= ( $imgSzAlternatives -> cxMax + 1  ) ) || !( Gen::GetArrField( $settImg, array( 'szAdaptBgCxMin' ), 0 ) <= $cx[ 0 ] ) )
			continue;

		$cx = $cx[ 0 ];
		$cy = ( int )round( ( float )$info[ 'cy' ] * ( $cx / $info[ 'cx' ] ) );

		if( $img === null )
		{
			$data = $imgSrc -> GetCont();
			if( ( $info[ 'mime' ] == 'image/png' && Img::IsDataPngAnimated( $data ) ) || ( $info[ 'mime' ] == 'image/gif' && Img::IsDataGifAnimated( $data ) ) )
				return( null );

			$img = Img::CreateFromData( $data );
			unset( $data );

			if( !$img )
			{

				return( null );
			}
		}

		$imgNew = Img::CreateCopyResample( $img,
			array( 'cx' => $cx, 'cy' => $cy ),
			array( 'x' => 0, 'y' => 0, 'cx' => $info[ 'cx' ], 'cy' => $info[ 'cy' ] ) );
		if( !$imgNew )
		{

			continue;
		}

		$imgNewCont = Img::GetData( $imgNew, $info[ 'mime' ] );
		imagedestroy( $imgNew );
		if( !$imgNewCont )
		{

			continue;
		}

		$fileType = Gen::GetFileName( $info[ 'mime' ] );

		if( Gen::GetArrField( $settImg, array( 'inlSml' ), false ) && $info[ 'mime' ] && strlen( $imgNewCont ) <= Gen::GetArrField( $settImg, array( 'inlSmlSize' ), 0 ) )
			$imgSrcAlter = _Images_ProcessSrc_InlineSmallEx( $info[ 'mime' ], $imgNewCont );
		else
		{
			if( !adkxsshiujqtfk( $ctxProcess, $settCache, array( 'img', $fileType ), $imgNewCont, $imgSrcAlter, $file ) )
				return( false );

			_Images_ProcessSrc_ConvertAll( $settImg, $imgNewCont, $file, _Images_ProcessSrcEx_FileMTime( $file ) );
		}

		Cdn_AdjustUrl( $ctxProcess, $settCdn, $imgSrcAlter, $fileType );
		Fullness_AdjustUrl( $ctxProcess, $imgSrcAlter );

		$imgSzAlternatives -> a[ ( string )$cx ] = array( 'img' => $imgSrcAlter, 'isImportant' => $isImportant );
	}

	if( $img )
		imagedestroy( $img );

	return( true );
}

function Images_ProcessSrc( &$ctxProcess, $imgSrc, $settCache, $settImg, $settCdn )
{
	if( !$imgSrc -> src )
		return( null );

	$imgSrc -> Init( $ctxProcess );
	if( !$imgSrc -> srcInfo )
		return( null );

	$fileType = strtolower( Gen::GetFileExt( (isset($imgSrc -> srcInfo[ 'srcWoArgs' ])?$imgSrc -> srcInfo[ 'srcWoArgs' ]:null) ) );

	$adjusted = Images_ProcessSrcEx( $ctxProcess, $imgSrc, $settCache, $settImg );
	if( $adjusted === false )
		return( false );

	if( Cdn_AdjustUrl( $ctxProcess, $settCdn, $imgSrc -> src, $fileType ) )
		$adjusted = true;
	if( Fullness_AdjustUrl( $ctxProcess, $imgSrc -> src, (isset($imgSrc -> srcInfo[ 'srcUrlFullness' ])?$imgSrc -> srcInfo[ 'srcUrlFullness' ]:null) ) )
		$adjusted = true;

	return( $adjusted );
}

function Images_ProcessSrcSet( &$ctxProcess, &$srcset, $settCache, $settImg, $settCdn )
{
	$apply = false;

	$srcItems = Ui::ParseSrcSetAttr( $srcset );
	foreach( $srcItems as &$srcItem )
	{
		$imgSrc = new ImgSrc( html_entity_decode( $srcItem[ 0 ] ) );

		$r = Images_ProcessSrc( $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn );
		if( $r === false )
			return( false );

		if( $r )
		{
			$srcItem[ 0 ] = $imgSrc -> src;
			$apply = true;
		}
	}

	if( !$apply )
		return( null );

	$srcset = Ui::GetSrcSetAttr( $srcItems, false );
	return( true );
}

function LazyLoad_SrcSubst( $width, $height, $exact = false )
{
	if( !$width )
		$width = 225;
	if( !$height )
		$height = $width / 3 * 2;
	return( 'data:image/svg+xml,' . rawurlencode( Ui::Tag( 'svg',

		null

		, array_merge( array( 'xmlns' => 'http://www.w3.org/2000/svg', 'viewBox' => '0 0 ' . $width . ' ' . $height ), $exact ? array( 'width' => $width, 'height' => $height ) : array() ) ) ) );

}

function _Images_ProcessItemLazy_Start( &$ctxProcess, $doc, $settImg, $item )
{
	if( $ctxProcess[ 'isAMP' ] || !Gen::GetArrField( $settImg, array( 'lazy', 'load' ), false ) )
		return( null );

	if( HtmlNd::FindUpByTag( $item, 'noscript' ) )
		return( null );

	if( Images_CheckLazyExcl( $ctxProcess, $doc, $settImg, $item ) )
		return( null );

	if( Gen::GetArrField( $settImg, array( 'lazy', 'del3rd' ), false ) )
	{
		if( $item -> getAttribute( 'loading' ) == 'lazy' )
			$item -> removeAttribute( 'loading' );

		HtmlNd::AddRemoveAttrClass( $item, array(), array( 'lazyload', 'blog-thumb-lazy-load', 'lazy-load', 'lazy', 'mfn-lazy', 'iso-lazy-load' ) );

		HtmlNd::RenameAttr( $item, 'data-src', 'src' );
		HtmlNd::RenameAttr( $item, 'data-srcset', 'srcset' );

		HtmlNd::RenameAttr( $item, 'data-orig-src', 'src' );
		HtmlNd::RenameAttr( $item, 'data-orig-srcset', 'srcset' );

		HtmlNd::RenameAttr( $item, 'data-lazy-src', 'src' );
		HtmlNd::RenameAttr( $item, 'data-lazy-srcset', 'srcset' );
	}

	return( true );
}

function _Images_ProcessItemLazy_Finish( &$ctxProcess, $doc, $settImg, $item, $imgSrc )
{
	$src = $item -> getAttribute( 'src' );
	if( !$src )
		return( null );
	if( !$item -> getAttribute( 'srcset' ) && Ui::IsSrcAttrData( $src ) )
		return( null );

	{
		$itemCopy = $item -> cloneNode( true );
		if( !$itemCopy )
			return( false );

		$itemNoScript = $doc -> createElement( 'noscript' );
		if( !$itemNoScript )
			return( false );

		$itemNoScript -> setAttribute( 'lzl', '' );
		$itemNoScript -> appendChild( $itemCopy );
		HtmlNd::InsertAfter( $item -> parentNode, $itemNoScript, $item );
	}

	$ctxProcess[ 'lazyload' ] = true;
	HtmlNd::AddRemoveAttrClass( $item, array( 'lzl' ) );

	$srcImgDim = $imgSrc ? $imgSrc -> GetInfo() : null;

	$width = $srcImgDim && $srcImgDim[ 'cx' ] ? $srcImgDim[ 'cx' ] : 225;
	$height = $srcImgDim && $srcImgDim[ 'cy' ] ? $srcImgDim[ 'cy' ] : $width / 3 * 2;

	HtmlNd::RenameAttr( $item, 'srcset', 'data-lzl-srcset' );
	HtmlNd::RenameAttr( $item, 'sizes', 'data-lzl-sizes' );

	$item -> setAttribute( 'data-lzl-src', $src );

	$item -> setAttribute( 'src', LazyLoad_SrcSubst( $width, $height, true ) );

	{
		for( $p = $item -> parentNode; $p && $p -> nodeType == XML_ELEMENT_NODE; $p = $p -> parentNode )
		{
			if( !in_array( 'woocommerce-product-gallery', Ui::ParseClassAttr( $p -> getAttribute( 'class' ) ) ) )
				continue;

			$styles = Ui::ParseStyleAttr( $p -> getAttribute( 'style' ) );
			$styles[ 'opacity' ] = 1;

			$p -> setAttribute( 'style', Ui::GetStyleAttr( $styles ) );
			break;
		}
	}

	return( true );
}

function Images_ProcessItemLazyBg( &$ctxProcess, $doc, $settImg, $item, $imgSrc )
{
	if( HtmlNd::FindUpByTag( $item, 'noscript' ) )
		return( false );

	if( $item -> hasAttribute( 'data-bg' ) )
		return( false );

	if( Images_CheckLazyExcl( $ctxProcess, $doc, $settImg, $item ) )
		return( false );

	$ctxProcess[ 'lazyload' ] = true;
	HtmlNd::AddRemoveAttrClass( $item, array( 'lzl' ) );

	$item -> setAttribute( 'data-lzl-bg', $imgSrc -> src );

	$srcImgDim = $imgSrc -> GetInfo();
	$width = $srcImgDim && $srcImgDim[ 'cx' ] ? $srcImgDim[ 'cx' ] : 225;
	$height = $srcImgDim && $srcImgDim[ 'cy' ] ? $srcImgDim[ 'cy' ] : $width / 3 * 2;
	$imgSrc -> src = LazyLoad_SrcSubst( $width, $height, true );

	return( true );
}

function Images_CheckLazyExcl( &$ctxProcess, $doc, $settImg, $item )
{
	$lazyExclItems = &$ctxProcess[ 'lazyExclItems' ];
	if( $lazyExclItems === null )
	{
		$lazyExclItems = array();

		$excls = Gen::GetArrField( $settImg, array( 'lazy', 'excl' ), array() );
		if( $excls )
		{
			$xpath = new \DOMXPath( $doc );

			foreach( $excls as $exclItemPath )
				foreach( HtmlNd::ChildrenAsArr( @$xpath -> query( $exclItemPath, $ctxProcess[ 'ndHtml' ] ) ) as $itemExcl )
					$lazyExclItems[] = $itemExcl;
		}
	}

	return( in_array( $item, $lazyExclItems, true ) );
}

function Images_CheckSzAdaptExcl( &$ctxProcess, $doc, $settImg, $item )
{
	$excls = Gen::GetArrField( $settImg, array( 'szAdaptExcl' ), array() );
	if( !$excls )
		return( false );

	$ctxSzAdaptExcl = (isset($ctxProcess[ 'ctxSzAdaptExcl' ])?$ctxProcess[ 'ctxSzAdaptExcl' ]:null);
	if( !$ctxSzAdaptExcl )
		$ctxProcess[ 'ctxSzAdaptExcl' ] = $ctxSzAdaptExcl = new AnyObj();

	$itemRoot = $ctxProcess[ 'ndHtml' ];
	if( is_string( $item ) )
	{
		if( !(isset($ctxSzAdaptExcl -> itemTmp)?$ctxSzAdaptExcl -> itemTmp:null) )
		{
			$ctxSzAdaptExcl -> itemTmpCont = $doc -> createElement( 'root' );
			$ctxSzAdaptExcl -> itemTmpCont -> appendChild( $ctxSzAdaptExcl -> itemTmp = $doc -> createElement( 'style' ) );
		}

		HtmlNd::SetValFromContent( $ctxSzAdaptExcl -> itemTmp, $item );
		$item = $ctxSzAdaptExcl -> itemTmp;
		$itemRoot = $ctxSzAdaptExcl -> itemTmpCont;
	}

	$xpath = new \DOMXPath( $doc );

	$found = false;
	foreach( $excls as $exclItem )
	{
		$items = HtmlNd::ChildrenAsArr( @$xpath -> query( $exclItem, $itemRoot ) );
		if( in_array( $item, $items, true ) )
		{
			$found = true;
			break;
		}
	}

	HtmlNd::SetValFromContent( (isset($ctxSzAdaptExcl -> itemTmp)?$ctxSzAdaptExcl -> itemTmp:null), '' );
	return( $found );
}

function Images_Process( &$ctxProcess, $doc, $settCache, $settImg, $settCdn )
{
	if( !( Gen::GetArrField( $settImg, array( 'srcAddLm' ), false ) || Gen::GetArrField( $settImg, array( 'inlSml' ), false ) || Gen::GetArrField( $settImg, array( 'lazy', 'setSize' ), false ) || Gen::GetArrField( $settImg, array( 'lazy', 'load' ), false ) || Gen::GetArrField( $settCdn, array( 'enable' ), false ) ) )
		return( true );

	$items = HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'img' ) );
	if( $ctxProcess[ 'isAMP' ] )
		$items = array_merge( $items, HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'amp-img' ) ) );
	foreach( $items as $item )
	{
		if( ContentProcess_IsAborted( $settCache ) ) return( true );

		if( !$item -> attributes )
			continue;

		$inlinedSize = 0;
		$imgSrc = null;

		$bLazy = _Images_ProcessItemLazy_Start( $ctxProcess, $doc, $settImg, $item );
		if( $bLazy === false )
			return( false );

		$attr = $item -> attributes -> getNamedItem( 'src' );
		if( $attr )
		{
			$imgSrc = new ImgSrc( html_entity_decode( $attr -> nodeValue ) );

			$r = Images_ProcessSrc( $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn );
			if( $r === false )
				return( false );

			if( $r )
			{
				$attr -> nodeValue = htmlspecialchars( $imgSrc -> src );

				if( Gen::GetArrField( $settImg, array( 'lazy', 'setSize' ), false ) && !$item -> hasAttribute( 'width' ) && !$item -> hasAttribute( 'height' ) && ( $srcImgDim = $imgSrc -> GetInfo() ) )
				{
					if( $srcImgDim[ 'cx' ] !== null && $srcImgDim[ 'cy' ] !== null )
					{
						$item -> setAttribute( 'width', ( int )round( ( float )$srcImgDim[ 'cx' ] ) );
						$item -> setAttribute( 'height', ( int )round( ( float )$srcImgDim[ 'cy' ] ) );
					}
				}
			}

			if( Ui::IsSrcAttrData( $imgSrc -> src ) )
				$inlinedSize = strlen( $imgSrc -> src );
		}

		if( $attrSrcSet = $item -> attributes -> getNamedItem( 'srcset' ) )
		{
			$srcset = $attrSrcSet -> nodeValue;

			$r = Images_ProcessSrcSet( $ctxProcess, $srcset, $settCache, $settImg, $settCdn );
			if( $r === false )
				return( false );

			if( $r )
				$attrSrcSet -> nodeValue = htmlspecialchars( $srcset );

			if( stripos( $srcset, 'data:' ) !== false )
				$inlinedSize = strlen( $srcset );
		}

		if( $inlinedSize >= 2048 && (isset($ctxProcess[ 'chunksEnabled' ])?$ctxProcess[ 'chunksEnabled' ]:null) )
			ContentMarkSeparate( $item );

		if( $bLazy && _Images_ProcessItemLazy_Finish( $ctxProcess, $doc, $settImg, $item, $imgSrc ) === false )
			return( false );
	}

	foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'picture' ) ) as $itemPict )
	{
		if( ContentProcess_IsAborted( $settCache ) ) return( true );

		foreach( $itemPict -> childNodes as $item )
		{
			if( $item -> nodeName != 'source' )
				continue;

			if( !$item -> attributes )
				continue;

			$attrSrcSet = $item -> attributes -> getNamedItem( 'srcset' );
			if( $attrSrcSet )
			{
				$srcset = $attrSrcSet -> nodeValue;

				$r = Images_ProcessSrcSet( $ctxProcess, $srcset, $settCache, $settImg, $settCdn );
				if( $r === false )
					return( false );

				if( $r )
					$attrSrcSet -> nodeValue = htmlspecialchars( $srcset );
			}

			if( Gen::GetArrField( $settImg, array( 'lazy', 'load' ), false ) )
			{

				{
					$itemCopy = $item -> cloneNode( true );
					if( !$itemCopy )
						return( false );

					$itemNoScript = $doc -> createElement( 'noscript' );
					if( !$itemNoScript )
						return( false );

					$itemNoScript -> setAttribute( 'lzl', '' );
					$itemNoScript -> appendChild( $itemCopy );
					HtmlNd::InsertAfter( $item -> parentNode, $itemNoScript, $item );
				}

				$ctxProcess[ 'lazyload' ] = true;
				HtmlNd::RenameAttr( $item, 'srcset', 'data-lzl-srcset' );
			}
		}
	}

	if( Gen::GetArrField( $settImg, array( 'srcAddLm' ), false ) || Gen::GetArrField( $settCdn, array( 'enable' ), false ) )
	{
		$srcImgDim = null;

		$settImgForMeta = Gen::ArrCopy( $settImg );
		Gen::SetArrField( $settImgForMeta, array( 'inlSml' ), false );

		foreach( $ctxProcess[ 'ndHead' ] -> childNodes as $item )
		{
			if( ContentProcess_IsAborted( $settCache ) ) return( true );

			$srcAttrName = null; $src = null;
			$bImg = true;

			if( $item -> nodeName == 'meta' )
			{
				$id = $item -> getAttribute( 'property' );
				if( !$id )
					$id = $item -> getAttribute( 'name' );

				if( $id && in_array( $id, array( 'og:image', 'og:image:secure_url', 'twitter:image', 'vk:image' ) ) )
					$srcAttrName = 'content';
			}
			else if( $item -> nodeName == 'link' )
			{
				switch( $item -> getAttribute( 'rel' ) )
				{
				case 'icon':
					$srcAttrName = 'href';
					break;

				case 'preload':
					switch( $item -> getAttribute( 'as' ) )
					{
					case 'image':
						$srcAttrName = 'href';
						break;

					case 'font':
						$srcAttrName = 'href';
						$bImg = false;
						break;
					}
					break;
				}
			}

			if( !$srcAttrName )
				continue;

			$src = $item -> getAttribute( $srcAttrName );
			if( !$src )
				continue;

			if( $bImg )
			{
				$src = new ImgSrc( $src );

				$r = Images_ProcessSrc( $ctxProcess, $src, $settCache, $settImgForMeta, $settCdn );
				if( $r === false )
					return( false );
			}
			else
			{
				$r = false;
				if( $srcInfo = Ui::IsSrcAttrData( $src ) ? false : GetSrcAttrInfo( $ctxProcess, null, null, $src ) )
				{
					if( Cdn_AdjustUrl( $ctxProcess, $settCdn, $src, Gen::GetFileExt( $srcInfo[ 'srcWoArgs' ] ) ) )
						$r = true;
					if( Fullness_AdjustUrl( $ctxProcess, $src, (isset($srcInfo[ 'srcUrlFullness' ])?$srcInfo[ 'srcUrlFullness' ]:null) ) )
						$r = true;
				}
			}

			if( $r )
				$item -> setAttribute( $srcAttrName, is_string( $src ) ? $src : $src -> src );
		}
	}

	return( true );
}

