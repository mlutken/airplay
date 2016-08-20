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

function ap_html_currencySelectTabs( $url, $valueSelected, $product )
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
        if ($product != "") $value .= "&amp;product=$product";
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

function ap_html_mediaFormatLinks( $url, $valueSelected, $aAllRows, $product = NULL )
{
    $ch = getParametersStartChar($url);
    $s = "";
    $count = 0;
    $total_count = 0;
	$aFormats = array(	
		'ALL' 		=> t('All'),
		'CD' 		=> t('CD'),
		'MP3' 		=> t('MP3'),
// 		'WMA' 		=> t('WMA'),
		'Vinyl'		=> t('Vinyl'),
		'DVD'		=> t('DVD'),
// 		'ACC' 		=> t('ACC'),
 		'Stream' 	=> t('Stream'),
// 		'Mobile'	=> t('Mobile'),
 		'Blu-ray'	=> t('Blu-ray'),
		'SACD'		=> t('SACD'),
//		'SACDH'		=> t('SACDH'),
//		'DVDA'		=> t('DVDA'),
//		'Single'	=> t('Single'),
		'T-Shirts'	=> t('T-Shirts'),
        'Hoodies'	=> t('Hoodies'),
        'Jackets'	=> t('Jackets'),
        'Caps'	    => t('Caps'),
        'Bags'	    => t('Bags'),
        'Bedding'	=> t('Bedding'),
        'Posters'	=> t('Posters'),
        'Mugs'	    => t('Mugs'),
        'Accessories' => t('Accessories'),
        'Other'	    => t('Other')
		);

	foreach ( $aFormats as $name => $text ) {
        $count = ap_get_number_of_media_formats_in_array ( $aAllRows, "media_format_name", $text );
        
        $total_count += $count;
        $class = "";

        if ( $name == $valueSelected ) { $class = "class='selected'"; }
        $value = "{$url}{$ch}media_format={$name}";

        if ($product != "") $value .= "&amp;product=$product";

        if ($text == "Alle" || $text == "All") {
            $s .= "<div class='media_format_all'><a href=\"{$value}\" $class rel=\"nofollow\">{$text}</a> (REPLACE_TOTAL_COUNT)</div>";
        }
        
        if ($count > 0) {
            $s .= "<div class='media_format'><a href=\"{$value}\" $class rel=\"nofollow\">{$text}</a> (" . $count . ")</div>";
        }
	}
    if ($product == "album" || $product == "song") {
        $s .= "<div class='media_format' id='tabs_media_format_streaming' style='display:none;'>Streaming</div>";
    }
    $s = str_replace("REPLACE_TOTAL_COUNT", $total_count, $s);
    
	return $s;
}

function ap_html_mediaFormatLinksAsDropDown( $url, $valueSelected, $aAllRows, $product = NULL, $element )
{
    $count = 0;
    $total_count = 0;
	$link_text = "";
    $formats_outputted = false; // Is formats outputted before "Choose format" - this way we need link "Choose format" or "Show other formats"
    
    // Formats to show before "dropdown".
    $aFormatsToShowAsLink = array(	
		'Vinyl'		=> t('Vinyl'),
		'MP3' 		=> t('MP3'),
		'CD' 			=> t('CD'),
		'T-Shirts' 	=> t('T-Shirts'),
		'Posters' 	=> t('Posters'),
		'Concert' 	=> t('Concert')
		);

	$aFormats = array(	
		'ALL' 		=> t('All'),
		'CD' 		=> t('CD'),
		'MP3' 		=> t('MP3'),
		'FLAC' 		=> t('FLAC'),
// 		'WMA' 		=> t('WMA'),
		'Vinyl'		=> t('Vinyl'),
		'DVD'		=> t('DVD'),
// 		'ACC' 		=> t('ACC'),
 		'Stream' 	=> t('Stream'),
// 		'Mobile'	=> t('Mobile'),
 		'Blu-ray'	=> t('Blu-ray'),
		'SACD'		=> t('SACD'),
//		'SACDH'		=> t('SACDH'),
//		'DVDA'		=> t('DVDA'),
//		'Single'	=> t('Single'),
		'T-Shirts'	=> t('T-Shirts'),
        'Hoodies'	=> t('Hoodies'),
        'Jackets'	=> t('Jackets'),
        'Caps'	    => t('Caps'),
        'Bags'	    => t('Bags'),
        'Bedding'	=> t('Bedding'),
        'Posters'	=> t('Posters'),
        'Mugs'	    => t('Mugs'),
        'Accessories' => t('Accessories'),
        'Other'	    => t('Other'),
		'Concert' 	=> t('Concert'),
		'Festival' 	=> t('Festival')
		);

    $ch = getParametersStartChar($url);
    $s = "<div class='media_format_dropdown'>";
	$s .= "<div class='header'>" . t("Buy") . "</div>";
    // Show links before the dropdown.
    foreach ( $aFormatsToShowAsLink as $name => $text ) {
        if ( $name == "Vinyl" || $name == "CD" || $name == "MP3" || $name == "T-Shirts" || $name == "Posters") {
            $count = ap_get_number_of_media_formats_in_array ( $aAllRows, "media_format_name", $text );
            if ($count > 0) {
                if ( $name == $valueSelected ) { $class = "class='selected'"; } else { $class = ""; }
                $value = "{$url}{$ch}media_format={$name}";
                if ($product != "") $value .= "&amp;product=$product";
                $s .= "<div style='float:left;line-height:15px;'><a href='{$value}' $class rel=\"nofollow\">{$name}</a>&nbsp;&nbsp;-&nbsp;&nbsp;</div>";
                $formats_outputted = true;
            }
        }
    }
	// Get correct link text
	if ($product == "merchandise") {
		if ($formats_outputted == true) {
			$link_text = t("More types");
		} else {
			$link_text = t("Choose types");
		}
	} else {
		if ($formats_outputted == true) {
			$link_text = t("More formats");
		} else {
			$link_text = t("Choose Format");
		}
	}
    // Output text - if link before or not.
    $s .= "<div class='link' style='float:left;'><a href='javascript:void(0);' id='media_format_header_link'>{$link_text}</a></div>";
    

    $s .= "<div id='media_formats'>";
    
	foreach ( $aFormats as $name => $text ) {
        $count = ap_get_number_of_media_formats_in_array ( $aAllRows, "media_format_name", $text );
        
        $total_count += $count;
        $class = "";

        if ( $name == $valueSelected ) { $class = "class='selected'"; }
        $value = "{$url}{$ch}media_format={$name}";

        if ($product != "") $value .= "&amp;product=$product";

        if ($text == "Alle" || $text == "All") {
            $s .= "<div class='media_format_all'><a href=\"{$value}\" $class rel=\"nofollow\">{$text}</a> <span class='count'>(REPLACE_TOTAL_COUNT)</span></div>";
        }
        
        $style = "style='display:block;'";
        if ($count == 0) { $style = "style='display:none;'"; }
        if ($text == "Alle" || $text == "All") { } else {
            $s .= "<div class='media_format' id='media_format_" . str_replace(" ", "_", strtolower($text)) . "' $style><a href=\"{$value}\" $class rel=\"nofollow\">{$text}</a> <span class='count'>(" . $count . ")</span></div>";
        }

	}
    $s .= "</div>";
    $s .= "</div>";
    $s .=
<<<SCRIPT
<script type="text/javascript">
    jQuery("$element #media_formats").hide();
    jQuery('$element #media_format_header_link,#media_formats').click(function(event) { jQuery('$element #media_formats').show(); event.stopPropagation(); });
    jQuery("html").click(function() { jQuery("$element #media_formats").hide(); });
</script>
SCRIPT;
    $s = str_replace("REPLACE_TOTAL_COUNT", $total_count, $s);
    
	return $s;
}

