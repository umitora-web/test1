<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

function GetYouTubeVideoAttrs( $id )
{
	$res = array( 'width' => 16, 'height' => 9 );

	$data = GetExtContents( 'https://www.youtube.com/watch?v=' . $id, $contMimeType, false );
	if( !$data )
		return( false );

	$metas = GetContentsMetaProps( $data );

	$w = (isset($metas[ 'og:video:width' ])?$metas[ 'og:video:width' ]:null);
	$h = (isset($metas[ 'og:video:height' ])?$metas[ 'og:video:height' ]:null);
	if( $w && $h )
	{
		$res[ 'width' ] = $w;
		$res[ 'height' ] = $h;
	}

	$res[ 'title' ] = (isset($metas[ 'title' ])?$metas[ 'title' ]:null);

	return( $res );
}

function GetYouTubeVideoThumbUrl( $ctxProcess, $id, $args = null )
{
	if( $id == 'videoseries' )
	{
		$data = GetExtContents( Net::UrlAddArgs( 'https://www.youtube.com/embed/videoseries', $args ), $contMimeType, false );
		if( !$data )
			return( '' );

		$data = GetContentsRawHead( $data );
		if( !$data )
			return( '' );

		$id = null;
		if( @preg_match( '@<link\\srel=["\']canonical["\']\\shref=["\']([^"\']*)@', $data, $m ) )
			$id = GetVideoThumbIdFromUrl( $ctxProcess, $m[ 1 ] );
	}

	if( !$id )
		return( '' );

	$res = 'https://i.ytimg.com/vi/' . $id . '/sddefault.jpg';

	$data = GetExtContents( 'https://www.youtube.com/watch?v=' . $id, $contMimeType, false );
	if( $data )
	{
		$metas = GetContentsMetaProps( $data );
		if( (isset($metas[ 'og:image' ])?$metas[ 'og:image' ]:null) )
			$res = $metas[ 'og:image' ];
	}

	return( $res );
}

function GetYouTubeVideoCtlContent()
{
	return( Ui::Tag( 'span', '<svg height="100%" version="1.1" viewBox="0 0 68 48" width="100%"><path class="ytp-large-play-button-bg" d="M66.52,7.74c-0.78-2.93-2.49-5.41-5.42-6.19C55.79,.13,34,0,34,0S12.21,.13,6.9,1.55 C3.97,2.33,2.27,4.81,1.48,7.74C0.06,13.05,0,24,0,24s0.06,10.95,1.48,16.26c0.78,2.93,2.49,5.41,5.42,6.19 C12.21,47.87,34,48,34,48s21.79-0.13,27.1-1.55c2.93-0.78,4.64-3.26,5.42-6.19C67.94,34.95,68,24,68,24S67.94,13.05,66.52,7.74z" fill="#f00"></path><path d="M 45,24 27,14 27,34" fill="#fff"></path></svg>', array( 'style' => array( 'position' => 'absolute', 'left' => '50%', 'top' => '50%', 'width' => '68px', 'height' => '48px', 'margin-left' => '-34px', 'margin-top' => '-24px', 'pointer-events' => 'none' ) ) ) );
}

function ApplyYouTubeVideoPlaceholder( $item, &$src, $lazyVideoCurId, $urlThumb, $sz = null )
{
	$data = '<!DOCTYPE html>' . Ui::Tag( 'html', Ui::Tag( 'body',
			Ui::Tag( 'a', null, array( 'href' => '#', 'onclick' => 'window.parent.postMessage(\'seraph-accel-lzl-v:' . $lazyVideoCurId . '\',\'*\');', 'style' => array( 'position' => 'absolute', 'width' => '100%', 'height' => '100%', 'background' => 'center / cover no-repeat url(' . $urlThumb . ')' ) ) ) .
			GetYouTubeVideoCtlContent()
			, array( 'style' => array( 'margin' => 0 ) )
		) );

	$item -> setAttribute( 'lzl-v', '' );
	$item -> setAttribute( 'data-id', $lazyVideoCurId );
	$item -> setAttribute( 'data-lzl-v-src', add_query_arg( array( 'autoplay' => 1, 'enablejsapi' => 1 ), $src ) );
	$item -> setAttribute( 'data-lzl-v-svc', 'youtube' );
	$item -> setAttribute( 'allow', 'autoplay' );
	$item -> setAttribute( 'src', $src = 'data:text/html,' . rawurlencode( $data ) );
}

function ApplyVimeoVideoPlaceholder( $item, &$src, $lazyVideoCurId, $urlThumb, $sz = null )
{
	$thumbStyles = $urlThumb ?
		array( 'background' => 'center / cover no-repeat url(\'' . $urlThumb . ( $sz ? ( '?mw=' . $sz[ 0 ] . '&mh=' . $sz[ 1 ] ) : '' ) . '\')' ) :
		array( 'background-color' => '#000' );

	$data = '<!DOCTYPE html>' . Ui::Tag( 'html', Ui::Tag( 'body',
			Ui::Tag( 'a', null, array( 'href' => '#', 'onclick' => 'window.parent.postMessage(\'seraph-accel-lzl-v:' . $lazyVideoCurId . '\',\'*\');', 'style' => array_merge( array( 'position' => 'absolute', 'width' => '100%', 'height' => '100%' ), $thumbStyles ) ) ) .
			Ui::Tag( 'span', '<svg height="100%" version="1.1" viewBox="0 0 66 40" width="100%"><path d="M 45,21 27,11 27,31" fill="#fff"></path></svg>', array( 'style' => array( 'background' => 'rgb(0,173,239)', 'border-radius' => '5px;', 'position' => 'absolute', 'left' => '50%', 'top' => '50%', 'width' => '66px', 'height' => '40px', 'margin-left' => '-33px', 'margin-top' => '-20px', 'pointer-events' => 'none' ) ) )
			, array( 'style' => array( 'margin' => 0 ) )
		) );

	$item -> setAttribute( 'lzl-v', '' );
	$item -> setAttribute( 'data-id', $lazyVideoCurId );
	$item -> setAttribute( 'data-lzl-v-src', add_query_arg( array( 'autoplay' => 1 ), $src ) );
	$item -> setAttribute( 'allow', 'autoplay' );
	$item -> setAttribute( 'src', $src = 'data:text/html,' . rawurlencode( $data ) );
}

function GetVimeoVideoThumbUrl( $id )
{

	if( !$id )
		return( null );

	if( $data = GetExtContents( 'https://player.vimeo.com/video/' . $id, $contMimeType ) )
	{

		$nPos = strpos( $data, '"base":"https://i.vimeocdn.com/video/' );
		if( $nPos !== false )
		{
			$nPos += 37;

			$nPosEnd = strpos( $data, '"', $nPos );
			if( $nPosEnd === false )
				return( null );

			return( 'https://i.vimeocdn.com/video/' . substr( $data, $nPos, $nPosEnd - $nPos ) . '.jpg' );
		}
	}

	if( $data = GetExtContents( 'https://vimeo.com/' . $id, $contMimeType ) )
	{

		$metas = GetContentsMetaProps( $data );
		if( $urlComps = Net::UrlParse( (isset($metas[ 'og:image' ])?$metas[ 'og:image' ]:null), Net::URLPARSE_F_QUERY ) )
			return( Gen::GetArrField( $urlComps, array( 'query', 'src0' ) ) );
	}

	return( 'https://vumbnail.com/' . $id . '.jpg' );
}

function GetVideoThumbIdFromUrl( $ctxProcess, $url, &$svc = null )
{
	$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, $url );

	if( ( $nPos = stripos( $srcInfo[ 'srcWoArgs' ], '.youtube.com/embed/' ) ) !== false )
	{
		$svc = 'youtube';
		return( substr( $srcInfo[ 'srcWoArgs' ], $nPos + 19 ) );
	}
	if( ( $nPos = stripos( $srcInfo[ 'srcWoArgs' ], '/youtu.be/' ) ) !== false )
	{
		$svc = 'youtube';
		return( substr( $srcInfo[ 'srcWoArgs' ], $nPos + 10 ) );
	}
	if( ( $nPos = stripos( $srcInfo[ 'srcWoArgs' ], 'youtube.com/watch' ) ) !== false )
	{
		$svc = 'youtube';
		return( (isset($srcInfo[ 'args' ][ 'v' ])?$srcInfo[ 'args' ][ 'v' ]:null) );
	}

	if( ( $nPos = stripos( $srcInfo[ 'srcWoArgs' ], 'player.vimeo.com/video/' ) ) !== false )
	{
		$svc = 'vimeo';
		return( substr( $srcInfo[ 'srcWoArgs' ], $nPos + 23 ) );
	}
	if( ( $nPos = stripos( $srcInfo[ 'srcWoArgs' ], '/vimeo.com/' ) ) !== false )
	{
		$svc = 'vimeo';
		return( substr( $srcInfo[ 'srcWoArgs' ], $nPos + 11 ) );
	}

	return( null );
}

function GetVideoThumbUrlFromUrl( $ctxProcess, $url, &$id = null )
{
	$id = GetVideoThumbIdFromUrl( $ctxProcess, $url, $svc );

	switch( $svc )
	{
	case 'youtube':			return( GetYouTubeVideoThumbUrl( $ctxProcess, $id ) );
	case 'vimeo':			return( GetVimeoVideoThumbUrl( $id ) );
	}

	return( null );
}

function Frames_Process( &$ctxProcess, $doc, $settCache, $settFrm, $settImg, $settCdn, $settJs )
{
	if( !( Gen::GetArrField( $settFrm, array( 'lazy', 'enable' ), false ) ) )
	    return( true );

	$yt = Gen::GetArrField( $settFrm, array( 'lazy', 'yt' ), false );
	$vm = Gen::GetArrField( $settFrm, array( 'lazy', 'vm' ), false );

	$body = $ctxProcess[ 'ndBody' ];

	$isImgLazy = Gen::GetArrField( $settImg, array( 'lazy', 'load' ), false );

	$lazyVideoCurId = 0;

	foreach( HtmlNd::ChildrenAsArr( $doc -> getElementsByTagName( 'iframe' ) ) as $item )
	{
		if( ContentProcess_IsAborted( $settCache ) ) return( true );

		if( HtmlNd::FindUpByTag( $item, 'noscript' ) )
			continue;

		if( Scripts_IsElemAs( $ctxProcess, $doc, $settJs, $item ) )
			continue;

		$src = $item -> getAttribute( 'src' );
		if( !$src || $src == 'about:blank' )
			continue;

		ContentMarkSeparate( $item, false );

		if( (isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) )
			continue;

		$srcInfo = GetSrcAttrInfo( $ctxProcess, null, null, $src );
		Fullness_AdjustUrl( $ctxProcess, $src, (isset($srcInfo[ 'srcUrlFullness' ])?$srcInfo[ 'srcUrlFullness' ]:null) );
		$item -> setAttribute( 'src', $src );

		$sz = array( $item -> getAttribute( 'width' ), $item -> getAttribute( 'height' ) );
		if( !is_numeric( substr( $sz[ 0 ], -1 ) ) || !is_numeric( substr( $sz[ 1 ], -1 ) ) )
			$sz = null;

		$isVideo = false;
		$id = GetVideoThumbIdFromUrl( $ctxProcess, $srcInfo[ 'url' ], $svc );
		if( $svc == 'youtube' && $yt )
		{
			if( !$id )
				continue;

			$isVideo = true;
			$lazyVideoCurId++;

			$urlThumb = GetYouTubeVideoThumbUrl( $ctxProcess, $id, $srcInfo[ 'args' ] );
			ApplyYouTubeVideoPlaceholder( $item, $src, $lazyVideoCurId, $urlThumb, $sz );

		}
		else if( $svc == 'vimeo' && $vm )
		{
			if( !$id )
				continue;

			$isVideo = true;
			$lazyVideoCurId++;

			$urlThumb = GetVimeoVideoThumbUrl( $id );
			ApplyVimeoVideoPlaceholder( $item, $src, $lazyVideoCurId, $urlThumb, $sz );
		}

		if( $isImgLazy && !Images_CheckLazyExcl( $ctxProcess, $doc, $settImg, $item ) )
		{
			$ctxProcess[ 'lazyload' ] = true;
			HtmlNd::AddRemoveAttrClass( $item, 'lzl' );
			$item -> setAttribute( 'data-lzl-src', $src );
			$item -> setAttribute( 'src', LazyLoad_SrcSubst( $sz ? $sz[ 0 ] : null, $sz ? $sz[ 1 ] : null ) );
		}

		if( $isVideo )
		{
			$item -> setAttribute( 'allowtransparency', 'true' );
			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'background' => '#000' ) ) ) );
		}
	}

	$xpath = null;

	if( Gen::GetArrField( $settFrm, array( 'lazy', 'elmntrBg' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-element ")][@data-settings]', $ctxProcess[ 'ndHtml' ] ) as $item )
		{
			$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
			if( Gen::GetArrField( $dataSett, array( 'background_background' ) ) == 'video' && ( $urlVideo = Gen::GetArrField( $dataSett, array( 'background_video_link' ) ) ) )
			{
				$container = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(@class,"elementor-background-video-container")][1]', $item ) );
				if( !$container )
					continue;

				if( $urlVideoThumb = GetVideoThumbUrlFromUrl( $ctxProcess, $urlVideo ) )
				{
					$container -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $container -> getAttribute( 'style' ) ), array( 'background' => 'center / cover no-repeat url(' . $urlVideoThumb . ')!important' ) ) ) );

					if( $isImgLazy && !Images_CheckLazyExcl( $ctxProcess, $doc, $settImg, $container ) )
					{
						$ctxProcess[ 'lazyload' ] = true;
						HtmlNd::AddRemoveAttrClass( $container, array( 'lzl' ) );
					}
				}
				else if( $itemVideo = HtmlNd::FirstOfChildren( $xpath -> query( './video[1]', $container ) ) )
				{
					$itemVideo -> setAttribute( 'src', $urlVideo );
					$itemVideo -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemVideo -> getAttribute( 'style' ) ), array( 'height' => '100%' ) ) ) );
				}
			}
		}
	}

	$bYouTubeFeedPlay = false;
	if( Gen::GetArrField( $settFrm, array( 'lazy', 'youTubeFeed' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/a[contains(concat(" ",normalize-space(@class)," ")," sby_video_thumbnail ")]', $ctxProcess[ 'ndHtml' ] ) as $item )
		{
			$id = $item -> getAttribute( 'data-video-id' );
			if( !$id )
				continue;

			$urlVideoThumbnail = $item -> getAttribute( 'data-full-res' );

			if( !$urlVideoThumbnail )
				continue;

			ContentMarkSeparate( $item -> parentNode, false );
			if( (isset($ctxProcess[ 'compatView' ])?$ctxProcess[ 'compatView' ]:null) )
				continue;

			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'background-image' => null, 'background' => 'center / cover no-repeat url(' . $urlVideoThumbnail . ')' ) ) ) );

			if( !HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," sby_play_btn ")]', $item ) ) )
			{
				$itemCtl = HtmlNd::Parse( GetYouTubeVideoCtlContent() );
				if( $itemCtl && $itemCtl -> firstChild )
					if( $itemCtl = $doc -> importNode( $itemCtl -> firstChild, true ) )
					{
						$item -> appendChild( $itemCtl );
						$item -> setAttribute( 'href', '#' );
						$item -> removeAttribute( 'target' );
						$item -> setAttribute( 'onclick', 'seraph_accel_youTubeFeedPlayVideo(this);return false' );
						$item -> setAttribute( 'data-lzl-clk-no', '1' );
						$bYouTubeFeedPlay = true;
					}
			}

			if( $itemPlayerContainer = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," sby_player_wrap ")]', $item -> parentNode ) ) )
			{

			}
		}
	}

	if( $lazyVideoCurId || $bYouTubeFeedPlay )
	{

		$item = $doc -> createElement( 'script' );
		if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
			$item -> setAttribute( 'type', 'text/javascript' );
		$item -> setAttribute( 'seraph-accel-crit', '1' );
		$item -> nodeValue = htmlspecialchars(

			"(function(c,d){function e(a,b){(function(a){if(c.YT)a();else{var b=d.createElement(\"script\");b.type=\"text/javascript\";b.src=\"https://www.youtube.com/iframe_api\";b.onload=a;d.head.appendChild(b)}})(function(){c.YT.ready(function(){new c.YT.Player(a,{events:{onReady:function(a){a.target.playVideo()}}});b&&\"string\"==typeof a.src&&(a.src=a.src.replace(\"autoplay=0\",\"autoplay=1\"))})})}c.addEventListener(\"message\",function(a){if(\"string\"==typeof a.data){a=a.data.split(\":\");var b=a[1];a=a[0];if(\"seraph-accel-lzl-v\"==\na&&(a=d.querySelectorAll('iframe[lzl-v][data-id=\"'+b+'\"]'),a.length)){b=0;if(1<a.length){for(;b<a.length;b++){var c=a[b];if(c.offsetWidth||c.offsetHeight||c.getClientRects().length)break}if(b==a.length)return}a=a[b];a.src=a.getAttribute(\"data-lzl-v-src\");\"youtube\"==a.getAttribute(\"data-lzl-v-svc\")&&e(a)}}},!1);c.seraph_accel_youTubeFeedPlayVideo=function(a){a.setAttribute(\"onclick\",\"return false\")}})(window,document);\n"
		);
		$body -> appendChild( $item );

		ContentMarkSeparate( $item, false );
	}

	return( true );
}

