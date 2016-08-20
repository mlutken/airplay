<?php 
// $Id$



function artist_decode_url( &$sArtistSubUrl, &$sArtistName, &$sArtistItemType, &$sItemTitle) {
//// 	dfb ($_GET, "_GET" );
// //   dfb ($aPath, "aPath" );
	$aPath = explode ( '/', $_GET['q'] );
	$sSubPage		= '';
	$sArtistName 	= '';
	$sTitle			= '';
    if ( count ($aPath) > 0 ) {
        $sArtistSubUrl = airplay_url_to_name($aPath[0]);
    } 
	if ( count ($aPath) > 1 ) {
		$sArtistName = airplay_url_to_name($aPath[1]);
	} 
	if ( count ($aPath) > 2 ) {
		$sArtistItemType = $aPath[2];
	} 
	if ( count ($aPath) > 3 ) {
		$sItemTitle = airplay_url_to_name($aPath[3]);
	} 


// //     // debug ..
// //     global $AP_ARTIST_SUB_URL;
// //     dfb ("SubPage, sArtistName, sTitle : '$sArtistSubUrl' / '$sArtistName' / '$sArtistItemType' / '$sItemTitle' " );
// //     $artist_name= ap_artist_suburl();
// //     dfb("ap_artist_suburl: $artist_name");
// // 
// //     global $conf;
// //     $lang = $conf['language_default'];
// //     dfb("ap_album_suburl lang: '{$lang->language}',  AP_ARTIST_SUB_URL: '$AP_ARTIST_SUB_URL'");
// //     dfb ($_SERVER['HTTP_HOST'], "_SERVER['HTTP_HOST']");
// //         // debug 

    return $sArtistName;
}