function ap_html_streamingcountAsDropDown($element, $languageCode) {
    $s = "<div class='streaming_dropdown'>";
    $s .= "<div id='streamings' style='display:block;'>";
    $s .= "<div class='notification' style='text-align:center;font-weight:bold;margin:0px 0px 5px 0px;'>";
    $s .= "<div class='searching'>" . t("Searching") . " ...</div><div class='finished' style='display:none;'>";
    if ($element == "#artist_wiki div.albums" || $element == "#album_wiki div.albums") {
        $s .= t("Album-titles for Streaming services");
    } else if ($element == "#artist_wiki div.songs" || $element == "#song_wiki div.songs") {
        $s .= t("Song-titles for Streaming services");
    }
    $s .= "</div></div>";
    $s .= "<div class='icons'>";
	$s .= "<div class='streaming_spotify'><div class='animation'><div class='counter'><span class='count' style='display:none;'>0</span></div><div class='image'></div><div class='logo' title='Spotify'></div></div></div>";
    if ($languageCode == "da") {
		$s .= "<div class='streaming_wimp'><div class='animation'><div class='counter'><span class='count' style='display:none;'>0</span></div><div class='image'></div><div class='logo' title='WiMP'></div></div></div>";
    }
	$s .= "<div class='streaming_deezer'><div class='animation'><div class='counter'><span class='count' style='display:none;'>0</span></div><div class='image'></div><div class='logo' title='Deezer'></div></div></div>";
    $s .= "<div class='streaming_rdio'><div class='animation'><div class='counter'><span class='count' style='display:none;'>0</span></div><div class='image'></div><div class='logo' title='Rdio'></div></div></div>";
    $s .= "<div class='streaming_napster'><div class='animation'><div class='counter'><span class='count' style='display:none;'>0</span></div><div class='image'></div><div class='logo' title='Napster'></div></div></div>";
    $s .= "</div>";
    $s .= "</div>";
    $s .= "</div>";
    $s .=
<<<SCRIPT
<script type="text/javascript">
    /*jQuery('$element #streaming_header_link,#streamings').click(function(event) { jQuery('$element #streamings').show(); event.stopPropagation(); });
    jQuery("html").click(function() { jQuery("$element #streamings").hide(); });*/
</script>
SCRIPT;

    return $s;
}

