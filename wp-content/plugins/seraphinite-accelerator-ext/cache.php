<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

require( __DIR__ . '/common.php' );

global $seraph_accel_g_cacheSkipData;

$idSubPart = Gen::SanitizeId( (isset($_GET[ 'seraph_accel_gp' ])?$_GET[ 'seraph_accel_gp' ]:null), null );
if( $idSubPart )
	unset( $_GET[ 'seraph_accel_gp' ] );

$hr = _Process( $seraph_accel_sites, $_GET, $idSubPart );
if( $hr == Gen::S_OK || Gen::HrFail( $hr ) )
{
	if( $idSubPart || !CacheDoCronAndEndRequest() )
	{
		flush();
		exit();
	}

	return;
}

if( $hr == Gen::S_NOTIMPL )
	return;

if( $hr == Gen::S_IO_PENDING )
{

	ob_start( 'seraph_accel\\_CbContentFinish' );
	ob_start( 'seraph_accel\\_CbContentProcess' );
}
else if( $seraph_accel_g_cacheSkipData )
{

	ob_start( 'seraph_accel\\_CbContentFinishSkip' );
	ob_start( 'seraph_accel\\_CbContentProcess' );
}

function _Process( $sites, $args, $idSubPart )
{

	if( (isset($_SERVER[ 'REQUEST_METHOD' ])?$_SERVER[ 'REQUEST_METHOD' ]:null) != 'GET' )
		return( Gen::S_FALSE );

	global $seraph_accel_g_noFo;
	global $seraph_accel_g_prepPrms;
	global $seraph_accel_g_cacheSkipData;
	global $seraph_accel_g_siteId;

	if( isset( $_SERVER[ 'HTTP_X_SERAPH_ACCEL_TEST' ] ) )
	{
		if( $idTest = Gen::SanitizeId( substr( $_SERVER[ 'HTTP_X_SERAPH_ACCEL_TEST' ], 0, 64 ) ) )
			@header( 'X-Seraph-Accel-test: ' . $idTest );
		unset( $idTest );
	}

	$seraph_accel_g_prepPrms = CacheExtractPreparePageParams( $args );
	if( $seraph_accel_g_prepPrms !== null )
	{
		if( $seraph_accel_g_prepPrms === false )
		{
			http_response_code( 400 );
			return( Gen::E_INVALIDARG );
		}

		@ignore_user_abort( true );
		@set_time_limit( 570 );

		if( (isset($seraph_accel_g_prepPrms[ 'selfTest' ])?$seraph_accel_g_prepPrms[ 'selfTest' ]:null) )
		{
			$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'selfTest' ) );
			return( Gen::S_FALSE );
		}

		if( !ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'stage' => 'get' ), true, true ) )
		{
			http_response_code( 599 );
			return( Gen::E_FAIL );
		}

	}

	if( !GetContCacheEarlySkipData( $path, $pathIsDir, $args ) )
	{
		$addrSite = GetRequestHost( $_SERVER );
		$seraph_accel_g_siteId = GetCacheSiteIdAdjustPath( $sites, $addrSite, $siteSubId, $path );
		if( $seraph_accel_g_siteId === null )
			$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'siteIdUnk' ) );

		unset( $addrSite );
	}

	$sett = Plugin::SettGet( Gen::CallFunc( 'seraph_accel_siteSettInlineDetach', array( $seraph_accel_g_siteId ) ) );
	$settCache = Gen::GetArrField( $sett, array( 'cache' ), array() );

	if( $seraph_accel_g_cacheSkipData )
	{
		if( (isset($sett[ 'hdrTrace' ])?$sett[ 'hdrTrace' ]:null) || ( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'request' ])?$sett[ 'logScope' ][ 'request' ]:null) ) )
			_ProcessOutHdrTrace( (isset($sett[ 'hdrTrace' ])?$sett[ 'hdrTrace' ]:null), (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'request' ])?$sett[ 'logScope' ][ 'request' ]:null), $seraph_accel_g_cacheSkipData[ 0 ], (isset($seraph_accel_g_cacheSkipData[ 1 ])?$seraph_accel_g_cacheSkipData[ 1 ]:null) );
		if( $seraph_accel_g_prepPrms !== null )
			ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'finish' => true, 'skip' => Gen::GetArrField( (isset($seraph_accel_g_cacheSkipData[ 1 ])?$seraph_accel_g_cacheSkipData[ 1 ]:null), array( 'reason' ), '' ) ), false, false );
		return( Gen::S_NOTIMPL );
	}

	{
		if( (isset($sett[ 'debug' ])?$sett[ 'debug' ]:null) && isset( $args[ 'seraph_accel_proc' ] ) )
		{
			$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => 'debugContProcForce' ) );
			return( Gen::S_FALSE );
		}
	}

	{
		$exclStatus = ContProcGetExclStatus( $seraph_accel_g_siteId, $settCache, $path, $pathIsDir, $args, $varsOut, true, $seraph_accel_g_prepPrms === null );
		if( $exclStatus )
		{
			$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => $exclStatus ) );
			return( Gen::S_FALSE );
		}

		extract( $varsOut );
		unset( $varsOut );
		unset( $exclStatus );
	}

	global $seraph_accel_g_dscFile;
	global $seraph_accel_g_dscFilePending;
	global $seraph_accel_g_dataPath;
	global $seraph_accel_g_viewPath;
	global $seraph_accel_g_prepOrigContHashPrev;

	$sessId = $userId ? (isset($sessInfo[ 'userSessId' ])?$sessInfo[ 'userSessId' ]:null) : (isset($sessInfo[ 'sessId' ])?$sessInfo[ 'sessId' ]:null);
	$viewId = GetCacheViewId( $settCache, $userAgent, $args );
	$cacheRootPath = GetCacheDir();
	$siteCacheRootPath = $cacheRootPath . '/s/' . $seraph_accel_g_siteId;
	$seraph_accel_g_viewPath = GetCacheViewsDir( $siteCacheRootPath, $siteSubId ) . '/' . $viewId;
	$ctxsPath = $seraph_accel_g_viewPath . '/c';

	{
		if( !$sessId || !$stateCookId || !Gen::GetArrField( $settCache, array( 'ctxSessSep' ), false ) )
			$sessId = '@';

		$sessFullId = $userId . '/s/' . $sessId;
		$ctxPathId = $sessFullId;

		if( Gen::GetArrField( $settCache, array( 'ctx' ), false ) )
			$_SERVER[ 'HTTP_X_SERAPH_ACCEL_SESSID' ] = $userId . '/' . $sessId;

		if( $stateCookId )
			$stateCookId = md5( $stateCookId );
		else
			$stateCookId = '@';

		$ctxPathId .= '/s/' . $stateCookId;
	}

	$objectId = '@';
	if( $pathIsDir )
		$objectId .= 'd';
	if( !empty( $args ) )
	{
		$argsCumulative = '';
		foreach( $args as $argKey => $argVal )
			$argsCumulative .= $argKey . $argVal;

		$objectId = $objectId . '.' . @md5( $argsCumulative );
		unset( $argsCumulative );
	}

	$seraph_accel_g_dataPath = GetCacheDataDir( $siteCacheRootPath );

	$seraph_accel_g_dscFile = $ctxsPath . '/' . $ctxPathId . '/o';
	if( $path )
		$seraph_accel_g_dscFile .= '/' . $path;
	$seraph_accel_g_dscFile .= '/' . $objectId . '.html.dat';

	if( $idSubPart )
	{
		$idSubPart = str_replace( '_', '.', $idSubPart );
		$subPartType = Gen::GetFileExt( $idSubPart );
		$idSubPart = Gen::GetFileExt( Gen::GetFileName( $idSubPart, true ) );

		$dscPart = Gen::GetArrField( CacheReadDsc( $seraph_accel_g_dscFile ), array( 'b', $idSubPart ), array() );
		if( !$dscPart )
		{
			http_response_code( 404 );
			return( Gen::S_OK );
		}

		if( _ProcessOutCachedData( $subPartType, $sett, $settCache, $dscPart, @filemtime( $seraph_accel_g_dscFile ), $tmCur, 'cache', null, true ) !== Gen::S_OK )
		{
			http_response_code( 599 );
			return( Gen::E_FAIL );
		}

		return( Gen::S_OK );
	}

	$seraph_accel_g_dscFilePending = $seraph_accel_g_dscFile . '.p';

	if( $seraph_accel_g_prepPrms !== null )
	{
		$seraph_accel_g_dscFilePending .= 'p';
        $seraph_accel_g_noFo = true;
	}

	$contProc = Gen::GetArrField( $sett, array( 'contPr', 'enable' ), false );

	$procTmLim = Gen::GetArrField( $settCache, array( 'procTmLim' ), 570 );

	$sessExpiration = (isset($sessInfo[ 'expiration' ])?$sessInfo[ 'expiration' ]:null);
	if( !$sessExpiration )
		$sessExpiration = $tmCur;

	$httpCacheControl = strtolower( (isset($_SERVER[ 'HTTP_CACHE_CONTROL' ])?$_SERVER[ 'HTTP_CACHE_CONTROL' ]:null) );

	$lazyInv = (isset($settCache[ 'lazyInv' ])?$settCache[ 'lazyInv' ]:null) && $sessFullId == '0/s/@';
	$lazyInvTmp = false;

	$timeoutCln = Gen::GetArrField( $settCache, array( 'timeoutCln' ), 0 ) * 60;
	$timeout = Gen::GetArrField( $settCache, array( 'timeout' ), 0 ) * 60;
	if( $timeoutCln && $timeout > $timeoutCln )
		$timeout = $timeoutCln;

	$reason = null;
	$dsc = null;
	$isCip = null;

	$dscFileTm = @filemtime( $seraph_accel_g_dscFile );
	$dscFileTmAge = $tmCur - $dscFileTm;

	if( !$dscFileTm || ( $timeoutCln > 0 && $dscFileTmAge > $timeoutCln && ( $dscFileTm >= 60 ) ) || ( $timeout > 0 ? ( $dscFileTmAge > $timeout ) : ( $dscFileTm < 60 ) ) || ( $tmCur > $sessExpiration ) || ( $sessFullId != '0/s/@' && $httpCacheControl == 'no-cache' && Gen::GetArrField( $settCache, array( 'ctxCliRefresh' ), false ) ) )
	{
		$lock = new Lock( 'dl', $cacheRootPath );
		if( !$lock -> Acquire() )
			return( Gen::E_FAIL );

		$dscFileTm = @filemtime( $seraph_accel_g_dscFile );

		if( $dscFileTm === false )
		{
			$ccs = _CacheContentStart( $tmCur, $procTmLim );
			$lock -> Release();

			if( $ccs === true )
			{

				if( $seraph_accel_g_prepPrms === null && $contProc )
				{
					$seraph_accel_g_cacheSkipData = array( 'revalidating-begin', array( 'reason' => 'initial', 'dscFile' => substr( $seraph_accel_g_dscFile, strlen( $cacheRootPath ) ) ) );
					return( Gen::S_FALSE );
				}

				return( Gen::S_IO_PENDING );
			}

			if( $ccs === false )
			{
				if( $seraph_accel_g_prepPrms !== null )
				{
					ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'finish' => true, 'skip' => 'alreadyProcessing' ), false, false );
					return( Gen::S_OK );
				}

				$seraph_accel_g_cacheSkipData = array( 'revalidating', array( 'reason' => 'initial', 'dscFile' => substr( $seraph_accel_g_dscFile, strlen( $cacheRootPath ) ) ) );
				return( Gen::S_FALSE );
			}
		}
		else
		{

			$dsc = CacheReadDsc( $seraph_accel_g_dscFile );

			$dscFileTmAge = $tmCur - $dscFileTm;

			if( $dscFileTm === 0 )
				$reason = 'forced';
			else if( $timeoutCln > 0 && $dscFileTmAge > $timeoutCln && ( $dscFileTm >= 60 ) )
			{
				$reason = 'timeoutClnExpired';

				$lazyInv = false;

			}
			else if( $timeout > 0 ? ( $dscFileTmAge > $timeout ) : ( $dscFileTm < 60 ) )
			{
				$reason = ( $dscFileTm === 5 ) ? 'initial' : ( ( $dscFileTm === 10 ) ? 'forced' : 'timeoutExpired' );

				if( $dsc )
					$seraph_accel_g_prepOrigContHashPrev = (isset($dsc[ 'h' ])?$dsc[ 'h' ]:null);

			}
			else if( $tmCur > $sessExpiration )
				$reason = 'userSessionExpired';
			else if( $sessFullId != '0/s/@' && $httpCacheControl == 'no-cache' && Gen::GetArrField( $settCache, array( 'ctxCliRefresh' ), false ) )
				$reason = 'forcedFromClient';

			if( $reason )
			{
				$ccs = _CacheContentStart( $tmCur, $procTmLim );
				if( $ccs === true )
				{
					if( $dscFileTm === 0 && !@touch( $seraph_accel_g_dscFile, 10 ) )
					{
						$lock -> Release();
						return( Gen::E_FAIL );
					}

					$lock -> Release();

					if( $lazyInv )
					{
						$isCip = false;
						$lazyInvTmp = ( $reason === 'forced' ) ? (isset($settCache[ 'lazyInvForcedTmp' ])?$settCache[ 'lazyInvForcedTmp' ]:null) : (isset($settCache[ 'lazyInvTmp' ])?$settCache[ 'lazyInvTmp' ]:null);
						if( $seraph_accel_g_prepPrms !== null )
							$seraph_accel_g_prepPrms[ 'lazyInvTmp' ] = $lazyInvTmp;
					}
					else

					{

						if( $seraph_accel_g_prepPrms === null && $contProc )
						{
							$seraph_accel_g_cacheSkipData = array( 'revalidating-begin', array( 'reason' => $reason, 'dscFile' => substr( $seraph_accel_g_dscFile, strlen( $cacheRootPath ) ) ) );
							return( Gen::S_FALSE );
						}

						return( Gen::S_IO_PENDING );
					}
				}
				else if( $ccs === false )
				{
					$lock -> Release();

					$isCip = true;

					if( !$lazyInv )

					{
						if( !( $dsc && (isset($dsc[ 't' ])?$dsc[ 't' ]:null) ) && $seraph_accel_g_prepPrms === null )
						{
							$seraph_accel_g_cacheSkipData = array( 'revalidating', array( 'reason' => $reason, 'dscFile' => substr( $seraph_accel_g_dscFile, strlen( $cacheRootPath ) ) ) );
							return( Gen::S_FALSE );
						}
					}
				}
				else
					$lock -> Release();
			}
			else
				$lock -> Release();
		}

		unset( $lock );
	}
	else
		$dsc = CacheReadDsc( $seraph_accel_g_dscFile );

	if( $seraph_accel_g_prepPrms === null )
	{

		if( $contProc )
		{
			$b = true;
			foreach( array( '@\\Wcompatible\\W@', '@facebookexternalhit@', '@Go-http-client@i', '@Google-Adwords-Instant@i', '@Googlebot-Image@i', '@GoogleYoutube@i', '@IonCrawl@i', '@Chrome-Lighthouse@i', '@GTmetrix@i', '@RankMathApi@i', '@validator\\.w3\\.org@i', '@ZoominfoBot@i' ) as $e )
			{
				if( preg_match( $e, $userAgent ) )
				{
					$b = false;
					break;
				}
			}

			if( $b )
				lfjikztqjqji( $seraph_accel_g_siteId, $tmCur, true );
		}

		$reasonOutputErr;
		if( !$dsc )
			$reasonOutputErr = 'brokenDsc';
		else
		{
			$hr = _ProcessOutCachedData( null, $sett, $settCache, $dsc, $dscFileTm, $tmCur, $isCip ? 'revalidating' : ( $isCip === false ? 'revalidating-begin' : 'cache' ), $reason, true );
			if( Gen::HrFail( $hr ) )
				return( $hr );

			if( $hr == Gen::S_FALSE )
				$reasonOutputErr = 'brokenData';
		}

		if( $reasonOutputErr )
		{
			$lock = new Lock( 'dl', $cacheRootPath );
			if( !$lock -> Acquire() )
				return( Gen::E_FAIL );

			@touch( $seraph_accel_g_dscFile, 0 );

			if( $isCip === false || _CacheContentStart( $tmCur, $procTmLim ) === true )
			{
				$lock -> Release();

				if( $contProc )
				{
					$seraph_accel_g_cacheSkipData = array( 'revalidating-begin', array( 'reason' => $reasonOutputErr, 'dscFile' => substr( $seraph_accel_g_dscFile, strlen( $cacheRootPath ) ) ) );
					return( Gen::S_FALSE );
				}

				return( Gen::S_IO_PENDING );
			}

			$lock -> Release();

			$seraph_accel_g_cacheSkipData = array( 'revalidating', array( 'reason' => $reasonOutputErr, 'dscFile' => substr( $seraph_accel_g_dscFile, strlen( $cacheRootPath ) ) ) );
			return( Gen::S_FALSE );
		}
	}
	else
		$hr = Gen::S_OK;

	if( $isCip === false )
	{
		if( $seraph_accel_g_prepPrms === null )
		{
			$bgEnabled = Gen::CloseCurRequestSessionForContinueBgWork();

			if( $contProc || !$bgEnabled )
				return( CacheSetCurRequestToPrepareAsync( $contProc ? $seraph_accel_g_siteId : null, $lazyInvTmp, $bgEnabled ) ? Gen::S_OK : Gen::S_FALSE );

			$seraph_accel_g_noFo = true;
			return( Gen::S_IO_PENDING );
		}

		if( $dscFileTm && !(isset($seraph_accel_g_prepPrms[ 'tmp' ])?$seraph_accel_g_prepPrms[ 'tmp' ]:null) && $dsc && (isset($dsc[ 't' ])?$dsc[ 't' ]:null) )
		{
			if( $ctxData = CacheDscGetDataCtx( $settCache, $dsc, '', $seraph_accel_g_dataPath, $tmCur, 'html' ) )
			{
				$obj = new AnyObj();

				$obj -> cont = CacheDscDataOutput( $ctxData, false );
				{
					$posTmpTimeStamp = strrpos( $obj -> cont, '<!-- seraph-accel-tmpTimeStamp: ', -1 );
					if( $posTmpTimeStamp !== false )
						$obj -> cont = substr( $obj -> cont, 0, $posTmpTimeStamp );
				}

				$obj -> cb = function( $obj )
				{

					$obj -> cont = OnEarlyContentComplete( $obj -> cont );
					echo( _CbContentFinish( $obj -> cont ) );

					exit();
				};

				if( $obj -> cont )
				{

					_CacheStdHdrs( (isset($settCache[ 'srv' ])?$settCache[ 'srv' ]:null) );
					add_action( 'wp_loaded', array( $obj, 'cb' ), -999999  );
					return( Gen::S_FALSE );
				}
			}
		}

		$seraph_accel_g_noFo = true;
		return( Gen::S_IO_PENDING );
	}

	if( $seraph_accel_g_prepPrms !== null )
	{
		ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'finish' => true, 'skip' => $isCip ? 'alreadyProcessing' : 'alreadyProcessed' ), false, false );
		return( $hr );
	}

	return( $hr );
}

