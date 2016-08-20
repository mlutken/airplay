<?php
require_once ('utils.php');

$g_IsProd = null;

function forceIsProd( $bIsProd )
{
    global $g_IsProd;
    $g_IsProd = $bIsProd;
}

/** Path of current servers document root. 
Typically either '/'  or '/Site' 
\note Don't use this from cronjobs. Use getSiteRoot() or getSiteRootParent() */
function getDocRoot()
{
    static $sDocRoot = "";
    if ( $sDocRoot != "" )	return $sDocRoot;
    $sDocRoot = "/_ERROR_DOCROOT_";
    if ( isset($_SERVER['SERVER_NAME']) ) {
	    $sDocRoot = "/";	// Assume server is NOT localhost or our testserver
     	if ( stristr($_SERVER['SERVER_NAME'], 'mapzter.com')) 	$sDocRoot = "/";
     	if ( stristr($_SERVER['SERVER_NAME'], 'localhost')) 	$sDocRoot = "/";
    }
    return $sDocRoot;
}


function getPathDelim()
{
    static $cPathDelim = "";
    if ($cPathDelim != "")
        return $cPathDelim;

    $cPathDelim = "/";
    if (PHP_OS == "Linux")
        $cPathDelim = "/";
    elseif (PHP_OS == "WINNT")
        $cPathDelim = "\\";
    // TODO: Need to find out how many values this can assume, on different windows systems, mostly XP, Vista and Windows 7

    return $cPathDelim;
}


function getSiteLogDir()
{
    return getSiteRootParent() . "log/";
}

function getSiteRootParent()
{
    static $sSiteRootParent = "";
    if ($sSiteRootParent != "")
        return $sSiteRootParent;

    $aPath = explode(getPathDelim(), __file__);
    if ($aPath[0] == '')
        $sSiteRootParent = '/'; // Assume unix like system
    else
        $sSiteRootParent = $aPath[0] . '\\'; //Assume Windows like system

    // We know this file is located in ../public_html/SystemSettings.php, so if we chop
    // of the last 1 parts in the full filename we get our "parent document root" directory.
    // IMPORTANT: I this file is moved to another location in our (svn) filetree, this function
    // might need fixing. Ie. the  'count($aPath)-3' expression below needs fx. to be for example
    // 'count($aPath)-1' OR 'count($aPath)-2'.
    for ($i = 1; $i < count($aPath) - 2; $i++) {
        $sSiteRootParent .= $aPath[$i] . '/'; // 	We use forward slashes on all systems.
    }
    return $sSiteRootParent;
}

function getSiteRoot()
{
    static $sSiteRoot = "";
    if ($sSiteRoot != "")
        return $sSiteRoot;
    $sSiteRoot = getSiteRootParent() . 'public_html/';
    return $sSiteRoot;
}

function getTempDir()
{
    static $sTempDir = "";
    if ($sTempDir != "")
        return $sTempDir;
    $sTempDir = getSiteRoot() . 'temp/';
    if (!file_exists($sTempDir ) ) {
		mkdir( $sTempDir, 0755, true ); 
	}
    return $sTempDir;
}


/// Are we running in command line mode ?
function isCLI()
{
	static $bIsCLI = "";
	if ( $bIsCLI != "")	return $bIsCLI;
	$bIsCLI = false;
	if ( array_key_exists('argv', $_SERVER) ) {
		if ( count($_SERVER['argv']) > 0 ) {
			$bIsCLI = true;
		}
	}
	return $bIsCLI;
}


function onLocalHost()
{
    static $bOnLocalHost = "";
    if ( $bOnLocalHost != "")	return $bOnLocalHost;
        
	$sServerUrl = "localhost";
	$bOnLocalHost = true;
	if ( isset( $_SERVER['HTTP_HOST'] ) )	$sServerUrl = $_SERVER['HTTP_HOST'];
	
	// --- Find plugin name from URL ---
	$iPos1 = strrpos( $sServerUrl, '.localhost' );	// Ends with .localhost
	$iPos2 = strrpos( $sServerUrl, 'localhost' );	// Ends with localhost
	
	if ( $iPos1 !== false ) {
		$sServerUrl = substr  ( $sServerUrl, 0,  $iPos1 );
	}
	else if ( $iPos2 !== false ) {
	}
	else {	
	////	printf ("<br>NOT LOCALHOST<br>");
		$bOnLocalHost = false; 
	}
	if ( get_current_user() == "sleipner" )	$bOnLocalHost = false;

	return $bOnLocalHost;
}

function isProd()
{
    global $g_IsProd;
	
	static $bIsProd = null;
	if ( $bIsProd != null )	return $bIsProd;
    $bIsProd = null;
    
    if ( $g_IsProd != null ) {
        $bIsProd = $g_IsProd;
    }
	else {
        $bIsProd = true;
        if ( onLocalHost() )	$bIsProd = false;
        if ( array_key_exists( "PROD", $_GET ) )	{
            $iProd = $_GET["PROD"];
            if ( $iProd == 1 )	$bIsProd = true;
            else				$bIsProd = false;
        }
        else if ( isCLI() ) {
            logMsg("isCLI");
            $ARGS = arguments();
            if ( array_key_exists( "PROD", $ARGS ) )	{
                $iProd = $ARGS["PROD"];
                if ( $iProd == 1 )	$bIsProd = true;
                else				$bIsProd = false;
            }
        }
    }
	return $bIsProd;
}

function serverUrl()
{
	static $sServerUrl = "";
	if ( $sServerUrl != "")	return $sServerUrl;
	$sServerUrl = "localhost";
	if ( isset( $_SERVER['HTTP_HOST'] ) )	$sServerUrl = $_SERVER['HTTP_HOST'];

	$iPos1 = strrpos( $sServerUrl, '.localhost' );	// Ends with .localhost
	if ( $iPos1 !== false ) {
		$sServerUrl = substr  ( $sServerUrl, 0,  $iPos1 );
	}
	return $sServerUrl;
}


function getRealIpAddr()
{
    $ip = "127.0.0.1";
    if ( !onLocalHost() ) {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
        {
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
        {
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
            $ip=$_SERVER['REMOTE_ADDR'];
        }
    }
    return $ip;
}

////loadEnvironment();

// // // ---------------
// // // --- FirePHP ---
// // // ---------------
// // 
// // require_once('utils/FirePHPCore/fb.php');
// // ob_start();
// // $firephp = FirePHP::getInstance(true);
// // 
// // 
// // function logFire($var, $name = "" )
// // {
// // 	static $firephp = "";
// // 	//if ( isProd() ) return;
// // 	if ( $firephp == "")	{
// // 		$firephp = FirePHP::getInstance(true);
// // 	}
// // 	$firephp->log($var, $name);
// // }


?>