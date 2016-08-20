<?php


function contains ( $haystack, $needle, $case=true )
{
   if ( $case ) return strpos($haystack, $needle, 0) !== false;
   return stripos($haystack, $needle, 0) !== false;
}

function icontains ( $haystack, $needle)
{
   return stripos($haystack, $needle, 0) !== false;
}

function startsWith ( $haystack, $needle, $case=true )
{
   if ( $case ) return strpos($haystack, $needle, 0) === 0;

   return stripos($haystack, $needle, 0) === 0;
}

function endsWith ( $haystack, $needle, $caseSensitive=true )
{
  $expectedPosition = strlen($haystack) - strlen($needle);

  if ( $caseSensitive ) return strrpos($haystack, $needle, 0) === $expectedPosition;

  return strripos($haystack, $needle, 0) === $expectedPosition;
}

function explodeTrim($delimiter, $string)
{
	$aTmp = explode($delimiter, $string);
	$a = array();
	foreach ( $aTmp as $s) {
		$a[] = trim($s);
	}
	return $a;
}

function isAnyInString($haystack, $aNeedles)
{
	$bFoundOne = false;
	foreach ( $aNeedles as $needle) {
		$pos = strpos($haystack, $needle );
		if ( $pos !== false ) {
			$bFoundOne = true;
			break;
		}
	}
	return $bFoundOne;
}

function iIsAnyInString($haystack, $aNeedles)
{
	$bFoundOne = false;
	foreach ( $aNeedles as $needle) {
		$pos = stripos($haystack, $needle );
		if ( $pos !== false ) {
			$bFoundOne = true;
			break;
		}
	}
	return $bFoundOne;
}


function findAllInString($haystack, $aNeedles)
{
	$aAllFound = array();
	foreach ( $aNeedles as $needle ) {
		$pos = strpos($haystack, $needle );
		if ( $pos !== false ) {
			$aAllFound[] = $needle;
		}
	}
	return $aAllFound;
}


function stringRemoveAll($aRemove, $subject)
{
	foreach ( $aRemove as $remove) {
		$subject = str_replace ( $remove , '' , $subject);
	}
	return simplifyWhiteSpace($subject);
}


function stringReplaceAll($aSearchReplace, $subject)
{
	foreach ( $aSearchReplace as $search => $replace ) {
		$subject = str_replace ( $search, $replace, $subject);
	}
	$subject = simplifyWhiteSpace($subject);
	return $subject;
}




/** Collaps whitespaces so no more than one consequtive whitespace in the returned 
string. Also the string is 'normal' trimmed for leading and triling whitespace. 
\return String with all multiple whitespaces replaces by one and with any leading and trailing spaces removed. */
function simplifyWhiteSpace ($string) 
{
    $s = preg_replace( '/\s+/', ' ', $string ); // Simplify spaces
    return trim( $s ); 
} 


/** Split a multibyte (ie. UTF-8 string) into an array of chars. 
\return array of chars. */
function utf8StringToArray ($string) 
{
    $strlen = mb_strlen($string);
    while ($strlen) {
        $array[] = mb_substr($string,0,1,"UTF-8");
        $string = mb_substr($string,1,$strlen,"UTF-8");
        $strlen = mb_strlen($string);
    }
    return $array;
} 



/** Splits a string in words and returs a string with each word substituted for its 
soundex string. Ex. 'Black Celebration' => 'B420 C416'. If name is number only, then return that number.
\note Splitting up in words and taking soundex on each yields a very diffrent result 
      compared to taking sooundex on the complete string.
\return Soundex of the string taken word by word. */
function calcSoundex($sName)
{
    /* If name is number only then return number */
    if (is_numeric($sName)) return $sName;
    
    $aWords         = explode(' ', $sName );
    $sSoundex = '';
    $iCountWords = count($aWords);
    for ( $i = 0; $i < $iCountWords; $i++ ) {
        if ( $i > 0 ) $sSoundex   .= ' ';
        $sSoundex   .= soundex($aWords[$i]);;
    }
    return $sSoundex;
}


/** Splits a string in words and returs a string with each word substituted for its 
soundex string. Ex. 'Black Celebration' => 'BLK SLBRXN'. 
\return Metaphone of the string taken word by word. */
function calcMetaphone($sName)
{
    $aWords         = explode(' ', $sName );
    $sMetaphone = '';
    $iCountWords = count($aWords);
    for ( $i = 0; $i < $iCountWords; $i++ ) {
        if ( $i > 0 ) $sMetaphone   .= ' ';
        $sMetaphone   .= metaphone($aWords[$i]);;
    }
    return $sMetaphone;
}