function _CacheStdHdrs( $allowCache = false )
{

	if( $allowCache )
	{
		@header( 'Cache-Control: public, max-age=0, s-maxage=3600' );
	}
	else
	{
		@header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
		@header( 'Pragma: no-cache' );
	}

	@header( 'Expires: Wed, 11 Jan 1984 05:00:00 GMT' );
	@header( 'Content-Type: text/html; charset=UTF-8' );
}

function _ProcessOutHdrTrace( $bHdr, $bLog, $state, $data = null, $dscFile = null )
{
	$debugInfo = ' state=' . $state . ';';
	if( $dscFile )
		$debugInfo .= ' dscFile="' . substr( $dscFile, strlen( GetCacheDir() ) ) . '";';

	if( is_array( $data ) )
		foreach( $data as $dataK => $dataV )
		{
			$v = '';
			switch( gettype( $dataV ) )
			{
			case 'array':		$v = @json_encode( $dataV, JSON_INVALID_UTF8_IGNORE ); break;
			case 'string':		$v = '"' . $dataV . '"'; break;
			case 'boolean':		$v = $dataV ? 'true' : 'false'; break;
			default:			$v .= $dataV; break;
			}

			$debugInfo .= ' ' . $dataK . '=' . $v . ';';
		}

	if( $bHdr )
		@header( 'X-Seraph-Accel-Cache: 2.19.7;' . $debugInfo );

	if( $bLog )
		LogWrite( $debugInfo . ' URL: ' . GetCurRequestUrl() . '; Agent: ' . ( isset( $_SERVER[ 'SERAPH_ACCEL_ORIG_USER_AGENT' ] ) ? $_SERVER[ 'SERAPH_ACCEL_ORIG_USER_AGENT' ] : $_SERVER[ 'HTTP_USER_AGENT' ] ) . '; IP: ' . (isset($_SERVER[ 'REMOTE_ADDR' ])?$_SERVER[ 'REMOTE_ADDR' ]:'<UNK>'), Ui::MsgInfo, 'HTTP trace' );
}

