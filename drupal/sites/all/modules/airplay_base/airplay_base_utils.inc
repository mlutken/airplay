<?php 
// $Id$

function ap_db_get_active()
{
    //global
}

/// ap_compress_gz("test.php","test.gz");
function ap_compress_gz($srcName, $dstName)
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


/// ap_uncompress_gz("test.gz","test.php");
function ap_uncompress_gz($srcName, $dstName) {
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


function ap_xml_escape( $str )
{
    static $chars_a     = array( "&"    , "'"       , "\""      , ">"   , "<"       );
    static $escaped_a   = array( "&amp;", "&apos;"  , "&quot;"  , "&gt;", "&lt;"    );
    return str_replace ($chars_a, $escaped_a, $str );
}

function ap_list_dir( $sDir='.', $bRecursive = true, $sNameContains ='' ) 
{
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
				$aFiles = array_merge( $aFiles, ap_list_dir($sFilePath, $bRecursive, $sNameContains) );
			}
			else {
				if ( $sNameContains == '' )	{
                    array_push( $aFiles, $sFilePath );
                }
				else { 
                    if ( strpos( $sFilePath, $sNameContains ) !== false ) 	{
                        array_push( $aFiles, $sFilePath );
                    }
                }
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


/** Read a timestamp from a file. Assumes file are open and readable. 
\return The unix-style time as an integer.
\sa http://dk.php.net/manual/en/function.strtotime.php */
function readTimeStamp( $hFile ) ///< Handle to open readable file
{
	rewind($hFile);						// Read from beginning
	$sFormattedRead = fread( $hFile, 30 );
	$iTimeStampRead = strtotime($sFormattedRead);
	return $iTimeStampRead;
}

/** Write a timestamp from a file. Assumes file are open and readable. 
The timestamp is written in ISO 8601 format.
\return The timestamp actually written ( the a unix-style time as an integer).
\sa http://dk.php.net/manual/en/function.date.php
\sa http://dk.php.net/manual/en/function.time.php */
function writeTimeStamp( $hFile ) ///< Handle to open writable file
{
	rewind($hFile);								// Write from beginning
	$iTimeStampWrite = time();					// Current time measured in the number of seconds since the Unix Epoch (January 1 1970 00:00:00 GMT). 
	$sDateWrite = date("c", $iTimeStampWrite );	// "c" formats as ISO 8601 date, see http://dk.php.net/manual/en/function.date.php
	fwrite( $hFile, $sDateWrite );
	return $iTimeStampWrite;
}


// -----------------------------------------
// --- HTML formatting utility functions ---
// -----------------------------------------

function getParametersStartChar($url) 
{
    if ( strpos($url, "?") !== false )  return "&";
    else                                return "?";
}

function ap_html_currencySelect( $url, $valueSelected )
{
    $ch = getParametersStartChar($url);
    $currency = ap_user_locale_currency();
	$s ="<select class='tableSelect' onchange='window.location=this.value;'>";
	$aCurrencies = array(
		'DKK'=> 'DKK', 
		'EUR'=> 'EUR', 
		'GBP'=> 'GBP', 
		'NOK'=> 'NOK',
		'SEK'=> 'SEK', 
		'USD'=> 'USD'  
	);
	foreach ( $aCurrencies as $name => $text ) {
		$selected = "";
		if ( $name == $valueSelected )	$selected = "selected='selected'";
		$value = "{$url}{$ch}currency={$name}";
		$s .= "<option value='{$value}' {$selected} >{$text}</option>\n";
	}
	$s .= "</select>";
	return $s;
}

function ap_html_mediaFormatSelect( $url, $valueSelected )
{
    $ch = getParametersStartChar($url);
	$s ="<select class='tableSelect' onchange='window.location=this.value;'>";
	$aFormats = array(	
		'ALL' 		=> t('All'),
		'CD' 		=> t('CD'),
		'MP3' 		=> t('MP3'),
 		'WMA' 		=> t('WMA'),
		'Vinyl'		=> t('Vinyl'),
		'DVD'		=> t('DVD'),
// 		'ACC' 		=> t('ACC'),
// 		'Stream' 	=> t('Stream'),
// 		'Mobile'	=> t('Mobile'),
// 		'Blu-ray'	=> t('Blu-ray'),
		'SACD'		=> t('SACD'),
//		'SACDH'		=> t('SACDH'),
		'DVDA'		=> t('DVDA'),
		'Single'	=> t('Single')
		);
		
	foreach ( $aFormats as $name => $text ) {
		$selected = "";
		if ( $name == $valueSelected )	$selected = "selected='selected'";
		$value = "{$url}{$ch}media_format={$name}";
		$s .= "<option value='{$value}' {$selected} >{$text}</option>\n";
	}
	$s .= "</select>";
	return $s;
}


function ap_html_titleNameSortSelect( $url, $valueSelected )
{
    $ch = getParametersStartChar($url);
	$s ="<select class='tableSelect' onchange='window.location=this.value;'>";
	$aSortDirs = array(
		'name_ASC'	=> t('Ascending'), 
		'name_DESC'	=> t('Descending') 
	);
	$s .= "<option value='{$url}'>- " . t('sort by') . " -</option>\n";
	foreach ( $aSortDirs as $name => $text ) {
		$selected = "";
		if ( $name == $valueSelected )	$selected = "selected='selected'";
		$value = "{$url}{$ch}title_name_year_sort={$name}";
		$s .= "<option value='{$value}' {$selected} >{$text}</option>\n";
	}
	$s .= "</select>";
	return $s;
}


function ap_html_titleYearSortSelect( $url, $valueSelected )
{
    $ch = getParametersStartChar($url);
	$s ="<select class='tableSelect' onchange='window.location=this.value;'>";
	$aSortDirs = array(
		'year_ASC'	=> t('Ascending'), 
		'year_DESC'	=> t('Descending') 
	);
	$s .= "<option value='{$url}'>- " . t('sort by') . " -</option>\n";
	foreach ( $aSortDirs as $year => $text ) {
		$selected = "";
		if ( $year == $valueSelected )	$selected = "selected='selected'";
		$value = "{$url}{$ch}title_name_year_sort={$year}";
		$s .= "<option value='{$value}' {$selected} >{$text}</option>\n";
	}
	$s .= "</select>";
	return $s;
}


function ap_simplify_item_name( $sItemName, $artist_name )
{
//    return $sItemName;

    // If artist name is part of item_name then remove it
    $sItemNamePreprocessed = $sItemName; 
    $sItemNamePreprocessed = str_replace( $artist_name, "", $sItemNamePreprocessed ); 

    // Remove possible leading tracknumber like ' 03. - Name of track' 
    // ' 03. Name of track', ' 03 - Name of track', but NOT ' 03 Name of track'
    // since the last form could be actual item name which begins with number.
    $regex = array("/^\s*(-\s*)?/", "/^\s*\d+\.?(\s*-\s*)?/");
    $replace = array( "","" );
/*    $regex = array("/^\s*(-\s*)?/", "/^\s*\d+\.(\s*-\s*)?/", "/^\s*\d+\s(\s*-\s*)/");
    $replace = array( "","", "" );*/
    $sItemNamePreprocessed = preg_replace( $regex, $replace, $sItemNamePreprocessed ); 
    
    if ( strlen($sItemNamePreprocessed) <= 3 ) return $sItemName;
    
	$s = "";
	$aItemName = str_split($sItemNamePreprocessed);
	$aStateStack = array(0);		// 0: normal, 1: Skip chars
	foreach( $aItemName as $c ) {
		
		$chAdd = '';
		switch ( $c ) {
/*			case '-' :
				$aStateStack[] = 1;
				break;*/
			case '(' :
				$aStateStack[] = 1;
				break;
			case ')' :
				array_pop($aStateStack); 
				break;
			case '[' :
				$aStateStack[] = 1;
				break;
			case ']' :
				array_pop($aStateStack); 
				break;
			case '+' :
			case ':' :
            case ',' :
            case '/' :
				$chAdd = ' '; 
				break;
            case '"' :
            case '.' :
			case "'" :
			case '!' :
            case '^' :
				$chAdd = ''; 
				break;
/*			case '/' :
				$chAdd = ' / '; 
				break;*/
			default:
				$chAdd = $c;
		}
		$iStackLen = count($aStateStack);
		if ( $iStackLen >= 1 ) {
			if ( $aStateStack[$iStackLen -1] == 0 ) {
				$s .= $chAdd;
			}
		}
	}	
 	$s = trim($s);

	$s = preg_replace( '/\s+/', ' ', trim( $s ) ); // Simplify spaces

	return $s;
}

function ap_simplify_album_name( $sAlbumName, $artist_name )
{
    return ap_simplify_item_name( $sAlbumName, $artist_name );
}

function ap_simplify_song_name( $sSongName, $artist_name )
{
    return ap_simplify_item_name( $sSongName, $artist_name );
}


/*
	* soundex() - Calculate the soundex key of a string
	* similar_text() - Calculate the similarity between two strings
	* metaphone() - Calculate the metaphone key of a string
*/

function ap_encode_album_name( $sAlbumName, $artist_name )
{
	$s = $sAlbumName;
	$s = metaphone( ap_simplify_album_name($s), $artist_name );
	return $s;
}


// --------------------
// --- String utils ---
// --------------------

/** Determine if string ends with certain substring.
\return True if sString ends with sTest, false otherwise. */
function ap_ends_with( $sString, $sTest )               
{   
    // Look at the end of FullStr for the substring the size of EndStr
    $sFullStrEnd = substr($sString, strlen($sString) - strlen($sTest));
    return strcmp ( $sFullStrEnd, $sTest) == 0;
}   


// ---------------------
// --- AddThis utils ---
// ---------------------

function ap_getAddThis()
{
    $s .= '<div style="float:left" class="addthis_toolbox addthis_default_style ">';
    $s .= '<a class="addthis_button_facebook"></a>';
    $s .= '<a class="addthis_button_google"></a>';
    $s .= '<a class="addthis_button_email"></a>';
    $s .= '<a class="addthis_button_google_plusone"></a>';
    $s .= '<a class="addthis_button_compact"></a>';
//    $s .= '<a class="addthis_counter addthis_bubble_style"></a>';
    $s .= '</div>';

//     $s .= '<div style="float:left">&nbsp;</div>';
//     $s .= '<div style="float:left" class="addthis_toolbox addthis_default_style" >';
//     $s .= '<a class="addthis_button_google_plusone"></a>';
//     $s .= '</div>';

    $s .= '<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4e134212425abf97"></script>';
    
    return $s;
}

// function ap_getAddThis()
// {
//     return "<a href='http://www.airplaymusic.dk/' class='addthis_button'></a><script type='text/javascript' src='http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4e134212425abf97'></script>";
// }
// 

// ------------------------
// --- Sorting functors ---
// ------------------------

function year_DESC ( $item1,$item2 )
{
    if ( (int)$item1['year'] == (int)$item2['year']) return 0;
    return ( (int)$item1['year'] < (int)$item2['year']) ? 1 : -1;
}

function year_ASC ( $item1,$item2 )
{
    if ( (int)$item1['year'] == (int)$item2['year']) return 0;
    return ( (int)$item1['year'] > (int)$item2['year']) ? 1 : -1;
}


function name_DESC ( $item1,$item2 )
{
    if ($item1['name'] == $item2['name']) return 0;
    return ($item1['name'] < $item2['name']) ? 1 : -1;
}

function name_ASC ( $item1,$item2 )
{
    if ($item1['name'] == $item2['name']) return 0;
    return ($item1['name'] > $item2['name']) ? 1 : -1;
}


function album_simple_name_DESC ( $item1,$item2 )
{
    if ($item1['album_simple_name'] == $item2['album_simple_name']) return 0;
    return ($item1['album_simple_name'] < $item2['album_simple_name']) ? 1 : -1;
}

function album_simple_name_ASC ( $item1,$item2 )
{
    if ($item1['album_simple_name'] == $item2['album_simple_name']) return 0;
    return ($item1['album_simple_name'] > $item2['album_simple_name']) ? 1 : -1;
}