function ap_get_small_streaming_icons($product, $youtube_url = null) {
    $s = "<div class='icons'>";
	$s .= "<div style='width:80%;float:left;text-align:left;'>";
    $s .= "<span class='spotify'><a href='javascript:void(0);' target='_blank'><div class='icon'>&nbsp;</div></a></span>";
    $s .= "<span class='wimp'><a href='javascript:void(0);' target='_blank'><div class='icon'>&nbsp;</div></a></span>";
    $s .= "<span class='deezer'><a href='javascript:void(0);' target='_blank'><div class='icon'>&nbsp;</div></a></span>";
    $s .= "<span class='rdio'><a href='javascript:void(0);' target='_blank'><div class='icon'>&nbsp;</div></a></span>";
    $s .= "<span class='napster'><a href='javascript:void(0);' target='_blank'><div class='icon'>&nbsp;</div></a></span>";
	$s .= "<span class='spacer'>&nbsp;</span>";
	$s .= "</div>";
	
	if ($product == "song") {
		$s .= "<div style='width:20%;float:left;text-align:right;'>";
		//$s .= "<span class='youtube'><a href='http://www.youtube.com/results?search_query=$youtube_url' target='_blank'><img src='/images/icons/youtube_icon.png' width='43' height='18' title='YouTube' alt='YouTube' border='0' /></a></span>";
		$s .= "<span class='youtube'><a href='http://www.youtube.com/results?search_query=$youtube_url' target='_blank'><div class='icon'>&nbsp;</div></a></span>";
		$s .= "</div>";
	}
	
    $s .= "</div>";
    return $s;
}