function _ProcessOutCachedData( $objSubType, $sett, $settCache, $dsc, $dscFileTm, $tmCur, $stateValidate, $reason, $out, &$output = null )
{
	global $seraph_accel_g_dscFile;
	global $seraph_accel_g_dataPath;

	$encoding = '';

	$acceptEncodings = array_map( 'trim', explode( ',', strtolower( (isset($_SERVER[ 'HTTP_ACCEPT_ENCODING' ])?$_SERVER[ 'HTTP_ACCEPT_ENCODING' ]:null) ) ) );
	{
		$acceptEncodingsRaw = $acceptEncodings;
		$acceptEncodings = array();
		foreach( $acceptEncodingsRaw as $acceptEncodingRaw )
		{
			$parts = array_map( 'trim', explode( ';', $acceptEncodingRaw ) );
			if( count( $parts ) )
			{
				$parts = $parts[ 0 ];
				if( $parts != 'br' || IsBrotliAvailable() )
					$acceptEncodings[ $parts ] = true;
			}
		}

		unset( $parts );
		unset( $acceptEncodingsRaw );
		unset( $acceptEncodingRaw );
	}

	$encs = Gen::GetArrField( $settCache, array( 'encs' ), array() );

	{
		foreach( $encs as $enc )
		{
			if( $enc === '' )
				continue;

			if( $acceptEncodings[ $enc ] )
			{
				$encoding = $enc;
				break;
			}
		}
	}
	unset( $encs );
	unset( $acceptEncodings );

	if( $encoding === 'compress' )
		$encoding = '';

	$ctxData = CacheDscGetDataCtx( $settCache, $dsc, $encoding, $seraph_accel_g_dataPath, $tmCur, $objSubType === null ? 'html' : $objSubType );
	if( !$ctxData || ( $objSubType === null && !CacheDscValidateDepsData( $dsc, $seraph_accel_g_dataPath ) ) )
	{

		@unlink( $seraph_accel_g_dscFile );
		return( Gen::S_FALSE );
	}

	if( $encoding )
	{
		@ini_set( 'zlib.output_compression', 'Off' );
		@ini_set( 'brotli.output_compression', 'Off' );
	}

	if( $objSubType === null )
		_CacheStdHdrs( (isset($settCache[ 'srv' ])?$settCache[ 'srv' ]:null) );
	else
	{
		switch( $objSubType )
		{
		case 'css':		$objSubType = 'text/css'; break;
		case 'js':		$objSubType = 'application/javascript'; break;
		default:		$objSubType = 'text/html'; break;
		}

		@header( 'Content-Type: ' . $objSubType . '; charset=UTF-8' );
	}

	@header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $dscFileTm ) . ' GMT' );

	if( $ctxData[ 'sizeRaw' ] !== null )
		@header( 'Content-Length: '. $ctxData[ 'sizeRaw' ] );

	if( $encoding )
		@header( 'Content-Encoding: ' . $encoding );

	foreach( Gen::GetArrField( $dsc, array( 'hd' ), array() ) as $hdr )
		@header( $hdr );

	if( (isset($sett[ 'hdrTrace' ])?$sett[ 'hdrTrace' ]:null) || ( $objSubType === null && (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'request' ])?$sett[ 'logScope' ][ 'request' ]:null) ) )
	{
		$debugData = array();
		if( $reason )
			$debugData[ 'reason' ] = $reason;
		if( (isset($dsc[ 't' ])?$dsc[ 't' ]:null) )
			$debugData[ 'cacheTmp' ] = true;
		$debugData[ 'date' ] = gmdate( 'Y-m-d H:i:s', $dscFileTm );
		$debugData[ 'dscFile' ] = substr( $seraph_accel_g_dscFile, strlen( GetCacheDir() ) );

		$debugData = array_merge( $debugData, array_filter( $ctxData, function( $k ) { return( in_array( $k, array( 'encoding', 'recompress', 'compressedEncoding', 'sizeRaw', 'size' ) ) ); }, ARRAY_FILTER_USE_KEY ), array( 'parts' => count( $ctxData[ 'oiFs' ] ) ),
			(isset($sett[ 'debugInfo' ])?$sett[ 'debugInfo' ]:null) ? array( 'PLG_DIR' => __DIR__, '_SERVER' => $_SERVER ) : array()
		);

		_ProcessOutHdrTrace( (isset($sett[ 'hdrTrace' ])?$sett[ 'hdrTrace' ]:null), $objSubType === null && (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'request' ])?$sett[ 'logScope' ][ 'request' ]:null), $stateValidate, $debugData );
	}

	$output = CacheDscDataOutput( $ctxData, $out );
	if( $output !== false )
		return( Gen::S_OK );

	@unlink( $seraph_accel_g_dscFile );
	return( Gen::E_FAIL );
}

