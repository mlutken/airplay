<?php 
// $Id$

//$G_NORMAL_TO_URxx

global $G_URL_TO_NORMAL_fromUrl;
global $G_URL_TO_NORMAL_toNormal;	

$G_URL_TO_NORMAL_fromUrl    =  array("_"   , "-AND-", "-SLASH-", "-QMARK-", "-PERCENT-", "-PLUS-"  );
$G_URL_TO_NORMAL_toNormal   =  array(" "    , "&"   , "/"      , "?"  , "%"  , "+"    );

/** Converts from url-name to real name. 

*/
function airplay_change_language_url( $from_domain, $to_domain ) 
{
	global $conf;
	$lang = $conf['language_default'];
	$sUrl = "";
	
	// Convert domain
	if ($from_domain == "da" && $to_domain == "da") { $sUrl =  str_replace("airplaymusic.co.uk", "airplaymusic.dk", $lang->domain); }
	else if ($from_domain == "da" && $to_domain == "en") { $sUrl =  str_replace("airplaymusic.dk", "airplaymusic.co.uk", $lang->domain); }
	else if ($from_domain == "en" && $to_domain == "en") { $sUrl =  str_replace("airplaymusic.dk", "airplaymusic.co.uk", $lang->domain); }
	else if ($from_domain == "en" && $to_domain == "da") { $sUrl =  str_replace("airplaymusic.co.uk", "airplaymusic.dk", $lang->domain); }

	$aPath = explode ( '/', $_GET['q'] );
	if ( count ($aPath) > 1) {
        $sArtistSubUrl = $aPath[0];
		if ($sArtistSubUrl == "kunstner" || $sArtistSubUrl == "artist") {
			if ($lang->language = "da" && $to_domain == "da") { $sArtistSubUrl = "kunstner"; }
			else if ($lang->language = "da" && $to_domain == "en") { $sArtistSubUrl = "artist"; }
			else if ($lang->language = "en" && $to_domain == "da") { $sArtistSubUrl = "kunstner"; }
			else if ($lang->language = "en" && $to_domain == "en") { $sArtistSubUrl = "artist"; }
			$sArtistName = airplay_url_to_name($aPath[1]);
			$sUrl .= "/{$sArtistSubUrl}/" . airplay_name_to_url($sArtistName);
		}
	} 
	if ( count ($aPath) > 3 ) {
		if ($sArtistSubUrl == "artist" || $sArtistSubUrl == "kunstner") {
			$sArtistItemType = $aPath[2];
			$sItemName = airplay_url_to_name($aPath[3]);
			if ($lang->language = "da" && $to_domain == "da" && $sArtistItemType == "sang") { $sArtistItemType = "sang"; }
			else if ($lang->language = "da" && $to_domain == "en" && $sArtistItemType == "sang") { $sArtistItemType = "song"; }
			else if ($lang->language = "en" && $to_domain == "da" && $sArtistItemType == "song") { $sArtistItemType = "sang"; }
			else if ($lang->language = "en" && $to_domain == "en" && $sArtistItemType == "song") { $sArtistItemType = "song"; }
			else { $sArtistItemType = "album"; }
			$sUrl .= "/{$sArtistItemType}/" . airplay_name_to_url($sItemName);
		}
	}

	return $sUrl;
}


/** Converts from url-name to real name. 
E.g: 
\code
\endcode
\return 'Normal' name with spaces instead of hyphens etc.
*/
function airplay_url_to_name( $sUrlName ) 
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
function airplay_name_to_url( $sName ) 
{
 	global $G_URL_TO_NORMAL_fromUrl;
 	global $G_URL_TO_NORMAL_toNormal;	
 	$sUrl = str_replace  ( $G_URL_TO_NORMAL_toNormal, $G_URL_TO_NORMAL_fromUrl, $sName );	
	return $sUrl;
}


// ------------------------------
// --- Language specific urls ---
// ------------------------------
function ap_artist_suburl()
{
    global $conf;
    static $suburls = array(
        'da' => 'kunstner',
        'en' => 'artist'
    );

    $lang = $conf['language_default'];
    return $suburls[$lang->language];
} 

function ap_album_suburl()
{

    global $conf;
    static $suburls = array(
        'da' => 'album',
        'en' => 'album'
    );

    $lang = $conf['language_default'];
    return $suburls[$lang->language];
} 

function ap_song_suburl()
{
    global $conf;
    static $suburls = array(
        'da' => 'sang',
        'en' => 'song'
    );

    $lang = $conf['language_default'];
    return $suburls[$lang->language];
} 


function ap_item_suburl($item)
{
    if ( $item == "album" ) return  ap_album_suburl();
    else                    return  ap_song_suburl(); 
} 

/** Get the artist URL given an artist name. The current language 
is taken into account when creating URL. Eg. if current language is danish 
we get 'Kim Larsen' => 'kunstner/Kim_Larsen' */
function ap_artist_url( $artist_name )
{
    return ap_artist_suburl() . "/" . airplay_name_to_url( $artist_name );
}


/** Get the album URL given an artist,album name. The current language 
is taken into account when creating URL. Eg. if current language is danish 
we get 'Kim Larsen', 'Midt om natten' => 'kunstner/Kim_Larsen/album/Midt_om_natten' */
function ap_album_url( $artist_name, $album_simple_name )
{
    return ap_artist_url($artist_name) . "/" . ap_album_suburl() . "/" . airplay_name_to_url( $album_simple_name );
}

/** Get the song URL given an artist,song name. The current language 
is taken into account when creating URL. Eg. if current language is danish 
we get 'Kim Larsen', 'Midt om natten' => 'kunstner/Kim_Larsen/sang/Midt_om_natten' */
function ap_song_url( $artist_name, $song_simple_name )
{
    return ap_artist_url($artist_name) . "/" . ap_song_suburl() . "/" . airplay_name_to_url( $song_simple_name );
}

// --- Function used to replace affiliate link from a given url.
function ap_replace_affiliate_link($buy_at_url, $affiliate_link, $affiliate_encode_times) {
	if ($affiliate_encode_times == 0) {
		return str_replace("[TARGET_URL]", $buy_at_url ,$affiliate_link);
	} else {
		for ($i=0;$i<=$affiliate_encode_times;$i++) {
			$buy_at_url = urlencode($buy_at_url);
		}
		return str_replace("[TARGET_URL]", $buy_at_url ,$affiliate_link);
	}
}


