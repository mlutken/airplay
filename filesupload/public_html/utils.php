<?php


/** Delete a cookie. */
function cookieDelete( $sCookieName ) 
{
	//setcookie( $sCookieName, "", mktime(12,0,0,1, 1, 1990) );
	setcookie ( $sCookieName, "", time() - 3600);
// 	logMsg( "" )
}

//mysql_real_escape_string

/** Get a POST/GET url parameter if present or return empty string if not. 
Returned value is filtered through 'addslashes'/'mysql_real_escape_string' to prevent 
possible SQL injections in case the parameter is used in database lookups. */
function getUrlParam( $sParamName, $sValueIfNotFound="" ) 
{
	if ( array_key_exists( $sParamName, $_GET ) )	{
		return addslashes( $_GET[$sParamName] );
	}
	else return $sValueIfNotFound;
}

function strValToBool( $sVal )
{
	if ( $sVal == '' )	return false;
	return strcasecmp ( $sVal, 'false' ) == 0 ? false : true;  
}

/// Validate password syntax
function 	validPaswordSimple( $sPassword )
{
	return ( strlen($sPassword) > 0 );
}


/** Get array value safe. Returns value from array of key given or 
the default value if not found */
function getArraySafe( $sKeyName, $aArray, $valueIfNotFound='' ) 
{
	if ( !is_array ($aArray) )						return $valueIfNotFound;
	if ( array_key_exists( $sKeyName, $aArray ) )	return $aArray[$sKeyName];
	else 											return $valueIfNotFound;
}


/** Get the difference of two arrays based solely on their key names. */
function diffArrayKeys( $aCompareFrom, $aCompareAgainst ) 
{
	$a = array();
	foreach ( $aCompareFrom as $sKey => $val ) {
		if ( !array_key_exists ( $sKey, $aCompareAgainst ) )	$a[$sKey] = $aCompareFrom[$sKey];
	}
	return $a;
}