function _GetCcf( $oiCi, $encoding, $dataPath, $tmUpdate, $type, $dataComprExts )
{
	$oiCi .= '.' . $type;

	$ext = _GetDataFileEncExt( $encoding, true );
	if( $ext === null || !in_array( $ext, $dataComprExts ) )
		return( null );

	if( $type != 'html' )
		$dataPath .= '/' . $type;

	$oiCf = $dataPath . '/' . $oiCi . $ext;

	@touch( $oiCf, $tmUpdate );
	return( array( 'path' => $oiCf, 'fmt' => $ext ) );
}

function _GetCfc( $oiCf, $out = false )
{
	if( !$out )
		return( @file_get_contents( $oiCf[ 'path' ] ) );

	$file = @fopen( $oiCf[ 'path' ], 'rb' );
	if( !$file )
		return( false );

	while( !@feof( $file ) && ( @connection_status() == 0 ) )
		CacheWriteOut( @fread( $file, 0x10000 ) );

	return( true );
}

function CacheDscGetDataCtxFirstFile( $oiCi, &$ctxData, $dataPath, $tmUpdate, $type, $dataComprExts )
{
	$encoding = $ctxData[ 'encoding' ];

	$oiCf = _GetCcf( $oiCi, $encoding, $dataPath, $tmUpdate, $type, $dataComprExts );
	if( $oiCf )
	{
		$ctxData[ 'compressedEncoding' ] = $encoding;
		return( $oiCf );
	}

	$ctxData[ 'recompress' ] = true;

	$encodings = array( '', 'gzip', 'deflate', 'compress', 'br' );
	if( !in_array( $encoding, $encodings ) )
		return( null );

	foreach( $encodings as $encoding )
	{
		$oiCf = _GetCcf( $oiCi, $encoding, $dataPath, $tmUpdate, $type, $dataComprExts );
		if( $oiCf )
		{
			$ctxData[ 'compressedEncoding' ] = $encoding;
			return( $oiCf );
		}
	}

	return( null );
}