/** Function to determine whether a given string represents a single digit (0-9). 
\param $c Char/string to test.
\return true if string represents a digit, false otherwise. */
function is_digit( $c )
{
    if ( is_numeric($c) ) {
        $i = intval($c);
        return 0 <= $i && $i <= 9;
    }
    return false;
}


/** Function pretty print an associative array. 
\param $a Array to print.
\param $headLine Optional headline. */
function pp_array( $a, $headLine = '' )
{
    if ( $headLine != '' ) printf("--- $headLine ---\n");
    foreach ( $a as $k => $v ) {
        printf ( "$k\t\t: $v\n" );
    }
}


// ---------------------
// --- URL functions ---
// ---------------------


global $G_URL_TO_NORMAL_fromUrl;
global $G_URL_TO_NORMAL_toNormal;   


//TODO: Should we have more substitutions? ";", "/", "?", ":", "@", "=" and "&" 
$G_URL_TO_NORMAL_fromUrl    =  array("_"   , "-and-", "-slash-", "-qmark-", "-percent-", "-plus-"  );
$G_URL_TO_NORMAL_toNormal   =  array(" "   , "&"    , "/"      , "?"      , "%"        , "+"       );


/** Converts from url-name to real name. 
E.g: 
\code
\endcode
\return 'Normal' name with spaces instead of hyphens etc.
*/
function urlToName( $sUrlName ) 
{
    global $G_URL_TO_NORMAL_fromUrl;
    global $G_URL_TO_NORMAL_toNormal;   
    $sName = str_replace  ( $G_URL_TO_NORMAL_fromUrl ,$G_URL_TO_NORMAL_toNormal, $sUrlName );   
    return $sName;
}


/** Converts from real name to url-name . 
E.g: 
\code
\endcode
\return 'Url-name' name with hyphens instead of spaces etc.
*/
function nameToUrl( $sName ) 
{
    global $G_URL_TO_NORMAL_fromUrl;
    global $G_URL_TO_NORMAL_toNormal;   
    $sUrl = str_replace  ( $G_URL_TO_NORMAL_toNormal, $G_URL_TO_NORMAL_fromUrl, $sName );   
    return $sUrl;
}

// ------------------------------
// --- Solr utility functions ---
// ------------------------------

function arrayToSolrXmlString( $aData  )
{
    $s = "<doc>\n";
    foreach( $aData as $k => $v ) {
        $s .= "<field name=\"{$k}\"><![CDATA[{$v}]]></field>\n";
    }
    $s .= "</doc>\n";
    return $s;
}


// --------------------------------
// --- FileDb utility functions ---
// --------------------------------
/** Calculate a hash value from string returning a 32 bit integer. 
Other hash functions like this:	$hash32 = hexdec( substr( hash( 'sha1', $artist_id), 0, 15) );
*/
function hash32($s)
{	
    return crc32($s);
}

function nameToIDLowercase($name_lower_case)
{
	if ( '' == $name_lower_case ) return '';
	$id = nameToUrl($name_lower_case);
	if ( strlen($id) > 200 ) {
		$s = substr($id, 0, 200);
		$id = $s . '_^^_' . sha1($id);
	}
    return $id;
}

function nameToID($name)
{
	if ( '' == $name ) return '';
	$n = mb_strtolower( $name, 'UTF-8' );
	$id = nameToUrl($n);
	if ( strlen($id) > 200 ) {
		$s = substr($id, 0, 200);
		$id = $s . '_^^_' . sha1($id);
	}
    return $id;
}

function moduloDirFromID($id)
{	
    $iModuloDir = hash32($id) % 16384;
    return $iModuloDir;
}

function moduloDirFromHash32($hash32)
{	
    $iModuloDir = $hash32 % 16384;
    return $iModuloDir;
}


function itemBaseNameToID( $item_base_name, $item_type )
{
	if ( '' == $item_base_name || '' == $item_type ) return '';
	return nameToID( $item_base_name ) . '^' . $item_type;
}

/** Create an item_price_id. The caller must ensure that all params are valid. 
\sa createItemPriceID */
function createItemPriceIDRaw( $item_price_name, $media_format_id, $record_store_name, $item_used, $item_type )
{
	$id = "{$item_price_name}^{$media_format_id}^{$record_store_name}^{$item_used}^{$item_type}";
	return mb_strtolower( $id, 'UTF-8' );
}

/** Create a checked item_price_id from discrete parameters. The parameters are checked to see if they are 
	all valid to use for creting the ID. If a valid ID could not be created an empty string ID 
	is returned. */