/**
Validate an email address.
Provide email address (raw input)
Returns true if the email address has the email 
address format and the domain exists.
\see http://www.linuxjournal.com/article/9585 
\see http://www.sslug.dk/emailarkiv/netvaerk/2006_09/msg00006.html */
function validEmail( $email )
{
	$isValid = true;
	$atIndex = strrpos($email, "@");
	if (is_bool($atIndex) && !$atIndex)
	{
		$isValid = false;
	}
	else
	{
		$domain = substr($email, $atIndex+1);
		$local = substr($email, 0, $atIndex);
		$localLen = strlen($local);
		$domainLen = strlen($domain);
		if ($localLen < 1 || $localLen > 64)
		{
			// local part length exceeded
			$isValid = false;
		}
		else if ($domainLen < 1 || $domainLen > 255)
		{
			// domain part length exceeded
			$isValid = false;
		}
		else if ($local[0] == '.' || $local[$localLen-1] == '.')
		{
			// local part starts or ends with '.'
			$isValid = false;
		}
		else if (preg_match('/\\.\\./', $local))
		{
			// local part has two consecutive dots
			$isValid = false;
		}
		else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
		{
			// character not valid in domain part
			$isValid = false;
		}
		else if (preg_match('/\\.\\./', $domain))
		{
			// domain part has two consecutive dots
			$isValid = false;
		}
		else if	(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\","",$local)))
		{
			// character not valid in local part unless 
			// local part is quoted
			if (!preg_match('/^"(\\\\"|[^"])+"$/',
				str_replace("\\\\","",$local)))
			{
			$isValid = false;
			}
		}
		if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
		{
			// domain not found in DNS
			$isValid = false;
		}
	}
	return $isValid;
}


function listDir( $sDir='.', $bRecursive = true, $sNameContains ='' ) {
	
	$iLen = strlen($sDir);
	if ( $iLen > 0 && $sDir[$iLen -1] == '/' )	$sDir = substr( $sDir, 0, -1);
	
	$aFiles = array();
	if ( is_dir($sDir) ) {
		$fh = opendir($sDir);
		while ( ($file = readdir($fh) ) !== false) {
			# loop through the files, skipping . and .., and recursing if necessary
			if (strcmp($file, '.')==0 || strcmp($file, '..')==0) continue;
			$sFilePath = $sDir . '/' . $file;
			if ( $bRecursive && is_dir($sFilePath) ) {
				$aFiles = array_merge( $aFiles, listDir($sFilePath) );
			}
			else {
				if ( $sNameContains == '' )									array_push( $aFiles, $sFilePath );
				else if ( strpos( $sFilePath, $sNameContains ) !== false ) 	array_push( $aFiles, $sFilePath );
			}
		}
		closedir($fh);
	} 
	else {
		# false if the function was called with an invalid non-directory argument
		$aFiles = false;
	}
	return $aFiles;
}


function showProgress( $iCount, $iIterationsPerDot = 50 )
{	
	if ( $iCount % $iIterationsPerDot == 0 ) {
		printf (".") ;
		if ( $iCount % ($iIterationsPerDot*100) == 0 ) printf("<br>\n");
		if ( php_sapi_name() != 'cli' ) ob_flush();                      
		flush();    
	}
}	

/**
Here's an update to the script a couple of people gave below to read arguments from $argv of the form --name=VALUE and -flag. Changes include:

Don't use $_ARG - $_ is generally considered reserved for the engine.
Don't use regex where a string operation will do just as nicely
Don't overwrite --name=VALUE with -flag when 'name' and 'flag' are the same thing
Allow for VALUE that has an equals sign in it
\see http://dk.php.net/features.commandline
*/
function arguments() {
	global $argv;
	$ARG = array();
	foreach ($argv as $arg) {
		if (strpos($arg, '--') === 0) {
			$compspec = explode('=', $arg);
			$key = str_replace('--', '', array_shift($compspec));
			$value = join('=', $compspec);
			$ARG[$key] = $value;
		} elseif (strpos($arg, '-') === 0) {
			$key = str_replace('-', '', $arg);
			if (!isset($ARG[$key])) $ARG[$key] = true;
		}
	}
	return $ARG;
}

/// uncompress("test.gz","test.php");
function uncompress($srcName, $dstName) {
	$iBlockSize = 8192;
	$fp = fopen($dstName, "w");
	$zp = gzopen($srcName, "r");
	$contents = '';
	while (!gzeof($zp)) {
		$contents = gzread($zp, $iBlockSize);
		fwrite  ( $fp  , $contents );		
	}
	fclose($fp);
	gzclose($zp);
} 


/// compress("test.php","test.gz");
function compress($srcName, $dstName)
{
	$iBlockSize = 8192;
	$fp = fopen($srcName, "r");
	$zp = gzopen($dstName, "w9");
	$contents = '';
	while (!feof($fp)) {
		$contents = fread($fp, $iBlockSize);
		gzwrite  ( $zp  , $contents );		
	}
	fclose($fp);
	gzclose($zp);
}


function getStatndardHTMLHeader( $sPageTitle = "" )
{
	$sHeader = "<html xmlns='http://www.w3.org/1999/xhtml'>\n<head>\n\t<meta http-equiv='content-type' content='text/html; charset=utf-8'/>\n</head>\n<body>\n";
	return $sHeader;
}

function downloadFile( $sUrlFilePath, $sDestDir ) 
{
	$pathParts = pathinfo($sUrlFilePath);
	$sBaseName = $pathParts['basename'];
	$sExtension = $pathParts['extension'];
	$sFileNameWE = $pathParts['filename'];
	
	
	$fp = fopen ( $sDestDir . $sBaseName, 'w+');	//This is the file where we save the information
	$ch = curl_init($sUrlFilePath);//Here is the file we are downloading
	curl_setopt($ch, CURLOPT_TIMEOUT, 50);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	$bSuccess = curl_exec($ch);
	curl_close($ch);
	fclose($fp);
	if ( $bSuccess ) 	return $sDestDir . $sBaseName;
	else 				return false;
	//dirname(__FILE__)
}

function toLowerMySqlEscape( $s ) 
{
	$s = mb_strtolower( $s, 'UTF-8' );
	$s = mysql_real_escape_string( $s );
	return $s;
}


?>