function CacheDscGetDataCtx( $settCache, $dsc, $encoding, $dataPath, $tmUpdate, $type )
{
	$oiCs = (isset($dsc[ 'p' ])?$dsc[ 'p' ]:null);
	if( !is_array( $oiCs ) || !$oiCs )
		return( null );

	$dataComprExts = Gen::GetArrField( $settCache, array( 'dataCompr' ), array() );
	if( empty( $dataComprExts ) )
		$dataComprExts[] = '';
	foreach( $dataComprExts as &$dataComprExt )
		$dataComprExt = _GetDataFileComprExt( $dataComprExt );

	$ctxData = array( 'encoding' => $encoding, 'recompress' => false, 'oiFs' => array() );
	{
		$oiCi = $oiCs[ 0 ];

		$oiCf = CacheDscGetDataCtxFirstFile( $oiCi, $ctxData, $dataPath, $tmUpdate, $type, $dataComprExts );
		if( !$oiCf )
			return( null );

		$ctxData[ 'fmt' ] = $oiCf[ 'fmt' ];
	}

	$fmt = $ctxData[ 'fmt' ];

	if( !$ctxData[ 'recompress' ] )
	{
		switch( $encoding )
		{
		case 'deflate':
			if( $fmt != '.deflu' )
				$ctxData[ 'recompress' ] = true;
			break;

		case 'compress':
			if( $fmt != '.deflu' )
				$ctxData[ 'recompress' ] = true;
			break;

		case 'gzip':
			if( $fmt != '.deflu' && count( $oiCs ) > 1 )
				$ctxData[ 'recompress' ] = true;
			break;

		case 'br':
			if( $fmt != '.brua' && count( $oiCs ) > 1 )
				$ctxData[ 'recompress' ] = true;
			break;

		}
	}

	$recompress = $ctxData[ 'recompress' ];

	$size = 0;
	$sizeRaw = 0;
	$content = '';

	for( $i = 0; $i < count( $oiCs ); $i++ )
	{
		if( $i )
		{
			$oiCi = $oiCs[ $i ];

			$oiCf = _GetCcf( $oiCi, $ctxData[ 'compressedEncoding' ], $dataPath, $tmUpdate, $type, $dataComprExts );
			if( !$oiCf )
				return( null );
			}

		$ctxData[ 'oiFs' ][] = $oiCf;
		$oiCos = GetCacheCos( $oiCi );
		$size += $oiCos;

		if( $recompress )
		{
			$oiCd = _GetCfc( $oiCf );
			if( $oiCd === false || !CacheCvs( strlen( $oiCd ), $oiCos ) )
				return( null );

			switch( $fmt )
			{
			case '.gz':				$oiCd = @gzdecode( $oiCd ); break;
			case '.deflu':		$oiCd = @gzinflate( $oiCd . "\x03\0" ); break;
			case '.br':				$oiCd = Gen::CallFunc( 'brotli_uncompress', array( $oiCd ), false ); break;
			case '.brua':		$oiCd = Gen::CallFunc( 'brotli_uncompress', array( "\x6b\x00" . $oiCd . "\x03" ), false ); break;
			}

			if( $oiCd === false )
				return( null );

			$content .= $oiCd;
		}
		else
		{
			$oiCfs = @filesize( $oiCf[ 'path' ] );
			if( !CacheCvs( $oiCfs, $oiCos ) )
				return( null );
			$sizeRaw += $oiCfs;
		}

	}

	if( !$recompress )
	{
		switch( $encoding )
		{
		case 'deflate':
			if( $fmt == '.deflu' )
				$sizeRaw += 2;
			break;

		case 'compress':
			if( $fmt == '.deflu' )
				$sizeRaw += 2 + 2 + 4;
			break;

		case 'gzip':
			if( $fmt == '.deflu' )
				$sizeRaw += 10 + 2 + 4 + 4;
			break;

		case 'br':
			if( $fmt == '.brua' )
				$sizeRaw += 2 + 1;
			break;
		}
	}
	else
	{
		switch( $encoding )
		{
		case 'deflate':		$content = @gzdeflate( $content, 6 ); break;
		case 'compress':	$content = @gzcompress( $content, 6 ); break;
		case 'gzip':		$content = @gzencode( $content, 6 ); break;
		case 'br':			$content = Gen::CallFunc( 'brotli_compress', array( $content, 7 ), false ); break;
		}

		if( $content === false )
			return( null );

		$sizeRaw = strlen( $content );
	}

	$ctxData[ 'content' ] = $content;
	$ctxData[ 'size' ] = $size;
	$ctxData[ 'sizeRaw' ] = $sizeRaw;
	$ctxData[ 'crc32' ] = $dsc[ 'c' ];
	$ctxData[ 'adler32' ] = $dsc[ 'a' ];
	return( $ctxData );
}

function CacheDscValidateDepsData( $dsc, $dataPath )
{
	static $g_aaCheckExt = array( 'css' => array( 'css' ), 'js' => array( 'js' ), 'img' => array( 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'webp','avif'  ) );

	foreach( Gen::GetArrField( $dsc, array( 's' ), array() ) as $childType => $children )
	{
		$aCheckExt = (isset($g_aaCheckExt[ $childType ])?$g_aaCheckExt[ $childType ]:null);
		if( !$aCheckExt )
			continue;

		$dataPathSubType = $dataPath . '/' . $childType;
		foreach( $children as $childId )
		{
			$found = false;
			foreach( $aCheckExt as $fileExt )
			{
				if( !@file_exists( CacheCgf( $dataPathSubType, $childId, $fileExt ) ) )
					continue;

				$found = true;
				break;
			}

			if( !$found )
				return( false );
		}
	}

	foreach( Gen::GetArrField( $dsc, array( 'b' ), array() ) as $idSubPart => $dscPart )
		if( !CacheDscValidateDepsData( $dscPart, $dataPath ) )
			return( false );

	return( true );
}