function ContParts_Process( &$ctxProcess, $doc, $settCache, $settCp, $settImg, $settCdn, $jsNotCritsDelayTimeout )
{

	$xpath = null;

	if( !$jsNotCritsDelayTimeout )
	    return( true );

	if( Gen::GetArrField( $settCp, array( 'sldBdt' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[@data-bdt-slideshow]|.//*[@bdt-slideshow]' ) as $item )
		{
			$dataSett = $item -> getAttribute( 'data-bdt-slideshow' );
			if( !$dataSett )
				$dataSett = $item -> getAttribute( 'bdt-slideshow' );

			$dataSett = @json_decode( $dataSett, true );
			$minHeight = Gen::GetArrField( $dataSett, array( 'min-height' ) );
			HtmlNd::AddRemoveAttrClass( $item, array( 'bdt-slideshow' ) );

			if( $itemSlides = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," bdt-slideshow-items ")][1]', $item ) ) )
			{
				if( $minHeight )
					$itemSlides -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemSlides -> getAttribute( 'style' ) ), array( 'min-height' => '' . $minHeight . 'px' ) ) ) );

				if( $itemFirstSlide = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," bdt-slideshow-item ")][1]', $itemSlides ) ) )
				{
					HtmlNd::AddRemoveAttrClass( $itemFirstSlide, array( 'bdt-active' ) );
					if( $itemSlideCoverBgCont = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," bdt-position-cover ")][1]', $itemFirstSlide ) ) )
					{
						if( $itemSlideCoverBg = HtmlNd::FirstOfChildren( $xpath -> query( './img[@bdt-cover][1]', $itemSlideCoverBgCont ) ) )
						{
							$itemSlideCoverBgCont -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemSlideCoverBg -> getAttribute( 'style' ) ), array( 'background' => 'center / cover no-repeat url(' . $itemSlideCoverBg -> getAttribute( 'src' ) . ')' ) ) ) );
							$itemSlideCoverBg -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemSlideCoverBg -> getAttribute( 'style' ) ), array( 'visibility' => 'hidden' ) ) ) );
						}
					}
				}
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'swBdt' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," bdt-switcher-item-content-inner ")][not(preceding-sibling::*)]' ) as $item )
			HtmlNd::AddRemoveAttrClass( $item, array( 'bdt-active' ) );
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrBgSldshw' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-element ")][contains(@data-settings,"background_slideshow_gallery")]' ) as $item )
		{
			$urlFirstImg = Gen::GetArrField( @json_decode( $item -> getAttribute( 'data-settings' ), true ), array( 'background_slideshow_gallery', 0, 'url' ) );
			if( !$urlFirstImg )
				continue;

			$dataId = $item -> getAttribute( 'data-id' );
			if( !$dataId )
				continue;

			$cssSel = '.elementor-element-' . $dataId;
			if( in_array( 'elementor-invisible', HtmlNd::GetAttrClass( $item ) ) )
				$cssSel .= '.elementor-invisible';

			$itemStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemStyle, $cssSel . '{background: center / cover no-repeat url(' . $urlFirstImg . ')!important;}' );
			$item -> parentNode -> insertBefore( $itemStyle, $item );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'prtThSkel' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," theme-porto ")]//*[contains(concat(" ",normalize-space(@class)," ")," skeleton-loading ")]' ) as $item )
		{
			if( $itemTmp = HtmlNd::FirstOfChildren( $xpath -> query( './' . $item -> nodeName . '[contains(concat(" ",normalize-space(@class)," ")," skeleton-body ")][1]', $item -> parentNode ) ) )
				$item -> parentNode -> removeChild( $itemTmp );

			if( $itemTpl = HtmlNd::FirstOfChildren( $xpath -> query( './script[@type="text/template"][1]', $item ) ) )
			{
				if( $itemTmp = HtmlNd::Parse( @json_decode( trim( $itemTpl -> nodeValue ) ), LIBXML_NONET ) )
				{
					$item -> removeChild( $itemTpl );
					if( $itemTmp = $doc -> importNode( $itemTmp, true ) )
						HtmlNd::MoveChildren( $item, $itemTmp );
				}

				HtmlNd::AddRemoveAttrClass( $item, array(), array( 'skeleton-loading' ) );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'fltsmThBgFill' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/body[contains(concat(" ",normalize-space(@class)," ")," flatsome ")]//*[contains(concat(" ",normalize-space(@class)," ")," bg ")][contains(concat(" ",normalize-space(@class)," ")," fill ")][contains(concat(" ",normalize-space(@class)," ")," bg-fill ")]' ) as $item )
			HtmlNd::AddRemoveAttrClass( $item, array( 'bg-loaded' ) );
	}

	if( Gen::GetArrField( $settCp, array( 'ukSldshw' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," uk-slideshow-items ")][@uk-height-viewport]' ) as $item )
		{
			$props = Ui::ParseStyleAttr( $item -> getAttribute( 'uk-height-viewport' ) );
			if( !$props )
				continue;

			if( isset( $props[ 'minHeight' ] ) )
				$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'min-height' => $props[ 'minHeight' ] . 'px' ) ) ) );

			if( $itemFirstSlide = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," el-item ")][1]', $item ) ) )
				HtmlNd::AddRemoveAttrClass( $itemFirstSlide, array( 'uk-active' ) );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'sldN2Ss' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," n2-section-smartslider ")]' ) as $item )
		{
			$tplApplied = false;
			foreach( $xpath -> query( './/template[@data-loading-type="afterOnLoad"]', $item ) as $itemTpl )
			{
				HtmlNd::MoveChildren( $itemTpl -> parentNode, $itemTpl );
				$itemTpl -> parentNode -> removeChild( $itemTpl );
				$tplApplied = true;
			}

			if( $tplApplied )
				$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'height' => null ) ) ) );
		}

		$bResponsiveScript = false;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," n2-ss-slider ")]' ) as $itemSld )
		{

			if( $itemBulletTpl = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," n2-bullet ")][1]', $itemSld ) ) )
			{
				$itemBulletTpl -> removeAttribute( 'style' );

				$i = 0;
				foreach( $xpath -> query( './/*[@data-slide-public-id]', $itemSld ) as $item )
				{
					$itemBullet = $itemBulletTpl -> cloneNode( true );
					$itemBulletCont = $doc -> createElement( 'div' );
					$itemBulletCont -> appendChild( $itemBullet );
					$itemBulletTpl -> parentNode -> appendChild( $itemBulletCont );

					if( $i === 0 )
						HtmlNd::AddRemoveAttrClass( $itemBullet, array( 'n2-active' ) );

					$i++;
				}

				$itemBulletTpl -> parentNode -> removeChild( $itemBulletTpl );
			}

			$bResponsive = false;
			$items = HtmlNd::ChildrenAsArr( $xpath -> query( './/*[@data-slide-public-id="1"]//*[contains(concat(" ",normalize-space(@class)," ")," n2-ss-layer ")][contains(concat(" ",normalize-space(@class)," ")," n-uc-")]', $itemSld ) );

			$itemsNeedClone = array();
			foreach( $items as $item )
			{
				$idParent = $item -> getAttribute( 'data-parentid' );
				if( !$idParent )
					continue;

				$itemParent = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@id="' . $idParent . '"]', $itemSld ) );
				if( !$itemParent || $itemParent -> parentNode !== $item -> parentNode )
					continue;

				$itemsNeedClone[] = $itemParent;
				$itemsNeedClone[] = $item;
			}

			$fnGetClone = function( $fnGetClone, $xpath, $itemSld, $item )
			{
				$idParent = $item -> getAttribute( 'data-parentid' );
				if( $idParent )
				{
					$itemParent = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@data-id-ex="' . $idParent . '"]', $itemSld ) );
					if( !$itemParent )
						if( $itemParent = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@id="' . $idParent . '"]', $itemSld ) ) )
							$itemParent = $fnGetClone( $fnGetClone, $xpath, $itemSld, $itemParent );
						else
							$itemParent = $item -> parentNode;
				}
				else
					$itemParent = $item -> parentNode;

				$id = $item -> getAttribute( 'id' );
				if( $id )
					if( $itemClone = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@data-id-ex="' . $id . '"]', $itemSld ) ) )
						return( $itemClone );

				HtmlNd::AddRemoveAttrClass( $item, 'js-lzl-n-ing' );
				$itemClone = $item -> cloneNode( true );
				$itemParent -> appendChild( $itemClone );
				HtmlNd::AddRemoveAttrClass( $itemClone, 'js-lzl-ing', 'js-lzl-n-ing' );
				HtmlNd::RenameAttr( $itemClone, 'id', 'data-id-ex' );
				HtmlNd::RenameAttr( $itemClone, 'data-parentid', 'data-parentid-ex' );
				return( $itemClone );
			};

			foreach( $items as $item )
			{
				$layerSelectorEx = '';
				if( in_array( $item, $itemsNeedClone, true ) )
				{
					$item = $fnGetClone( $fnGetClone, $xpath, $itemSld, $item );
					$layerSelectorEx = '.js-lzl-ing';
				}

				$layerSelectorUnique = '';
				foreach( Ui::ParseClassAttr( $item -> getAttribute( 'class' ) ) as $class )
					if( Gen::StrStartsWith( $class, 'n-uc-' ) )
					{
						$layerSelectorUnique = '.' . $class;
						break;
					}

				$rotation = $item -> getAttribute( 'data-rotation' );
				$responsiveposition = $item -> getAttribute( 'data-responsiveposition' );
				$responsivesize = $item -> getAttribute( 'data-responsivesize' );
				$bHasParent = !!$item -> getAttribute( 'data-parentid-ex' );

				if( $responsiveposition || $responsivesize )
					$bResponsive = true;

				{
					$style = Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) );

					if( $itemSld -> getAttribute( 'data-ss-legacy-font-scale' ) && $item -> getAttribute( 'data-sstype' ) == 'layer' )
					{
						$style[ 'font-size' ] = $bHasParent ? '100%' : 'calc(100%*var(--ss-responsive-scale)*var(--ssfont-scale))';
					}

					if( $style )
						$item -> setAttribute( 'style', Ui::GetStyleAttr( $style ) );
				}

				$stylesSeparated = array( 'desktop' => array(), 'tablet' => array(), 'mobile' => array() );

				foreach( $stylesSeparated as $view => &$styleSeparated )
				{
					if( ( $v = $item -> getAttribute( 'data-' . $view . 'portraitwidth' ) ) !== null )
						$styleSeparated[ 'width' ] = is_numeric( $v ) ? ( 'calc(' . $v . 'px' . ( $responsivesize ? ' * var(--ss-responsive-scale))' : '' ) ) : ( $v == 'auto' ? '100%' : $v );
					if( ( $v = $item -> getAttribute( 'data-' . $view . 'portraitheight' ) ) !== null )
						$styleSeparated[ 'height' ] = is_numeric( $v ) ? ( 'calc(' . $v . 'px' . ( $responsivesize ? ' * var(--ss-responsive-scale))' : '' ) ) : $v;

					$left = $item -> getAttribute( 'data-' . $view . 'portraitleft' );
					$top = $item -> getAttribute( 'data-' . $view . 'portraittop' );
					$translate = array( 0, 0 );

					switch( $item -> getAttribute( 'data-' . $view . 'portraitalign' ) )
					{
					case 'center':
						$translate[ 0 ] = '-50%';
						break;

					case 'right':
						$translate[ 0 ] = '-100%';
						break;

					default:
						break;
					}
					switch( $item -> getAttribute( $bHasParent ? 'data-' . $view . 'portraitparentalign' : 'data-' . $view . 'portraitalign' ) )
					{
					case 'center':
						$styleSeparated[ 'left' ] = 'calc(50%' . ( $left !== null ? ( ' + ' . $left . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) ) : '' ) . ')';
						break;

					case 'right':
						$styleSeparated[ 'left' ] = 'calc(100%' . ( $left !== null ? ( ' + ' . $left . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) ) : '' ) . ')';
						break;

					default:
						if( $left )
							$styleSeparated[ 'left' ] = 'calc(' . $left . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) . ')';
						break;
					}

					switch( $item -> getAttribute( 'data-' . $view . 'portraitvalign' ) )
					{
					case 'middle':
						$translate[ 1 ] = '-50%';
						break;

					case 'bottom':
						$translate[ 1 ] = '-100%';
						break;

					default:
						break;
					}
					switch( $item -> getAttribute( $bHasParent ? 'data-' . $view . 'portraitparentvalign' : 'data-' . $view . 'portraitvalign' ) )
					{
					case 'middle':
						$styleSeparated[ 'top' ] = 'calc(50%' . ( $top !== null ? ( ' + ' . $top . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) ) : '' ) . ')';
						break;

					case 'bottom':
						$styleSeparated[ 'top' ] = 'calc(100%' . ( $top !== null ? ( ' + ' . $top . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) ) : '' ) . ')';
						break;

					default:
						if( $top )
							$styleSeparated[ 'top' ] = 'calc(' . $top . 'px' . ( $responsiveposition ? ' * var(--ss-responsive-scale)' : '' ) . ')';
						break;
					}

					if( $translate[ 0 ] || $translate[ 1 ] )
					{
						$styleSeparated[ 'transform' ] = 'translate(' . $translate[ 0 ] . ', ' . $translate[ 1 ] . ')';
						if( $rotation )
							$styleSeparated[ 'transform' ] .= ' rotate(' . $rotation . 'deg)';
						$styleSeparated[ 'transform' ] .= '!important';
					}
				}
				unset( $styleSeparated );

				{
					$cont = '';
					foreach( $stylesSeparated as $view => $styleSeparated )
					{
						if( !$styleSeparated )
							continue;

						if( $view == 'tablet' )
							$cont .= '@media (orientation: landscape) and (max-width: 1199px) and (min-width: 901px), (orientation: portrait) and (max-width: 1199px) and (min-width: 701px) {' . "\n";
						else if( $view == 'mobile' )
							$cont .= '@media (orientation: landscape) and (max-width: 900px), (orientation: portrait) and (max-width: 700px) {' . "\n";

						$cont .= '.n2-ss-slider:not(.n2-ss-loaded) .n2-ss-layer' . $layerSelectorEx . $layerSelectorUnique . '{' . Ui::GetStyleAttr( $styleSeparated ) . '}' . "\n";

						if( $view != 'desktop' )
							$cont .= '}' . "\n";
					}

					if( $cont )
					{
						$itemStyle = $doc -> createElement( 'style' );
						if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
							$itemStyle -> setAttribute( 'type', 'text/css' );
						HtmlNd::SetValFromContent( $itemStyle, $cont );
						$item -> parentNode -> insertBefore( $itemStyle, $item );
					}
				}
			}

			if( $bResponsive )
			{
				$maxWidth = '1200';
				if( $itemSizeLimit = HtmlNd::FirstOfChildren( $xpath -> query( './/svg[contains(concat(" ",normalize-space(@class)," ")," n2-ss-slide-limiter ")][1]', $itemSld ) ) )
				{
					$viewBox = $itemSizeLimit -> getAttribute( 'viewbox' );
					$m = array();
					if( $viewBox && preg_match( '@^\\s*\\d+\\s+\\d+\\s+(\\d+)\\s+(\\d+)\\s*$@', $viewBox, $m ) )
					{
						$maxWidth = $m[ 1 ];

					}
				}

				$itemSld -> setAttribute( 'max-width', $maxWidth );

				$bResponsiveScript = true;
			}
		}

		if( $bResponsiveScript )
		{
			$itemScript = $doc -> createElement( 'script' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemScript -> setAttribute( 'type', 'text/javascript' );
			$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
			HtmlNd::SetValFromContent( $itemScript, '
				function seraph_accel_cp_sldN2Ss_calcSizes( e )
				{
					var nScale = e.clientWidth / parseInt( e.getAttribute( "max-width" ), 10 );
					var nScaleLim = parseInt( e.getAttribute( "data-ss-legacy-font-scale" ), 10 ) ? (1+1/6) : 1;
					e.style.setProperty( "--ss-responsive-scale", nScale > nScaleLim ? nScaleLim : nScale );
				}

				(
					function( d )
					{
						function OnEvt( evt )
						{
							d.querySelectorAll( ".n2-ss-slider:not(.n2-ss-loaded)[max-width]" ).forEach( seraph_accel_cp_sldN2Ss_calcSizes );
						}

						d.addEventListener( "seraph_accel_calcSizes", OnEvt, { capture: true, passive: true } );
						d.addEventListener( "seraph_accel_beforeJsDelayLoad", function( evt ) { d.removeEventListener( "seraph_accel_calcSizes", OnEvt, { capture: true, passive: true } ); }, { capture: true, passive: true } );
					}
				)( document );
			' );
			$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
		}

	}

	if( Gen::GetArrField( $settCp, array( 'tdThumbCss' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," td-thumb-css ")][@data-type="css_image"]' ) as $item )
		{
			$imgSrc = $item -> getAttribute( 'data-img-url' );
			if( !$imgSrc )
				continue;

			$item -> removeAttribute( 'data-img-url' );
			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'background-image' => 'url("' . $imgSrc . '")' ) ) ) );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmsKitImgCmp' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		$itemsCmnStyle = null;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementskit-image-comparison ")]' ) as $item )
		{
			$offs = $item -> getAttribute( 'data-offset' );
			if( !$offs )
				continue;

			if( !$itemsCmnStyle )
			{
				$itemsCmnStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemsCmnStyle, '
					.image-comparison-container:not(.twentytwenty-container) {
						overflow: hidden;
						position: relative;
					}

					.image-comparison-container:not(.twentytwenty-container) > img:first-child {
						position: absolute;
						object-fit: cover;
						object-position: 0 0;
					}

					.twentytwenty-horizontal .image-comparison-container:not(.twentytwenty-container) > img:first-child {
						width: calc(var(--data-offset) * 100%);
						height: 100%;
						border-top-right-radius: 0;
						border-bottom-right-radius: 0;
					}

					.twentytwenty-vertical .image-comparison-container:not(.twentytwenty-container) > img:first-child {
						width: 100%;
						height: calc(var(--data-offset) * 100%);
						border-bottom-left-radius: 0;
						border-bottom-right-radius: 0;
					}

					.image-comparison-container.twentytwenty-container > .twentytwenty-handle.js-lzl-ing {
						display: none;
					}

					.image-comparison-container .twentytwenty-handle.js-lzl-ing {
						box-sizing: content-box;
					}

					.twentytwenty-horizontal .image-comparison-container:not(.twentytwenty-container) .twentytwenty-handle.js-lzl-ing {
						left: calc(var(--data-offset) * 100%);
					}

					.twentytwenty-vertical .image-comparison-container:not(.twentytwenty-container) .twentytwenty-handle.js-lzl-ing {
						top: calc(var(--data-offset) * 100%);
					}
				' );
				$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
			}

			$isVert = in_array( 'image-comparison-container-vertical', HtmlNd::GetAttrClass( $item ) );

			HtmlNd::AddRemoveAttrClass( $item -> parentNode, 'twentytwenty-' . ( $isVert ? 'vertical' : 'horizontal' ) );
			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( '--data-offset' => $offs ) ) ) );

			$itemCtl = HtmlNd::Parse( '<div class="twentytwenty-handle js-lzl-ing"><span class="twentytwenty-' . ( $isVert ? 'down' : 'left' ) . '-arrow"></span><span class="twentytwenty-' . ( $isVert ? 'up' : 'right' ) . '-arrow"></span></div>' );
			if( $itemCtl && $itemCtl -> firstChild )
				if( $itemCtl = $doc -> importNode( $itemCtl -> firstChild, true ) )
					$item -> appendChild( $itemCtl );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'haCrsl' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );
		$itemsCmnStyle = null;

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ha-carousel ")][@data-settings]' ) as $item )
		{
			$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
			if( !$dataSett )
				continue;

			$dataId = $item -> getAttribute( 'data-id' );
			if( !$dataId )
				continue;

			if( !( $itemSlides = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," ha-slick--carousel ")][1]', $item ) ) ) )
				continue;

			$aSlides = HtmlNd::ChildrenAsArr( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," slick-slide ")]', $itemSlides ) );
			if( !$aSlides )
				continue;

			if( !$itemsCmnStyle )
			{
				$itemsCmnStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemsCmnStyle, '
					.ha-slick--carousel.js-lzl-ing {
						width: 400%;
						text-align: center;
						margin-left: -150%;
					}

					.ha-slick--carousel.js-lzl-ing.slick-initialized,
					.ha-slick--carousel:not(.js-lzl-ing):not(.slick-initialized) {
						display: none!important;
					}

					.ha-slick--carousel.js-lzl-ing:not(.slick-initialized),
					.ha-slick--carousel:not(.js-lzl-ing).slick-initialized {
						display: block!important;
					}

					.ha-slick--carousel.js-lzl-ing .slick-slide {
						display: inline-block;
						float: none;
					}
				' );
				$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
			}

			$aViews = array( 'slides_to_show_mobile' => 1125, 'slides_to_show_tablet' => 1230, 'slides_to_show' => null );

			$nShowMax = 0;
			foreach( $aViews as $p => $maxWidth )
			{
				if( !isset( $dataSett[ $p ] ) )
					continue;

				$nShow = ( int )$dataSett[ $p ] + 2;
				if( $nShowMax < $nShow )
					$nShowMax = $nShow;
			}

			$itemStyleCont = '';
			$maxWidthPrev = null;
			foreach( $aViews as $p => $maxWidth )
			{
				if( !isset( $dataSett[ $p ] ) )
					continue;

				$nShow = ( int )$dataSett[ $p ] + 2;

				if( $maxWidthPrev || $maxWidth )
					$itemStyleCont .= '@media ' . ( $maxWidthPrev ? ( '(min-width: ' . ( $maxWidthPrev + 1 ) . 'px)' ) : '' ) . ( $maxWidthPrev && $maxWidth ? ' and ' : '' ) . ( $maxWidth ? ( '(max-width: ' . $maxWidth . 'px)' ) : '' ) . ' {' . "\n";

				$itemStyleCont .= '.ha-carousel.elementor-element-' . $dataId . ' .ha-slick--carousel.js-lzl-ing .slick-slide {width: calc((100% / 4 - 100px) / ' . ( $nShow - 2 ) . ');}' . "\n";
				for( $i = 0; $i < ( int )( ( $nShowMax - $nShow ) / 2 ); $i++ )
				{
					$itemStyleCont .= '.ha-carousel.elementor-element-' . $dataId . ' .ha-slick--carousel.js-lzl-ing .slick-slide:nth-child(' . ( $i + 1 ) . '),';
					$itemStyleCont .= '.ha-carousel.elementor-element-' . $dataId . ' .ha-slick--carousel.js-lzl-ing .slick-slide:nth-child(' . ( $nShowMax - $i ) . '),';
				}
				if( ( !( $nShow % 2 ) && ( $nShowMax % 2 ) ) || ( ( $nShow % 2 ) && !( $nShowMax % 2 ) ) )
					$itemStyleCont .= '.ha-carousel.elementor-element-' . $dataId . ' .ha-slick--carousel.js-lzl-ing .slick-slide:nth-child(' . ( $nShowMax - ( int )( ( $nShowMax - $nShow ) / 2 ) ) . '),';

				$itemStyleCont = rtrim( $itemStyleCont, ',' );
				$itemStyleCont .= ' {display:none;}' . "\n";

				if( $maxWidthPrev || $maxWidth )
					$itemStyleCont .= '}' . "\n";

				if( $maxWidth )
					$maxWidthPrev = $maxWidth;
			}

			{
				$itemStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
				$item -> parentNode -> insertBefore( $itemStyle, $item );
			}

			$itemSlidesTmp = $itemSlides -> cloneNode( false );
			$itemSlides -> parentNode -> appendChild( $itemSlidesTmp );

			for( $i = 0; $i < ( int )( $nShowMax / 2 ); $i++ )
			{
				$idx = count( $aSlides ) - ( int )( $nShowMax / 2 ) + $i;
				if( $idx >= 0 )
					$slide = $aSlides[ $idx ] -> cloneNode( true );
				else
					$slide = $aSlides[ 0 ] -> cloneNode( true );
				$itemSlidesTmp -> appendChild( $slide );
			}

			$slide = $aSlides[ 0 ] -> cloneNode( true );

			$itemSlidesTmp -> appendChild( $slide );

			for( $i = 0; $i < ( int )( $nShowMax / 2 ); $i++ )
			{
				$idx = $i + 1;
				if( $idx < count( $aSlides ) )
					$slide = $aSlides[ $idx ] -> cloneNode( true );
				else
					$slide = $aSlides[ 0 ] -> cloneNode( true );
				$itemSlidesTmp -> appendChild( $slide );
			}

			HtmlNd::AddRemoveAttrClass( $itemSlidesTmp, array( 'slick-slider', 'js-lzl-ing' ) );

			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'overflow' => 'hidden' ) ) ) );

			if( (isset($dataSett[ 'navigation' ])?$dataSett[ 'navigation' ]:null) == 'dots' )
			{
				HtmlNd::AddRemoveAttrClass( $itemSlidesTmp, 'slick-dotted' );

				$itemCtl = HtmlNd::Parse( '<ul class="slick-dots" role="tablist"></ul>' );
				if( $itemCtl && $itemCtl -> firstChild )
					if( $itemCtl = $doc -> importNode( $itemCtl -> firstChild, true ) )
						$itemSlidesTmp -> appendChild( $itemCtl );

				$itemDot = HtmlNd::Parse( '<li class="" role="presentation"><button type="button" role="tab"></button></li>' );
				if( $itemDot && $itemDot -> firstChild )
					$itemDot = $doc -> importNode( $itemDot -> firstChild, true );

				$itemCtl -> appendChild( $itemDot );
				for( $i = 1; $i < count( $aSlides ); $i++ )
					$itemCtl -> appendChild( $itemDot -> cloneNode( true ) );
				HtmlNd::AddRemoveAttrClass( $itemDot, array( 'slick-active' ) );
			}

			{
				$itemNoScript = $doc -> createElement( 'noscript' );
				$itemNoScript -> setAttribute( 'data-lzl-bjs', '' );
				$itemSlides -> parentNode -> insertBefore( $itemNoScript, $itemSlides );
				$itemNoScript -> appendChild( $itemSlides );
				ContNoScriptItemClear( $itemNoScript );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrTabs' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-tabs ")]' ) as $item )
		{
			if( $itemFirstTabTitle = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-tabs-wrapper ")]//*[contains(concat(" ",normalize-space(@class)," ")," elementor-tab-title ")][@data-tab="1"]', $item ) ) )
			{
				HtmlNd::AddRemoveAttrClass( $itemFirstTabTitle, array( 'elementor-active' ) );
			}

			if( $itemFirstTabBody = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-tabs-content-wrapper ")]//*[contains(concat(" ",normalize-space(@class)," ")," elementor-tab-content ")][@data-tab="1"]', $item ) ) )
			{
				HtmlNd::AddRemoveAttrClass( $itemFirstTabBody, array( 'elementor-active' ) );
				$itemFirstTabBody -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemFirstTabBody -> getAttribute( 'style' ) ), array( 'display' => 'block' ) ) ) );
				$itemFirstTabBody -> removeAttribute( 'hidden' );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrAdvTabs' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," eael-advance-tabs ")]' ) as $item )
		{
			{
				$itemFirstTabTitle = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," eael-tabs-nav ")]//*[contains(concat(" ",normalize-space(@class)," ")," eael-tab-item-trigger ")][contains(concat(" ",normalize-space(@class)," ")," active-default ")]', $item ) );
				if( !$itemFirstTabTitle )
					$itemFirstTabTitle = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," eael-tabs-nav ")]//*[contains(concat(" ",normalize-space(@class)," ")," eael-tab-item-trigger ")][@data-tab="1"]', $item ) );
				if( $itemFirstTabTitle )
					HtmlNd::AddRemoveAttrClass( $itemFirstTabTitle, 'active' );
			}

			{
				$itemFirstTabBody = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," eael-tabs-content ")]//*[contains(concat(" ",normalize-space(@class)," ")," eael-tab-content-item ")][contains(concat(" ",normalize-space(@class)," ")," active-default ")]', $item ) );
				if( !$itemFirstTabBody )
					$itemFirstTabBody = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," eael-tabs-content ")]//*[contains(concat(" ",normalize-space(@class)," ")," eael-tab-content-item ")][1]', $item ) );
				if( $itemFirstTabBody )
					HtmlNd::AddRemoveAttrClass( $itemFirstTabBody, 'active' );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'phtncThmb' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/a[contains(concat(" ",normalize-space(@class)," ")," photonic-lb ")]' ) as $item )
		{
			foreach( array( 'href', 'data-download-url' ) as $attr )
			{
				if( $src = $item -> getAttribute( $attr ) )
				{
					$imgSrc = new ImgSrc( html_entity_decode( $src ) );

					$r = Images_ProcessSrc( $ctxProcess, $imgSrc, $settCache, $settImg, $settCdn );
					if( $r === false )
						return( false );

					if( $r )
						$item -> setAttribute( $attr, $imgSrc -> src );

					unset( $imgSrc );
				}
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrVids' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$widgetId = 0;

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-video ")][@data-settings]' ) as $item )
		{
			$itemVideoPlaceholder = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-video ")]', $item ) );
			if( !$itemVideoPlaceholder )
				continue;

			$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );
			switch( Gen::GetArrField( $dataSett, array( 'video_type' ) ) )
			{
			case 'youtube':
				if( $id = GetVideoThumbIdFromUrl( $ctxProcess, Gen::GetArrField( $dataSett, array( 'youtube_url' ), '' ) ) )
				{
					$metas = GetYouTubeVideoAttrs( $id );

					$autoplay = Gen::GetArrField( $dataSett, array( 'autoplay' ) ) == 'yes';
					$mute = Gen::GetArrField( $dataSett, array( 'mute' ) ) == 'yes';
					$loop = Gen::GetArrField( $dataSett, array( 'loop' ) ) == 'yes';
					$controls = Gen::GetArrField( $dataSett, array( 'controls' ) ) == 'yes';
					$start = Gen::GetArrField( $dataSett, array( 'start' ) );

					$itemVideoPlaceholder = HtmlNd::SetTag( $itemVideoPlaceholder, 'iframe' );
					$itemVideoPlaceholder -> setAttribute( 'frameborder', '0' );
					$itemVideoPlaceholder -> setAttribute( 'allowfullscreen', '1' );
					$itemVideoPlaceholder -> setAttribute( 'allow', 'accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture' . ( $autoplay ? ' autoplay;' : '' ) );
					$itemVideoPlaceholder -> setAttribute( 'src', Net::UrlAddArgs( 'https://www.youtube.com/embed/' . $id, array( 'start' => $start ? $start : null, 'autoplay' => $autoplay ? '1' : null, 'controls' => $controls ? '1' : null, 'mute' => $mute ? '1' : null, 'loop' => $loop ? '1' : null, 'rel' => '0', 'playsinline' => '0', 'modestbranding' => '0', 'enablejsapi' => '1', 'origin' => Wp::GetSiteRootUrl() ) ) );
					if( (isset($metas[ 'title' ])?$metas[ 'title' ]:null) )
						$itemVideoPlaceholder -> setAttribute( 'title', $metas[ 'title' ] );

					switch( Gen::GetArrField( $dataSett, array( 'aspect_ratio' ) ) )
					{
					case '169':
						$itemVideoPlaceholder -> setAttribute( 'width', '640' );
						$itemVideoPlaceholder -> setAttribute( 'height', '360' );
						break;

					default:
						$itemVideoPlaceholder -> setAttribute( 'width', '640' );
						$itemVideoPlaceholder -> setAttribute( 'height', '360' );
						break;
					}
				}

				break;
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'jetMobMenu' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," jet-mobile-menu ")][@data-menu-options]' ) as $item )
		{
			$dataSett = @json_decode( $item -> getAttribute( 'data-menu-options' ), true );
			$itemToggleClosedIcon = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," jet-mobile-menu__refs ")]/*[@ref="toggleClosedIcon"]', $item ) );
			if( !$itemToggleClosedIcon )
				continue;

			$toggleText = Gen::GetArrField( $dataSett, array( 'toggleText' ), '' );

			$itemToggle = HtmlNd::Parse( '<div class="jet-mobile-menu__instance jet-mobile-menu__instance--' . Gen::GetArrField( $dataSett, array( 'menuLayout' ), '' ) . '-layout ' . Gen::GetArrField( $dataSett, array( 'menuPosition' ), '' ) . '-container-position ' . Gen::GetArrField( $dataSett, array( 'togglePosition' ), '' ) . '-toggle-position js-lzl-ing"><div tabindex="1" class="jet-mobile-menu__toggle"><div class="jet-mobile-menu__toggle-icon">' . HtmlNd::DeParse( $itemToggleClosedIcon, false ) . '</div>' . ( $toggleText ? '<span class="jet-mobile-menu__toggle-text">' . $toggleText . '</span>' : '' ) . '</div></div>' );
			if( $itemToggle && $itemToggle -> firstChild )
				if( $itemToggle = $doc -> importNode( $itemToggle -> firstChild, true ) )
				{
					$item -> insertBefore( $itemToggle, $item -> firstChild );
					$adjusted = true;
				}
		}

		if( $adjusted )
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, 'body:not(.seraph-accel-js-lzl-ing) .jet-mobile-menu__instance.js-lzl-ing{display:none!important;}body.seraph-accel-js-lzl-ing .jet-mobile-menu__instance:not(.js-lzl-ing){display:none!important;}' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrNavMenu' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-nav-menu ")][@data-settings]' ) as $item )
		{
			$itemSubMenuIconTpl = null;
			foreach( $xpath -> query( './/nav[contains(concat(" ",normalize-space(@class)," ")," elementor-nav-menu--main ")]/*[contains(concat(" ",normalize-space(@class)," ")," elementor-nav-menu ")]/li[contains(concat(" ",normalize-space(@class)," ")," menu-item-has-children ")]/*[contains(concat(" ",normalize-space(@class)," ")," elementor-item ")]', $item ) as $itemMenu )
			{
				if( !$itemSubMenuIconTpl )
				{
					$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );

					$itemSubMenuIconTpl = Gen::GetArrField( $dataSett, array( 'submenu_icon', 'value' ) );
					if( strpos( $itemSubMenuIconTpl, '<' ) === false )
						$itemSubMenuIconTpl = '<i class="' . $itemSubMenuIconTpl . '"></i>';
					$itemSubMenuIconTpl = HtmlNd::Parse( '<span class="sub-arrow js-lzl-ing">' . $itemSubMenuIconTpl . '</span>' );
					if( $itemSubMenuIconTpl && $itemSubMenuIconTpl -> firstChild )
						$itemSubMenuIconTpl = $doc -> importNode( $itemSubMenuIconTpl -> firstChild, true );
				}

				$itemMenu -> appendChild( $itemSubMenuIconTpl -> cloneNode( true ) );
				$adjusted = true;
			}
		}

		if( $adjusted )
		{
			$itemsCmnStyle = $doc -> createElement( 'style' );
			if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
			HtmlNd::SetValFromContent( $itemsCmnStyle, '.elementor-widget-nav-menu ul[data-smartmenus-id] .sub-arrow.js-lzl-ing {display:none!important;}' );
			$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
		}
	}

	{
		$fnEtPbGetClassId = function( $item, $classType )
		{
			$classes = $item -> getAttribute( 'class' );
			if( !is_string( $classes ) )
				return( null );
			if( !@preg_match( '@\\s' . $classType . '(_\\d+)\\s@', ' ' . $classes . ' ', $m ) )
				return( null );
			return( $classType . $m[ 1 ] );
		};

		$aEtPbMaxSizes = array( 'phone' => 767, 'tablet' => 980 );

		if( Gen::GetArrField( $settCp, array( 'diviMvImg' ), false ) )
		{
			if( !$xpath )
				$xpath = new \DOMXPath( $doc );

			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_module ")][contains(concat(" ",normalize-space(@class)," ")," et_pb_image ")]' ) as $itemContainer )
			{
				$itemClassId = $fnEtPbGetClassId( $itemContainer, 'et_pb_image' );
				if( $itemClassId === null )
					continue;

				$item = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@data-et-multi-view]', $itemContainer ) );
				if( !$item )
					continue;

				$dataSett = @json_decode( $item -> getAttribute( 'data-et-multi-view' ), true );
				$views = Gen::GetArrField( $dataSett, array( 'schema', 'attrs' ), array() );
				unset( $views[ 'desktop' ] );
				if( !$views )
					continue;

				$item -> removeAttribute( 'data-et-multi-view' );
				$item -> setAttribute( 'data-et-multi-view-id', 'desktop' );
				$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );

				foreach( $views as $viewId => $attrs )
				{
					if( !is_array( $attrs ) )
						continue;

					$itemContView = $item -> cloneNode( true );
					$itemContView -> setAttribute( 'data-et-multi-view-id', $viewId );

					foreach( $attrs as $attrKey => $attrVal )
						$itemContView -> setAttribute( $attrKey, $attrVal );

					$item -> parentNode -> appendChild( $itemContView );
				}

				$views[ 'desktop' ] = array();

				$itemStyleCont = '';
				if( isset( $views[ 'phone' ] ) && isset( $views[ 'tablet' ] ) && isset( $views[ 'desktop' ] ) )
				{
					$itemStyleCont = '
						@media (max-width: ' . $aEtPbMaxSizes[ 'phone' ] . 'px)
						{
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view-id="phone"]
							{ display:unset!important; }
						}

						@media (min-width: ' . ( $aEtPbMaxSizes[ 'phone' ] + 1 ) . 'px) and (max-width: ' . $aEtPbMaxSizes[ 'tablet' ] . 'px)
						{
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view-id="tablet"]
							{ display:unset!important; }
						}

						@media (min-width: ' . ( $aEtPbMaxSizes[ 'tablet' ] + 1 ) . 'px)
						{
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view-id="desktop"]
							{ display:unset!important; }
						}
					';
				}
				else if( isset( $views[ 'phone' ] ) && isset( $views[ 'desktop' ] ) )
				{
					$itemStyleCont = '
						@media (max-width: ' . $aEtPbMaxSizes[ 'phone' ] . 'px)
						{
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view-id="phone"]
							{ display:unset!important; }
						}

						@media (min-width: ' . ( $aEtPbMaxSizes[ 'phone' ] + 1 ) . 'px)
						{
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view-id="desktop"]
							{ display:unset!important; }
						}
					';
				}
				else if( isset( $views[ 'tablet' ] ) && isset( $views[ 'desktop' ] ) )
				{
					$itemStyleCont = '
						@media (max-width: ' . $aEtPbMaxSizes[ 'tablet' ] . 'px)
						{
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view-id="tablet"]
							{ display:unset!important; }
						}

						@media (min-width: ' . ( $aEtPbMaxSizes[ 'tablet' ] + 1 ) . 'px)
						{
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view-id="desktop"]
							{ display:unset!important; }
						}
					';
				}

				if( $itemStyleCont )
				{

					$itemStyle = $doc -> createElement( 'style' );
					if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
						$itemStyle -> setAttribute( 'type', 'text/css' );
					HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
					$item -> parentNode -> insertBefore( $itemStyle, $item );
				}
			}
		}

		if( Gen::GetArrField( $settCp, array( 'diviMvText' ), false ) )
		{
			if( !$xpath )
				$xpath = new \DOMXPath( $doc );

			foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_module ")][contains(concat(" ",normalize-space(@class)," ")," et_pb_text ")]' ) as $itemContainer )
			{
				$itemClassId = $fnEtPbGetClassId( $itemContainer, 'et_pb_text' );
				if( $itemClassId === null )
					continue;

				$item = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@data-et-multi-view]', $itemContainer ) );
				if( !$item )
					continue;

				$dataSett = @json_decode( $item -> getAttribute( 'data-et-multi-view' ), true );
				$views = Gen::GetArrField( $dataSett, array( 'schema', 'content' ), array() );
				if( !$views )
					continue;

				HtmlNd::CleanChildren( $item );

				foreach( $views as $viewId => $cont )
				{
					if( !is_string( $cont ) )
						continue;

					if( !( $itemContView = HtmlNd::ParseAndImport( $doc, Ui::Tag( 'div', $cont ) ) ) )
						continue;

					$itemContView -> setAttribute( 'data-et-multi-view-id', $viewId );
					$itemContView -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemContView -> getAttribute( 'style' ) ), array( 'display' => 'none' ) ) ) );
					$item -> appendChild( $itemContView );
				}

				$itemStyleCont = '';
				if( isset( $views[ 'phone' ] ) && isset( $views[ 'tablet' ] ) && isset( $views[ 'desktop' ] ) )
				{
					$itemStyleCont = '
						@media (max-width: ' . $aEtPbMaxSizes[ 'phone' ] . 'px)
						{
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view]:not(.et_multi_view_swapped),
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view]:not(.et_multi_view_swapped) > [data-et-multi-view-id="phone"]
							{ display:unset!important; }
						}

						@media (min-width: ' . ( $aEtPbMaxSizes[ 'phone' ] + 1 ) . 'px) and (max-width: ' . $aEtPbMaxSizes[ 'tablet' ] . 'px)
						{
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view]:not(.et_multi_view_swapped),
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view]:not(.et_multi_view_swapped) > [data-et-multi-view-id="tablet"]
							{ display:unset!important; }
						}

						@media (min-width: ' . ( $aEtPbMaxSizes[ 'tablet' ] + 1 ) . 'px)
						{
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view]:not(.et_multi_view_swapped),
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view]:not(.et_multi_view_swapped) > [data-et-multi-view-id="desktop"]
							{ display:unset!important; }
						}
					';
				}
				else if( isset( $views[ 'phone' ] ) && isset( $views[ 'desktop' ] ) )
				{
					$itemStyleCont = '
						@media (max-width: ' . $aEtPbMaxSizes[ 'phone' ] . 'px)
						{
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view]:not(.et_multi_view_swapped),
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view]:not(.et_multi_view_swapped) > [data-et-multi-view-id="phone"]
							{ display:unset!important; }
						}

						@media (min-width: ' . ( $aEtPbMaxSizes[ 'phone' ] + 1 ) . 'px)
						{
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view]:not(.et_multi_view_swapped),
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view]:not(.et_multi_view_swapped) > [data-et-multi-view-id="desktop"]
							{ display:unset!important; }
						}
					';
				}
				else if( isset( $views[ 'tablet' ] ) && isset( $views[ 'desktop' ] ) )
				{
					$itemStyleCont = '
						@media (max-width: ' . $aEtPbMaxSizes[ 'tablet' ] . 'px)
						{
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view]:not(.et_multi_view_swapped),
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view]:not(.et_multi_view_swapped) > [data-et-multi-view-id="tablet"]
							{ display:unset!important; }
						}

						@media (min-width: ' . ( $aEtPbMaxSizes[ 'tablet' ] + 1 ) . 'px)
						{
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view]:not(.et_multi_view_swapped),
							.et_pb_module.' . $itemClassId . ' [data-et-multi-view]:not(.et_multi_view_swapped) > [data-et-multi-view-id="desktop"]
							{ display:unset!important; }
						}
					';
				}

				if( $itemStyleCont )
				{
					$itemStyle = $doc -> createElement( 'style' );
					if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
						$itemStyle -> setAttribute( 'type', 'text/css' );
					HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
					$item -> parentNode -> insertBefore( $itemStyle, $item );
				}
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'diviVidBox' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," et_pb_video_box ")]/iframe' ) as $item )
		{
			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( '--width' => $item -> getAttribute( 'width' ), '--height' => $item -> getAttribute( 'height' ) ) ) ) );
			HtmlNd::RenameAttr( $item, 'src', 'data-lzl-src' );
			$adjusted = true;
		}

		if( $adjusted )
		{
			{
				$itemsCmnStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemsCmnStyle, '
					.et_pb_video_box > iframe
					{
						height: 0;
						padding-top: calc(var(--height) / var(--width) * 100%);
					}
				' );
				$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
			}

			{
				$itemScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemScript -> setAttribute( 'type', 'text/javascript' );
				$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemScript, '
					document.addEventListener( "seraph_accel_beforeJsDelayLoad",
						function( evt )
						{
							document.querySelectorAll( ".et_pb_video_box>iframe" ).forEach( function( i ){ i.src = i.getAttribute( "data-lzl-src" ) } );
						}
						, { capture: true, passive: true }
					);
				' );
				$ctxProcess[ 'ndBody' ] -> appendChild( $itemScript );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'fusionBgVid' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$adjusted = false;
		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," fusion-background-video-wrapper ")]/iframe' ) as $item )
		{
			$itemWrapper = $item -> parentNode;

			$urlThumb = GetVimeoVideoThumbUrl( $itemWrapper -> getAttribute( 'data-vimeo-video-id' ) );
			$itemWrapper -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemWrapper -> getAttribute( 'style' ) ), array( 'opacity' => null, 'width' => '100%', 'background' => 'center / cover no-repeat url(' . $urlThumb . ')' ) ) ) );

			HtmlNd::RenameAttr( $item, 'src', 'data-lzl-src' );

			$adjusted = true;
		}

		if( $adjusted )
		{
			{
				$itemsCmnStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
				    $itemsCmnStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemsCmnStyle, '
				    .fusion-background-video-wrapper:not([style*="opacity:"]) > iframe
				    {
				        opacity: 0;
				    }
				' );
				$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
			}

			{
				$itemScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemScript -> setAttribute( 'type', 'text/javascript' );
				$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemScript, '
					document.addEventListener( "seraph_accel_beforeJsDelayLoad",
						function( evt )
						{
							document.querySelectorAll( ".fusion-background-video-wrapper>iframe" ).forEach( function( i ){ i.src = i.getAttribute( "data-lzl-src" ) } );
						}
						, { capture: true, passive: true }
					);
				' );
				$ctxProcess[ 'ndBody' ] -> appendChild( $itemScript );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'scrlSeq' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		foreach( $xpath -> query( './/scrollsequence' ) as $item )
		{
			$itemContainer = HtmlNd::FirstOfChildren( $xpath -> query( './/section[contains(concat(" ",normalize-space(@class)," ")," scrollsequence-wrap ")]', $item ) );
			if( !$itemContainer )
				continue;

			$id = $itemContainer -> getAttribute( 'id' );
			if( !$id )
				continue;

			@preg_match( '@ssq-uid-\\d+-\\d+-(\\d+)@', $id, $idCfg );
			if( !$idCfg )
				continue;
			$idCfg = $idCfg[ 1 ];

			$cfg = _Scrollsequence_GetFrontendCfg( $idCfg, HtmlNd::FirstOfChildren( $xpath -> query( './/script[contains(concat(" ",normalize-space(@class)," ")," scrollsequence-input-script ")]', $item ) ) );
			if( !$cfg )
				continue;

			$itemStyleCont = '';

			$itemStyleCont .= '
				scrollsequence #' . $id . '.scrollsequence-wrap:not([style*="visibility:"]) .scrollsequence-page:first-child {
					display: block !important;
					background: center / cover no-repeat url(' . Gen::GetArrField( $cfg, array( 'page', 0, 'imagesFull', 0 ), '' ) . ');
				}
			';

			if( $itemStyleCont )
			{
				$itemStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
				$itemContainer -> parentNode -> insertBefore( $itemStyle, $itemContainer );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'elmntrWdgtGal' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$cfg = _Elmntr_GetFrontendCfg( HtmlNd::FirstOfChildren( $xpath -> query( './/script[@id="elementor-frontend-js-before"]' ) ) );

		foreach( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-widget-gallery ")][@data-settings]' ) as $item )
		{
			$dataSett = @json_decode( $item -> getAttribute( 'data-settings' ), true );

			$itemContainer = HtmlNd::FirstOfChildren( $xpath -> query( './/*[contains(concat(" ",normalize-space(@class)," ")," elementor-gallery__container ")]', $item ) );
			if( !$itemContainer )
				continue;

			$content_hover_animation = Gen::GetArrField( $dataSett, array( 'content_hover_animation' ), '' );

			$aImage = array();
			$itemImgContainerIdx = -1;
			foreach( $itemContainer -> childNodes as $itemImgContainer )
			{
				if( $itemImgContainer -> nodeType != XML_ELEMENT_NODE )
					continue;

				$itemImgContainerIdx++;

				$itemImg = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," elementor-gallery-item__image ")]', $itemImgContainer ) );
				if( !$itemImg )
					continue;

				$itemImg -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemImg -> getAttribute( 'style' ) ), array( 'background-image' => 'url(' . $itemImg -> getAttribute( 'data-thumbnail' ) . ')' ) ) ) );
				HtmlNd::AddRemoveAttrClass( $itemImg, 'e-gallery-image-loaded' );

				$aImage[] = ( object )array( 'nd' => $itemImgContainer, 'sz' => ( object )array( 'cx' => ( int )$itemImg -> getAttribute( 'data-width' ), 'cy' => ( int )$itemImg -> getAttribute( 'data-height' ) ), 'cssChildIdx' => $itemImgContainerIdx + 1 );

				$itemCont = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," elementor-gallery-item__content ")]', $itemImgContainer ) );
				if( $itemCont )
				{
					foreach( $itemCont -> childNodes as $itemContChild )
					{
						if( $itemContChild -> nodeType != XML_ELEMENT_NODE )
							continue;
						HtmlNd::AddRemoveAttrClass( $itemContChild, 'elementor-animated-item--' . $content_hover_animation );
					}

					$itemOverlay = HtmlNd::FirstOfChildren( $xpath -> query( './*[contains(concat(" ",normalize-space(@class)," ")," elementor-gallery-item__overlay ")]', $itemImgContainer ) );
					if( $itemOverlay )
						HtmlNd::AddRemoveAttrClass( $itemOverlay, 'elementor-animated-item--' . $content_hover_animation );
				}
			}

			if( !$aImage )
				continue;

			$itemCssSel = '.elementor-element-' . $item -> getAttribute( 'data-id' );

			$itemStyleCont = '';

			$layout = Gen::GetArrField( $dataSett, array( 'gallery_layout' ), '' );
			HtmlNd::AddRemoveAttrClass( $itemContainer, array( 'e-gallery--ltr', 'e-gallery-' . $layout ) );

			if( $layout == 'justified' )
			{
				foreach( array( array( 'type' => '_mobile', 'widthAlign' => 766, 'cxMax' => ( Gen::GetArrField( $cfg, array( 'views', 'mobile' ), 0 ) - 1 ) ), array( 'type' => '_tablet', 'widthAlign' => 767, 'cxMin' => Gen::GetArrField( $cfg, array( 'views', 'mobile' ), 0 ), 'cxMax' => ( Gen::GetArrField( $cfg, array( 'views', 'tablet' ), 0 ) - 1 ) ), array( 'type' => '', 'widthAlign' => 767, 'cxMin' => Gen::GetArrField( $cfg, array( 'views', 'tablet' ), 0 ) ) ) as $view )
				{
					$viewGap = Gen::GetArrField( $dataSett, array( 'gap' . $view[ 'type' ] ), array() );
					$viewIdealRowHeight = Gen::GetArrField( $dataSett, array( 'ideal_row_height' . $view[ 'type' ] ), array() );
					if( Gen::GetArrField( $viewIdealRowHeight, array( 'unit' ), '' ) != 'px' || Gen::GetArrField( $viewGap, array( 'unit' ), '' ) != 'px' )
						continue;

					$aRow = array();
					$iCurRow = -1;
					$nCurAvailWidth = 0;
					$cyTotal = 0;
					foreach( $aImage as $image )
					{

						if( !$nCurAvailWidth )
						{
							$nCurAvailWidth = $view[ 'widthAlign' ];
							$iCurRow ++;
							$aRow[ $iCurRow ] = array( 'a' => array(), 'cxAdapted' => 0, 'cy' => 0 );
						}

						$cxAdapted = ( int )round( $image -> sz -> cx * ( ( float )Gen::GetArrField( $viewIdealRowHeight, array( 'size' ), 0 ) / $image -> sz -> cy ) );
						$aRow[ $iCurRow ][ 'a' ][] = array( 'image' => $image, 'cxAdapted' => $cxAdapted );
						$aRow[ $iCurRow ][ 'cxAdapted' ] += $cxAdapted;

						if( $nCurAvailWidth < $cxAdapted )
							$nCurAvailWidth = 0;
						else
						{
							$nCurAvailWidth -= $cxAdapted;
							if( $nCurAvailWidth / $view[ 'widthAlign' ] < 0.2 )
								$nCurAvailWidth = 0;
						}

						if( !$nCurAvailWidth )
						{
							$aRow[ $iCurRow ][ 'cy' ] = ( int )round( ( float )Gen::GetArrField( $viewIdealRowHeight, array( 'size' ), 0 ) * ( $view[ 'widthAlign' ] / ( $aRow[ $iCurRow ][ 'cxAdapted' ] + ( count( $aRow[ $iCurRow ][ 'a' ] ) - 1 ) * Gen::GetArrField( $viewGap, array( 'size' ), 0 ) ) ) );
							$cyTotal += $aRow[ $iCurRow ][ 'cy' ];
						}
					}

					$itemStyleCont .= "\n" . '@media (' . ( isset( $view[ 'cxMin' ] ) ? ( 'min-width: ' . $view[ 'cxMin' ] . 'px' ) : '' ) . ( isset( $view[ 'cxMin' ] ) && isset( $view[ 'cxMax' ] ) ? ') and (' : '' ) . ( isset( $view[ 'cxMax' ] ) ? ( 'max-width: ' . $view[ 'cxMax' ] . 'px' ) : '' ) . ') {';

					$cyCur = 0;
					foreach( $aRow as $iCurRow => $row )
					{
						$cxAdaptedCur = 0;
						foreach( $row[ 'a' ] as $iCurCol => $col )
						{
							$itemStyleCont .= "\n" . $itemCssSel . ' .elementor-gallery__container:not([style*=container-aspect-ratio]) .e-gallery-item:nth-child(' . $col[ 'image' ] -> cssChildIdx . ') {
								--item-width: ' . ( ( float )$col[ 'cxAdapted' ] / $row[ 'cxAdapted' ] ) . ';
								--gap-count: ' . ( count( $row[ 'a' ] ) - 1 ) . ';
								--item-height: ' . ( ( float )$row[ 'cy' ] / ( $cyTotal ? $cyTotal : 1 ) ) . ';
								--item-start: ' . ( ( float )$cxAdaptedCur / $row[ 'cxAdapted' ] ) . ';
								--item-row-index: ' . $iCurCol . ';
								--item-top: ' . ( ( float )$cyCur / ( $cyTotal ? $cyTotal : 1 ) ) . ';
								--row: ' . $iCurRow . ';
							}';

							$cxAdaptedCur += $col[ 'cxAdapted' ];
						}

						$cyCur += $row[ 'cy' ];
					}

					$itemStyleCont .= "\n" . $itemCssSel . ' .elementor-gallery__container:not([style*=container-aspect-ratio]) {
						--container-aspect-ratio: ' . ( ( float )( $cyTotal  ) / $view[ 'widthAlign' ] ) . ';
						--hgap: ' . Gen::GetArrField( $viewGap, array( 'size' ), 0 ) . 'px;
						--vgap: ' . Gen::GetArrField( $viewGap, array( 'size' ), 0 ) . 'px;
						--rows: ' . count( $aRow ) . ';
					}';

					$itemStyleCont .= "\n" . '}';
				}

				$itemStyleCont .= "\n" . $itemCssSel . ' .e-gallery-justified:not([style*=container-aspect-ratio]) .e-gallery-item {
						height: calc(var(--item-height) * (100% - var(--vgap) * var(--rows)));
						top: calc(var(--item-top) * (100% - var(--vgap) * var(--rows)) + (var(--row) * var(--vgap)));
				}';

				$itemStyleCont .= "\n" . $itemCssSel . ' .e-gallery-justified:not([style*=container-aspect-ratio]) {
						padding-bottom: calc(var(--container-aspect-ratio) * 100% + var(--vgap) * var(--rows));
				}';
			}
			else if( $layout == 'grid' )
			{
				$aspect_ratio = explode( ':', Gen::GetArrField( $dataSett, array( 'aspect_ratio' ), '' ) );
				if( count( $aspect_ratio ) == 2 )
					$aspect_ratio = ( float )$aspect_ratio[ 1 ] / ( float )$aspect_ratio[ 0 ];

				foreach( array( array( 'type' => '_mobile', 'cxMax' => ( Gen::GetArrField( $cfg, array( 'views', 'mobile' ), 0 ) - 1 ) ), array( 'type' => '_tablet', 'cxMin' => Gen::GetArrField( $cfg, array( 'views', 'mobile' ), 0 ), 'cxMax' => ( Gen::GetArrField( $cfg, array( 'views', 'tablet' ), 0 ) - 1 ) ), array( 'type' => '', 'cxMin' => Gen::GetArrField( $cfg, array( 'views', 'tablet' ), 0 ) ) ) as $view )
				{
					$viewGap = Gen::GetArrField( $dataSett, array( 'gap' . $view[ 'type' ] ), array() );
					if( Gen::GetArrField( $viewGap, array( 'unit' ), '' ) != 'px' )
						continue;

					$nCols = Gen::GetArrField( $dataSett, array( 'columns' . $view[ 'type' ] ), 0 );

					$itemStyleCont .= "\n" . '@media (' . ( isset( $view[ 'cxMin' ] ) ? ( 'min-width: ' . $view[ 'cxMin' ] . 'px' ) : '' ) . ( isset( $view[ 'cxMin' ] ) && isset( $view[ 'cxMax' ] ) ? ') and (' : '' ) . ( isset( $view[ 'cxMax' ] ) ? ( 'max-width: ' . $view[ 'cxMax' ] . 'px' ) : '' ) . ') {';

					$itemStyleCont .= "\n" . $itemCssSel . ' .elementor-gallery__container:not([style*=container-aspect-ratio]) {
						--container-aspect-ratio: 100%;
						--aspect-ratio: ' . ( $aspect_ratio * 100 ) . '%;
						--hgap: ' . Gen::GetArrField( $viewGap, array( 'size' ), 0 ) . 'px;
						--vgap: ' . Gen::GetArrField( $viewGap, array( 'size' ), 0 ) . 'px;
						--columns: ' . $nCols . ';
						--rows: ' . ( int )ceil( ( float )count( $aImage ) / $nCols ) . ';
					}';

					$itemStyleCont .= "\n" . '}';
				}

				$itemStyleCont .= "\n" . $itemCssSel . ' .e-gallery-grid:not([style*=container-aspect-ratio]).e-gallery--animated .e-gallery-item {
					width: unset;
					height: unset;
					left: unset;
					top: unset;
					position: unset;
				}';

				$itemStyleCont .= "\n" . $itemCssSel . ' .e-gallery-grid:not([style*=container-aspect-ratio]).e-gallery--animated {
					display: grid;
					grid-gap: var(--vgap) var(--hgap);
					grid-template-columns: repeat(var(--columns), 1fr);
				}';
			}
			else if( $layout == 'masonry' )
			{
				foreach( array( array( 'type' => '_mobile', 'cxMax' => ( Gen::GetArrField( $cfg, array( 'views', 'mobile' ), 0 ) - 1 ) ), array( 'type' => '_tablet', 'cxMin' => Gen::GetArrField( $cfg, array( 'views', 'mobile' ), 0 ), 'cxMax' => ( Gen::GetArrField( $cfg, array( 'views', 'tablet' ), 0 ) - 1 ) ), array( 'type' => '', 'cxMin' => Gen::GetArrField( $cfg, array( 'views', 'tablet' ), 0 ) ) ) as $view )
				{
					$viewGap = Gen::GetArrField( $dataSett, array( 'gap' . $view[ 'type' ] ), array() );
					if( Gen::GetArrField( $viewGap, array( 'unit' ), '' ) != 'px' )
						continue;

					$nCols = Gen::GetArrField( $dataSett, array( 'columns' . $view[ 'type' ] ), 0 );

					$itemStyleCont .= "\n" . '@media (' . ( isset( $view[ 'cxMin' ] ) ? ( 'min-width: ' . $view[ 'cxMin' ] . 'px' ) : '' ) . ( isset( $view[ 'cxMin' ] ) && isset( $view[ 'cxMax' ] ) ? ') and (' : '' ) . ( isset( $view[ 'cxMax' ] ) ? ( 'max-width: ' . $view[ 'cxMax' ] . 'px' ) : '' ) . ') {';

					$aCol = array();
					for( $iCol = 0; $iCol < $nCols; $iCol++ )
						$aCol[ $iCol ] = array( 'a' => array(), 'cy' => 0 );

					$colDefWidth = 100;
					$iCol = 0;
					foreach( $aImage as $image )
					{

						$cy = ( int )round( $image -> sz -> cy * ( ( float )$colDefWidth / $image -> sz -> cx ) );
						$aCol[ $iCol ][ 'a' ][] = array( 'image' => $image, 'cy' => $cy, 'y' => $aCol[ $iCol ][ 'cy' ] );
						$aCol[ $iCol ][ 'cy' ] += $cy;

						$iCol++;
						if( $iCol == $nCols )
							$iCol = 0;
					}

					$cyTotal = 0;
					$nMaxGaps = 0;
					foreach( $aCol as $col )
					{
						if( $col[ 'cy' ] > $cyTotal )
						{
							$cyTotal = $col[ 'cy' ];
							$nMaxGaps = count( $col[ 'a' ] ) - 1;
						}
					}

					foreach( $aCol as $iCol => $col )
					{
						foreach( $col[ 'a' ] as $iRow => $row )
						{
							$itemStyleCont .= "\n" . $itemCssSel . ' .elementor-gallery__container:not([style*=highest-column-gap-count]) .e-gallery-item:nth-child(' . $row[ 'image' ] -> cssChildIdx . ') {
								--item-height: ' . ( ( float )$row[ 'image' ] -> sz -> cy / $row[ 'image' ] -> sz -> cx * 100 ) . '%;
								--item-height-ex: ' . ( ( float )$row[ 'cy' ] / ( $cyTotal ? $cyTotal : 1 ) ) . ';
								--column: ' . $iCol . ';
								--items-in-column:  ' . $iRow . ';
								--percent-height: ' . ( ( float )$row[ 'y' ] / ( $cyTotal ? $cyTotal : 1 ) * 100 ) . '%;
								--item-top: ' . ( ( float )$row[ 'y' ] / ( $cyTotal ? $cyTotal : 1 ) ) . ';
							}';
						}
					}

					$itemStyleCont .= "\n" . $itemCssSel . ' .elementor-gallery__container:not([style*=highest-column-gap-count]) {
						--hgap: ' . Gen::GetArrField( $viewGap, array( 'size' ), 0 ) . 'px;
						--vgap: ' . Gen::GetArrField( $viewGap, array( 'size' ), 0 ) . 'px;
						--columns: ' . $nCols . ';
						--highest-column-gap-count: ' . $nMaxGaps . ';
						padding-bottom: ' . ( ( float )$cyTotal / ( $nCols * $colDefWidth + ( $nCols - 1 ) * Gen::GetArrField( $viewGap, array( 'size' ), 0 ) ) * 100 ) . '%;
					}';

					$itemStyleCont .= "\n" . '}';
				}

				$itemStyleCont .= "\n" . $itemCssSel . ' .e-gallery-masonry:not([style*=highest-column-gap-count]) .e-gallery-item {
						height: calc(var(--item-height-ex) * (100% - var(--vgap) * var(--highest-column-gap-count)));
						top: calc(var(--item-top) * (100% - var(--vgap) * var(--highest-column-gap-count)) + (var(--items-in-column) * var(--vgap)));
				}';
			}

			if( $itemStyleCont )
			{
				$itemStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
				$item -> parentNode -> insertBefore( $itemStyle, $item );
			}
		}
	}

	if( Gen::GetArrField( $settCp, array( 'sldRev' ), false ) )
	{
		if( !$xpath )
			$xpath = new \DOMXPath( $doc );

		$itemInitCmnScr = null;

		$adjusted = false;
		$adjustedBubbles = false;
		foreach( $xpath -> query( './/rs-module' ) as $item )
		{
			if( !$itemInitCmnScr )
				$itemInitCmnScr = HtmlNd::FirstOfChildren( $xpath -> query( './/script[contains(text(),".revolutionInit(")]' ) );

			$prms = _RevSld_GetPrmsFromScr( $item, $itemInitCmnScr );
			if( !$prms )
				continue;

			$aItemSlide = HtmlNd::ChildrenAsArr( $xpath -> query( './rs-slides/rs-slide', $item ) );
			if( !$aItemSlide )
				continue;

			$nSlides = count( $aItemSlide );
			$itemFirstSlide = $aItemSlide[ 0 ];

			$aItemStyle = array( array(), array(), array(), array() );

			$aGridWidth = array_reverse( Gen::GetArrField( $prms, array( 'start', 'gw' ), array() ) );
			if( count( $aGridWidth ) == 1 )
				$aGridWidth = array_fill( 0, count( $aItemStyle ), $aGridWidth[ 0 ] );

			$aWidth = array_reverse( Gen::GetArrField( $prms, array( 'start', 'rl' ), array() ) );
			if( count( $aWidth ) == 1 )
				$aWidth = array_fill( 0, count( $aItemStyle ), $aWidth[ 0 ] );

			if( count( $aWidth ) != count( $aItemStyle ) )
				continue;

			$keepBPHeight = Gen::GetArrField( $prms, array( 'init', 'keepBPHeight' ) );
			$layout = Gen::GetArrField( $prms, array( 'init', 'sliderLayout' ), '' );
			$itemId = $item -> getAttribute( 'id' );
			$item -> setAttribute( 'data-lzl-widths', @json_encode( $aWidth ) );
			$item -> setAttribute( 'data-lzl-widths-g', @json_encode( $aGridWidth ) );
			$item -> setAttribute( 'data-lzl-layout', $layout );

			if( $layout != 'fullscreen' )
			{
				$aHeigh = Gen::GetArrField( $prms, array( 'start', 'gh' ), array() );
				if( count( $aHeigh ) == 1 )
					$aHeigh = array_fill( 0, count( $aItemStyle ), $aHeigh[ 0 ] );

				for( $i = 0; $i < count( $aItemStyle ); $i++ )
				{
					$h = (isset($aHeigh[ $i ])?$aHeigh[ $i ]:'0') . 'px';
					if( !$keepBPHeight )
						$h = 'calc(' . $h . '*var(--lzl-rs-scale))';
					$aItemStyle[ $i ][ '#' . $itemId . ':not(.revslider-initialised)' ][ 'height' ] = $h . '!important';
				}
			}

			$itemSlidesTmp = $doc -> createElement( 'rs-slides' );
			HtmlNd::AddRemoveAttrClass( $itemSlidesTmp, 'js-lzl-ing' );
			$item -> appendChild( $itemSlidesTmp );
			$itemSlidesTmp -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemSlidesTmp -> getAttribute( 'style' ) ), array( 'width' => '100%', 'height' => '100%' ) ) ) );

			$itemFirstSlideTmp = $itemFirstSlide -> cloneNode( true );
			$itemSlidesTmp -> appendChild( $itemFirstSlideTmp );
			$itemFirstSlideTmp -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $itemFirstSlideTmp -> getAttribute( 'style' ) ), array( 'width' => '100%', 'height' => '100%' ) ) ) );

			$slideMediaFilter = $itemFirstSlideTmp -> getAttribute( 'data-mediafilter' );

			$itemStyleCont = '';

			$itemSlideChild = null;
			$itemSlideChildNext = null;
			$itemSlideBgContainer = null;
			$iCurBubblesRand = 0;
			while( $itemSlideChild = ( $itemSlideChildNext ? $itemSlideChildNext : HtmlNd::GetNextTreeChild( $itemFirstSlideTmp, $itemSlideChild ) ) )
			{
				$itemSlideChildNext = null;
				if( $itemSlideChild -> nodeType != XML_ELEMENT_NODE )
					continue;

				$aClass = HtmlNd::GetAttrClass( $itemSlideChild );

				$bResponsiveSizes = $itemSlideChild -> getAttribute( 'data-rsp_bd' ) !== 'off';
				$bResponsiveOffsets = $itemSlideChild -> getAttribute( 'data-rsp_o' ) !== 'off';
				$bResponsiveChildren = $itemSlideChild -> getAttribute( 'data-rsp_ch' ) === 'on';

				$bBaseAlignLayerArea = $itemSlideChild -> getAttribute( 'data-basealign' );
				$bBaseAlignLayerArea = $bBaseAlignLayerArea ? ( $bBaseAlignLayerArea !== 'slide' ) : $keepBPHeight;

				$isLayer = $itemSlideChild -> nodeName == 'rs-layer' || in_array( 'rs-layer', $aClass );
				$isContainer = $itemSlideChild -> nodeName == 'rs-row' || $itemSlideChild -> nodeName == 'rs-column' || $itemSlideChild -> nodeName == 'rs-group';

				$itemParent = $itemSlideChild -> parentNode;
				$itemInsertBefore = $itemSlideChild -> nextSibling;

				if( $itemSlideChild -> nodeName == 'img' && in_array( 'rev-slidebg', $aClass ) )
				{
					$itemSlideChildNext = HtmlNd::GetNextTreeChild( $itemFirstSlideTmp, $itemSlideChild );
					$attrPanZoom = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-panzoom' ) );

					$srcImg = $itemSlideChild -> getAttribute( 'data-lazyload' );
					if( !$srcImg )
						$srcImg = $itemSlideChild -> getAttribute( 'src' );
					$attrBg = array_merge( array( 'p' => 'center' ), _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-bg' ) ) );

					$attrPanZoomOffsetEndXY = explode( '/', Gen::GetArrField( $attrPanZoom, array( 'oe' ), '0px/0px' ) );
					$attrPanZoomScale = ( float )Gen::GetArrField( $attrPanZoom, array( 'se' ), '100%' ) / 100;

					$attrBgP = explode( ' ', Gen::GetArrField( $attrBg, array( 'p' ), '' ) );
					if( count( $attrBgP ) < 2 )
						$attrBgP[ 1 ] = $attrBgP[ 0 ];

					switch( $attrBgP[ 0 ] )
					{
					case 'left':					$attrBgP[ 0 ] = '0%'; break;
					case 'middle': case 'center':	$attrBgP[ 0 ] = '50%'; break;
					case 'right':					$attrBgP[ 0 ] = '100%'; break;
					}

					switch( $attrBgP[ 1 ] )
					{
					case 'top':						$attrBgP[ 1 ] = '0%'; break;
					case 'middle': case 'center':	$attrBgP[ 1 ] = '50%'; break;
					case 'bottom':					$attrBgP[ 1 ] = '100%'; break;
					}

					$attrBgP[ 0 ] = 'calc(' . $attrBgP[ 0 ] . ' + ' . _RevSld_GetSize( false, Gen::GetArrField( $attrPanZoomOffsetEndXY, array( 0 ), '0' ) ) . ' / ' . $attrPanZoomScale . ')';
					$attrBgP[ 1 ] = 'calc(' . $attrBgP[ 1 ] . ' + ' . _RevSld_GetSize( false, Gen::GetArrField( $attrPanZoomOffsetEndXY, array( 1 ), '0' ) ) . ' / ' . $attrPanZoomScale . ')';

					$itemSlideChildTmp = HtmlNd::CreateTag( $doc, 'div', array( 'style' => array( 'width' => '100%', 'height' => '100%', 'background' => ( isset( $attrBg[ 'c' ] ) ? ( ( string )$attrBg[ 'c' ] . ( Gen::StrStartsWith( ( string )$attrBg[ 'c' ], array( '#', 'rgb', 'hsl' ) ) ? '' : ',' ) . ' ' ) : '' ) . implode( ' ', $attrBgP ) . ' / cover no-repeat url(' . $srcImg . ')', 'transform' => 'scale(' . $attrPanZoomScale . ') rotate(' . Gen::GetArrField( $attrPanZoom, array( 're' ), '0deg' ) . ')' ) ) );
					$itemParent -> replaceChild( $itemSlideChildTmp, $itemSlideChild );
					$itemSlideChild = $itemSlideChildTmp;

					$itemSlideBgItem = HtmlNd::CreateTag( $doc, 'rs-sbg', array( 'class' => array( $slideMediaFilter ), 'style' => array( 'width' => '100%', 'height' => '100%' ) ), array( $itemSlideChild ) );

					if( $itemSlideBgContainer )
					{
						$itemSlideBgContainer -> appendChild( $itemSlideBgItem );
					}
					else
					{
						$itemSlideBgContainer = HtmlNd::CreateTag( $doc, 'rs-sbg-wrap', null, array( $itemSlideBgItem ) );
						$itemParent -> insertBefore( HtmlNd::CreateTag( $doc, 'rs-sbg-px', null, array( $itemSlideBgContainer ) ), $itemInsertBefore );
					}
				}
				else if( $itemSlideChild -> nodeName == 'rs-bgvideo' )
				{
					HtmlNd::AddRemoveAttrClass( $itemSlideChild, array( $slideMediaFilter ) );

					$itemSlideChildNext = HtmlNd::GetNextTreeChild( $itemFirstSlideTmp, $itemSlideChild );

					$itemSlideBgItem = $itemSlideChild;
					$itemSlideBgItem -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'rs-fullvideo-cover' ) ) ) );
					$itemSlideBgItem -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'html5vid', 'rs_html5vidbasicstyles', 'fullcoveredvideo' ) ), array( HtmlNd::CreateTag( $doc, 'video', array( 'autoplay' => '', 'muted' => '', 'loop' => '', 'preload' => 'auto', 'style' => array( 'object-fit' => 'cover', 'background-size' => 'cover', 'opacity' => '0', 'width' => '100%', 'height' => '100%', 'position' => 'absolute', 'left' => '0px', 'top' => '0px' ) ), array( HtmlNd::CreateTag( $doc, 'source', array( 'src' => $itemSlideChild -> getAttribute( 'data-mp4' ), 'type' => array( 'video/mp4' ) ) ) ) ) ) ) );
					$itemSlideBgItem -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'tp-video-play-button' ) ), array( HtmlNd::CreateTag( $doc, 'i', array( 'class' => array( 'revicon-right-dir' ) ) ), HtmlNd::CreateTag( $doc, 'span', array( 'class' => array( 'tp-revstop' ) ), array( $doc -> createTextNode( ' ' ) ) ) ) ) );

					if( $itemSlideBgContainer )
					{
					    $itemSlideBgContainer -> appendChild( $itemSlideBgItem );
					}
					else
					{
					    $itemSlideBgContainer = HtmlNd::CreateTag( $doc, 'rs-sbg-wrap', null, array( $itemSlideBgItem ) );
					    $itemParent -> insertBefore( HtmlNd::CreateTag( $doc, 'rs-sbg-px', null, array( $itemSlideBgContainer ) ), $itemInsertBefore );
					}
				}
				else if( $isLayer || $isContainer )
				{
					$id = $itemSlideChild -> getAttribute( 'id' );
					$itemIdWrap = $id . '-wrap';

					$itemChildSelector = '.js-lzl-ing #' . $id;
					$itemChildSelectorWrap = '.js-lzl-ing #' . $itemIdWrap;

					$attrXy = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-xy' ), count( $aItemStyle ) );
					$attrDim = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-dim' ) );
					$attrText = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-text' ) );
					$attrPadding = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-padding' ) );
					$attrMargin = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-margin' ) );
					$attrBorder = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-border' ) );
					$attrBTrans = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-btrans' ), count( $aItemStyle ) );
					$attrTextStroke = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-tst' ) );
					$attrType = $itemSlideChild -> getAttribute( 'data-type' );
					$attrWrapperClass = $itemSlideChild -> getAttribute( 'data-wrpcls' );
					$attrVisibility = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-vbility' ) );

					$attrColor = trim( ( string )$itemSlideChild -> getAttribute( 'data-color' ) );
					if( strlen( $attrColor ) )
						$attrColor = explode( '||', ( string )$attrColor );
					else
						$attrColor = array();

					$attrDisplay = $itemSlideChild -> getAttribute( 'data-disp' );
					if( !$attrDisplay )
						$attrDisplay = null;

					if( !isset( $attrText[ 'ls' ] ) )
						$attrText[ 'ls' ] = '0';

					$styleSeparated = array( 'color' => $attrColor ? null : '#fff', 'position' => ( $itemParent === $itemFirstSlideTmp || $itemParent -> nodeName == 'rs-group' ) ? 'absolute' : 'relative', 'display' => $attrDisplay );
					$styleSeparatedWrap = array( 'position' => $styleSeparated[ 'position' ], 'display' => $attrDisplay, 'pointer-events' => 'auto' );

					$offsSuffix = $bBaseAlignLayerArea ? ' + 1px * var(--lzl-rs-diff-y) * 0.225' : null;

					if( $attrType != 'row' && $attrType != 'column' && !HtmlNd::FindUpBy( $itemSlideChild, function( $nd, $data ) { return( $nd -> nodeName == 'rs-column' ); } ) )
					{
						$a = array_fill( 0, count( $aItemStyle ), array() );
						$aW = array_fill( 0, count( $aItemStyle ), array() );
						for( $i = 0; $i < count( $aItemStyle ); $i++ )
						{
							$translate = array( 0, 0 );
							$offset = array( Gen::GetArrField( $attrXy, array( 'xo', $i ), '0' ), Gen::GetArrField( $attrXy, array( 'yo', $i ), '0' ) );

							{
								$widhtIsRelative = strpos( ( string )_RevSld_GetIdxPropVal( $attrDim, array( 'w' ), $i, 'auto' ), '%' ) !== false;
								$prefix = null;
								switch( $alignX = Gen::GetArrField( $attrXy, array( 'x', $i ), '' ) )
								{
								case 'c':
								case 'm':
									$translate[ 0 ] = '-50%';
									$prefix = '50% + ';
									break;

								case 'r':
									$translate[ 0 ] = '-100%';
									$prefix = ( $widhtIsRelative ? '' : '-1px * var(--lzl-rs-extra-x) + ' ) . '100% - ';
									break;

								default:
									$prefix = ( $widhtIsRelative ? '' : '1px * var(--lzl-rs-extra-x) + ' );
									if( Gen::StrEndsWith( $alignX, 'px' ) )
									{
										$offset[ 0 ] = $alignX;
										$translate[ 0 ] = '-50%';
										$prefix = '50% + ';
									}
								}

								$aW[ $i ][ 'left' ] = _RevSld_GetSize( $bResponsiveOffsets, $offset[ 0 ], $prefix );
							}

							{
								$prefix = null;
								switch( $alignY = Gen::GetArrField( $attrXy, array( 'y', $i ), '' ) )
								{
								case 'c':
								case 'm':
									$translate[ 1 ] = '-50%';
									$prefix = '50% + ';
									break;

								case 'b':
									$translate[ 1 ] = '-100%';
									$prefix = '100% - ';
									break;

								default:
									if( Gen::StrEndsWith( $alignY, 'px' ) )
									{
										$offset[ 1 ] = $alignY;
										$translate[ 1 ] = '-50%';
										$prefix = '50% + ';
									}
								}

								$aW[ $i ][ 'top' ] = _RevSld_GetSize( $bResponsiveOffsets, $offset[ 1 ], $prefix, $offsSuffix );
							}

							if( $translate[ 0 ] || $translate[ 1 ] )
								$a[ $i ][ 'transform' ] = 'translate(' . $translate[ 0 ] . ', ' . $translate[ 1 ] . ')!important';
						}
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
						_RevSld_SetStyleAttr( $styleSeparatedWrap, $aItemStyle, $itemChildSelectorWrap, $aW );
					}

					$aSizeChild = array();
					foreach( array( 'w' => 'width', 'h' => 'height' ) as $f => $t )
					{
						$a = array();
						foreach( ( array )(isset($attrDim[ $f ])?$attrDim[ $f ]:'auto') as $i => $v )
						{
							$v = $a[ $i ][ $t ] = _RevSld_GetSize( $bResponsiveSizes, $v . ( is_numeric( $v ) ? 'px' : '' ) );
							$aSizeChild[ $i ][ $t ] = $v == 'auto' ? 'auto' : null;
						}
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );

						if( Gen::StrEndsWith( $a[ 0 ][ $t ], '%' ) )
							$styleSeparatedWrap[ $t ] = '100%';
					}

					{
						$a = array(); foreach( ( array )(isset($attrDim[ 'w' ])?$attrDim[ 'w' ]:'auto') as $i => $v ) $a[ $i ][ 'white-space' ] = ( $v == 'auto' ) ? 'nowrap' : ( is_array( (isset($attrText[ 'w' ])?$attrText[ 'w' ]:null) ) ? (isset($attrText[ 'w' ][ $i ])?$attrText[ 'w' ][ $i ]:null) : (isset($attrText[ 'w' ])?$attrText[ 'w' ]:null) );
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					if( $attrColor )
					{
						$a = array(); foreach( $attrColor as $i => $v ) $a[ $i ][ 'color' ] = $attrColor[ $i ];
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					foreach( array( 'fw' => 'font-weight', 'a' => 'text-align' ) as $f => $t )
					{
						$a = array(); foreach( ( array )(isset($attrText[ $f ])?$attrText[ $f ]:null) as $i => $v ) $a[ $i ][ $t ] = $v;
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					foreach( array( 'f' => 'float' ) as $f => $t )
						$styleSeparatedWrap[ $t ] = (isset($attrText[ $f ])?$attrText[ $f ]:null);

					foreach( array( 's' => 'font-size', 'l' => 'line-height', 'ls' => 'letter-spacing' ) as $f => $t )
					{
						$a = array(); foreach( ( array )(isset($attrText[ $f ])?$attrText[ $f ]:null) as $i => $v ) if( $v !== null ) $a[ $i ][ $t ] = _RevSld_GetSize( $bResponsiveSizes, $v . ( Gen::StrEndsWith( $v, 'px' ) ? '' : 'px' ) );
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					foreach( array( 'l' => 'padding-left', 'r' => 'padding-right', 't' => 'padding-top', 'b' => 'padding-bottom' ) as $f => $t )
					{
						$a = array(); foreach( ( array )(isset($attrPadding[ $f ])?$attrPadding[ $f ]:null) as $i => $v ) if( $v !== null ) $a[ $i ][ $t ] = _RevSld_GetSize( $bResponsiveSizes, $v . 'px' );
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					foreach( array( 'l' => 'margin-left', 'r' => 'margin-right', 't' => 'margin-top', 'b' => 'margin-bottom' ) as $f => $t )
					{
						if( $itemSlideChild -> nodeName == 'rs-row' )
							$t = str_replace( 'margin-', 'padding-', $t );
						$a = array(); foreach( ( array )(isset($attrMargin[ $f ])?$attrMargin[ $f ]:null) as $i => $v ) if( $v !== null ) $a[ $i ][ $t ] = $v . 'px';
						_RevSld_SetStyleAttr( $styleSeparatedWrap, $aItemStyle, $itemChildSelectorWrap, $a );
					}

					foreach( array( 'bos' => 'border-style', 'boc' => 'border-color', 'bow' => 'border-width', 'bor' => 'border-radius' ) as $f => $t )
					{
						$a = array(); foreach( ( array )(isset($attrBorder[ $f ])?$attrBorder[ $f ]:null) as $i => $v ) $a[ $i ][ $t ] = ( $f == 'bow' ) ? _RevSld_GetSize( false, $v . 'px' ) : $v;
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					foreach( array( 'w' => '-webkit-text-stroke-width', 'c' => '-webkit-text-stroke-color' ) as $f => $t )
					{
						$a = array(); foreach( ( array )(isset($attrTextStroke[ $f ])?$attrTextStroke[ $f ]:null) as $i => $v ) $a[ $i ][ $t ] = $v;
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					if( $attrVisibility )
					{

						$a = array(); foreach( $attrVisibility[ '' ] as $i => $v ) if( $v === 'f' ) $a[ $i ][ 'display' ] = 'none'; else $a[ $i ][ '' ] = '';
						_RevSld_SetStyleAttr( $styleSeparatedWrap, $aItemStyle, $itemChildSelectorWrap, $a );
					}

					if( isset( $attrBTrans[ 'rZ' ] ) )
					{
						$a = array(); foreach( ( array )$attrBTrans[ 'rZ' ] as $i => $v ) $a[ $i ][ 'transform' ] = 'rotate(' . ( string )$v . 'deg)!important';
						_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
					}

					if( $attrType == 'image' && ( $itemImg = HtmlNd::FirstOfChildren( $xpath -> query( './/img', $itemSlideChild ) ) ) )
					{
						HtmlNd::RenameAttr( $itemImg, 'data-lazyload', 'src' );

						$styleSeparatedImg = array();
						_RevSld_SetStyleAttr( $styleSeparatedImg, $aItemStyle, $itemChildSelector . ' > img', $aSizeChild );
						$itemImg -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), $styleSeparatedImg ) ) );
						unset( $styleSeparatedImg );

					}

					if( $attrType == 'video' )
					{

						$mp4Url = $itemSlideChild -> getAttribute( 'data-mp4' );

						$itemSlideChild -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => array( 'html5vid', 'rs_html5vidbasicstyles' ), 'style' => array( 'box-sizing' => 'content-box', 'border-color' => 'transparent', 'border-style' => 'none', 'left' => '0px', 'top' => '0px' ) ), array(
							HtmlNd::CreateTag( $doc, 'video', array( 'preload' => 'auto', 'style' => array( 'opacity' => '1', 'width' => '100%', 'height' => '100%', 'display' => 'block' ) ), array(
								HtmlNd::CreateTag( $doc, 'source', array( 'type' => 'video/mp4', 'src' => $mp4Url ) )
							) )
						) ) );
					}

					if( $posterUrl = $itemSlideChild -> getAttribute( 'data-poster' ) )
					{

						$itemSlideChild -> appendChild( HtmlNd::CreateTag( $doc, 'rs-poster', array( 'class' => 'noSwipe', 'style' => array( 'background-image' => 'url(' . $posterUrl . ')' ) ) ) );
					}

					$actions = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-actions' ) );
					if( $actions )
					{
						if( Gen::GetArrField( $actions, array( 'a' ) ) == 'startlayer' )
						{
							$idLayer = Gen::GetArrField( $actions, array( 'layer' ) );
							if( $idLayer && ( $itemLayerToHide = HtmlNd::FirstOfChildren( $xpath -> query( './/*[@id="' . $idLayer . '"]', $itemSlidesTmp ) ) ) )
								HtmlNd::AddRemoveAttrClass( $itemLayerToHide, 'js-lzl-ing-disp-none' );
						}
					}

					$frameChars = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-frame_0_chars' ) );
					if( $frameChars )
					{
						$frameChars1 = _RevSld_GetAttrs( $itemSlideChild -> getAttribute( 'data-frame_1_chars' ) );
						if( $frameChars1 && ( ( int )Gen::GetArrField( $frameChars1, array( 'd' ) ) > ( int )Gen::GetArrField( $frameChars, array( 'd' ) ) ) )
						    $frameChars = $frameChars1;
						unset( $frameChars1 );

						$aChars = trim( $itemSlideChild -> textContent );
						$aChars = function_exists( 'mb_str_split' ) ? mb_str_split( $aChars ) : str_split( $aChars );
						HtmlNd::CleanChildren( $itemSlideChild );

						$itemWord = HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'rs_splitted_words' ) );
						foreach( $aChars as $i => $char )
						{
							$itemChar = HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'rs_splitted_chars', 'style' => array(
								'display' => 'inline-block',
								'transform-origin' => '50% 50%',
								'transform' => 'translate3d(' . _RevSld_GetIdxPropVal( $frameChars, array( 'x', 'cyc' ), $i, '0' ) . 'px, ' . _RevSld_GetIdxPropVal( $frameChars, array( 'y', 'cyc' ), $i, '0' ) . 'px, ' . _RevSld_GetIdxPropVal( $frameChars, array( 'z', 'cyc' ), $i, '0' ) . 'px) rotate(' . _RevSld_GetIdxPropVal( $frameChars, array( 'rZ', 'cyc' ), $i, '0' ) . 'deg)'
							) ), array( $char === ' ' ? $doc -> createEntityReference( 'nbsp' ) : $doc -> createTextNode( $char ) ) );
							$itemWord -> appendChild( $itemChar );
						}

						$itemSlideChild -> appendChild( HtmlNd::CreateTag( $doc, 'div', array( 'class' => 'rs_splitted_lines' ), array( $itemWord ) ) );
					}

					$bubbleMorph = @json_decode( $itemSlideChild -> getAttribute( 'data-bubblemorph' ), true );
					if( $bubbleMorph )
					{

						static $g_aBubblePosRand = array(0=>array(0=>array(0=>82,1=>82,),1=>array(0=>92,1=>68,),2=>array(0=>66,1=>69,),3=>array(0=>30,1=>100,),),1=>array(0=>array(0=>86,1=>19,),1=>array(0=>73,1=>86,),2=>array(0=>16,1=>9,),3=>array(0=>12,1=>87,),),2=>array(0=>array(0=>37,1=>78,),1=>array(0=>27,1=>5,),2=>array(0=>55,1=>92,),3=>array(0=>40,1=>7,),),3=>array(0=>array(0=>87,1=>83,),1=>array(0=>44,1=>81,),2=>array(0=>46,1=>69,),3=>array(0=>69,1=>67,),),4=>array(0=>array(0=>75,1=>93,),1=>array(0=>67,1=>84,),2=>array(0=>42,1=>77,),3=>array(0=>14,1=>34,),),5=>array(0=>array(0=>8,1=>17,),1=>array(0=>4,1=>19,),2=>array(0=>29,1=>51,),3=>array(0=>60,1=>8,),),6=>array(0=>array(0=>87,1=>98,),1=>array(0=>49,1=>15,),2=>array(0=>89,1=>52,),3=>array(0=>21,1=>27,),),7=>array(0=>array(0=>38,1=>5,),1=>array(0=>27,1=>19,),2=>array(0=>7,1=>40,),3=>array(0=>7,1=>98,),),8=>array(0=>array(0=>43,1=>93,),1=>array(0=>24,1=>73,),2=>array(0=>66,1=>75,),3=>array(0=>14,1=>75,),),9=>array(0=>array(0=>99,1=>91,),1=>array(0=>38,1=>4,),2=>array(0=>64,1=>61,),3=>array(0=>78,1=>28,),),10=>array(0=>array(0=>1,1=>20,),1=>array(0=>46,1=>28,),2=>array(0=>42,1=>71,),3=>array(0=>23,1=>45,),),11=>array(0=>array(0=>54,1=>41,),1=>array(0=>39,1=>34,),2=>array(0=>21,1=>4,),3=>array(0=>85,1=>84,),),12=>array(0=>array(0=>1,1=>66,),1=>array(0=>61,1=>38,),2=>array(0=>82,1=>32,),3=>array(0=>12,1=>25,),),13=>array(0=>array(0=>29,1=>89,),1=>array(0=>79,1=>47,),2=>array(0=>63,1=>95,),3=>array(0=>78,1=>80,),),14=>array(0=>array(0=>48,1=>28,),1=>array(0=>82,1=>62,),2=>array(0=>56,1=>23,),3=>array(0=>74,1=>68,),),15=>array(0=>array(0=>22,1=>23,),1=>array(0=>20,1=>56,),2=>array(0=>87,1=>66,),3=>array(0=>93,1=>85,),),16=>array(0=>array(0=>40,1=>4,),1=>array(0=>97,1=>14,),2=>array(0=>76,1=>35,),3=>array(0=>97,1=>11,),),17=>array(0=>array(0=>42,1=>86,),1=>array(0=>87,1=>57,),2=>array(0=>16,1=>56,),3=>array(0=>73,1=>14,),),18=>array(0=>array(0=>7,1=>19,),1=>array(0=>43,1=>71,),2=>array(0=>16,1=>82,),3=>array(0=>62,1=>41,),),19=>array(0=>array(0=>95,1=>93,),1=>array(0=>29,1=>78,),2=>array(0=>45,1=>88,),3=>array(0=>10,1=>7,),),20=>array(0=>array(0=>40,1=>0,),1=>array(0=>14,1=>76,),2=>array(0=>40,1=>72,),3=>array(0=>53,1=>91,),),21=>array(0=>array(0=>19,1=>65,),1=>array(0=>58,1=>56,),2=>array(0=>85,1=>86,),3=>array(0=>1,1=>27,),),22=>array(0=>array(0=>14,1=>34,),1=>array(0=>91,1=>57,),2=>array(0=>49,1=>65,),3=>array(0=>60,1=>65,),),23=>array(0=>array(0=>95,1=>66,),1=>array(0=>100,1=>96,),2=>array(0=>46,1=>2,),3=>array(0=>55,1=>42,),),24=>array(0=>array(0=>19,1=>79,),1=>array(0=>60,1=>85,),2=>array(0=>99,1=>54,),3=>array(0=>79,1=>26,),),25=>array(0=>array(0=>66,1=>28,),1=>array(0=>62,1=>45,),2=>array(0=>81,1=>23,),3=>array(0=>52,1=>97,),),26=>array(0=>array(0=>76,1=>75,),1=>array(0=>95,1=>11,),2=>array(0=>3,1=>78,),3=>array(0=>61,1=>39,),),27=>array(0=>array(0=>53,1=>64,),1=>array(0=>19,1=>15,),2=>array(0=>78,1=>14,),3=>array(0=>67,1=>73,),),28=>array(0=>array(0=>1,1=>10,),1=>array(0=>58,1=>92,),2=>array(0=>54,1=>92,),3=>array(0=>82,1=>68,),),29=>array(0=>array(0=>48,1=>51,),1=>array(0=>8,1=>49,),2=>array(0=>48,1=>44,),3=>array(0=>34,1=>93,),),30=>array(0=>array(0=>94,1=>12,),1=>array(0=>65,1=>58,),2=>array(0=>52,1=>24,),3=>array(0=>67,1=>16,),),31=>array(0=>array(0=>18,1=>42,),1=>array(0=>77,1=>32,),2=>array(0=>97,1=>66,),3=>array(0=>33,1=>12,),),32=>array(0=>array(0=>37,1=>91,),1=>array(0=>44,1=>47,),2=>array(0=>89,1=>84,),3=>array(0=>20,1=>57,),),33=>array(0=>array(0=>5,1=>17,),1=>array(0=>71,1=>8,),2=>array(0=>75,1=>48,),3=>array(0=>29,1=>20,),),34=>array(0=>array(0=>25,1=>24,),1=>array(0=>11,1=>99,),2=>array(0=>98,1=>87,),3=>array(0=>76,1=>18,),),35=>array(0=>array(0=>10,1=>72,),1=>array(0=>48,1=>30,),2=>array(0=>49,1=>99,),3=>array(0=>47,1=>62,),),36=>array(0=>array(0=>30,1=>33,),1=>array(0=>67,1=>38,),2=>array(0=>61,1=>75,),3=>array(0=>40,1=>96,),),37=>array(0=>array(0=>81,1=>85,),1=>array(0=>30,1=>86,),2=>array(0=>14,1=>54,),3=>array(0=>9,1=>49,),),38=>array(0=>array(0=>94,1=>29,),1=>array(0=>34,1=>33,),2=>array(0=>45,1=>32,),3=>array(0=>38,1=>82,),),39=>array(0=>array(0=>98,1=>17,),1=>array(0=>40,1=>11,),2=>array(0=>5,1=>12,),3=>array(0=>26,1=>77,),),40=>array(0=>array(0=>81,1=>37,),1=>array(0=>58,1=>86,),2=>array(0=>40,1=>60,),3=>array(0=>10,1=>63,),),41=>array(0=>array(0=>0,1=>54,),1=>array(0=>90,1=>7,),2=>array(0=>22,1=>78,),3=>array(0=>3,1=>70,),),42=>array(0=>array(0=>87,1=>97,),1=>array(0=>50,1=>54,),2=>array(0=>85,1=>20,),3=>array(0=>82,1=>10,),),43=>array(0=>array(0=>56,1=>33,),1=>array(0=>92,1=>92,),2=>array(0=>23,1=>53,),3=>array(0=>82,1=>61,),),44=>array(0=>array(0=>83,1=>81,),1=>array(0=>78,1=>47,),2=>array(0=>29,1=>46,),3=>array(0=>3,1=>49,),),45=>array(0=>array(0=>53,1=>100,),1=>array(0=>59,1=>25,),2=>array(0=>47,1=>78,),3=>array(0=>83,1=>14,),),46=>array(0=>array(0=>30,1=>100,),1=>array(0=>34,1=>86,),2=>array(0=>22,1=>87,),3=>array(0=>69,1=>7,),),47=>array(0=>array(0=>97,1=>9,),1=>array(0=>61,1=>29,),2=>array(0=>50,1=>89,),3=>array(0=>83,1=>30,),),48=>array(0=>array(0=>75,1=>44,),1=>array(0=>71,1=>58,),2=>array(0=>62,1=>55,),3=>array(0=>88,1=>92,),),49=>array(0=>array(0=>77,1=>82,),1=>array(0=>68,1=>17,),2=>array(0=>86,1=>62,),3=>array(0=>28,1=>8,),),50=>array(0=>array(0=>70,1=>97,),1=>array(0=>5,1=>63,),2=>array(0=>65,1=>39,),3=>array(0=>52,1=>47,),),51=>array(0=>array(0=>37,1=>50,),1=>array(0=>36,1=>87,),2=>array(0=>44,1=>14,),3=>array(0=>79,1=>49,),),52=>array(0=>array(0=>32,1=>77,),1=>array(0=>95,1=>13,),2=>array(0=>100,1=>55,),3=>array(0=>85,1=>31,),),53=>array(0=>array(0=>45,1=>17,),1=>array(0=>91,1=>73,),2=>array(0=>84,1=>81,),3=>array(0=>28,1=>14,),),54=>array(0=>array(0=>71,1=>9,),1=>array(0=>60,1=>38,),2=>array(0=>50,1=>59,),3=>array(0=>61,1=>75,),),55=>array(0=>array(0=>66,1=>10,),1=>array(0=>71,1=>27,),2=>array(0=>47,1=>10,),3=>array(0=>78,1=>10,),),56=>array(0=>array(0=>50,1=>75,),1=>array(0=>38,1=>61,),2=>array(0=>11,1=>15,),3=>array(0=>100,1=>8,),),57=>array(0=>array(0=>13,1=>42,),1=>array(0=>55,1=>61,),2=>array(0=>97,1=>26,),3=>array(0=>89,1=>21,),),58=>array(0=>array(0=>50,1=>37,),1=>array(0=>0,1=>90,),2=>array(0=>48,1=>74,),3=>array(0=>95,1=>74,),),59=>array(0=>array(0=>50,1=>8,),1=>array(0=>76,1=>28,),2=>array(0=>54,1=>91,),3=>array(0=>53,1=>62,),),60=>array(0=>array(0=>77,1=>82,),1=>array(0=>30,1=>70,),2=>array(0=>53,1=>0,),3=>array(0=>35,1=>11,),),61=>array(0=>array(0=>80,1=>25,),1=>array(0=>13,1=>13,),2=>array(0=>80,1=>70,),3=>array(0=>34,1=>72,),),62=>array(0=>array(0=>39,1=>80,),1=>array(0=>62,1=>28,),2=>array(0=>83,1=>85,),3=>array(0=>8,1=>2,),),63=>array(0=>array(0=>12,1=>10,),1=>array(0=>60,1=>38,),2=>array(0=>61,1=>70,),3=>array(0=>90,1=>10,),),64=>array(0=>array(0=>81,1=>69,),1=>array(0=>93,1=>94,),2=>array(0=>94,1=>7,),3=>array(0=>35,1=>57,),),65=>array(0=>array(0=>78,1=>29,),1=>array(0=>47,1=>55,),2=>array(0=>40,1=>88,),3=>array(0=>54,1=>53,),),66=>array(0=>array(0=>38,1=>53,),1=>array(0=>47,1=>30,),2=>array(0=>25,1=>100,),3=>array(0=>21,1=>72,),),67=>array(0=>array(0=>31,1=>58,),1=>array(0=>53,1=>21,),2=>array(0=>56,1=>29,),3=>array(0=>92,1=>17,),),68=>array(0=>array(0=>34,1=>88,),1=>array(0=>17,1=>61,),2=>array(0=>28,1=>61,),3=>array(0=>52,1=>53,),),69=>array(0=>array(0=>73,1=>60,),1=>array(0=>19,1=>79,),2=>array(0=>90,1=>49,),3=>array(0=>20,1=>93,),),70=>array(0=>array(0=>21,1=>46,),1=>array(0=>47,1=>99,),2=>array(0=>31,1=>70,),3=>array(0=>84,1=>92,),),71=>array(0=>array(0=>4,1=>32,),1=>array(0=>25,1=>36,),2=>array(0=>91,1=>55,),3=>array(0=>31,1=>30,),),72=>array(0=>array(0=>38,1=>40,),1=>array(0=>52,1=>92,),2=>array(0=>47,1=>92,),3=>array(0=>7,1=>68,),),73=>array(0=>array(0=>77,1=>87,),1=>array(0=>9,1=>10,),2=>array(0=>80,1=>47,),3=>array(0=>16,1=>60,),),74=>array(0=>array(0=>11,1=>100,),1=>array(0=>96,1=>67,),2=>array(0=>4,1=>1,),3=>array(0=>68,1=>57,),),75=>array(0=>array(0=>47,1=>7,),1=>array(0=>19,1=>93,),2=>array(0=>88,1=>71,),3=>array(0=>29,1=>68,),),76=>array(0=>array(0=>20,1=>4,),1=>array(0=>21,1=>94,),2=>array(0=>59,1=>80,),3=>array(0=>77,1=>8,),),77=>array(0=>array(0=>18,1=>65,),1=>array(0=>35,1=>24,),2=>array(0=>65,1=>68,),3=>array(0=>37,1=>85,),),78=>array(0=>array(0=>50,1=>16,),1=>array(0=>80,1=>34,),2=>array(0=>16,1=>72,),3=>array(0=>98,1=>33,),),79=>array(0=>array(0=>64,1=>40,),1=>array(0=>74,1=>65,),2=>array(0=>35,1=>29,),3=>array(0=>70,1=>75,),),80=>array(0=>array(0=>53,1=>59,),1=>array(0=>49,1=>56,),2=>array(0=>88,1=>20,),3=>array(0=>35,1=>49,),),81=>array(0=>array(0=>51,1=>58,),1=>array(0=>67,1=>75,),2=>array(0=>70,1=>61,),3=>array(0=>37,1=>35,),),82=>array(0=>array(0=>30,1=>54,),1=>array(0=>46,1=>93,),2=>array(0=>97,1=>33,),3=>array(0=>92,1=>46,),),83=>array(0=>array(0=>53,1=>28,),1=>array(0=>46,1=>43,),2=>array(0=>12,1=>32,),3=>array(0=>8,1=>58,),),84=>array(0=>array(0=>14,1=>28,),1=>array(0=>23,1=>69,),2=>array(0=>52,1=>36,),3=>array(0=>59,1=>66,),),85=>array(0=>array(0=>17,1=>44,),1=>array(0=>46,1=>16,),2=>array(0=>27,1=>26,),3=>array(0=>90,1=>63,),),86=>array(0=>array(0=>23,1=>25,),1=>array(0=>17,1=>64,),2=>array(0=>76,1=>87,),3=>array(0=>7,1=>100,),),87=>array(0=>array(0=>50,1=>30,),1=>array(0=>41,1=>34,),2=>array(0=>25,1=>32,),3=>array(0=>86,1=>34,),),88=>array(0=>array(0=>93,1=>62,),1=>array(0=>74,1=>41,),2=>array(0=>51,1=>2,),3=>array(0=>86,1=>32,),),89=>array(0=>array(0=>7,1=>67,),1=>array(0=>58,1=>0,),2=>array(0=>19,1=>57,),3=>array(0=>92,1=>92,),),90=>array(0=>array(0=>17,1=>13,),1=>array(0=>87,1=>73,),2=>array(0=>91,1=>14,),3=>array(0=>64,1=>18,),),91=>array(0=>array(0=>70,1=>30,),1=>array(0=>78,1=>71,),2=>array(0=>87,1=>17,),3=>array(0=>76,1=>78,),),92=>array(0=>array(0=>18,1=>85,),1=>array(0=>29,1=>49,),2=>array(0=>94,1=>76,),3=>array(0=>85,1=>42,),),93=>array(0=>array(0=>2,1=>22,),1=>array(0=>51,1=>12,),2=>array(0=>13,1=>65,),3=>array(0=>14,1=>66,),),94=>array(0=>array(0=>94,1=>63,),1=>array(0=>87,1=>82,),2=>array(0=>17,1=>56,),3=>array(0=>3,1=>68,),),95=>array(0=>array(0=>75,1=>51,),1=>array(0=>98,1=>96,),2=>array(0=>18,1=>51,),3=>array(0=>7,1=>35,),),96=>array(0=>array(0=>32,1=>96,),1=>array(0=>65,1=>14,),2=>array(0=>5,1=>41,),3=>array(0=>31,1=>32,),),97=>array(0=>array(0=>26,1=>61,),1=>array(0=>27,1=>74,),2=>array(0=>78,1=>47,),3=>array(0=>10,1=>83,),),98=>array(0=>array(0=>64,1=>46,),1=>array(0=>12,1=>89,),2=>array(0=>0,1=>7,),3=>array(0=>69,1=>25,),),99=>array(0=>array(0=>65,1=>27,),1=>array(0=>91,1=>39,),2=>array(0=>87,1=>10,),3=>array(0=>57,1=>17,),),100=>array(0=>array(0=>38,1=>65,),1=>array(0=>5,1=>40,),2=>array(0=>64,1=>43,),3=>array(0=>34,1=>97,),),101=>array(0=>array(0=>12,1=>33,),1=>array(0=>23,1=>33,),2=>array(0=>15,1=>41,),3=>array(0=>94,1=>28,),),102=>array(0=>array(0=>2,1=>37,),1=>array(0=>42,1=>8,),2=>array(0=>40,1=>27,),3=>array(0=>97,1=>54,),),103=>array(0=>array(0=>45,1=>99,),1=>array(0=>24,1=>76,),2=>array(0=>18,1=>26,),3=>array(0=>37,1=>44,),),104=>array(0=>array(0=>69,1=>5,),1=>array(0=>47,1=>75,),2=>array(0=>79,1=>31,),3=>array(0=>96,1=>36,),),105=>array(0=>array(0=>30,1=>75,),1=>array(0=>66,1=>51,),2=>array(0=>92,1=>49,),3=>array(0=>52,1=>18,),),106=>array(0=>array(0=>54,1=>32,),1=>array(0=>32,1=>12,),2=>array(0=>33,1=>29,),3=>array(0=>7,1=>40,),),107=>array(0=>array(0=>25,1=>52,),1=>array(0=>96,1=>87,),2=>array(0=>57,1=>60,),3=>array(0=>64,1=>6,),),108=>array(0=>array(0=>77,1=>98,),1=>array(0=>93,1=>1,),2=>array(0=>61,1=>76,),3=>array(0=>8,1=>58,),),109=>array(0=>array(0=>75,1=>37,),1=>array(0=>85,1=>10,),2=>array(0=>27,1=>27,),3=>array(0=>39,1=>92,),),110=>array(0=>array(0=>5,1=>85,),1=>array(0=>91,1=>33,),2=>array(0=>98,1=>6,),3=>array(0=>60,1=>33,),),111=>array(0=>array(0=>38,1=>64,),1=>array(0=>31,1=>49,),2=>array(0=>48,1=>69,),3=>array(0=>57,1=>7,),),112=>array(0=>array(0=>64,1=>28,),1=>array(0=>24,1=>2,),2=>array(0=>36,1=>19,),3=>array(0=>42,1=>63,),),113=>array(0=>array(0=>1,1=>1,),1=>array(0=>72,1=>95,),2=>array(0=>70,1=>3,),3=>array(0=>83,1=>71,),),114=>array(0=>array(0=>33,1=>11,),1=>array(0=>35,1=>99,),2=>array(0=>31,1=>62,),3=>array(0=>69,1=>58,),),115=>array(0=>array(0=>95,1=>9,),1=>array(0=>40,1=>36,),2=>array(0=>49,1=>99,),3=>array(0=>0,1=>69,),),116=>array(0=>array(0=>24,1=>70,),1=>array(0=>11,1=>68,),2=>array(0=>41,1=>8,),3=>array(0=>83,1=>45,),),117=>array(0=>array(0=>71,1=>94,),1=>array(0=>97,1=>90,),2=>array(0=>38,1=>87,),3=>array(0=>100,1=>51,),),118=>array(0=>array(0=>17,1=>57,),1=>array(0=>20,1=>88,),2=>array(0=>28,1=>41,),3=>array(0=>36,1=>95,),),119=>array(0=>array(0=>94,1=>33,),1=>array(0=>58,1=>73,),2=>array(0=>75,1=>64,),3=>array(0=>24,1=>10,),),120=>array(0=>array(0=>54,1=>12,),1=>array(0=>59,1=>56,),2=>array(0=>98,1=>61,),3=>array(0=>39,1=>6,),),121=>array(0=>array(0=>50,1=>36,),1=>array(0=>9,1=>87,),2=>array(0=>74,1=>34,),3=>array(0=>75,1=>40,),),122=>array(0=>array(0=>3,1=>71,),1=>array(0=>92,1=>3,),2=>array(0=>47,1=>73,),3=>array(0=>48,1=>80,),),123=>array(0=>array(0=>64,1=>8,),1=>array(0=>58,1=>90,),2=>array(0=>85,1=>81,),3=>array(0=>72,1=>22,),),124=>array(0=>array(0=>48,1=>72,),1=>array(0=>69,1=>11,),2=>array(0=>5,1=>69,),3=>array(0=>82,1=>16,),),125=>array(0=>array(0=>99,1=>49,),1=>array(0=>47,1=>17,),2=>array(0=>74,1=>98,),3=>array(0=>56,1=>41,),),126=>array(0=>array(0=>89,1=>9,),1=>array(0=>91,1=>0,),2=>array(0=>53,1=>90,),3=>array(0=>12,1=>30,),),127=>array(0=>array(0=>98,1=>22,),1=>array(0=>2,1=>27,),2=>array(0=>84,1=>10,),3=>array(0=>73,1=>90,),),128=>array(0=>array(0=>17,1=>66,),1=>array(0=>6,1=>15,),2=>array(0=>23,1=>91,),3=>array(0=>58,1=>44,),),129=>array(0=>array(0=>79,1=>24,),1=>array(0=>7,1=>87,),2=>array(0=>41,1=>90,),3=>array(0=>33,1=>96,),),130=>array(0=>array(0=>89,1=>10,),1=>array(0=>32,1=>99,),2=>array(0=>35,1=>7,),3=>array(0=>72,1=>51,),),131=>array(0=>array(0=>44,1=>43,),1=>array(0=>32,1=>34,),2=>array(0=>10,1=>5,),3=>array(0=>49,1=>40,),),132=>array(0=>array(0=>63,1=>18,),1=>array(0=>79,1=>77,),2=>array(0=>78,1=>12,),3=>array(0=>61,1=>23,),),133=>array(0=>array(0=>39,1=>21,),1=>array(0=>5,1=>8,),2=>array(0=>41,1=>89,),3=>array(0=>63,1=>19,),),134=>array(0=>array(0=>5,1=>73,),1=>array(0=>67,1=>32,),2=>array(0=>7,1=>91,),3=>array(0=>44,1=>5,),),135=>array(0=>array(0=>5,1=>44,),1=>array(0=>87,1=>62,),2=>array(0=>38,1=>79,),3=>array(0=>63,1=>54,),),136=>array(0=>array(0=>56,1=>5,),1=>array(0=>81,1=>68,),2=>array(0=>10,1=>29,),3=>array(0=>100,1=>36,),),137=>array(0=>array(0=>92,1=>71,),1=>array(0=>90,1=>9,),2=>array(0=>65,1=>76,),3=>array(0=>26,1=>87,),),138=>array(0=>array(0=>11,1=>48,),1=>array(0=>56,1=>91,),2=>array(0=>93,1=>64,),3=>array(0=>99,1=>2,),),139=>array(0=>array(0=>7,1=>26,),1=>array(0=>60,1=>74,),2=>array(0=>65,1=>89,),3=>array(0=>76,1=>26,),),140=>array(0=>array(0=>3,1=>31,),1=>array(0=>48,1=>41,),2=>array(0=>64,1=>64,),3=>array(0=>63,1=>7,),),141=>array(0=>array(0=>54,1=>15,),1=>array(0=>94,1=>58,),2=>array(0=>61,1=>22,),3=>array(0=>33,1=>81,),),142=>array(0=>array(0=>86,1=>46,),1=>array(0=>76,1=>8,),2=>array(0=>15,1=>20,),3=>array(0=>65,1=>66,),),143=>array(0=>array(0=>80,1=>84,),1=>array(0=>56,1=>29,),2=>array(0=>75,1=>36,),3=>array(0=>73,1=>86,),),144=>array(0=>array(0=>71,1=>16,),1=>array(0=>13,1=>36,),2=>array(0=>4,1=>16,),3=>array(0=>72,1=>9,),),145=>array(0=>array(0=>55,1=>88,),1=>array(0=>4,1=>58,),2=>array(0=>19,1=>84,),3=>array(0=>62,1=>25,),),146=>array(0=>array(0=>73,1=>38,),1=>array(0=>43,1=>13,),2=>array(0=>30,1=>4,),3=>array(0=>73,1=>79,),),147=>array(0=>array(0=>17,1=>54,),1=>array(0=>33,1=>78,),2=>array(0=>14,1=>13,),3=>array(0=>97,1=>65,),),148=>array(0=>array(0=>27,1=>5,),1=>array(0=>15,1=>39,),2=>array(0=>38,1=>72,),3=>array(0=>18,1=>11,),),149=>array(0=>array(0=>78,1=>99,),1=>array(0=>54,1=>20,),2=>array(0=>71,1=>8,),3=>array(0=>4,1=>64,),),150=>array(0=>array(0=>58,1=>51,),1=>array(0=>69,1=>44,),2=>array(0=>33,1=>19,),3=>array(0=>67,1=>88,),),151=>array(0=>array(0=>69,1=>33,),1=>array(0=>22,1=>64,),2=>array(0=>30,1=>61,),3=>array(0=>75,1=>96,),),152=>array(0=>array(0=>38,1=>89,),1=>array(0=>96,1=>25,),2=>array(0=>43,1=>83,),3=>array(0=>20,1=>30,),),153=>array(0=>array(0=>87,1=>44,),1=>array(0=>84,1=>51,),2=>array(0=>1,1=>94,),3=>array(0=>92,1=>88,),),154=>array(0=>array(0=>43,1=>46,),1=>array(0=>37,1=>90,),2=>array(0=>5,1=>13,),3=>array(0=>58,1=>85,),),155=>array(0=>array(0=>37,1=>57,),1=>array(0=>98,1=>75,),2=>array(0=>90,1=>62,),3=>array(0=>3,1=>61,),),156=>array(0=>array(0=>25,1=>68,),1=>array(0=>30,1=>36,),2=>array(0=>10,1=>48,),3=>array(0=>44,1=>15,),),157=>array(0=>array(0=>8,1=>22,),1=>array(0=>91,1=>46,),2=>array(0=>80,1=>64,),3=>array(0=>72,1=>62,),),158=>array(0=>array(0=>96,1=>60,),1=>array(0=>89,1=>53,),2=>array(0=>78,1=>73,),3=>array(0=>70,1=>27,),),159=>array(0=>array(0=>42,1=>65,),1=>array(0=>51,1=>77,),2=>array(0=>98,1=>36,),3=>array(0=>53,1=>67,),),160=>array(0=>array(0=>19,1=>2,),1=>array(0=>70,1=>54,),2=>array(0=>45,1=>2,),3=>array(0=>1,1=>0,),),161=>array(0=>array(0=>3,1=>99,),1=>array(0=>58,1=>5,),2=>array(0=>26,1=>45,),3=>array(0=>15,1=>33,),),162=>array(0=>array(0=>88,1=>9,),1=>array(0=>50,1=>97,),2=>array(0=>46,1=>27,),3=>array(0=>50,1=>45,),),163=>array(0=>array(0=>94,1=>24,),1=>array(0=>62,1=>40,),2=>array(0=>52,1=>72,),3=>array(0=>10,1=>13,),),164=>array(0=>array(0=>33,1=>14,),1=>array(0=>6,1=>31,),2=>array(0=>16,1=>36,),3=>array(0=>20,1=>72,),),165=>array(0=>array(0=>43,1=>78,),1=>array(0=>76,1=>67,),2=>array(0=>49,1=>26,),3=>array(0=>94,1=>15,),),166=>array(0=>array(0=>5,1=>65,),1=>array(0=>11,1=>82,),2=>array(0=>20,1=>37,),3=>array(0=>12,1=>15,),),167=>array(0=>array(0=>47,1=>26,),1=>array(0=>97,1=>70,),2=>array(0=>22,1=>62,),3=>array(0=>60,1=>66,),),168=>array(0=>array(0=>39,1=>21,),1=>array(0=>23,1=>55,),2=>array(0=>76,1=>4,),3=>array(0=>76,1=>66,),),169=>array(0=>array(0=>77,1=>85,),1=>array(0=>77,1=>5,),2=>array(0=>82,1=>61,),3=>array(0=>7,1=>82,),),170=>array(0=>array(0=>16,1=>29,),1=>array(0=>54,1=>24,),2=>array(0=>60,1=>0,),3=>array(0=>12,1=>72,),),171=>array(0=>array(0=>81,1=>29,),1=>array(0=>62,1=>30,),2=>array(0=>11,1=>17,),3=>array(0=>69,1=>53,),),172=>array(0=>array(0=>92,1=>95,),1=>array(0=>2,1=>58,),2=>array(0=>1,1=>82,),3=>array(0=>73,1=>13,),),173=>array(0=>array(0=>33,1=>19,),1=>array(0=>90,1=>42,),2=>array(0=>32,1=>72,),3=>array(0=>25,1=>72,),),174=>array(0=>array(0=>19,1=>96,),1=>array(0=>60,1=>31,),2=>array(0=>7,1=>96,),3=>array(0=>11,1=>69,),),175=>array(0=>array(0=>51,1=>41,),1=>array(0=>27,1=>97,),2=>array(0=>39,1=>24,),3=>array(0=>85,1=>41,),),176=>array(0=>array(0=>48,1=>28,),1=>array(0=>71,1=>62,),2=>array(0=>22,1=>14,),3=>array(0=>69,1=>92,),),177=>array(0=>array(0=>5,1=>25,),1=>array(0=>18,1=>48,),2=>array(0=>2,1=>95,),3=>array(0=>3,1=>59,),),178=>array(0=>array(0=>96,1=>37,),1=>array(0=>50,1=>90,),2=>array(0=>27,1=>49,),3=>array(0=>3,1=>71,),),179=>array(0=>array(0=>74,1=>9,),1=>array(0=>55,1=>12,),2=>array(0=>19,1=>5,),3=>array(0=>97,1=>27,),),180=>array(0=>array(0=>33,1=>73,),1=>array(0=>15,1=>43,),2=>array(0=>88,1=>81,),3=>array(0=>21,1=>82,),),181=>array(0=>array(0=>39,1=>49,),1=>array(0=>73,1=>10,),2=>array(0=>47,1=>96,),3=>array(0=>37,1=>54,),),182=>array(0=>array(0=>21,1=>16,),1=>array(0=>54,1=>99,),2=>array(0=>84,1=>33,),3=>array(0=>97,1=>13,),),183=>array(0=>array(0=>34,1=>13,),1=>array(0=>78,1=>88,),2=>array(0=>42,1=>19,),3=>array(0=>57,1=>44,),),184=>array(0=>array(0=>18,1=>82,),1=>array(0=>12,1=>100,),2=>array(0=>73,1=>26,),3=>array(0=>60,1=>43,),),185=>array(0=>array(0=>66,1=>71,),1=>array(0=>71,1=>26,),2=>array(0=>15,1=>100,),3=>array(0=>24,1=>93,),),186=>array(0=>array(0=>95,1=>73,),1=>array(0=>74,1=>79,),2=>array(0=>22,1=>26,),3=>array(0=>58,1=>64,),),187=>array(0=>array(0=>94,1=>22,),1=>array(0=>80,1=>98,),2=>array(0=>48,1=>62,),3=>array(0=>92,1=>2,),),188=>array(0=>array(0=>63,1=>8,),1=>array(0=>40,1=>81,),2=>array(0=>83,1=>43,),3=>array(0=>29,1=>53,),),189=>array(0=>array(0=>18,1=>66,),1=>array(0=>26,1=>82,),2=>array(0=>93,1=>70,),3=>array(0=>29,1=>66,),),190=>array(0=>array(0=>61,1=>0,),1=>array(0=>24,1=>57,),2=>array(0=>31,1=>94,),3=>array(0=>34,1=>83,),),191=>array(0=>array(0=>31,1=>66,),1=>array(0=>31,1=>87,),2=>array(0=>62,1=>92,),3=>array(0=>2,1=>66,),),192=>array(0=>array(0=>28,1=>54,),1=>array(0=>65,1=>36,),2=>array(0=>90,1=>36,),3=>array(0=>76,1=>6,),),193=>array(0=>array(0=>16,1=>74,),1=>array(0=>69,1=>24,),2=>array(0=>34,1=>39,),3=>array(0=>32,1=>76,),),194=>array(0=>array(0=>89,1=>100,),1=>array(0=>49,1=>37,),2=>array(0=>40,1=>10,),3=>array(0=>67,1=>98,),),195=>array(0=>array(0=>59,1=>63,),1=>array(0=>71,1=>46,),2=>array(0=>1,1=>18,),3=>array(0=>53,1=>33,),),196=>array(0=>array(0=>12,1=>2,),1=>array(0=>81,1=>8,),2=>array(0=>36,1=>30,),3=>array(0=>62,1=>14,),),197=>array(0=>array(0=>73,1=>55,),1=>array(0=>30,1=>8,),2=>array(0=>59,1=>16,),3=>array(0=>54,1=>91,),),198=>array(0=>array(0=>34,1=>28,),1=>array(0=>90,1=>49,),2=>array(0=>100,1=>40,),3=>array(0=>80,1=>61,),),199=>array(0=>array(0=>25,1=>13,),1=>array(0=>69,1=>38,),2=>array(0=>99,1=>96,),3=>array(0=>31,1=>62,),),200=>array(0=>array(0=>16,1=>84,),1=>array(0=>0,1=>95,),2=>array(0=>58,1=>63,),3=>array(0=>59,1=>7,),),201=>array(0=>array(0=>51,1=>11,),1=>array(0=>74,1=>45,),2=>array(0=>39,1=>32,),3=>array(0=>24,1=>37,),),202=>array(0=>array(0=>34,1=>39,),1=>array(0=>83,1=>28,),2=>array(0=>52,1=>32,),3=>array(0=>46,1=>40,),),203=>array(0=>array(0=>45,1=>80,),1=>array(0=>99,1=>96,),2=>array(0=>51,1=>74,),3=>array(0=>8,1=>65,),),204=>array(0=>array(0=>3,1=>42,),1=>array(0=>78,1=>65,),2=>array(0=>84,1=>20,),3=>array(0=>62,1=>99,),),205=>array(0=>array(0=>32,1=>62,),1=>array(0=>56,1=>50,),2=>array(0=>60,1=>69,),3=>array(0=>10,1=>27,),),206=>array(0=>array(0=>40,1=>94,),1=>array(0=>49,1=>81,),2=>array(0=>94,1=>30,),3=>array(0=>54,1=>56,),),207=>array(0=>array(0=>40,1=>24,),1=>array(0=>48,1=>71,),2=>array(0=>62,1=>39,),3=>array(0=>44,1=>60,),),208=>array(0=>array(0=>18,1=>60,),1=>array(0=>78,1=>99,),2=>array(0=>9,1=>59,),3=>array(0=>74,1=>55,),),209=>array(0=>array(0=>83,1=>92,),1=>array(0=>83,1=>1,),2=>array(0=>42,1=>33,),3=>array(0=>10,1=>56,),),210=>array(0=>array(0=>86,1=>82,),1=>array(0=>70,1=>29,),2=>array(0=>89,1=>49,),3=>array(0=>47,1=>81,),),211=>array(0=>array(0=>0,1=>75,),1=>array(0=>58,1=>85,),2=>array(0=>66,1=>43,),3=>array(0=>86,1=>18,),),212=>array(0=>array(0=>85,1=>42,),1=>array(0=>6,1=>26,),2=>array(0=>58,1=>42,),3=>array(0=>0,1=>81,),),213=>array(0=>array(0=>76,1=>4,),1=>array(0=>94,1=>94,),2=>array(0=>85,1=>29,),3=>array(0=>97,1=>3,),),214=>array(0=>array(0=>67,1=>78,),1=>array(0=>94,1=>67,),2=>array(0=>13,1=>46,),3=>array(0=>64,1=>43,),),215=>array(0=>array(0=>96,1=>1,),1=>array(0=>63,1=>58,),2=>array(0=>50,1=>67,),3=>array(0=>88,1=>33,),),216=>array(0=>array(0=>43,1=>49,),1=>array(0=>55,1=>17,),2=>array(0=>92,1=>65,),3=>array(0=>0,1=>89,),),217=>array(0=>array(0=>3,1=>48,),1=>array(0=>45,1=>40,),2=>array(0=>3,1=>65,),3=>array(0=>97,1=>35,),),218=>array(0=>array(0=>51,1=>61,),1=>array(0=>82,1=>27,),2=>array(0=>93,1=>60,),3=>array(0=>0,1=>80,),),219=>array(0=>array(0=>44,1=>63,),1=>array(0=>51,1=>48,),2=>array(0=>98,1=>71,),3=>array(0=>17,1=>32,),),220=>array(0=>array(0=>20,1=>39,),1=>array(0=>49,1=>11,),2=>array(0=>56,1=>72,),3=>array(0=>18,1=>26,),),221=>array(0=>array(0=>74,1=>11,),1=>array(0=>19,1=>87,),2=>array(0=>79,1=>16,),3=>array(0=>80,1=>72,),),222=>array(0=>array(0=>31,1=>98,),1=>array(0=>32,1=>58,),2=>array(0=>99,1=>86,),3=>array(0=>27,1=>95,),),223=>array(0=>array(0=>20,1=>16,),1=>array(0=>68,1=>16,),2=>array(0=>81,1=>23,),3=>array(0=>83,1=>24,),),224=>array(0=>array(0=>79,1=>38,),1=>array(0=>45,1=>10,),2=>array(0=>4,1=>70,),3=>array(0=>36,1=>42,),),225=>array(0=>array(0=>82,1=>33,),1=>array(0=>76,1=>86,),2=>array(0=>64,1=>74,),3=>array(0=>13,1=>52,),),226=>array(0=>array(0=>9,1=>49,),1=>array(0=>78,1=>78,),2=>array(0=>71,1=>93,),3=>array(0=>27,1=>8,),),227=>array(0=>array(0=>14,1=>66,),1=>array(0=>84,1=>54,),2=>array(0=>22,1=>51,),3=>array(0=>9,1=>63,),),228=>array(0=>array(0=>75,1=>15,),1=>array(0=>92,1=>88,),2=>array(0=>29,1=>7,),3=>array(0=>68,1=>41,),),229=>array(0=>array(0=>75,1=>26,),1=>array(0=>74,1=>24,),2=>array(0=>25,1=>92,),3=>array(0=>75,1=>68,),),230=>array(0=>array(0=>78,1=>82,),1=>array(0=>89,1=>45,),2=>array(0=>76,1=>70,),3=>array(0=>45,1=>27,),),231=>array(0=>array(0=>62,1=>22,),1=>array(0=>88,1=>20,),2=>array(0=>15,1=>6,),3=>array(0=>71,1=>69,),),232=>array(0=>array(0=>69,1=>63,),1=>array(0=>77,1=>70,),2=>array(0=>8,1=>74,),3=>array(0=>41,1=>99,),),233=>array(0=>array(0=>52,1=>76,),1=>array(0=>57,1=>0,),2=>array(0=>55,1=>55,),3=>array(0=>15,1=>36,),),234=>array(0=>array(0=>41,1=>5,),1=>array(0=>5,1=>7,),2=>array(0=>79,1=>4,),3=>array(0=>24,1=>7,),),235=>array(0=>array(0=>52,1=>16,),1=>array(0=>19,1=>65,),2=>array(0=>26,1=>43,),3=>array(0=>80,1=>60,),),236=>array(0=>array(0=>25,1=>56,),1=>array(0=>97,1=>47,),2=>array(0=>44,1=>17,),3=>array(0=>90,1=>80,),),237=>array(0=>array(0=>60,1=>96,),1=>array(0=>79,1=>28,),2=>array(0=>72,1=>62,),3=>array(0=>86,1=>73,),),238=>array(0=>array(0=>72,1=>65,),1=>array(0=>63,1=>21,),2=>array(0=>86,1=>57,),3=>array(0=>37,1=>86,),),239=>array(0=>array(0=>75,1=>58,),1=>array(0=>65,1=>66,),2=>array(0=>33,1=>69,),3=>array(0=>82,1=>7,),),240=>array(0=>array(0=>1,1=>29,),1=>array(0=>44,1=>30,),2=>array(0=>36,1=>64,),3=>array(0=>60,1=>83,),),241=>array(0=>array(0=>87,1=>36,),1=>array(0=>86,1=>84,),2=>array(0=>24,1=>84,),3=>array(0=>50,1=>37,),),242=>array(0=>array(0=>84,1=>39,),1=>array(0=>67,1=>14,),2=>array(0=>84,1=>32,),3=>array(0=>33,1=>0,),),243=>array(0=>array(0=>27,1=>22,),1=>array(0=>21,1=>46,),2=>array(0=>26,1=>85,),3=>array(0=>83,1=>19,),),244=>array(0=>array(0=>72,1=>36,),1=>array(0=>80,1=>78,),2=>array(0=>56,1=>25,),3=>array(0=>38,1=>67,),),245=>array(0=>array(0=>92,1=>53,),1=>array(0=>5,1=>31,),2=>array(0=>77,1=>74,),3=>array(0=>91,1=>46,),),246=>array(0=>array(0=>84,1=>78,),1=>array(0=>18,1=>45,),2=>array(0=>56,1=>89,),3=>array(0=>99,1=>21,),),247=>array(0=>array(0=>37,1=>67,),1=>array(0=>52,1=>30,),2=>array(0=>3,1=>15,),3=>array(0=>55,1=>82,),),248=>array(0=>array(0=>97,1=>31,),1=>array(0=>44,1=>60,),2=>array(0=>17,1=>86,),3=>array(0=>56,1=>95,),),249=>array(0=>array(0=>13,1=>52,),1=>array(0=>33,1=>56,),2=>array(0=>44,1=>24,),3=>array(0=>55,1=>1,),),250=>array(0=>array(0=>4,1=>87,),1=>array(0=>83,1=>39,),2=>array(0=>78,1=>32,),3=>array(0=>29,1=>92,),),251=>array(0=>array(0=>4,1=>85,),1=>array(0=>95,1=>42,),2=>array(0=>90,1=>64,),3=>array(0=>7,1=>37,),),252=>array(0=>array(0=>12,1=>57,),1=>array(0=>48,1=>0,),2=>array(0=>95,1=>9,),3=>array(0=>34,1=>53,),),253=>array(0=>array(0=>16,1=>94,),1=>array(0=>44,1=>35,),2=>array(0=>66,1=>63,),3=>array(0=>43,1=>72,),),254=>array(0=>array(0=>32,1=>65,),1=>array(0=>30,1=>76,),2=>array(0=>38,1=>61,),3=>array(0=>8,1=>29,),),255=>array(0=>array(0=>58,1=>84,),1=>array(0=>18,1=>77,),2=>array(0=>95,1=>27,),3=>array(0=>12,1=>62,),),256=>array(0=>array(0=>25,1=>78,),1=>array(0=>55,1=>92,),2=>array(0=>93,1=>43,),3=>array(0=>47,1=>49,),),257=>array(0=>array(0=>1,1=>48,),1=>array(0=>93,1=>59,),2=>array(0=>20,1=>94,),3=>array(0=>81,1=>44,),),258=>array(0=>array(0=>64,1=>42,),1=>array(0=>11,1=>38,),2=>array(0=>17,1=>76,),3=>array(0=>100,1=>43,),),259=>array(0=>array(0=>64,1=>21,),1=>array(0=>34,1=>88,),2=>array(0=>98,1=>15,),3=>array(0=>16,1=>2,),),260=>array(0=>array(0=>2,1=>54,),1=>array(0=>38,1=>49,),2=>array(0=>40,1=>4,),3=>array(0=>6,1=>80,),),261=>array(0=>array(0=>2,1=>19,),1=>array(0=>48,1=>100,),2=>array(0=>26,1=>93,),3=>array(0=>1,1=>91,),),262=>array(0=>array(0=>88,1=>36,),1=>array(0=>98,1=>30,),2=>array(0=>78,1=>26,),3=>array(0=>78,1=>94,),),263=>array(0=>array(0=>26,1=>17,),1=>array(0=>36,1=>39,),2=>array(0=>6,1=>94,),3=>array(0=>58,1=>41,),),264=>array(0=>array(0=>63,1=>38,),1=>array(0=>81,1=>73,),2=>array(0=>89,1=>38,),3=>array(0=>98,1=>34,),),265=>array(0=>array(0=>11,1=>48,),1=>array(0=>1,1=>5,),2=>array(0=>25,1=>1,),3=>array(0=>20,1=>62,),),266=>array(0=>array(0=>92,1=>91,),1=>array(0=>34,1=>93,),2=>array(0=>7,1=>35,),3=>array(0=>88,1=>62,),),267=>array(0=>array(0=>97,1=>9,),1=>array(0=>17,1=>65,),2=>array(0=>36,1=>100,),3=>array(0=>60,1=>24,),),268=>array(0=>array(0=>70,1=>18,),1=>array(0=>31,1=>49,),2=>array(0=>70,1=>58,),3=>array(0=>98,1=>99,),),269=>array(0=>array(0=>95,1=>91,),1=>array(0=>25,1=>80,),2=>array(0=>69,1=>40,),3=>array(0=>48,1=>65,),),270=>array(0=>array(0=>56,1=>33,),1=>array(0=>1,1=>86,),2=>array(0=>41,1=>23,),3=>array(0=>93,1=>78,),),271=>array(0=>array(0=>78,1=>89,),1=>array(0=>13,1=>69,),2=>array(0=>77,1=>81,),3=>array(0=>21,1=>77,),),272=>array(0=>array(0=>82,1=>33,),1=>array(0=>22,1=>67,),2=>array(0=>79,1=>16,),3=>array(0=>62,1=>60,),),273=>array(0=>array(0=>64,1=>29,),1=>array(0=>42,1=>37,),2=>array(0=>12,1=>4,),3=>array(0=>27,1=>54,),),274=>array(0=>array(0=>100,1=>95,),1=>array(0=>91,1=>81,),2=>array(0=>66,1=>6,),3=>array(0=>27,1=>21,),),275=>array(0=>array(0=>63,1=>45,),1=>array(0=>37,1=>89,),2=>array(0=>54,1=>48,),3=>array(0=>13,1=>15,),),276=>array(0=>array(0=>87,1=>77,),1=>array(0=>7,1=>71,),2=>array(0=>73,1=>17,),3=>array(0=>84,1=>8,),),277=>array(0=>array(0=>47,1=>58,),1=>array(0=>23,1=>11,),2=>array(0=>32,1=>14,),3=>array(0=>70,1=>36,),),278=>array(0=>array(0=>27,1=>86,),1=>array(0=>52,1=>91,),2=>array(0=>31,1=>34,),3=>array(0=>42,1=>42,),),279=>array(0=>array(0=>2,1=>16,),1=>array(0=>25,1=>17,),2=>array(0=>26,1=>78,),3=>array(0=>12,1=>62,),),280=>array(0=>array(0=>13,1=>28,),1=>array(0=>3,1=>35,),2=>array(0=>79,1=>15,),3=>array(0=>95,1=>34,),),281=>array(0=>array(0=>48,1=>35,),1=>array(0=>5,1=>51,),2=>array(0=>85,1=>42,),3=>array(0=>36,1=>18,),),282=>array(0=>array(0=>21,1=>16,),1=>array(0=>20,1=>59,),2=>array(0=>77,1=>1,),3=>array(0=>85,1=>95,),),283=>array(0=>array(0=>0,1=>78,),1=>array(0=>98,1=>46,),2=>array(0=>37,1=>73,),3=>array(0=>3,1=>44,),),284=>array(0=>array(0=>5,1=>96,),1=>array(0=>48,1=>11,),2=>array(0=>43,1=>24,),3=>array(0=>42,1=>96,),),285=>array(0=>array(0=>99,1=>63,),1=>array(0=>62,1=>74,),2=>array(0=>57,1=>45,),3=>array(0=>5,1=>65,),),286=>array(0=>array(0=>9,1=>2,),1=>array(0=>28,1=>15,),2=>array(0=>52,1=>64,),3=>array(0=>47,1=>9,),),287=>array(0=>array(0=>40,1=>2,),1=>array(0=>22,1=>69,),2=>array(0=>41,1=>97,),3=>array(0=>6,1=>40,),),288=>array(0=>array(0=>65,1=>98,),1=>array(0=>90,1=>1,),2=>array(0=>67,1=>34,),3=>array(0=>30,1=>41,),),289=>array(0=>array(0=>47,1=>21,),1=>array(0=>63,1=>12,),2=>array(0=>61,1=>96,),3=>array(0=>12,1=>43,),),290=>array(0=>array(0=>26,1=>90,),1=>array(0=>73,1=>85,),2=>array(0=>32,1=>36,),3=>array(0=>0,1=>37,),),291=>array(0=>array(0=>41,1=>50,),1=>array(0=>40,1=>92,),2=>array(0=>44,1=>34,),3=>array(0=>39,1=>55,),),292=>array(0=>array(0=>20,1=>92,),1=>array(0=>63,1=>9,),2=>array(0=>8,1=>25,),3=>array(0=>41,1=>96,),),293=>array(0=>array(0=>33,1=>48,),1=>array(0=>33,1=>14,),2=>array(0=>70,1=>98,),3=>array(0=>22,1=>70,),),294=>array(0=>array(0=>80,1=>66,),1=>array(0=>22,1=>92,),2=>array(0=>51,1=>88,),3=>array(0=>38,1=>60,),),295=>array(0=>array(0=>79,1=>28,),1=>array(0=>53,1=>73,),2=>array(0=>3,1=>87,),3=>array(0=>28,1=>79,),),296=>array(0=>array(0=>71,1=>4,),1=>array(0=>89,1=>18,),2=>array(0=>21,1=>40,),3=>array(0=>28,1=>54,),),297=>array(0=>array(0=>24,1=>4,),1=>array(0=>86,1=>94,),2=>array(0=>95,1=>2,),3=>array(0=>71,1=>100,),),298=>array(0=>array(0=>99,1=>40,),1=>array(0=>97,1=>10,),2=>array(0=>87,1=>25,),3=>array(0=>46,1=>54,),),299=>array(0=>array(0=>49,1=>77,),1=>array(0=>66,1=>3,),2=>array(0=>39,1=>45,),3=>array(0=>2,1=>95,),),300=>array(0=>array(0=>54,1=>8,),1=>array(0=>33,1=>72,),2=>array(0=>7,1=>44,),3=>array(0=>79,1=>24,),),301=>array(0=>array(0=>89,1=>14,),1=>array(0=>0,1=>79,),2=>array(0=>69,1=>23,),3=>array(0=>82,1=>8,),),302=>array(0=>array(0=>55,1=>38,),1=>array(0=>63,1=>87,),2=>array(0=>12,1=>48,),3=>array(0=>56,1=>28,),),303=>array(0=>array(0=>60,1=>63,),1=>array(0=>72,1=>43,),2=>array(0=>27,1=>3,),3=>array(0=>79,1=>75,),),304=>array(0=>array(0=>76,1=>38,),1=>array(0=>47,1=>96,),2=>array(0=>97,1=>24,),3=>array(0=>70,1=>25,),),305=>array(0=>array(0=>4,1=>11,),1=>array(0=>10,1=>76,),2=>array(0=>25,1=>91,),3=>array(0=>56,1=>20,),),306=>array(0=>array(0=>41,1=>28,),1=>array(0=>66,1=>63,),2=>array(0=>50,1=>31,),3=>array(0=>21,1=>97,),),307=>array(0=>array(0=>9,1=>13,),1=>array(0=>21,1=>15,),2=>array(0=>62,1=>21,),3=>array(0=>43,1=>50,),),308=>array(0=>array(0=>85,1=>22,),1=>array(0=>45,1=>94,),2=>array(0=>7,1=>51,),3=>array(0=>46,1=>24,),),309=>array(0=>array(0=>85,1=>5,),1=>array(0=>27,1=>63,),2=>array(0=>49,1=>82,),3=>array(0=>44,1=>45,),),310=>array(0=>array(0=>54,1=>100,),1=>array(0=>9,1=>1,),2=>array(0=>45,1=>2,),3=>array(0=>99,1=>40,),),311=>array(0=>array(0=>36,1=>0,),1=>array(0=>24,1=>34,),2=>array(0=>55,1=>65,),3=>array(0=>39,1=>6,),),312=>array(0=>array(0=>27,1=>14,),1=>array(0=>18,1=>50,),2=>array(0=>9,1=>9,),3=>array(0=>56,1=>99,),),313=>array(0=>array(0=>83,1=>100,),1=>array(0=>95,1=>94,),2=>array(0=>81,1=>17,),3=>array(0=>88,1=>2,),),314=>array(0=>array(0=>30,1=>90,),1=>array(0=>28,1=>14,),2=>array(0=>44,1=>99,),3=>array(0=>50,1=>47,),),315=>array(0=>array(0=>50,1=>76,),1=>array(0=>41,1=>64,),2=>array(0=>17,1=>38,),3=>array(0=>40,1=>57,),),316=>array(0=>array(0=>10,1=>98,),1=>array(0=>78,1=>16,),2=>array(0=>42,1=>58,),3=>array(0=>53,1=>78,),),317=>array(0=>array(0=>5,1=>65,),1=>array(0=>90,1=>72,),2=>array(0=>12,1=>28,),3=>array(0=>30,1=>95,),),318=>array(0=>array(0=>28,1=>72,),1=>array(0=>55,1=>93,),2=>array(0=>21,1=>33,),3=>array(0=>100,1=>44,),),319=>array(0=>array(0=>18,1=>84,),1=>array(0=>21,1=>75,),2=>array(0=>44,1=>11,),3=>array(0=>6,1=>48,),),320=>array(0=>array(0=>44,1=>21,),1=>array(0=>91,1=>34,),2=>array(0=>57,1=>8,),3=>array(0=>34,1=>59,),),321=>array(0=>array(0=>44,1=>82,),1=>array(0=>3,1=>41,),2=>array(0=>6,1=>52,),3=>array(0=>22,1=>36,),),322=>array(0=>array(0=>6,1=>81,),1=>array(0=>97,1=>31,),2=>array(0=>31,1=>63,),3=>array(0=>53,1=>54,),),323=>array(0=>array(0=>34,1=>61,),1=>array(0=>23,1=>8,),2=>array(0=>59,1=>82,),3=>array(0=>100,1=>11,),),324=>array(0=>array(0=>5,1=>48,),1=>array(0=>99,1=>91,),2=>array(0=>13,1=>92,),3=>array(0=>9,1=>76,),),325=>array(0=>array(0=>40,1=>84,),1=>array(0=>85,1=>15,),2=>array(0=>54,1=>91,),3=>array(0=>75,1=>57,),),326=>array(0=>array(0=>39,1=>11,),1=>array(0=>36,1=>66,),2=>array(0=>44,1=>5,),3=>array(0=>11,1=>83,),),327=>array(0=>array(0=>62,1=>73,),1=>array(0=>86,1=>92,),2=>array(0=>40,1=>43,),3=>array(0=>92,1=>30,),),328=>array(0=>array(0=>61,1=>32,),1=>array(0=>82,1=>79,),2=>array(0=>49,1=>11,),3=>array(0=>42,1=>21,),),329=>array(0=>array(0=>97,1=>30,),1=>array(0=>96,1=>19,),2=>array(0=>73,1=>60,),3=>array(0=>56,1=>75,),),330=>array(0=>array(0=>58,1=>2,),1=>array(0=>68,1=>33,),2=>array(0=>27,1=>79,),3=>array(0=>45,1=>59,),),331=>array(0=>array(0=>46,1=>3,),1=>array(0=>67,1=>86,),2=>array(0=>63,1=>47,),3=>array(0=>45,1=>21,),),332=>array(0=>array(0=>65,1=>84,),1=>array(0=>4,1=>2,),2=>array(0=>9,1=>65,),3=>array(0=>58,1=>63,),),333=>array(0=>array(0=>64,1=>38,),1=>array(0=>51,1=>2,),2=>array(0=>83,1=>44,),3=>array(0=>80,1=>46,),),334=>array(0=>array(0=>98,1=>83,),1=>array(0=>41,1=>3,),2=>array(0=>69,1=>11,),3=>array(0=>72,1=>22,),),335=>array(0=>array(0=>81,1=>86,),1=>array(0=>88,1=>52,),2=>array(0=>91,1=>12,),3=>array(0=>71,1=>79,),),336=>array(0=>array(0=>65,1=>10,),1=>array(0=>19,1=>11,),2=>array(0=>14,1=>39,),3=>array(0=>0,1=>7,),),337=>array(0=>array(0=>10,1=>49,),1=>array(0=>94,1=>18,),2=>array(0=>71,1=>23,),3=>array(0=>59,1=>54,),),338=>array(0=>array(0=>81,1=>85,),1=>array(0=>100,1=>93,),2=>array(0=>26,1=>93,),3=>array(0=>22,1=>46,),),339=>array(0=>array(0=>78,1=>11,),1=>array(0=>48,1=>81,),2=>array(0=>38,1=>5,),3=>array(0=>33,1=>39,),),340=>array(0=>array(0=>88,1=>63,),1=>array(0=>42,1=>56,),2=>array(0=>15,1=>63,),3=>array(0=>20,1=>46,),),341=>array(0=>array(0=>86,1=>64,),1=>array(0=>42,1=>78,),2=>array(0=>9,1=>62,),3=>array(0=>36,1=>44,),),342=>array(0=>array(0=>0,1=>91,),1=>array(0=>8,1=>87,),2=>array(0=>90,1=>4,),3=>array(0=>6,1=>53,),),343=>array(0=>array(0=>2,1=>95,),1=>array(0=>94,1=>87,),2=>array(0=>53,1=>53,),3=>array(0=>36,1=>74,),),344=>array(0=>array(0=>44,1=>18,),1=>array(0=>53,1=>2,),2=>array(0=>33,1=>73,),3=>array(0=>65,1=>14,),),345=>array(0=>array(0=>69,1=>96,),1=>array(0=>43,1=>18,),2=>array(0=>71,1=>30,),3=>array(0=>78,1=>73,),),346=>array(0=>array(0=>3,1=>78,),1=>array(0=>0,1=>29,),2=>array(0=>3,1=>43,),3=>array(0=>49,1=>87,),),347=>array(0=>array(0=>51,1=>97,),1=>array(0=>51,1=>55,),2=>array(0=>7,1=>24,),3=>array(0=>64,1=>12,),),348=>array(0=>array(0=>80,1=>79,),1=>array(0=>1,1=>57,),2=>array(0=>18,1=>53,),3=>array(0=>15,1=>33,),),349=>array(0=>array(0=>31,1=>34,),1=>array(0=>6,1=>70,),2=>array(0=>35,1=>11,),3=>array(0=>71,1=>63,),),350=>array(0=>array(0=>37,1=>0,),1=>array(0=>92,1=>0,),2=>array(0=>44,1=>95,),3=>array(0=>19,1=>83,),),351=>array(0=>array(0=>30,1=>68,),1=>array(0=>39,1=>20,),2=>array(0=>97,1=>80,),3=>array(0=>69,1=>76,),),352=>array(0=>array(0=>37,1=>7,),1=>array(0=>13,1=>32,),2=>array(0=>39,1=>51,),3=>array(0=>97,1=>66,),),353=>array(0=>array(0=>53,1=>79,),1=>array(0=>48,1=>81,),2=>array(0=>53,1=>99,),3=>array(0=>70,1=>92,),),354=>array(0=>array(0=>81,1=>36,),1=>array(0=>36,1=>87,),2=>array(0=>14,1=>94,),3=>array(0=>93,1=>55,),),355=>array(0=>array(0=>44,1=>76,),1=>array(0=>21,1=>87,),2=>array(0=>5,1=>31,),3=>array(0=>51,1=>77,),),356=>array(0=>array(0=>26,1=>29,),1=>array(0=>59,1=>37,),2=>array(0=>85,1=>2,),3=>array(0=>22,1=>82,),),357=>array(0=>array(0=>9,1=>61,),1=>array(0=>12,1=>99,),2=>array(0=>84,1=>31,),3=>array(0=>26,1=>19,),),358=>array(0=>array(0=>85,1=>76,),1=>array(0=>63,1=>19,),2=>array(0=>99,1=>25,),3=>array(0=>93,1=>53,),),359=>array(0=>array(0=>11,1=>0,),1=>array(0=>80,1=>97,),2=>array(0=>60,1=>76,),3=>array(0=>87,1=>70,),),360=>array(0=>array(0=>13,1=>9,),1=>array(0=>7,1=>2,),2=>array(0=>58,1=>30,),3=>array(0=>47,1=>16,),),361=>array(0=>array(0=>40,1=>27,),1=>array(0=>12,1=>77,),2=>array(0=>5,1=>97,),3=>array(0=>36,1=>34,),),362=>array(0=>array(0=>76,1=>21,),1=>array(0=>41,1=>23,),2=>array(0=>99,1=>26,),3=>array(0=>75,1=>90,),),363=>array(0=>array(0=>66,1=>67,),1=>array(0=>12,1=>31,),2=>array(0=>14,1=>63,),3=>array(0=>33,1=>17,),),364=>array(0=>array(0=>19,1=>18,),1=>array(0=>85,1=>8,),2=>array(0=>37,1=>69,),3=>array(0=>35,1=>70,),),365=>array(0=>array(0=>58,1=>19,),1=>array(0=>57,1=>71,),2=>array(0=>31,1=>84,),3=>array(0=>7,1=>64,),),366=>array(0=>array(0=>17,1=>41,),1=>array(0=>36,1=>11,),2=>array(0=>69,1=>68,),3=>array(0=>40,1=>52,),),367=>array(0=>array(0=>64,1=>55,),1=>array(0=>23,1=>75,),2=>array(0=>64,1=>76,),3=>array(0=>36,1=>68,),),368=>array(0=>array(0=>75,1=>53,),1=>array(0=>2,1=>73,),2=>array(0=>60,1=>76,),3=>array(0=>73,1=>69,),),369=>array(0=>array(0=>21,1=>23,),1=>array(0=>61,1=>19,),2=>array(0=>0,1=>16,),3=>array(0=>51,1=>79,),),370=>array(0=>array(0=>98,1=>17,),1=>array(0=>44,1=>80,),2=>array(0=>21,1=>66,),3=>array(0=>86,1=>73,),),371=>array(0=>array(0=>36,1=>66,),1=>array(0=>68,1=>55,),2=>array(0=>11,1=>62,),3=>array(0=>53,1=>5,),),372=>array(0=>array(0=>73,1=>83,),1=>array(0=>96,1=>41,),2=>array(0=>87,1=>40,),3=>array(0=>69,1=>77,),),373=>array(0=>array(0=>61,1=>77,),1=>array(0=>90,1=>79,),2=>array(0=>99,1=>42,),3=>array(0=>62,1=>81,),),374=>array(0=>array(0=>54,1=>81,),1=>array(0=>9,1=>64,),2=>array(0=>100,1=>99,),3=>array(0=>7,1=>100,),),375=>array(0=>array(0=>33,1=>50,),1=>array(0=>75,1=>35,),2=>array(0=>3,1=>80,),3=>array(0=>30,1=>43,),),376=>array(0=>array(0=>39,1=>9,),1=>array(0=>10,1=>54,),2=>array(0=>99,1=>63,),3=>array(0=>33,1=>15,),),377=>array(0=>array(0=>58,1=>13,),1=>array(0=>10,1=>77,),2=>array(0=>75,1=>17,),3=>array(0=>42,1=>44,),),378=>array(0=>array(0=>51,1=>89,),1=>array(0=>46,1=>92,),2=>array(0=>6,1=>71,),3=>array(0=>43,1=>54,),),379=>array(0=>array(0=>62,1=>21,),1=>array(0=>80,1=>53,),2=>array(0=>50,1=>54,),3=>array(0=>59,1=>33,),),380=>array(0=>array(0=>21,1=>96,),1=>array(0=>90,1=>64,),2=>array(0=>32,1=>92,),3=>array(0=>23,1=>83,),),381=>array(0=>array(0=>64,1=>81,),1=>array(0=>72,1=>17,),2=>array(0=>55,1=>86,),3=>array(0=>2,1=>6,),),382=>array(0=>array(0=>53,1=>30,),1=>array(0=>60,1=>58,),2=>array(0=>14,1=>53,),3=>array(0=>89,1=>98,),),383=>array(0=>array(0=>39,1=>29,),1=>array(0=>21,1=>29,),2=>array(0=>47,1=>99,),3=>array(0=>3,1=>55,),),384=>array(0=>array(0=>91,1=>90,),1=>array(0=>20,1=>24,),2=>array(0=>44,1=>91,),3=>array(0=>69,1=>65,),),385=>array(0=>array(0=>19,1=>87,),1=>array(0=>0,1=>44,),2=>array(0=>19,1=>100,),3=>array(0=>15,1=>82,),),386=>array(0=>array(0=>85,1=>82,),1=>array(0=>93,1=>75,),2=>array(0=>13,1=>44,),3=>array(0=>96,1=>11,),),387=>array(0=>array(0=>33,1=>66,),1=>array(0=>37,1=>41,),2=>array(0=>36,1=>1,),3=>array(0=>69,1=>83,),),388=>array(0=>array(0=>96,1=>63,),1=>array(0=>19,1=>33,),2=>array(0=>77,1=>21,),3=>array(0=>67,1=>63,),),389=>array(0=>array(0=>53,1=>82,),1=>array(0=>34,1=>59,),2=>array(0=>96,1=>20,),3=>array(0=>85,1=>74,),),390=>array(0=>array(0=>30,1=>47,),1=>array(0=>9,1=>97,),2=>array(0=>76,1=>78,),3=>array(0=>88,1=>94,),),391=>array(0=>array(0=>29,1=>70,),1=>array(0=>20,1=>58,),2=>array(0=>59,1=>91,),3=>array(0=>43,1=>13,),),392=>array(0=>array(0=>85,1=>60,),1=>array(0=>34,1=>40,),2=>array(0=>18,1=>75,),3=>array(0=>82,1=>2,),),393=>array(0=>array(0=>99,1=>31,),1=>array(0=>68,1=>95,),2=>array(0=>48,1=>5,),3=>array(0=>64,1=>42,),),394=>array(0=>array(0=>60,1=>14,),1=>array(0=>86,1=>34,),2=>array(0=>77,1=>63,),3=>array(0=>20,1=>54,),),395=>array(0=>array(0=>3,1=>65,),1=>array(0=>91,1=>30,),2=>array(0=>37,1=>47,),3=>array(0=>100,1=>54,),),396=>array(0=>array(0=>60,1=>39,),1=>array(0=>60,1=>50,),2=>array(0=>98,1=>64,),3=>array(0=>43,1=>5,),),397=>array(0=>array(0=>97,1=>66,),1=>array(0=>87,1=>81,),2=>array(0=>22,1=>68,),3=>array(0=>81,1=>83,),),398=>array(0=>array(0=>1,1=>81,),1=>array(0=>69,1=>64,),2=>array(0=>28,1=>31,),3=>array(0=>36,1=>16,),),399=>array(0=>array(0=>78,1=>23,),1=>array(0=>26,1=>92,),2=>array(0=>49,1=>85,),3=>array(0=>3,1=>73,),),);

						$itemSvgDefs = HtmlNd::CreateTag( $doc, 'defs', array(), array(
							HtmlNd::CreateTag( $doc, 'filter', array( 'id' => $id . '-f-blur-sm', 'x' => '-100%', 'y' => '-100%', 'width' => '400%', 'height' => '400%' ), array(
								HtmlNd::CreateTag( $doc, 'feGaussianBlur', array( 'result' => 'blur', 'stdDeviation' => '2' ), array(
								) ),
								HtmlNd::CreateTag( $doc, 'feComponentTransfer', array(), array(
									HtmlNd::CreateTag( $doc, 'feFuncA', array( 'type' => 'linear', 'slope' => '180', 'intercept' => '-70' ) ),
								) ),
							) ),

							HtmlNd::CreateTag( $doc, 'filter', array( 'id' => $id . '-f-blur', 'x' => '-100%', 'y' => '-100%', 'width' => '400%', 'height' => '400%' ), array(
								HtmlNd::CreateTag( $doc, 'feGaussianBlur', array( 'result' => 'blur', 'stdDeviation' => '10' ), array(
								) ),
								HtmlNd::CreateTag( $doc, 'feComponentTransfer', array(), array(
									HtmlNd::CreateTag( $doc, 'feFuncA', array( 'type' => 'linear', 'slope' => '180', 'intercept' => '-70' ) ),
								) ),
							) ),
						) );

						$bg = Gen::GetArrField( $bubbleMorph, array( 'bg' ) );
						if( is_string( $bg ) && preg_match( '@^rgba\\(\\s*\\d+\\s*,\\s*\\d+\\s*,\\s*\\d+\\s*,\\s*0\\s*\\)$@', $bg ) )
							$bg = null;

						if( $bg )
						{
							if( is_array( $bg ) )
							{

								$type = Gen::GetArrField( $bg, array( 'type' ), '' );
								if( $type )
								{
									$attrs = array( 'id' => $id . '-bubbles-bg' );
									$angle = ( float )Gen::GetArrField( $bg, array( 'angle' ) );
									if( $angle )
										$attrs[ 'gradientTransform' ] = 'rotate(' . ( $angle - 90 ) . ')';

									$itemSvgDefs -> appendChild( $itemBg = HtmlNd::CreateTag( $doc, $type . 'Gradient', $attrs ) );
									foreach( Gen::GetArrField( $bg, array( 'colors' ), array() ) as $color )
									{
										$itemBg -> appendChild( HtmlNd::CreateTag( $doc, 'stop', array( 'offset' => ( string )Gen::GetArrField( $color, array( 'position' ), 0 ) . '%', 'stop-color' => Gen::GetArrField( $color, array( 'a' ), 1.0 ) !== 1.0 ? sprintf( 'rgba(%d,%d,%d,%d)', Gen::GetArrField( $color, array( 'r' ), 0 ), Gen::GetArrField( $color, array( 'g' ), 0 ), Gen::GetArrField( $color, array( 'b' ), 0 ), Gen::GetArrField( $color, array( 'a' ), 0.0 ) ) : sprintf( 'rgb(%d,%d,%d)', Gen::GetArrField( $color, array( 'r' ), 0 ), Gen::GetArrField( $color, array( 'g' ), 0 ), Gen::GetArrField( $color, array( 'b' ), 0 ) ) ) ) );
									}

									$bg = 'url(#' . $id . '-bubbles-bg)';
								}
							}
						}

						$itemSlideChild -> appendChild( $itemSvg = HtmlNd::CreateTag( $doc, 'svg', array( 'version' => '1.1', 'xmlns' => 'http://www.w3.org/2000/svg', 'overflow' => 'visible' ), array( $itemSvgDefs,  ) ) );

						$aSpeedX = array_map( function( $v ) { return( ( float )$v ); }, explode( '|', Gen::GetArrField( $bubbleMorph, array( 'speedx' ), '' ) ) );
						$aSpeedY = array_map( function( $v ) { return( ( float )$v ); }, explode( '|', Gen::GetArrField( $bubbleMorph, array( 'speedy' ), '' ) ) );
						$aBorderColor = explode( '|', Gen::GetArrField( $bubbleMorph, array( 'bordercolor' ), '' ) );
						$aBorderSize = explode( '|', Gen::GetArrField( $bubbleMorph, array( 'bordersize' ), '' ) );
						$nBubblesMax = 0;
						foreach( explode( '|', Gen::GetArrField( $bubbleMorph, array( 'num' ), '' ) ) as $i => $nBubbles )
						{
							$nBubbles = min( count( $g_aBubblePosRand ) - $iCurBubblesRand, $nBubbles );
							if( $nBubblesMax < $nBubbles )
								$nBubblesMax = $nBubbles;

							if( ( int )(isset($aBorderSize[ $i ])?$aBorderSize[ $i ]:'') )
							{
								$itemSvgBorderSub1 = HtmlNd::CreateTag( $doc, 'g', array( 'class' => 'bubbles b-ext' ) );
								$itemSvgBorderSub2 = HtmlNd::CreateTag( $doc, 'g', array( 'class' => 'bubbles b-int' ) );
								$itemSvg -> appendChild( HtmlNd::CreateTag( $doc, 'mask', array( 'class' => 'v' . $i, 'id' => $id . '-bubbles-v' . $i . '-border', 'style' => array( 'display' => 'none' ) ), array( $itemSvgBorderSub1, $itemSvgBorderSub2 ) ) );
							}
							else
							{
								$itemSvgBorderSub1 = null;
								$itemSvgBorderSub2 = null;
							}

							if( $bg )
							{
								$itemSvgBody = HtmlNd::CreateTag( $doc, 'g', array( 'class' => 'bubbles body' ) );
								$itemSvg -> appendChild( HtmlNd::CreateTag( $doc, 'mask', array( 'class' => 'v' . $i, 'id' => $id . '-bubbles-v' . $i . '-body', 'style' => array( 'display' => 'none' ) ), array( $itemSvgBody ) ) );
							}
							else
								$itemSvgBody = null;

							for( $iBubble = 0; $iBubble < $nBubbles; $iBubble++ )
							{
								$dur = ( (isset($aSpeedX[ $i ])?$aSpeedX[ $i ]:0.0) + (isset($aSpeedY[ $i ])?$aSpeedY[ $i ]:0.0) ) / 2;
								$dur = $dur ? ( 2.5 / $dur ) : 50;

								{
									$durShift = 0.3 * $dur * ( ( $iBubble + 1 ) / ( float )$nBubbles );
									if( $iBubble % 2 )
										$durShift *= -1;
									$dur += $durShift;
								}

								$keyTimes = ''; $valuesX = ''; $valuesY = '';
								$jn = count( $g_aBubblePosRand[ $iCurBubblesRand + $iBubble ] );
								for( $j = 0; $j < $jn; $j++ )
								{
									$keyTimes .= ( string )( ( float )$j / $jn ) . ';';
									$valuesX .= ( string )$g_aBubblePosRand[ $iCurBubblesRand + $iBubble ][ $j ][ 0 ] . '%;';
									$valuesY .= ( string )$g_aBubblePosRand[ $iCurBubblesRand + $iBubble ][ $j ][ 1 ] . '%;';
								}
								$keyTimes .= '1';
								$valuesX .= ( string )$g_aBubblePosRand[ $iCurBubblesRand + $iBubble ][ 0 ][ 0 ] . '%;';
								$valuesY .= ( string )$g_aBubblePosRand[ $iCurBubblesRand + $iBubble ][ 0 ][ 1 ] . '%;';

								$itemSvgBubble = HtmlNd::CreateTag( $doc, 'circle', array( 'class' => 'b' . $iBubble ), array(
									HtmlNd::CreateTag( $doc, 'animate', array( 'attributeName' => 'cx', 'keyTimes' => $keyTimes, 'values' => $valuesX, 'dur' => ( string )$dur . 's', 'repeatCount' => 'indefinite' ) ),
									HtmlNd::CreateTag( $doc, 'animate', array( 'attributeName' => 'cy', 'keyTimes' => $keyTimes, 'values' => $valuesY, 'dur' => ( string )$dur . 's', 'repeatCount' => 'indefinite' ) ),
								) );

								$bItemSvgBubbleNeedClone = false;
								foreach( array( $itemSvgBorderSub1, $itemSvgBorderSub2, $itemSvgBody ) as $itemSvgBubbleContainer )
								{
									if( !$itemSvgBubbleContainer )
										continue;

									if( $bItemSvgBubbleNeedClone )
										$itemSvgBubble = $itemSvgBubble -> cloneNode( true );
									else
										$bItemSvgBubbleNeedClone = true;

									$itemSvgBubbleContainer -> appendChild( $itemSvgBubble );
								}
							}

							if( $itemSvgBorderSub1 )
								$itemSvg -> appendChild( HtmlNd::CreateTag( $doc, 'rect', array( 'class' => 'v' . $i, 'mask' => 'url(#' . $id . '-bubbles-v' . $i . '-border)', 'fill' => (isset($aBorderColor[ $i ])?$aBorderColor[ $i ]:''), 'style' => array( 'display' => 'none' ) ), array() ) );
							if( $itemSvgBody )
								$itemSvg -> appendChild( HtmlNd::CreateTag( $doc, 'rect', array( 'class' => 'v' . $i, 'mask' => 'url(#' . $id . '-bubbles-v' . $i . '-body)', 'fill' => $bg, 'style' => array( 'display' => 'none' ) ), array() ) );

							_RevSld_SetStyleAttrEx( $aItemStyle, '#' . $id . ' .v' . $i, $i, array( 'display' => 'initial!important' ) );
						}

						$iCurBubblesRand += $nBubblesMax;

						{
							$a = array();
							foreach( explode( '|', Gen::GetArrField( $bubbleMorph, array( $f ), '' ) ) as $i => $v )
								$a[ $i ][ $t ] = $v;
							_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
						}

						foreach( array( 'bufferx' => '--buffer-x', 'buffery' => '--buffer-y', 'bordersize' => '--border-size' ) as $f => $t )
						{
							$a = array(); foreach( explode( '|', Gen::GetArrField( $bubbleMorph, array( $f ), '0' ) ) as $i => $v ) $a[ $i ][ $t ] = _RevSld_GetSize( false, $v );
							_RevSld_SetStyleAttr( $styleSeparated, $aItemStyle, $itemChildSelector, $a );
						}

						{
							$itemScript = $doc -> createElement( 'script' );
							if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
								$itemScript -> setAttribute( 'type', 'text/javascript' );
							$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
							HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_sldRev_bubblemorph_calcSizes(document.currentScript.parentNode);' );
							$itemSlideChild -> insertBefore( $itemScript, $itemSlideChild -> firstChild );
						}

						$adjustedBubbles = true;
					}

					$styleSeparated = array_merge( Ui::ParseStyleAttr( $itemSlideChild -> getAttribute( 'style' ) ), $styleSeparated );
					$styleSeparatedWrap[ 'z-index' ] = (isset($styleSeparated[ 'z-index' ])?$styleSeparated[ 'z-index' ]:null);

					$itemSlideChild -> setAttribute( 'style', Ui::GetStyleAttr( $styleSeparated ) );
					$itemParent -> insertBefore( HtmlNd::CreateTag( $doc, $isLayer ? 'rs-layer-wrap' : ( $itemSlideChild -> nodeName . '-wrap' ), array( 'id' => $itemIdWrap, 'class' => array( 'rs-parallax-wrap', $attrWrapperClass, $itemSlideChild -> nodeName == 'rs-row' ? 'slider-row-wrap' : null ), 'style' => $styleSeparatedWrap ), array( HtmlNd::CreateTag( $doc, 'rs-loop-wrap', array( 'style' => array( 'position' => $styleSeparated[ 'position' ], 'width' => (isset($styleSeparatedWrap[ 'width' ])?$styleSeparatedWrap[ 'width' ]:null), 'height' => (isset($styleSeparatedWrap[ 'height' ])?$styleSeparatedWrap[ 'height' ]:null), 'display' => $attrDisplay ) ), array( HtmlNd::CreateTag( $doc, 'rs-mask-wrap', array( 'style' => array( 'position' => $styleSeparated[ 'position' ], 'overflow' => 'visible', 'width' => (isset($styleSeparatedWrap[ 'width' ])?$styleSeparatedWrap[ 'width' ]:null), 'height' => (isset($styleSeparatedWrap[ 'height' ])?$styleSeparatedWrap[ 'height' ]:null), 'display' => $attrDisplay ) ), array( $itemSlideChild ) ) ) ) ) ), $itemInsertBefore );
				}
			}

			if( Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'enable' ) ) && $nSlides )
			{
				$direction = Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'direction' ), 'horizontal' );
				$alignHor = Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'h_align' ), 'center' );
				$alignVer = Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'v_align' ), 'bottom' );
				$space = Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'space' ), 5 );

				$obj = new AnyObj();
				$obj -> cb =
					function( $obj, $m )
					{
						return( $obj -> itemSlide -> getAttribute( 'data-' . $m[ 1 ] ) );
					};

				$itemBulletsTmp = '';
				for( $i = 0; $i < $nSlides; $i++ )
				{
					$obj -> itemSlide = $aItemSlide[ $i ];

					$attrs = array( 'class' => 'tp-bullet ' . ( $i === 0 ? 'selected' : '' ), 'style' => array( 'position' => 'relative!important' ) );
					if( $direction == 'horizontal' )
					{
						if( $i )
							$attrs[ 'style' ][ 'margin-left' ] = ( string )$space . 'px';
						$attrs[ 'style' ][ 'display' ] = 'inline-block!important';
					}
					else
					{
						if( $i )
							$attrs[ 'style' ][ 'margin-top' ] = ( string )$space . 'px';
					}

					$itemBulletsTmp .= Ui::Tag( 'rs-bullet', preg_replace_callback( '@{{([^{}]+)}}@', array( $obj, 'cb' ), Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'tmp' ), '' ) ), $attrs );
				}

				unset( $obj );

				$attrs = array( 'class' => array( 'tp-bullets', 'js-lzl-ing', Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'style' ) ), $direction, 'nav-dir-' . $direction, 'nav-pos-hor-' . $alignHor, 'nav-pos-ver-' . $alignVer ), 'style' => array( 'display' => 'flex', 'flex-wrap' => 'wrap', 'z-index' => 1000, 'position' => 'absolute', 'counter-reset' => 'section' ) );
				{
					$translate = array( '0%', '0%' );

					{
						switch( $alignHor )
						{
						case 'center': case 'middle':	$translate[ 0 ] = '-50% + ';	$pos = '50%'; break;
						case 'right':					$translate[ 0 ] = '-100% - ';	$pos = '100%'; break;
						default:						$pos = '0%';
						}

						$attrs[ 'style' ][ 'left' ] = $pos;
					}

					{
						switch( $alignVer )
						{
						case 'center': case 'middle':	$translate[ 1 ] = '-50% + ';	$pos = '50%'; break;
						case 'bottom':					$translate[ 1 ] = '-100% - ';	$pos = '100%'; break;
						default:						$pos = '0%';
						}

						$attrs[ 'style' ][ 'top' ] = $pos;
					}

					if( $translate[ 0 ] || $translate[ 1 ] )
						$attrs[ 'style' ][ 'transform' ] = 'translate(' . _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'h_offset' ), 0 ), $translate[ 0 ] ) . ', ' . _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'v_offset' ), 20 ), $translate[ 1 ] ) . ')!important';
				}

				$itemBulletsTmp = HtmlNd::ParseAndImport( $doc, Ui::Tag( 'rs-bullets', $itemBulletsTmp, $attrs ) );
				$item -> appendChild( $itemBulletsTmp );

				if( Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'hide_under' ) ) )
					$itemStyleCont .= '@media (max-width: ' . _RevSld_GetSize( false, ( int )Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'hide_under' ) ) - 1 ) . '){#' . $itemId . ' .tp-bullets-lzl{display:none!important;}}';
			}

			if( Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', 'enable' ) ) && $nSlides )
			{
				foreach( array( 'left', 'right' ) as $type )
				{
					$attrs = array();
					$attrs[ 'class' ] = array( 'tp-' . $type . 'arrow', 'tparrows', 'js-lzl-ing', Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', 'style' ), '' ) );

					$translate = array( 0, 0 );
					if( $type == 'left' )
					{
						$prefix = null;
						$attrs[ 'style' ][ 'left' ] = _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', $type, 'h_offset' ), 20 ), $prefix );
					}
					else
					{
						$translate[ 0 ] = '-100%';
						$prefix = '100% - ';
						$attrs[ 'style' ][ 'left' ] = _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', $type, 'h_offset' ), 20 ), $prefix );
					}

					{
						$translate[ 1 ] = '-50%';
						$prefix = '50% + ';
						$attrs[ 'style' ][ 'top' ] = _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', $type, 'v_offset' ), 0 ), $prefix );
					}

					if( $translate[ 0 ] || $translate[ 1 ] )
						$attrs[ 'style' ][ 'transform' ] = 'translate(' . $translate[ 0 ] . ', ' . $translate[ 1 ] . ')!important';

					$item -> appendChild( HtmlNd::ParseAndImport( $doc, Ui::Tag( 'rs-arrow', Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', 'tmp' ), '' ), $attrs ) ) );
				}

				if( Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', 'hide_under' ) ) )
					$itemStyleCont .= '@media (max-width: ' . Gen::GetArrField( $prms, array( 'init', 'navigation', 'arrows', 'hide_under' ), '' ) . '){#' . $itemId . ' rs-arrow.js-lzl-ing{display:none!important;}}';
			}

			if( Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'enable' ) ) )
			{
				$contTabs = '';

				$obj = new AnyObj();
				$obj -> cb =
					function( $obj, $m )
					{
						return( $obj -> itemSlide -> getAttribute( 'data-p' . $m[ 1 ] ) );
					};

				$visibleAmount = Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'visibleAmount' ), $nSlides );
				foreach( $aItemSlide as $i => $obj -> itemSlide )
				{
					if( $i == $visibleAmount )
						break;

					$contTabs .= Ui::Tag( 'rs-tab', preg_replace_callback( '@{{param(\\d+)}}@', array( $obj, 'cb' ), Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'tmp' ) ) )
						, array(
							'data-liindex' => $i,
							'data-key' => $obj -> itemSlide -> getAttribute( 'data-key' ),
							'class' => array( 'tp-tab', $i === 0 ? 'selected' : '' ),
							'style' => array(
								'display' => 'inline-block!important',
								'position' => 'relative',
								'width' => '' . Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'width' ), 0 ) . 'px',
								'height' => '100%',
								'margin-right' => ( $i + 1 == $visibleAmount ) ? null : ( '' . Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'space' ), 0 ) . 'px' ),
							),
						) );
				}

				unset( $obj );

				if( $contTabs )
				{
					$widthTotal = $visibleAmount * Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'width' ), 0 ) + ( $visibleAmount - 1 ) * Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'space' ), 0 ) + 2 * Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'mhoff' ), 0 );
					$height = Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'height' ), 0 );
					$contTabs = Ui::Tag( 'rs-tabs',
						Ui::Tag( 'rs-navmask',
							Ui::Tag( 'rs-tabs-wrap',
								$contTabs
							, array(
								'class' => array( 'tp-tabs-inner-wrapper' ),
								'style' => array(
									'display' => 'flex',
									'max-height' => '' . $height . 'px',
									'height' => '' . $height . 'px',
								),
							) )
						, array(
							'class' => array( 'tp-tab-mask' ),
							'style' => array(
								'padding' => '' . Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'mvoff' ), 0 ) . 'px ' . Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'mhoff' ), 0 ) . 'px',
							),
						) )
					, array(
						'class' => array( 'js-lzl-ing', 'nav-dir-horizontal', 'nav-pos-ver-bottom', 'nav-pos-hor-center', 'rs-nav-element', 'tp-tabs', 'tp-span-wrapper', 'inner', Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'style' ), '' ) ),
						'style' => array(
							'background' => Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'wrapper_color' ) ),
							'transform' => 'translate(0, -100%)',
							'top' => _RevSld_GetSize( false, Gen::GetArrField( $prms, array( 'init', 'navigation', 'tabs', 'v_offset' ), 0 ), '100% - ' ),
							'left' => '0',
							'padding-left' => 'calc(50% - (' . $widthTotal . 'px / 2))',
						),
					) );
					$item -> appendChild( HtmlNd::ParseAndImport( $doc, $contTabs ) );
				}
			}

			$aWidthUnique = array();
			for( $iDevice = 0; $iDevice < count( $aWidth ); $iDevice++ )
			{
				$width = $aWidth[ count( $aWidth ) - 1 - $iDevice ];
				if( !isset( $aWidthUnique[ $width ] ) )
					$aWidthUnique[ $width ] = $iDevice;
			}
			$aWidthUnique = array_reverse( $aWidthUnique, true );

			$iWidth = 0;
			$widthPrev = 0;
			foreach( $aWidthUnique as $width => $iDevice )
			{
				if( $aItemStyle[ $iDevice ] )
				{
					$itemStyleCont .= '@media';
					if( $iWidth > 0 )
						$itemStyleCont .= ' (min-width: ' . ( $widthPrev ) . 'px)';
					if( $iWidth > 0 && $iWidth < count( $aWidthUnique ) - 1 )
						$itemStyleCont .= ' and';
					if( $iWidth < count( $aWidthUnique ) - 1 )
						$itemStyleCont .= ' (max-width: ' . ( $width - 1 ) . 'px)';

					$itemStyleCont .= '{' . Ui::GetStyleSels( $aItemStyle[ $iDevice ] ) . '}';
				}

				$iWidth++;
				$widthPrev = $width;
			}

			if( $itemStyleCont )
			{
				$itemStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemStyle, $itemStyleCont );
				$item -> parentNode -> insertBefore( $itemStyle, $item );
			}

			$item -> setAttribute( 'style', Ui::GetStyleAttr( array_merge( Ui::ParseStyleAttr( $item -> getAttribute( 'style' ) ), array( '--lzl-rs-scale' => '1' ) ) ) );

			{
				$itemScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemScript -> setAttribute( 'type', 'text/javascript' );
				$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemScript, 'seraph_accel_cp_sldRev_calcSizes(document.currentScript.parentNode);' );
				$item -> insertBefore( $itemScript, $item -> firstChild );
			}

			$adjusted = true;
		}

		if( $adjusted )
		{
			{
				$itemsCmnStyle = $doc -> createElement( 'style' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemsCmnStyle -> setAttribute( 'type', 'text/css' );
				HtmlNd::SetValFromContent( $itemsCmnStyle, '
					rs-slides.js-lzl-ing > rs-slide,
					rs-slides.js-lzl-ing > rs-slide *:not(.tp-video-play-button) {
						visibility: visible !important;
						opacity: 1 !important;
					}

					rs-module.revslider-initialised > rs-tabs.js-lzl-ing,
					rs-module:not([style*=lzl-rs-scale]) rs-slides.js-lzl-ing {
						visibility: hidden !important;
					}

					rs-module-wrap {
						visibility: visible !important;
						height: unset !important;
					}

					rs-module.revslider-initialised > rs-slides.js-lzl-ing,
					rs-module.revslider-initialised .tp-bullets-lzl,
					rs-module.revslider-initialised > rs-arrow.js-lzl-ing,
					.js-lzl-ing-disp-none {
						display: none !important;
					}

					.js-lzl-ing .rev_row_zone_middle {
						transform: translate(0,-50%);
						top: calc(50%);
					}

					rs-slides.js-lzl-ing rs-layer[data-type="image"] img,
					rs-slides.js-lzl-ing .rs-layer[data-type="image"] img {
						object-fit: fill;
						width: 100%;
						height: 100%;
					}

					rs-slides.js-lzl-ing [data-bubblemorph] svg {
						position: absolute;
						left: calc(var(--sz) / 2 + var(--buffer-x));
						top: calc(var(--sz) / 2 + var(--buffer-y));
						width: calc(100% - var(--sz) - 2 * var(--buffer-x));
						height: calc(100% - var(--sz) - 2 * var(--buffer-y));
					}

					rs-slides.js-lzl-ing [data-bubblemorph] .bubbles.b-ext > circle {
						r: calc(0.97 * var(--sz) / 2);
						fill: white;
					}

					rs-slides.js-lzl-ing [data-bubblemorph] .bubbles.b-int > circle {
						r: calc(0.97 * var(--sz) / 2 - var(--border-size));
						fill: black;
					}

					rs-slides.js-lzl-ing [data-bubblemorph] .bubbles.body > circle {
						r: calc(0.97 * var(--sz) / 2 - var(--border-size));
						fill: white;
					}

					rs-slides.js-lzl-ing [data-bubblemorph] .bubbles {
						-webkit-filter: var(--flt);
						filter: var(--flt);
					}

					rs-slides.js-lzl-ing [data-bubblemorph] rect[mask] {
						x:	calc(-1 * var(--sz) / 2);
						y:	calc(-1 * var(--sz) / 2);
						width:	calc(100% + var(--sz));
						height:	calc(100% + var(--sz));
					}
				' );
				$ctxProcess[ 'ndHead' ] -> appendChild( $itemsCmnStyle );
			}

			{
				$itemScript = $doc -> createElement( 'script' );
				if( apply_filters( 'seraph_accel_jscss_addtype', false ) )
					$itemScript -> setAttribute( 'type', 'text/javascript' );
				$itemScript -> setAttribute( 'seraph-accel-crit', '1' );
				HtmlNd::SetValFromContent( $itemScript, '
					function seraph_accel_cp_sldRev_calcSizes( e )
					{
						var aWidths = JSON.parse( e.getAttribute( "data-lzl-widths" ) );
						var aWidthsGrid = JSON.parse( e.getAttribute( "data-lzl-widths-g" ) );

						for( var j = 0; j < aWidths.length; j++ )
							if( window.innerWidth < aWidths[ j ] )
								break;

						if( j == aWidths.length )
							j = aWidths.length - 1;

						var nScale = e.clientWidth / aWidthsGrid[ j ];
						if( nScale > 1 )
							nScale = 1;

						var nDiff = aWidths[ j ] - e.clientWidth;
						if( nDiff < 0 )
							nDiff = 0;

						var nExtra = ( e.clientWidth - aWidthsGrid[ j ] ) / 2;
						if( nExtra < 0 )
							nExtra = 0;

						e.style.setProperty( "--lzl-rs-scale", nScale );
						e.style.setProperty( "--lzl-rs-diff-y", nDiff );
						e.style.setProperty( "--lzl-rs-extra-x", nExtra );
					}

				' .
				( $adjustedBubbles ? '
					function seraph_accel_cp_sldRev_bubblemorph_calcSizes( e )
					{
						var sz = Math.max( e.clientWidth, e.clientHeight ) / 5;
						e.style.setProperty( "--sz", "" + sz + "px" );
						e.style.setProperty( "--flt", "url(\\"#" + e.id + "-f-blur" + ( sz >= 30 ? "" : "-sm" ) + "\\")" );
					}

				' : '' )
				. '
					(
						function( d )
						{
							function OnEvt( evt )
							{
								d.querySelectorAll( "rs-module:not(.revslider-initialised)[data-lzl-widths]" ).forEach( seraph_accel_cp_sldRev_calcSizes );
				' .
				( $adjustedBubbles ? '
								d.querySelectorAll( "rs-module:not(.revslider-initialised) rs-slides.js-lzl-ing [data-bubblemorph]" ).forEach( seraph_accel_cp_sldRev_bubblemorph_calcSizes );
				' : '' )
				. '
							}

							d.addEventListener( "seraph_accel_calcSizes", OnEvt, { capture: true, passive: true } );
							d.addEventListener( "seraph_accel_beforeJsDelayLoad", function( evt ) { d.removeEventListener( "seraph_accel_calcSizes", OnEvt, { capture: true, passive: true } ); }, { capture: true, passive: true } );
						}
					)( document );
				' );
				$ctxProcess[ 'ndBody' ] -> insertBefore( $itemScript, $ctxProcess[ 'ndBody' ] -> firstChild );
			}
		}
	}

	return( true );
}

function _Scrollsequence_GetFrontendCfg( $id, $itemInitScr )
{
	if( !$itemInitScr )
		return( null );

	$m = array();
	if( !preg_match( '@{\\s*"ssqId"\\s*:\\s*"' . $id . '"@m', $itemInitScr -> nodeValue, $m, PREG_OFFSET_CAPTURE ) )
		return( null );

	$posStart = $m[ 0 ][ 1 ];
	$pos = Gen::JsonGetEndPos( $posStart, $itemInitScr -> nodeValue );
	if( $pos === null )
		return( null );

	$prms = @json_decode( Gen::JsObjDecl2Json( substr( $itemInitScr -> nodeValue, $posStart, $pos - $posStart ) ), true );
	if( !$prms )
		return( null );

	return( $prms );
}

function _Elmntr_GetFrontendCfg( $itemInitCmnScr )
{
	if( !$itemInitCmnScr )
		return( null );

	$m = array();
	if( !preg_match( '@\\WelementorFrontendConfig\\s*\\=\\s*@', $itemInitCmnScr -> nodeValue, $m, PREG_OFFSET_CAPTURE ) )
		return( null );

	$prms = @json_decode( Gen::JsObjDecl2Json( rtrim( substr( $itemInitCmnScr -> nodeValue, $m[ 0 ][ 1 ] + strlen( $m[ 0 ][ 0 ] ) ), " \n\r\t\v;" ) ), true );
	if( !$prms )
		return( null );

	foreach( array( 'mobile' => 767, 'tablet' => 1024 ) as $k => $def )
	{
		$nMax = Gen::GetArrField( $prms, array( 'responsive', 'breakpoints', $k, 'value' ), 0 );
		if( !$nMax )
			$nMax = Gen::GetArrField( $prms, array( 'responsive', 'breakpoints', $k, 'default_value' ), 0 );
		if( !$nMax )
			$nMax = $def;
		$prms[ 'views' ][ $k ] = $nMax;
	}

	return( $prms );
}

function _RevSld_GetPrmsFromScr( $item, $itemInitCmnScr )
{
	if( !$itemInitCmnScr )
		return( null );

	$prms = array();

	for( $itemInitScr = $item -> nextSibling; $itemInitScr; $itemInitScr = $itemInitScr -> nextSibling )
	{
		if( $itemInitScr -> nodeName != 'script' )
			continue;

		$m = array();
		if( !preg_match( '@^\\s*setREVStartSize\\(\\s*({[^}]*})@', $itemInitScr -> nodeValue, $m ) )
			continue;

		$m = @json_decode( Gen::JsObjDecl2Json( $m[ 1 ] ), true );
		if( !$m )
			return( null );

		$prms[ 'start' ] = $m;
		break;
	}

	if( !$itemInitScr )
		return( null );

	$cmdScrId = array();
	if( !preg_match( '@\\.\\s*RS_MODULES\\s*.\\s*modules\\s*\\[\\s*["\']([\\w\\-]+)["\']\\s*\\]@', $itemInitScr -> nodeValue, $cmdScrId ) )
		return( null );

	$cmdScrId = $cmdScrId[ 1 ];

	$posStart = array();
	if( !preg_match( '@\\WRS_MODULES\\s*.\\s*modules\\s*\\[\\s*["\']' . $cmdScrId . '["\']\\s*\\]\\s*=\\s*{@', $itemInitCmnScr -> nodeValue, $posStart, PREG_OFFSET_CAPTURE ) )
		return( null );

	$posStart = $posStart[ 0 ][ 1 ] + strlen( $posStart[ 0 ][ 0 ] );

	if( !preg_match( '@\\W(\\w+)\\.revolutionInit\\s*\\(\\s*@', $itemInitCmnScr -> nodeValue, $posStartInit, PREG_OFFSET_CAPTURE, $posStart ) )
		return( null );

	$posStart = $posStartInit[ 0 ][ 1 ] + strlen( $posStartInit[ 0 ][ 0 ] );
	$pos = Gen::JsonGetEndPos( $posStart, $itemInitCmnScr -> nodeValue );
	if( $pos === null )
		return( null );

	$prms[ 'init' ] = @json_decode( Gen::JsObjDecl2Json( substr( $itemInitCmnScr -> nodeValue, $posStart, $pos - $posStart ) ), true );

	if( Gen::GetArrField( $prms, array( 'init', 'navigation', 'bullets', 'enable' ) ) )
		$itemInitCmnScr -> nodeValue = substr_replace( $itemInitCmnScr -> nodeValue, $posStartInit[ 1 ][ 0 ] . '.children("rs-bullets.js-lzl-ing").remove();', $posStartInit[ 1 ][ 1 ], 0 );

	return( $prms );
}

function _RevSld_GetAttrs( $data, $nValsForce = false )
{
	$res = array();
	foreach( explode( ';', $data ) as $e )
	{
		if( !strlen( $e ) )
			continue;

		$e = explode( ':', $e );
		if( count( $e ) > 2 )
			continue;

		if( count( $e ) < 2 )
			array_splice( $e, 0, 0, array( '' ) );

		$iBracket = 0;
		for( $i = 0; $i < strlen( $e[ 1 ] ); $i++ )
		{
			$c = $e[ 1 ][ $i ];
			if( $c == '(' )
				$iBracket++;
			else if( $c == ')' )
				$iBracket--;
			else if( $iBracket > 0 && $c == ',' )
				$e[ 1 ][ $i ] = "\xFF";
		}

		if( strpos( $e[ 1 ], ',' ) !== false )
		{
			$e[ 1 ] = array_map(
				function( $e )
				{
					$e = trim( $e, " \t\n\r\0\x0B[]'" );
					return( $e );
				}
			, explode( ',', $e[ 1 ] ) );
		}
		else if( Gen::StrStartsWith( $e[ 1 ], 'cyc(' ) )
			$e[ 1 ] = array( 'cyc' => array_map( 'trim', explode( '|', substr( $e[ 1 ], 4, -1 ) ) ) );
		else if( $nValsForce )
			$e[ 1 ] = array_fill( 0, $nValsForce, $e[ 1 ] );

		$e[ 1 ] = Gen::StrReplace( "\xFF", ',', $e[ 1 ] );

		$res[ $e[ 0 ] ] = $e[ 1 ];
	}

	return( $res );
}

function _RevSld_GetSize( $scaleInit, $sz, $prefix = '', $suffix = '' )
{
	if( $sz === null )
		return( null );

	$res = '';

	$szSuffix = array();
	if( preg_match( '@\\D+$@', $sz, $szSuffix ) )
	{
		$szSuffix = $szSuffix[ 0 ];
		$sz = substr( $sz, 0, -strlen( $szSuffix ) );
	}
	else
		$szSuffix = '';

	$scale = false;
	if( $szSuffix == 'px' )
	{
		if( ( float )$sz )
			$scale = $scaleInit;
	}
	else if( !$szSuffix )
		$szSuffix = 'px';

	$calc = false;
	if( $scale || $prefix || $suffix )
		$calc = true;

	if( $calc )
		$res .= 'calc(';
	if( $prefix )
		$res .= $prefix;
	$res .= $sz . $szSuffix;
	if( $scale )
		$res .= ' * var(--lzl-rs-scale)';
	if( $suffix )
		$res .= $suffix;
	if( $calc )
		$res .= ')';

	return( $res );
}

function _RevSld_SetStyleAttrEx( &$aItemStyle, $itemChildSelector, $i, $styles )
{
	$aDst = &$aItemStyle[ $i ][ $itemChildSelector ];

	if( !is_array( $aDst ) )
	{
		$aDst = $styles;
		return;
	}

	if( isset( $styles[ 'transform' ] ) && isset( $aDst[ 'transform' ] ) )
	{
		$aDst[ 'transform' ] = ( Gen::StrEndsWith( $aDst[ 'transform' ], '!important' ) ? substr( $aDst[ 'transform' ], 0, strlen( $aDst[ 'transform' ] ) - 10 ) : $aDst[ 'transform' ] ) . ' ' . $styles[ 'transform' ];
		unset( $styles[ 'transform' ] );
	}

	$aDst = array_merge( $aDst, $styles );
}

function _RevSld_SetStyleAttr( &$styleSeparated, &$aItemStyle, $itemChildSelector, $a )
{
	if( count( $a ) == 1 )
	{
		$styleSeparated = array_merge( $styleSeparated, $a[ 0 ] );
		return;
	}

	foreach( $a as $i => $styles )
		_RevSld_SetStyleAttrEx( $aItemStyle, $itemChildSelector, $i, $styles );
}

function _RevSld_GetIdxPropVal( $props, $path, $i, $vDef = null )
{
	$props = ( array )Gen::GetArrField( $props, $path );
	$v = Gen::GetArrField( $props, array( $i ) );
	if( $v === null && $i !== 0 )
		$v = Gen::GetArrField( $props, array( 0 ) );
	return( $v !== null ? $v : $vDef );
}