function ap_get_number_of_media_formats_in_array ( $aAllRows , $key, $value, $product = NULL )
{
    $count = 0;
    foreach ($aAllRows as $a ) {
        if (strtolower($a[$key]) == strtolower($value)) {
            if ( array_key_exists ('album_prices_count', $a) )  { $count += $a["album_prices_count"]; }
            else if ( array_key_exists ('song_prices_count', $a) )  { $count += $a["song_prices_count"]; }
            else if ( array_key_exists ('merchandise_prices_count', $a) )  { $count += $a["merchandise_prices_count"]; }
			else if ( array_key_exists ('concert_prices_count', $a) )  { $count += $a["concert_prices_count"]; }
            else { $count++; }
        }
    }
    return $count;
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

/*
Function used to format date.
*/
function ap_format_date($date, $language_code) {
    if ($language_code == "da") {
        return substr($date, -2) . "." . substr($date, 5, 2) . "." . substr($date, 0, 4);
    } else {
        return $date;
    }
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


// ---------------------------------
// --- AP limit results in array ---
// ---------------------------------
function ap_limit_results_by_media_format($aAllRows, $media_format) {
    $aRows = array();
    
    foreach ($aAllRows as $a ) {
        if ((strtolower($a["media_format_name"]) == strtolower($media_format) ) || ($media_format == "ALL")) {
            $aRows[] = $a;
        }
    }
    return $aRows;
}
function ap_limit_results_by_media_format_artist($aAllRows, $media_format) {

    return $aAllRows;
}


// ---------------------
// --- AddThis utils ---
// ---------------------

function ap_getFollowIcons()
{
    $follow_ap_on = t("Follow Airplay Music on ");
    $s  = '';
    $s .= '<div id="social_media">';
	$s .= '<div class="label">' . $follow_ap_on . ':</div>';
	$s .= '<div class="icons">';
	$s .= '<div id="facebook"><a href="https://www.facebook.com/AirplaymusicDK" target="_blank" title="' . $follow_ap_on . ' Facebook"><span>&nbsp;</span></a></div>';
    $s .= '<div id="google_plus"><a href="https://plus.google.com/u/0/102349067624794918412" target="_blank" title="' . $follow_ap_on . ' Google+"><span>&nbsp;</span></a></div>';
    $s .= '<div id="linkedin"><a href="http://www.linkedin.com/company/airplay-music-aps" target="_blank" title="' . $follow_ap_on . ' LinkedIn"><span>&nbsp;</span></a></div>';
    $s .= '<div id="twitter"><a href="https://twitter.com/Airplay_Music" target="_blank" title="' . $follow_ap_on . ' Twitter"><span>&nbsp;</span></a></div>';
	$s .= '</div>';
	$s .= '</div>';
    return $s;
}

// ---------------------
// --- AddThis utils ---
// ---------------------
//follow ap on facebook
function ap_getAddThis()
{
    $s = '<div style="float:right;" class="addthis_default_style"><div id="addthis_icons">';
	$s .= '<a class="addthis_button_email" title="Email"><span class="icon"></span></a>';
    $s .= '<a class="addthis_button_facebook_like" fb:like:layout="button_count" fb:like:width="100" fb:like:height="20"></a>';
    $s .= '<a class="addthis_button_google_plusone" g:plusone:annotation="none" g:plusone:size="medium" g:plusone:href="https://plus.google.com/102349067624794918412/"></a>';
    $s .= '<a class="addthis_button_tweet" tw:via="Airplay_Music" tw:count="none"></a>';
    $s .= '</div></div>';
	// Moved to html.tpl template file.
    //$s .= '<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4e1345660edb6a27&async=1&domready=1"></script>';
	//$s .= '<script type="text/javascript">function initAddThis() { addthis.init() } initAddThis();</script>';
    
    return $s;
}


function ap_getBannerHTML() {
    $html = "<div id=\"banner_right_120x600\">";
    $html .= "<script type=\"text/javascript\">\n<!--\n";
    $html .= "google_ad_client = \"ca-pub-5272961488639461\";\n";
    $html .= "/* Airplay Music */\n";
    $html .= "google_ad_slot = \"3384912348\";\n";
    $html .= "google_ad_width = 120;\n";
    $html .= "google_ad_height = 600;\n";
    $html .= "//-->\n";
    $html .= "</script>\n";
    $html .= "<script type=\"text/javascript\" src=\"http://pagead2.googlesyndication.com/pagead/show_ads.js\">\n";
    $html .= "</script>\n";
    $html .= "</div>";
    return $html;
}

// ----------------------------------------------
// --- Return substring with ending html tags ---
// ----------------------------------------------

function getSubstringWithEndingHTMLTags($html, $length)
{
    $truncatedText = substr($html, $length);
    $pos = strpos($truncatedText, ">");
    if($pos !== false)
    {
        $html = substr($html, 0,$length + $pos + 1);
    }
    else
    {
        $html = substr($html, 0,$length);
    }

    preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
    $openedtags = $result[1];

    preg_match_all('#</([a-z]+)>#iU', $html, $result);
    $closedtags = $result[1];

    $len_opened = count($openedtags);

    if (count($closedtags) == $len_opened)
    {
        return $html;
    }

    $openedtags = array_reverse($openedtags);
    for ($i=0; $i < $len_opened; $i++)
    {
        if (!in_array($openedtags[$i], $closedtags))
        {
            $html .= '</'.$openedtags[$i].'>';
        }
        else
        {
            unset($closedtags[array_search($openedtags[$i], $closedtags)]);
        }
    }
    return $html;
}

/*
    Make an array with names of valid record_stores to use in the dynamic music lookup. This way we can make sure that WiMP does not start on ap.co.uk.
    Returns an array.
*/
function getDynamicMusicLookupValidRecordstores($languageCode, $lookup_type) {
    $oRecord_stores_streaming = array();
    $oRecord_stores_other = array();
    
    if ($lookup_type == "streaming") {
        $oRecord_stores_streaming = array("spotify", "rdio", "deezer", "napster");
        if ($languageCode == "da") {
            $oRecord_stores_streaming[] = "wimp";
        }
    } else if ($lookup_type == "MP3") {
        $oRecord_stores_other = array("itunes", "7digital");
    }
    $oRecord_stores = array_merge($oRecord_stores_streaming, $oRecord_stores_other);
    return $oRecord_stores;
}

/*
   Get the Javascript HTML for using on "artist" page.
*/
function getDynamicMusicLookupArtistAlbums($artist_name, $languageCode, $artist_id, $lookup_type) {
    $sHTML = "";
    $artist_name = str_replace("'", "\'", $artist_name);
    $oRecord_stores = getDynamicMusicLookupValidRecordstores($languageCode, $lookup_type);
    if ($languageCode == "da") {
        $language_code = "DK";
    } else {
        $language_code = "UK";
    }
    foreach ($oRecord_stores AS $record_stores) {
        $sHTML .= "
        var oAlbumJSON" . $record_stores ." = '';
        var oSongJSON" . $record_stores ." = '';
        jQuery('#product_albums table.list-price-table').ready(function() {
            jQuery.ajax({type: 'POST', url: '/dynamic_music_lookup.php', data: { s: '". $record_stores ."', aid: $artist_id, q: '', qa: '$artist_name', t: 'artist_album', c: '$language_code', timestamp: '" . microtime(true) . "'}, dataType: 'json', success: parseJSONItemsForArtistPageAlbums });
        });";
    }
    return $sHTML;
}

function getDynamicMusicLookupArtistSongs($artist_name, $languageCode, $artist_id, $lookup_type) {
    $sHTML = "";
    $artist_name = str_replace("'", "\'", $artist_name);
    $oRecord_stores = getDynamicMusicLookupValidRecordstores($languageCode, $lookup_type);
    if ($languageCode == "da") {
        $language_code = "DK";
    } else {
        $language_code = "UK";
    }
    foreach ($oRecord_stores AS $record_stores) {
        $sHTML .= "
        if (oSongJSON" . $record_stores ." == '') {
            jQuery('#product_songs table.list-price-table').ready(function() {
                jQuery.ajax({type: 'POST', url: '/dynamic_music_lookup.php', data: { s: '". $record_stores ."', aid: $artist_id, q: '', qa: '$artist_name', t: 'artist_song', c: '$language_code', timestamp: '" . microtime(true) . "'}, dataType: 'json', success: parseJSONItemsForArtistPageSongs });
            });
        }";
    }
    return $sHTML;
}

/*
   Get the Javascript HTML for using on "album" page.
*/
function getDynamicMusicLookupAlbum($artist_name, $album_name, $languageCode, $artist_id, $item_base_id, $lookup_type) {

    $artist_name = str_replace("'", "\'", $artist_name);
    $album_name = str_replace("'", "\'", $album_name);
    
    if ($languageCode == "da") {
        $language_code = "DK";
    } else {
        $language_code = "UK";
    }
    
    $oRecord_stores = getDynamicMusicLookupValidRecordstores($languageCode, $lookup_type);

    $sHTML = "<script type='text/javascript'>";
    foreach ($oRecord_stores AS $record_stores) {
        $sHTML .= "
            jQuery('list-price-table').ready(function() {
                    jQuery.ajax({type: 'POST', url: '/dynamic_music_lookup.php', data: { s: '". $record_stores ."', aid: $artist_id, iid: $item_base_id, q: '$album_name', qa: '$artist_name', t: 'album', c: '$language_code'}, dataType: 'json', success: parseJSONItemsForAlbumPage });
            });";
    }
    $sHTML .= "</script>";
    return $sHTML;
}

/*
   Get the Javascript HTML for using on "song" page.
*/
function getDynamicMusicLookupSong($artist_name, $song_name, $languageCode, $artist_id, $item_base_id, $lookup_type) {

    $artist_name = str_replace("'", "\'", $artist_name);
    $song_name = str_replace("'", "\'", $song_name);
    
    if ($languageCode == "da") {
        $language_code = "DK";
    } else {
        $language_code = "UK";
    }

    $oRecord_stores = getDynamicMusicLookupValidRecordstores($languageCode, $lookup_type);
    
    $sHTML = "<script type='text/javascript'>";
    foreach ($oRecord_stores AS $record_stores) {
        $sHTML .= "
            jQuery('list-price-table').ready(function() {
                    jQuery.ajax({type: 'POST', url: '/dynamic_music_lookup.php', data: { s: '". $record_stores ."', aid: $artist_id, iid: $item_base_id, q: '$song_name', qa: '$artist_name', t: 'song', c: '$language_code'}, dataType: 'json', success: parseJSONItemsForSongPage });
            });";
    }
    $sHTML .= "</script>";
    return $sHTML;
}

function getCurrencyFromToJSArrays () {
    $sHTML = "";
    
    $currencies = getCurrencyCodesFromToValues();
    
    $sHTML .= "<script type='text/javascript'>";
    $sHTML .= "var oCurrencyToEuro = new Array();var oCurrencyFromEuro = new Array();";

    foreach ($currencies AS $currency) {
        $sHTML .= "oCurrencyToEuro['" . $currency["currency_name"] . "'] = " . $currency["to_euro"] . "; oCurrencyFromEuro['" . $currency["currency_name"] . "'] = " . $currency["from_euro"] . ";";
    }
    
    $sHTML .= "</script>";
    return $sHTML;
}

/*
	Function used to get HTML structure to show Review in wiki section on album page.
*/
function lookupReview_wiki_html( $aData )
{
	$sHTML = "";
    foreach ( $aData AS $a ) {
        $review_rating   		= $a['review_rating'];
        $review_url      			= $a['review_url'];
		$link_text 					= $a["link_text"];
		$review_text_chars	= $a["review_text_chars"];
        if ($review_rating != "" && $review_url != "") {
            $sHTML .= "<div class='review'>";
            $sHTML .= "<div class='icons'>";
            $sHTML .= get_review_rating_to_icons_html($review_rating, $a['rating_max_value'], $a['image_full_path'], $a['image_blank_path'], $a['image_half_path'], $a["has_half_value"], $a["layout_type"]);
			if ($review_text_chars != 0) {
				$sHTML .= "<span class='name'>{$link_text}</span>";
			} else {
				$sHTML .= "<span class='name'><a href='{$review_url}' target='_blank' title='{$link_text}'>{$link_text}</a></span>";
			}
            $sHTML .= "</div>";
			if ($review_text_chars != 0) {
				$sHTML .= "<div class='text'>" . substr($a["review_text"], 0, $a["review_text_chars"]) . "...<br/><a href='{$review_url}' target='_blank' title='{$link_text}'>" . t("read more at") . " {$link_text}</a></div>";
			}
            $sHTML .= "</div>";
        }
    }
    return $sHTML;
}

/*
	Function used to get HTML for showing icons or value for reviews
	layout_type : 1 = icons, 2 = value no digits, 3 = value with digits.
*/
function get_review_rating_to_icons_html($rating, $max_rating, $image_full_path, $image_blank_path, $image_half_path, $has_half_value, $layout_type) {
    $html = "";
	// Rating with images.
	if ($layout_type == 1) {
		for ($rating_count = 1; $rating_count <= $max_rating; $rating_count++) {
			if ($rating_count < floor($rating)) {
				$html .= "<div class='rating_icons'><img src='{$image_full_path}' width='16' height='16' border='0' alt=''  title=''/></div>";
			} else if ($rating_count == floor($rating)) {
				if (substr($rating, -2) == ".5" && $has_half_value == 1) {
					$html .= "<div class='rating_icons'><img src='{$image_full_path}' width='16' height='16' border='0' alt=''  title=''/></div>";
					$html .= "<div class='rating_icons'><img src='{$image_half_path}' width='16' height='16' border='0' alt=''  title=''/></div>";
				} else {
					$html .= "<div class='rating_icons'><img src='{$image_full_path}' width='16' height='16' border='0' alt=''  title=''/></div>";
				}
			} else if ($rating_count > ceil($rating) ) {
				$html .= "<div class='rating_icons'><img src='{$image_blank_path}' width='16' height='16' border='0' alt=''  title=''/></div>";
			}
		}
	// Rating with value - no digits
	} else if ($layout_type == 2) {
		$html .= "<div class='rating_icons'>" . floor($rating) . " / {$max_rating}</div>";
	// Rating with value - with digits
	} else if ($layout_type == 3) {
		$html .= "<div class='rating_icons'>{$rating} / {$max_rating}.0</div>";
	}
    return $html;
}


// ------------------------
// --- Function to return 404 status codes - ie Googlebot ---
// ------------------------
// Prepare results more then 1 price for artists , more then 1 price for albums, more then 0 prices for songs.
// This make 404 status for ALL songs, albums, artist with no prices.
function get404StatusCodeForLowNumberOfPrices($artist_id, $item_base_id, $page) {

    $output_404_status = false;
    $sQuery = "";
    $aArgs = array();

    if ($page == "artist") {
        $sQuery = "SELECT COUNT(*) AS item_price_count FROM item_price WHERE artist_id = :artist_id";
        $aArgs[':artist_id'] = $artist_id;
    } else if ($page == "album") {
        $sQuery = "SELECT COUNT(*) AS item_price_count FROM item_price WHERE item_type = 1 AND item_base_id = :item_base_id AND artist_id = :artist_id";
        $aArgs[':artist_id'] = $artist_id;
        $aArgs[':item_base_id'] = $item_base_id;
    } else if ($page == "song") {
        $sQuery = "SELECT COUNT(*) AS item_price_count FROM item_price WHERE item_type = 2 AND item_base_id = :item_base_id AND artist_id = :artist_id";
        $aArgs[':artist_id'] = $artist_id;
        $aArgs[':item_base_id'] = $item_base_id;
    }
    
    if ($sQuery != "") {
        db_set_active('airplay_music_v1');
        $query_result = db_query($sQuery, $aArgs);
        db_set_active('default');

        while ( $a = $query_result->fetchAssoc() ) {
            // 404 if less then 2 prices for an artist.
            if ($page == "artist") {
                if ($a["item_price_count"] < 2) {
                    $output_404_status = true;
                }
            // 404 if less then 2 prices for an album.
            } else if ($page == "album") {
                if ($a["item_price_count"] < 2) {
                    $output_404_status = true;
                }
            // 404 if less then 1 prices for an song.
            } else if ($page == "song") {
                if ($a["item_price_count"] < 1) {
                    $output_404_status = true;
                }
            }
        }
    }
    
    // Output 404 status if Query returns no results
    if ($output_404_status == true) {
        header('HTTP/1.0 404 Not Found');
    }
}

// ---------------------------------
// --- Header "menu" icons   ---
// ---------------------------------
function ap_get_top_menu_icons() {

	$da_url = "";
	$uk_url = "";
	$sHtml = "";
	$language_code = ap_language_code();
	
	if ($language_code == "da") {
		$url = airplay_change_language_url("da", "da"); 
		if ($url != "") {
			$da_url = "<a href='{$url}' class='selected' title='Skift sprog til dansk'>Dansk</a>";
		} else {
			$da_url = "<a href='http://www.airplaymusic.dk' class='selected' title='Skift sprog til dansk'>Dansk</a>";
		}
		$url = airplay_change_language_url("da", "en"); 
		if ($url != "") {
			$uk_url = "<a href='{$url}' title='Change language to English'>English</a>";
		} else {
			$uk_url = "<a href='http://www.airplaymusic.co.uk' title='Change language to English'>English</a>";
		}
	} else {
		$url = airplay_change_language_url("en", "da"); 
		if ($url != "") {
			$da_url = "<a href='{$url}' title='Skift sprog til dansk'>Dansk</a>";
		} else {
			$da_url = "<a href='http://www.airplaymusic.dk' title='Skift sprog til dansk'>Dansk</a>";
		}
		$url = airplay_change_language_url("en", "en"); 
		if ($url != "") {
			$uk_url = "<a href='{$url}' class='selected' title='Change language to English'>English</a>";
		} else {
			$uk_url = "<a href='http://www.airplaymusic.co.uk' class='selected' title='Change language to English'>English</a>";
		}
	}
	
	$sHtml .= "<div id='top_menu_icons'>";
	// Only DK has Guide icon for concerts, festivals ....
	if ($language_code == "da") {
		$sHtml .= "<div id='dropdown_menu_element_1' class='top_menu_dropdown'><div class='item item_festival'><a title='Guide til musikfestival' href='/musikfestival-guide'>Musikfestival guide</a></div><div class='item item_concert'><a title='Koncert Guide' href='/koncert-guide'>Koncert guide</a></div><div class='item item_shop'><a title='Pladeforretning Guide' href='/pladeforretninger'>Pladeforretning guide</a></div></div>";
	}
	
	$sHtml .= "<div id='dropdown_menu_element_2' class='top_menu_dropdown'><div class='item item_lang_da'>{$da_url}</div><div class='item item_lang_en'>{$uk_url}</div></div>";
	
	// Only DK has User login and settings .... && user_is_logged_in()
	if ($language_code == "da") {
		if (!user_is_logged_in()) {
			$element['#type'] = 'hybridauth_widget';
			$login_html = drupal_render($element);
		} else {
			$login_html = "<div class='item item_user_settings'><a href='/bruger-indstillinger' title='Bruger indstillinger'>Indstillinger</a></div><div class='item item_user_logout'><a href='/user/logout' title='Logud'>Logud</a></div>";
		}
		$sHtml .= "<div id='dropdown_menu_element_3' class='top_menu_dropdown'>" . $login_html . "</div>";
	}
	
	$sHtml .= "<div id='top_menu_icons_container'>";
	$sHtml .= "<div id='top_menu_element_2' onClick='ap_top_menu_click(2);' title='" . t("Change language") . "'>&nbsp;</div>";
	// Only DK has User login and settings .... && user_is_logged_in()
	if ($language_code == "da") {
		$sHtml .= "<div id='top_menu_element_3' onClick='ap_top_menu_click(3);' title='Login / opret bruger'>&nbsp;</div>";
	}
	
	// Only DK has Guide icon for concerts, festivals ....
	if ($language_code == "da") {
		$sHtml .= "<div id='top_menu_element_1' onClick='ap_top_menu_click(1);'>Guider<span class='icon'>&nbsp;</span></div>";
	}
	$sHtml .= "</div>";

	$sHtml .= "</div>";
	if (isset($_SERVER['REQUEST_URI'])) {
		$page_url = substr($_SERVER["REQUEST_URI"], 1);
	} else {
		$page_url = "";
	}
	$sHtml .=
<<<SCRIPT
<script type='text/javascript'>
	jQuery('#top_menu_element_1').click(function(event) { event.stopPropagation(); });
	jQuery("html").click(function() { jQuery("#dropdown_menu_element_1").hide(); });
	if (jQuery('#top_menu_element_2').length) {
	jQuery('#top_menu_element_2').click(function(event) { event.stopPropagation(); });
	jQuery("html").click(function() { jQuery("#dropdown_menu_element_2").hide(); });
	}
	if (jQuery('#top_menu_element_3').length) {
	jQuery('#top_menu_element_3').click(function(event) { event.stopPropagation(); });
	jQuery("html").click(function() { jQuery("#dropdown_menu_element_3").hide(); });
	}
	jQuery('<li><a href="/user?current={$page_url}" title="Airplay Music" class="hybridauth-widget-provider" rel="nofollow" onclick="javascript:void(0);"><span class="hybridauth-icon hybridauth-icon-hybridauth-24 hybridauth-airplaymusic hybridauth-airplaymusic-hybridauth-24" title="Airplay Music"><\/span><\/a><\/li>').insertAfter("ul.hybridauth-widget li:last-child").last();
</script>
SCRIPT;
	
	return $sHtml;
}

function ap_agent_is_user_logged_in()
{
	$logged_in = false;
	/*global $ap_account;
	// Optimized for not accessing database,
	if (!isset($ap_account)) {
		global $user;
		$ap_account = user_uid_optional_load();
	}*/
	//if ($ap_account->uid != 0) {
	global $user;
	if ($user->uid != 0) {
		$logged_in = true;
	} else {
		$logged_in = false;
	}
	return $logged_in;
}

function ap_agent_user_not_logged_in_html() {
	$s = "";
	$s .= "<div id='price_agent_link_container'><div class='icon'>&nbsp;</div><div><a href='javascript:void(0);' title='Opret agent' onClick='jQuery(\"#price_agent_container\").show();' class='link'>AGENT</a></div></div>";
	$s .= "<div id='price_agent_container'><div class='header'>Airplay Music Agent</div>";
	$s .= "<div class='subheader'>Du skal være logget ind som bruger for at benytte denne service.</div>";
	$s .= "<div class='full'>Login / bruger-oprettelse finder du i menuen i toppen af denne side...</div></div>";
	$s .=
<<<SCRIPT
<script type="text/javascript">
	jQuery('#price_agent_link_container a.link,#price_agent_container').click(function(event) { jQuery('#price_agent_container').show(); event.stopPropagation(); }); 
	jQuery("html").click(function() { jQuery("#price_agent_container").hide(); });
</script>
SCRIPT;
	return $s;
}



function isBotIP() {
	$sIP = $_SERVER['REMOTE_ADDR'];
	// TODO : make this and array .......
	//157.56.92.147 157.55.33. // 157.55.36. - bingbot
	//173.208.183. // 178.63.47 // 148.251.75.46 // 78.46.172.174 - majestic12.co.uk
	//66.249. - Google
	//180.76.5 / 180.76.6 - Baiduspider
	//72.14.199.209 - Google ad-bot
	// 93.186.202. // 68.180.224.227 - YandexBot
	//37.58.100. - ahref bot
	//182.118.21. // 182.118.22. // 182.118.25. - 360spider
	// // 130.225.26.13 130.226.228.7 130.226.228.8 // Netarkivet
	if ( strstr($sIP, "130.225.26.13") || strstr($sIP, "130.226.228.7") || strstr($sIP, "130.226.228.8") || strstr($sIP, "78.46.172.174") || strstr($sIP, "93.186.202.") ||  strstr($sIP, "182.118.21.") ||  strstr($sIP, "182.118.22.") ||  strstr($sIP, "182.118.25.") || strstr($sIP, "148.251.75.46") ||  strstr($sIP, "37.58.100.") || strstr($sIP, "157.55.33.") || strstr($sIP, "157.55.35.") || strstr($sIP, "157.55.36.") || strstr($sIP, "157.56.92.147") || strstr($sIP, "66.249.") || strstr($sIP, "180.76.5.") || strstr($sIP, "180.76.6.") || strstr($sIP, "141.8.147.23") || strstr($sIP, "173.208.183.") || strstr($sIP, "178.63.47.")  || strstr($sIP, "72.14.199.209")  || strstr($sIP, "68.180.224.227") ) {
		return true;
	} else {
		if (isPossibleBot ($sIP) ==  true) {
			return true;
		} else {
			return false;
		}
	}
}

/*
	We asume that if a "user" makes more then 20 views a day - then it is a bot.
*/
function isPossibleBot($ip_address) {
	$count = 0;
	$sQuery = "SELECT count(*) AS nCount, ip_address FROM video where timestamp_created >= DATE_ADD(now(), INTERVAL -1 DAY) AND ip_address = :ip_address GROUP BY ip_address;";
    db_set_active('airplay_music_v1');
    $aArgs = array();
	$aArgs[':ip_address'] = $ip_address;
    $query_result = db_query_range($sQuery, 0, 1, $aArgs);
    while ( $a = $query_result->fetchAssoc() ) {
        $count = $a["nCount"];
    } 
    db_set_active('default');
	if ($count > 20) {
		return true;
	} else {
		return false;
	}
}


//------
// Video functions
//---------

function ap_getArtistSongEmbedVideoHTML($artist_name, $song_name) {
	$sHtml = "";
	$url_send ="https://admin.radrmusicnews.com/api/v1/players?apiKey=2cb8972c-0a1b-4902-997e-005f6b953ae0";
	$items = ap_radrmusic_searchForItems(urlencode($artist_name));
	if (count($items)) {
		$song_item = ap_radrmusic_getCorrectItemFromArtist($items, $artist_name, $song_name);
		if (count($song_item)) {
			$data = array(
			  "name" => "aplayername",
			  "type" => "EDITORIAL",
			  "width" => "300",
			  "height" => "169",
			  "theme" => "the only theme",
			  "videoIds" => array($song_item["id"])
			);
			$post_data = json_encode($data);
			$video_object = ap_radrmusic_sendPostData($url_send, $post_data);
			if (count($video_object)) {
				$sHtml = $video_object["embedHtml"];
			}
		}
	}
	return $sHtml;
}

function ap_getArtistEmbedVideoHTML($artist_name) {

	$sHtml = "";
	$url_send ="https://admin.radrmusicnews.com/api/v1/players?apiKey=2cb8972c-0a1b-4902-997e-005f6b953ae0";
	$items = ap_radrmusic_searchForItems(urlencode($artist_name));
	if (count($items)) {
		$ids = ap_radrmusic_getCorrectArtistFromArtist($items, $artist_name);
		if (count($ids)) {
			$data = array(
			  "name" => "aplayername",
			  "type" => "EDITORIAL",
			  "width" => "300",
			  "height" => "169",
			  "theme" => "the only theme",
			  "videoIds" => $ids
			);
			$post_data = json_encode($data);
			$video_object = ap_radrmusic_sendPostData($url_send, $post_data);
			if (count($video_object)) {
				$sHtml = $video_object["embedHtml"];
			}
		}
	}
	return $sHtml;
}

function ap_radrmusic_searchForItems($search_string) {
	$json = file_get_contents("https://admin.radrmusicnews.com/api/v1/search/videos?query={$search_string}&offset=0&limit=100&apiKey=2cb8972c-0a1b-4902-997e-005f6b953ae0");
	$json_data = json_decode($json, true);
	return $json_data;
}

function ap_radrmusic_getCorrectItemFromArtist($items, $artist_name, $song_name) {
	foreach($items["list"] AS $item) {
		if (mb_strtolower($item["artistName"]) == mb_strtolower($artist_name) && mb_strtolower($item["title"]) == mb_strtolower($song_name)) {
			return $item;
		}
	}
	return array();
}

function ap_radrmusic_getCorrectArtistFromArtist($items, $artist_name) {
	foreach($items["list"] AS $item) {
		if (mb_strtolower($item["artistName"]) == mb_strtolower($artist_name)) {
			$aItems[] = $item["id"];
		}
	}
	return $aItems;
}

function ap_radrmusic_sendPostData($url, $post_data){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
		$result = curl_exec($ch);
		if (!curl_errno($ch)) {
			curl_close($ch);
		}
		return json_decode($result, true);
	}

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


function item_base_name_DESC ( $item1,$item2 )
{
    if ($item1['item_base_name'] == $item2['item_base_name']) return 0;
    return ($item1['item_base_name'] < $item2['item_base_name']) ? 1 : -1;
}

function item_base_name_ASC ( $item1,$item2 )
{
    if ($item1['item_base_name'] == $item2['item_base_name']) return 0;
    return ($item1['item_base_name'] > $item2['item_base_name']) ? 1 : -1;
}

function item_year_DESC ( $item1,$item2 )
{
    if ( (int)$item1['item_year'] == (int)$item2['item_year']) return 0;
    return ( (int)$item1['item_year'] < (int)$item2['item_year']) ? 1 : -1;
}

function item_year_ASC ( $item1,$item2 )
{
    if ( (int)$item1['item_year'] == (int)$item2['item_year']) return 0;
    return ( (int)$item1['item_year'] > (int)$item2['item_year']) ? 1 : -1;
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


function song_simple_name_DESC ( $item1,$item2 )
{
    if ($item1['song_simple_name'] == $item2['song_simple_name']) return 0;
    return ($item1['song_simple_name'] < $item2['song_simple_name']) ? 1 : -1;
}

function song_simple_name_ASC ( $item1,$item2 )
{
    if ($item1['song_simple_name'] == $item2['song_simple_name']) return 0;
    return ($item1['song_simple_name'] > $item2['song_simple_name']) ? 1 : -1;
}