function CacheDscDataOutput( $ctxData, $out = true )
{
	$iubyvadkxs = $ctxData[ 'oiFs' ];
	$encoding = $ctxData[ 'encoding' ];
	$recompress = $ctxData[ 'recompress' ];
	$fmt = $ctxData[ 'fmt' ];

	if( !$iubyvadkxs )
		return( false );

	if( $recompress )
	{
		$content = $ctxData[ 'content' ];
		if( !$out )
			return( $content );

		CacheWriteOut( $content );
		return( true );
	}

	$content = '';

	switch( $encoding )
	{
	case 'deflate':
		break;

	case 'compress':
		if( $fmt == '.deflu' )
		{
			$oiCd = "\x78\xDA";
			if( $out )
				CacheWriteOut( $oiCd );
			else
				$content .= $oiCd;
		}
		break;

	case 'gzip':
		if( $fmt == '.deflu' )
		{
			$oiCd = "\x1F\x8B\x08\0\0\0\0\0\x02\x0A";
			if( $out )
				CacheWriteOut( $oiCd );
			else
				$content .= $oiCd;
		}
		break;

	case 'br':
		if( $fmt == '.brua' )
		{
			$oiCd = "\x6b\x00";
			if( $out )
				CacheWriteOut( $oiCd );
			else
				$content .= $oiCd;
		}
		break;
	}

	foreach( $iubyvadkxs as $oiCf )
	{

		$oiCd = _GetCfc( $oiCf, $out );
		if( $oiCd === false )
			return( false );

		if( !$out )
			$content .= $oiCd;

	}

	switch( $encoding )
	{
	case 'deflate':
		if( $fmt == '.deflu' )
		{
			$oiCd = "\x03\0";
			if( $out )
				CacheWriteOut( $oiCd );
			else
				$content .= $oiCd;
		}
		break;

	case 'compress':
		if( $fmt == '.deflu' )
		{
			$oiCd = "\x03\0" . $ctxData[ 'adler32' ];
			if( $out )
				CacheWriteOut( $oiCd );
			else
				$content .= $oiCd;
		}
		break;

	case 'gzip':
		if( $fmt == '.deflu' )
		{
			$oiCd = "\x03\0" . $ctxData[ 'crc32' ] . pack( 'V', $ctxData[ 'size' ] );
			if( $out )
				CacheWriteOut( $oiCd );
			else
				$content .= $oiCd;
		}
		break;

	case 'br':
		if( $fmt == '.brua' )
		{
			$oiCd = "\x03";
			if( $out )
				CacheWriteOut( $oiCd );
			else
				$content .= $oiCd;
		}
		break;
	}

	if( !$out )
		return( $content );

	CacheWriteOut( $content );
	return( true );
}

function CacheWriteOut( $data )
{

	print( $data );
}

function CacheDscWriteCancel( $dscDel = true, $updTime = false )
{
	global $seraph_accel_g_dscFile;
	global $seraph_accel_g_dscFilePending;

	if( $updTime )
		@touch( $seraph_accel_g_dscFile );

	@unlink( $seraph_accel_g_dscFilePending );
	if( Gen::GetFileExt( $seraph_accel_g_dscFilePending ) == 'pp' )
		@unlink( substr( $seraph_accel_g_dscFilePending, 0, -1 ) );

	if( $dscDel && !$updTime )
		@unlink( $seraph_accel_g_dscFile );
}

function _CacheSetRequestToPrepareAsyncEx( $siteId, $url, $hdrs, $tmp = false )
{
	if( !$siteId )
	{
		$tmBegin = Gen::GetCurRequestTime();
		wp_remote_get(
			add_query_arg( array( 'seraph_accel_prep' => @rawurlencode( @base64_encode( @json_encode( array( 'nonce' => hash_hmac( 'md5', '' . $tmBegin, NONCE_SALT ), '_tm' => '' . $tmBegin ) ) ) ) ), $url ),
			array( 'timeout' => 0.01, 'blocking' => false, 'sslverify' => false, 'headers' => $hdrs ) );

		return;
	}

	if( $tmp )
	{
		$tmBegin = Gen::GetCurRequestTime();
		wp_remote_get(
			add_query_arg( array( 'seraph_accel_prep' => @rawurlencode( @base64_encode( @json_encode( array( 'nonce' => hash_hmac( 'md5', '' . $tmBegin, NONCE_SALT ), 'tmp' => true, '_tm' => '' . $tmBegin ) ) ) ) ), $url ),
			array( 'timeout' => 0.01, 'blocking' => false, 'sslverify' => false, 'headers' => $hdrs ) );
	}

	if( CachePostPreparePageEx( $url, $siteId, 10, null, $hdrs ) )
		CachePushQueueProcessor();
}

function CacheSetCurRequestToPrepareAsync( $siteId, $tmp = false, $bgEnabled = false, $early = true )
{

	$obj = new AnyObj();
	$obj -> url = GetCurRequestUrl();
	$obj -> hdrs = Net::GetRequestHeaders();

	if( !$bgEnabled && $siteId && !$tmp )
	{
		Gen::MakeDir( $fileTempQueue = GetCacheDir() . '/qt', true );
		if( $fileTempQueue = tempnam( $fileTempQueue, '' ) )
		{
			if( @file_put_contents( $fileTempQueue, @serialize( array( 'u' => $obj -> url, 's' => $siteId, 'p' => 10, 'h' => $obj -> hdrs, 't' => microtime( true ) ) ) ) !== false )
			{

				if( @rename( $fileTempQueue, $fileTempQueue . '.dat' ) )
				{

					return( true );
				}
				else
					@unlink( $fileTempQueue );
			}
			else
				@unlink( $fileTempQueue );
		}
	}

	if( !$early )
	{
		_CacheSetRequestToPrepareAsyncEx( $siteId, $obj -> url, $obj -> hdrs, $tmp );
		return( false );
	}

	$obj -> siteId = $siteId;
	$obj -> tmp = $tmp;
	$obj -> cb = function( $obj ) { _CacheSetRequestToPrepareAsyncEx( $obj -> siteId, $obj -> url, $obj -> hdrs, $obj -> tmp ); };
	add_action( 'muplugins_loaded', array( $obj, 'cb' ) , 0 );

	if( IsCronEnabled() )
		add_action( 'wp_loaded', function() { if( Wp::GetFilters( 'init', 'wp_cron' ) ) wp_cron(); exit(); }, -999999 );
	else
		add_action( 'muplugins_loaded', function() { exit(); }, 1 );

	return( false );
}

function _CacheContentStart( $tmCur, $procTmLim )
{
	global $seraph_accel_g_dscFile;
	global $seraph_accel_g_dscFilePending;

	for( $try = 1; $try <= 2; $try++ )
	{
		$stm = null;
		$hr = Gen::FileOpenWithMakeDir( $stm, $seraph_accel_g_dscFilePending, 'x' );
		if( $stm )
		{
			@fclose( $stm );
			break;
		}

		if( $try == 2 )
			return( false );

		$dscFilePendingTm = @filemtime( $seraph_accel_g_dscFilePending );
		if( $dscFilePendingTm !== false && ( $tmCur - $dscFilePendingTm < $procTmLim ) )
			return( false );

		@unlink( $seraph_accel_g_dscFilePending );
	}

	return( true );
}