function createItemPriceID( $item_price_name, $media_format_id, $record_store_name, $item_used, $item_type )
{
	$media_format_id 	= (int)$media_format_id;
	$item_type			= (int)$item_type;
	
	if ( $media_format_id == 0 || $item_type == 0 || '' == $item_price_name || '' == $record_store_name ) return ''; 

	$item_used			= (int)$item_used;
	$id = "{$item_price_name}^{$media_format_id}^{$record_store_name}^{$item_used}^{$item_type}";
	return mb_strtolower( $id, 'UTF-8' );
}

/** Create a checked item_price_id from \a aPriceData data. If a valid ID could not be created an empty string ID 
	is returned. */
function createItemPriceIDFromData( $aItemPrice )
{
	return createItemPriceID( $aItemPrice['item_price_name'], $aItemPrice['media_format_id']
							, $aItemPrice['record_store_name'], $aItemPrice['item_used'], $aItemPrice['item_type'] );
}

function writeFileDbFile( $filePath, $aData  )
{
	global $g_fileDbPrettyJson;
	$s = '';
	if ($g_fileDbPrettyJson) $s = json_encode( $aData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	else $s = json_encode( $aData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	
	file_put_contents( $filePath, $s);
}

function readFileDbFile( $filePath )
{
	$a = array();
	if ( file_exists($filePath) ) {
		$s = file_get_contents( $filePath );
		$a = json_decode( $s, true );
	}
	return $a;
}

/** Update one associative array with dat from another, but only considering the field/key values in \a $aFields 
\sa updateAssocDataCheckReliability */
function updateAssocData( $aData, $aDataNew, $aFields )
{
	foreach( $aFields as $f ) {
		$v = $aDataNew[$f];
		if ( '' != $v ) {
			$aData[$f] = $v;
		}
		else if  ( stripos( $sFieldName , "timestamp_updated" ) !== false ) {
			$aData[$f] = time(); // date("Y-m-d H:i:s", time();
		}
	}
	return $aData;
}


/** Update one associative array with dat from another, but only considering the field/key values in \a $aFields 
\sa updateAssocDataCheckReliability */
function updateAssocDataCheckReliability( $aData, $aDataNew, $aFields, $reliabilityField )
{
	$reliabilityOld = (int)$aData[$reliabilityField];
	$reliabilityNew = (int)$aDataNew[$reliabilityField];
	
	$bNewDataBetter = $reliabilityNew > $reliabilityOld;

	foreach( $aFields as $f ) {
		$valNew = $aDataNew[$f];
		$valOld = $aData[$f];
		
		
		if ( is_numeric($valNew) ) {
			if ( $valNew != 0 ) {
				if ( $bNewDataBetter || $valOld == 0 ) {
					$aData[$f] = $valNew;
				}
			}
		}
		else if  ( $valNew != '' ) {
			if ( $bNewDataBetter || $valOld == "" ) {
				$aData[$f] = $valNew;
			}
		}
		else if  ( stripos( $sFieldName , "timestamp_updated" ) !== false ) {
			$aData[$f] = time(); // date("Y-m-d H:i:s", time();
		}
	}
	return $aData;
}


/** Iterate UTF8 string char-by-char. 
\see http://stackoverflow.com/questions/3666306/how-to-iterate-utf-8-string-in-php (answer 4)
*/
function nextCharUtf8($string, &$pointer){
    if(!isset($string[$pointer])) return false;
    $char = ord($string[$pointer]);
    if($char < 128){
        return $string[$pointer++];
    }else{
        if($char < 224){
            $bytes = 2;
        }elseif($char < 240){
            $bytes = 3;
        }elseif($char < 248){
            $bytes = 4;
        }elseif($char = 252){
            $bytes = 5;
        }else{
            $bytes = 6;
        }
        $str =  substr($string, $pointer, $bytes);
        $pointer += $bytes;
        return $str;
    }
}

function wrapStringAsHtmlPage($sHtmlContent, $pageTitle)
{
        $s =
<<<TEXT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{$pageTitle}</title>
    <body>
    {$sHtmlContent}
    </body>
TEXT;
	return $s;
}


// --------------------------------
// --- Currently not used stuff ---
// --------------------------------

/** Should do the same as utf8StringToArray , but not tested so much yet. 
note this function need
\todo figure out which of the two are best. */
function mb_str_split( $string ) 
{
    # Split at all position not after the start: ^
    # and not before the end: $
    mb_regex_encoding('UTF-8');
    mb_internal_encoding("UTF-8");
    return preg_split('/(?<!^)(?!$)/u', $string );
}



?>