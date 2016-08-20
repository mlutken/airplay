<?php


/**
Get current URL in various forms:

echo baseUrl();    //  will produce something like: http://stackoverflow.com/questions/2820723/
echo baseUrl(TRUE);    //  will produce something like: http://stackoverflow.com/
echo baseUrl(TRUE, TRUE); || echo base_url(NULL, TRUE);    //  will produce something like: http://stackoverflow.com/questions/

And finally
echo baseUrl(NULL, NULL, TRUE);
will produce something like: 
      array(3) {
          ["scheme"]=>
          string(4) "http"
          ["host"]=>
          string(12) "stackoverflow.com"
          ["path"]=>
          string(35) "/questions/2820723/"
      }

*/
if (!function_exists('baseUrl')) {
    function baseUrl($atRoot=FALSE, $atCore=FALSE, $parse=FALSE){
        if (isset($_SERVER['HTTP_HOST'])) {
            $http = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
            $hostname = $_SERVER['HTTP_HOST'];
            $dir =  str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

            $core = preg_split('@/@', str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(dirname(__FILE__))), NULL, PREG_SPLIT_NO_EMPTY);
            $core = $core[0];

            $tmplt = $atRoot ? ($atCore ? "%s://%s/%s/" : "%s://%s/") : ($atCore ? "%s://%s/%s/" : "%s://%s%s");
            $end = $atRoot ? ($atCore ? $core : $hostname) : ($atCore ? $core : $dir);
            $base_url = sprintf( $tmplt, $http, $hostname, $end );
        }
        else $base_url = 'http://localhost/';

        if ($parse) {
            $base_url = parse_url($base_url);
            if (isset($base_url['path'])) if ($base_url['path'] == '/') $base_url['path'] = '';
        }

        return $base_url;
    }
}

/** 
\see http://stackoverflow.com/questions/3349753/delete-directory-with-files-in-it */
function deleteDir($path)
{
    return is_file($path) ?
            @unlink($path) :
            array_map(__FUNCTION__, glob($path.'/*')) == @rmdir($path);
}


//function assocFromPOST( $)

function pretty_json($json) {
    
    $result = '';
    $pos = 0;
    $strLen = strlen($json);
    $indentStr = ' ';
    $newLine = "\n";
    $prevChar = '';
    $outOfQuotes = true;
        
    for ($i=0; $i<=$strLen; $i++) {
            
        // Grab the next character in the string.
        $char = substr($json, $i, 1);
            
        // Are we inside a quoted string?
        if ($char == '"' && $prevChar != '\\') {
            $outOfQuotes = !$outOfQuotes;
                
            // If this character is the end of an element,
            // output a new line and indent the next line.
        } else if(($char == '}' || $char == ']') && $outOfQuotes) {
            $result .= $newLine;
            $pos --;
            for ($j=0; $j<$pos; $j++) {
                $result .= $indentStr;
            }
        }
            
        // Add the character to the result string.
        $result .= $char;
            
        // If the last character was the beginning of an element,
        // output a new line and indent the next line.
        if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
            $result .= $newLine;
            if ($char == '{' || $char == '[') {
                $pos ++;
            }
                
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
            
        $prevChar = $char;
    }
        
    return $result;
}


/** Get  html code for a select 
\param $aOptions Associative array (optionValue => optionDisplayValue, ...)
\param $selectedValue The selected value. */
function htmlForSelect($aOptions, $selectedValue, $aAttributes )
{
    $s = '<select';
    foreach ( $aAttributes as $name => $value ) {
        $s .= " $name=\"$value\"";
    }
    $s .= ">\n";
    foreach ( $aOptions as $value => $displayValue ) {
        $selected = $selectedValue == $value ? 'selected=selected ' : '';
        $s .= "<option value='$value' $selected>$displayValue</option>\n";
    }
    $s .= "</select>\n";
    return $s;
}

/** Write _POST, _GET and _SESSION variables to file. 
Usefull as primitive ajax debugging. */
function dbgWritePostGetSession( $filePath = "/tmp/dbgWritePostGetSession.txt" )
{
	$sj = "--- _POST ---\n";
	$sj .= json_encode( $_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	$sj .= "--- _GET ---\n";
	$sj .= json_encode( $_GET, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	$sj .= "--- _SESSION ---\n";
	$sj .= json_encode( $_SESSION, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	file_put_contents( $filePath, $sj );
}

/** Write _POST, _GET and _SESSION variables to file and a user supplied string. 
Usefull as primitive ajax debugging. */
function dbgWritePostGetSessionString( $sString, $filePath = "/tmp/dbgWritePostGetSession.txt" )
{
    $sj = "--- _POST ---\n";
    $sj .= json_encode( $_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    $sj .= "--- _GET ---\n";
    $sj .= json_encode( $_GET, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    $sj .= "--- _SESSION ---\n";
    $sj .= json_encode( $_SESSION, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    $sj .= "--- Additional data ---\n";
    $sj .= $sString;
    file_put_contents( $filePath, $sj );
}



/** Write _POST, _GET and _SESSION variables to file and a user supplied array (data). 
Usefull as primitive ajax debugging. */
function dbgWritePostGetSessionData( $aData, $filePath = "/tmp/dbgWritePostGetSession.txt" )
{
	$sj = "--- _POST ---\n";
	$sj .= json_encode( $_POST, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	$sj .= "--- _GET ---\n";
	$sj .= json_encode( $_GET, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	$sj .= "--- _SESSION ---\n";
	$sj .= json_encode( $_SESSION, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	$sj .= "--- Additional data ---\n";
	$sj .= json_encode( $aData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	file_put_contents( $filePath, $sj );
}

?>