function _CbContentFinishSkip( $content )
{
	global $seraph_accel_g_dscFilePending;
	global $seraph_accel_g_dataPath;
	global $seraph_accel_g_cacheSkipData;
	global $seraph_accel_g_prepPrms;
	global $seraph_accel_g_siteId;

	$sett = Plugin::SettGet();
	$settCache = Gen::GetArrField( $sett, array( 'cache' ), array() );

	@ignore_user_abort( true );

	$skipStatus = Gen::GetArrField( (isset($seraph_accel_g_cacheSkipData[ 1 ])?$seraph_accel_g_cacheSkipData[ 1 ]:null), array( 'reason' ), '' );

	if( (isset($seraph_accel_g_prepPrms[ 'selfTest' ])?$seraph_accel_g_prepPrms[ 'selfTest' ]:null) )
	{
		$content = 'selfTest-' . $seraph_accel_g_prepPrms[ 'selfTest' ];
		sleep( 5 );
	}

	if( $seraph_accel_g_cacheSkipData[ 0 ] === 'revalidating-begin' )
	{
		$beginPrepare = false;
		$updateTmpCache = false;
		$skipStatus = null;

		$cacheSkipData = $seraph_accel_g_cacheSkipData;
		$seraph_accel_g_cacheSkipData = null;
		if( !GetContCacheEarlySkipData( $path, $pathIsDir, $args ) )
		{
			if( $skipStatus = ContProcGetSkipStatus( $content ) )
			{
				$seraph_accel_g_cacheSkipData = array( 'skipped', array( 'reason' => $skipStatus ) );
				if( $skipStatus == 'httpCode:500' || Gen::StrStartsWith( $skipStatus, 'err:php' ) )
					$beginPrepare = true;
			}
			else
			{
				$seraph_accel_g_cacheSkipData = $cacheSkipData;
				$beginPrepare = true;
				$updateTmpCache = true;
			}
		}

		if( $beginPrepare )
		{
			if( $updateTmpCache )
			{
				$seraph_accel_g_dscFilePending = $seraph_accel_g_dscFilePending . 'p';

				$lock = new Lock( 'dl', GetCacheDir() );
				if( $seraph_accel_g_dataPath && $lock -> Acquire() )
				{
					if( _CacheContentStart( Gen::GetCurRequestTime(), Gen::GetArrField( $settCache, array( 'procTmLim' ), 570 ) ) )
					{
						$lock -> Release();

						CacheDscUpdate( $lock, $settCache, $content, null, null, $seraph_accel_g_dataPath, true );
						if( Gen::GetArrField( $settCache, array( 'srvClr' ), false ) && function_exists( 'seraph_accel\\CacheExt_Clear' ) )
							CacheExt_Clear( GetCurRequestUrl() );
					}
					else
						$lock -> Release();
				}
				unset( $lock );
				$seraph_accel_g_dscFilePending = substr( $seraph_accel_g_dscFilePending, 0, -1 );
			}

			CacheSetCurRequestToPrepareAsync( $seraph_accel_g_siteId, false, false, false );
		}
		else
		{
			CacheDscWriteCancel( true, $skipStatus === 'notChanged' );
			if( $skipStatus !== 'notChanged' && Gen::GetArrField( $settCache, array( 'srvClr' ), false ) && function_exists( 'seraph_accel\\CacheExt_Clear' ) )
				CacheExt_Clear( GetCurRequestUrl() );
		}
	}

	if( (isset($sett[ 'hdrTrace' ])?$sett[ 'hdrTrace' ]:null) || ( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'request' ])?$sett[ 'logScope' ][ 'request' ]:null) ) )
		_ProcessOutHdrTrace( (isset($sett[ 'hdrTrace' ])?$sett[ 'hdrTrace' ]:null), (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'request' ])?$sett[ 'logScope' ][ 'request' ]:null), $seraph_accel_g_cacheSkipData[ 0 ], (isset($seraph_accel_g_cacheSkipData[ 1 ])?$seraph_accel_g_cacheSkipData[ 1 ]:null) );

	if( Gen::GetArrField( $settCache, array( 'chunks', 'enable' ) ) && GetContentProcessorForce( $sett ) )
		ContentMarkSeparateSofter( $content, false );

	if( $seraph_accel_g_prepPrms !== null )
	{
		ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'finish' => true, 'skip' => $skipStatus ), false, false );

		$httpCode = http_response_code();
		if( $httpCode >= 300 && $httpCode < 400 )
			http_response_code( 200 );
	}

	return( $content );
}

function _CbContentProcess( $content )
{
	if( !function_exists( 'seraph_accel\\OnEarlyContentComplete' ) )
		return( $content );
	return( OnEarlyContentComplete( $content, true ) );
}

function _CbContentFinish( $content )
{
	global $post;

	global $seraph_accel_g_dscFile;
	global $seraph_accel_g_dscFilePending;
	global $seraph_accel_g_dataPath;
	global $seraph_accel_g_noFo;
	global $seraph_accel_g_cacheObjChildren;
	global $seraph_accel_g_cacheObjSubs;
	global $seraph_accel_g_prepPrms;
	global $seraph_accel_g_prepOrigContHash;
	global $seraph_accel_g_prepLearnId;

	$sett = Plugin::SettGet();
	$settCache = Gen::GetArrField( $sett, array( 'cache' ), array() );

	$skipStatus = ContProcGetSkipStatus( $content );
	if( !$skipStatus && ContentProcess_IsAborted() )
		$skipStatus = 'aborted';

	if( $skipStatus )
	{
		if( $seraph_accel_g_prepPrms !== null )
		{

			$httpCode = http_response_code();
			if( $httpCode >= 300 && $httpCode < 400 )
				http_response_code( 200 );
		}

		if( !$seraph_accel_g_noFo && ( (isset($sett[ 'hdrTrace' ])?$sett[ 'hdrTrace' ]:null) || ( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'request' ])?$sett[ 'logScope' ][ 'request' ]:null) ) ) )
			_ProcessOutHdrTrace( (isset($sett[ 'hdrTrace' ])?$sett[ 'hdrTrace' ]:null), (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'request' ])?$sett[ 'logScope' ][ 'request' ]:null), 'skipped', array( 'reason' => $skipStatus ) );

		CacheDscWriteCancel( $skipStatus !== 'aborted' && !Gen::StrStartsWith( $skipStatus, 'lrnNeed' ), $skipStatus === 'notChanged' );

		if( $skipStatus !== 'aborted' && !Gen::StrStartsWith( $skipStatus, 'lrnNeed' ) && $skipStatus !== 'notChanged' && Gen::GetArrField( $settCache, array( 'srvClr' ), false ) && function_exists( 'seraph_accel\\CacheExt_Clear' ) )
			CacheExt_Clear( GetCurRequestUrl() );

		if( $seraph_accel_g_prepPrms !== null )
			ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'finish' => true, 'skip' => $skipStatus ), false, false );

		if( Gen::GetArrField( $settCache, array( 'chunks', 'enable' ) ) && GetContentProcessorForce( $sett ) )
			ContentMarkSeparateSofter( $content, false );

		return( $content );
	}

	$lock = new Lock( 'dl', GetCacheDir() );
	$dsc = CacheDscUpdate( $lock, $settCache, $content, $seraph_accel_g_cacheObjChildren, $seraph_accel_g_cacheObjSubs, $seraph_accel_g_dataPath, Gen::GetArrField( $seraph_accel_g_prepPrms, array( 'tmp' ) ), $seraph_accel_g_prepOrigContHash, $seraph_accel_g_prepLearnId );
	unset( $lock );

	if( !$dsc )
	{
		$skipStatus = 'dscFileUpdateError';
		if( !$seraph_accel_g_noFo && ( (isset($sett[ 'hdrTrace' ])?$sett[ 'hdrTrace' ]:null) || ( (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'request' ])?$sett[ 'logScope' ][ 'request' ]:null) ) ) )
			_ProcessOutHdrTrace( (isset($sett[ 'hdrTrace' ])?$sett[ 'hdrTrace' ]:null), (isset($sett[ 'log' ])?$sett[ 'log' ]:null) && (isset($sett[ 'logScope' ][ 'request' ])?$sett[ 'logScope' ][ 'request' ]:null), 'skipped', array( 'reason' => $skipStatus ), $seraph_accel_g_dscFile );

		if( $seraph_accel_g_prepPrms !== null )
		{
			if( Gen::LastErrDsc_Is() )
				$skipStatus .= ':' . rawurlencode( Gen::LastErrDsc_Get() );
			ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'finish' => true, 'skip' => $skipStatus ), false, false );
		}

		return( $content );
	}

	if( Gen::GetArrField( $settCache, array( 'srvClr' ), false ) && function_exists( 'seraph_accel\\CacheExt_Clear' ) )
		CacheExt_Clear( GetCurRequestUrl() );

	if( $seraph_accel_g_prepPrms !== null )
		ProcessCtlData_Update( (isset($seraph_accel_g_prepPrms[ 'pc' ])?$seraph_accel_g_prepPrms[ 'pc' ]:null), array( 'finish' => true, 'warns' => LastWarnDscs_Get() ), false, false );

	if( $seraph_accel_g_noFo )
		return( '' );

	$content = null;
	_ProcessOutCachedData( null, $sett, $settCache, $dsc, $dscFileTm, @filemtime( $seraph_accel_g_dscFile ), 'revalidated', null, false, $content );
	return( $content );
}

