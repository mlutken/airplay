<?php 
// $Id$

//$G_NORMAL_TO_URxx

global $G_URL_TO_NORMAL_fromUrl;
global $G_URL_TO_NORMAL_toNormal;	

$G_URL_TO_NORMAL_fromUrl    =  array("_"   , "-AND-", "-SLASH-", "-QMARK-"  );
$G_URL_TO_NORMAL_toNormal   =  array(" "    , "&"   , "/"      , "?"        );



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
//    dfb($suburls[$lang->language], "ap_artist_suburl");
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