function GetCacheViewId( $settCache, $userAgent, &$args )
{
	$stateId = '';
	$geoId = '';

	$type = 'cmn';
	if( (isset($settCache[ 'normAgent' ])?$settCache[ 'normAgent' ]:null) )
	{
		$_SERVER[ 'SERAPH_ACCEL_ORIG_USER_AGENT' ] = $_SERVER[ 'HTTP_USER_AGENT' ];
		$_SERVER[ 'HTTP_USER_AGENT' ] = 'Mozilla/99999.9 AppleWebKit/9999999.99 (KHTML, like Gecko) Chrome/999999.0.9999.99 Safari/9999999.99 seraph-accel-Agent/2.19.7';
	}

	if( (isset($settCache[ 'views' ])?$settCache[ 'views' ]:null) )
	{
		if( $viewsDeviceGrp = GetCacheViewDeviceGrp( $settCache, $userAgent ) )
		{
			$type = (isset($viewsDeviceGrp[ 'id' ])?$viewsDeviceGrp[ 'id' ]:null);
			if( (isset($settCache[ 'normAgent' ])?$settCache[ 'normAgent' ]:null) )
				$_SERVER[ 'HTTP_USER_AGENT' ] = GetViewTypeUserAgent( $viewsDeviceGrp );
		}

		$viewsGrps = Gen::GetArrField( $settCache, array( 'viewsGrps' ), array() );
		foreach( $viewsGrps as $viewsGrp )
		{
			if( !(isset($viewsGrp[ 'enable' ])?$viewsGrp[ 'enable' ]:null) )
				continue;

			AccomulateCookiesState( $stateId, $_COOKIE, Gen::GetArrField( $viewsGrp, array( 'cookies' ), array() ) );

			$viewsArgs = Gen::GetArrField( $viewsGrp, array( 'args' ), array() );
			foreach( $viewsArgs as $a )
			{
				foreach( $args as $argKey => $argVal )
				{
					if( strpos( $argKey, $a ) === 0 )
					{
						$stateId .= $argKey . $argVal;
						unset( $args[ $argKey ] );
					}
				}
			}
		}

		if( Gen::GetArrField( $settCache, array( 'viewsGeo', 'enable' ) ) )
		{
			$geoId = (isset($_SERVER[ 'HTTP_X_SERAPH_ACCEL_GEOID' ])?$_SERVER[ 'HTTP_X_SERAPH_ACCEL_GEOID' ]:null);
			if( !is_string( $geoId ) )
			{
				$ip = Net::GetRequestIp();

				$countryCode = GetCountryCodeByIp( $settCache, $ip );

				$geoId = null;
				$grpIsFirst = true;
				$countryCodeForce = null;
				foreach( Gen::GetArrField( $settCache, array( 'viewsGeo', 'grps' ), array() ) as $grpId => $grp )
				{
					if( !(isset($grp[ 'enable' ])?$grp[ 'enable' ]:null) )
						continue;

					$matched = false;
					$countryCodeFirstTmp = null;
					foreach( Gen::GetArrField( $grp, array( 'items' ), array() ) as $grpItem )
					{
						$aa = ExprConditionsSet_Parse( $grpItem );
						if( $countryCodeFirstTmp === null && ExprConditionsSet_IsTrivial( $aa ) )
							$countryCodeFirstTmp = $grpItem;

						foreach( $aa as $a )
						{
							$v = null;
							if( IsStrRegExp( $a[ 'expr' ] ) )
							{
								if( @preg_match( $a[ 'expr' ], $countryCode ) )
									$v = $countryCode;
							}
							else if( $countryCode === $a[ 'expr' ] )
								$v = $countryCode;

							$matched = ExprConditionsSet_ItemOp( $a, $v );
							if( !$matched )
								break;
						}

						if( $matched )
							break;
					}

					if( $matched )
					{
						$geoId = $grpIsFirst ? '' : $grpId;
						$countryCodeForce = $countryCodeFirstTmp;
						break;
					}

					$grpIsFirst = false;
				}

				if( $geoId === null )
					$geoId = $countryCode;

				if( $countryCodeForce )
				{

					static $g_aRegIpDef = array(
						'IN' => '165.22.217.1',
						'BG' => '195.123.228.1',
						'US' => '161.35.130.1',
					);

					if( isset( $g_aRegIpDef[ $countryCodeForce ] ) )
						$ip = $g_aRegIpDef[ $countryCodeForce ];
				}

				$_SERVER[ 'REMOTE_ADDR' ] = $ip;
				$_SERVER[ 'HTTP_X_SERAPH_ACCEL_GEOID' ] = $geoId;
				$_SERVER[ 'HTTP_X_SERAPH_ACCEL_GEO_REMOTE_ADDR' ] = $_SERVER[ 'REMOTE_ADDR' ];
				unset( $_SERVER[ 'HTTP_X_REAL_IP' ], $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] );
			}
		}
	}

	if( strlen( $geoId ) )
		$type .= '-' . $geoId;

	{
		$serverArgsTmp = Gen::ArrCopy( $_SERVER ); CorrectRequestScheme( $serverArgsTmp, 'client' );
		if( (isset($serverArgsTmp[ 'REQUEST_SCHEME' ])?$serverArgsTmp[ 'REQUEST_SCHEME' ]:null) == 'http' )
			$type .= '-ns';
	}

	$compatView = ContProcIsCompatView( $settCache, $userAgent );
	if( $compatView )
		$type .= '-' . $compatView;

	if( strlen( $stateId ) )
		$type .= '-' . md5( $stateId );

	return( $type );
}

