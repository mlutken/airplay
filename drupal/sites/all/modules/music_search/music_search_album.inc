<?php 
// $Id$

global $g_music_search_display_album_simple_BaseQuery;

$g_music_search_display_album_simple_BaseQuery = "
SELECT album_simple_name, artist_name   
FROM album_simple 
INNER JOIN artist ON album_simple.artist_id=artist.artist_id 
";

function music_search_display_album_matches( $album_simple_name, &$iExactMatches  )
{
	$sHtml = music_search_display_album_simple_matches_exact( $album_simple_name, $iExactMatches  );
	if ( $sHtml == '' ) $sHtml = music_search_display_album_simple_matches_like ( $album_simple_name );
	return $sHtml;
}


function music_search_display_album_simple_matches_exact ( $album_simple_name, &$iExactMatches  )
{
	global $G_DEFAULT_RESULTS_PER_PAGE, $g_music_search_display_album_simple_BaseQuery;
	$iResStart = 0;
	$iExactMatches = 0;
	$iResPerPage = $G_DEFAULT_RESULTS_PER_PAGE;
	$tGetPrices = t('Get prices');
	db_set_active('airplay_music');

	$aArgs = array();
	
	$sQuery = $g_music_search_display_album_simple_BaseQuery;
	
	
	if ( $album_simple_name != "" ) {
		$aArgs = array ($album_simple_name);
		$sQuery .= " WHERE album_simple.album_simple_name = '%s'"; 
	}
  	$sQuery .= " ORDER BY album_simple_name ASC";
	$query_result = db_query_range($sQuery, $aArgs, $iResStart, $iResPerPage);
	

	// ---------------------
	// --- Table content ---
	// ---------------------
 	$bEmptyQuery = true;
	while ($a = db_fetch_object($query_result)) {
		$iExactMatches++;
 		$bEmptyQuery = false;
// // 		$sLookupAlbumPricesUrl ="/prices?search_for=album";
// // 		$sLookupAlbumPricesUrl .= "&lookup=" . urlencode($a->album_simple_name ) ;
// // 		$sLookupAlbumPricesUrl .= "&artist_name=" . urlencode($a->artist_name);
        $sLookupAlbumPricesUrl  = "/" . ap_artist_suburl() . "/"  . airplay_name_to_url($a->artist_name);
        $sLookupAlbumPricesUrl .= "/" . ap_album_suburl() . "/" .  airplay_name_to_url($a->album_simple_name);

        $sArtistLink ="<a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($a->artist_name) .  "'>" . $a->artist_name . "</a>";
		$row[]  = array( 
  			"<a href='$sLookupAlbumPricesUrl'>" . $a->album_simple_name . "</a>",
			$sArtistLink,
 			"<a href='$sLookupAlbumPricesUrl'>$tGetPrices</a>",
			);
	}
	db_set_active('default');
	
	// --------------------
	// --- Table header ---
	// --------------------
	$header = array( 
		t('Album') . ' ( ' . t('Exact match') . ' )', 
		t('Artist'), 
		t('Buy'), 
		);


	// --- Render Table ---
	$tableHTML = "";
 	if ( !$bEmptyQuery) $tableHTML = theme('table', $header, $row);
	
	$sHtml .= $tableHTML;
	return $sHtml;
}


function music_search_display_album_simple_matches_like ( $album_simple_name )
{
	global $G_DEFAULT_RESULTS_PER_PAGE, $g_music_search_display_album_simple_BaseQuery;
	$iResStart = 0;
	$iResPerPage = $G_DEFAULT_RESULTS_PER_PAGE;
	$tGetPrices = t('Get prices');
	db_set_active('airplay_music');

	$aArgs = array();
	
	$sQuery = $g_music_search_display_album_simple_BaseQuery;
	
	if ( $album_simple_name != "" ) {
		$aArgs = array ($album_simple_name);
		$sQuery .= " WHERE album_simple.album_simple_name LIKE '%s%%'"; 
// 		$sQuery .= " WHERE album_simple.album_simple_name LIKE '%%%s%%'"; 
	}
  	$sQuery .= " ORDER BY album_simple_name ASC";
	$query_result = db_query_range($sQuery, $aArgs, $iResStart, $iResPerPage);
	

	// ---------------------
	// --- Table content ---
	// ---------------------
 	$bEmptyQuery = true;
	while ($a = db_fetch_object($query_result)) {
 		$bEmptyQuery = false;
// // 		$sLookupAlbumPricesUrl ="/prices?search_for=album";
// // 		$sLookupAlbumPricesUrl .= "&lookup=" . urlencode($a->album_simple_name ) ;
// //         $sLookupAlbumPricesUrl .= "&artist_name=" . urlencode($a->artist_name);
        $sLookupAlbumPricesUrl  = "/" . ap_artist_suburl() . "/"  . airplay_name_to_url($a->artist_name);
        $sLookupAlbumPricesUrl .= "/" . ap_album_suburl() . "/" .  airplay_name_to_url($a->album_simple_name);

        $sArtistLink ="<a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($a->artist_name) .  "'>" . $a->artist_name . "</a>";
		$row[]  = array( 
  			"<a href='$sLookupAlbumPricesUrl'>" . $a->album_simple_name . "</a>",
 			$sArtistLink,
 			"<a href='$sLookupAlbumPricesUrl'>$tGetPrices</a>",
			);
	}
	db_set_active('default');
	
	// --------------------
	// --- Table header ---
	// --------------------
	$header = array( 
		t('Album') . ' ( ' . t('Similar matches') . ' )', 
		t('Artist'), 
		t('Buy'), 
		);
	

	// --- Render Table ---
	$tableHTML = "";
 	if ( !$bEmptyQuery) $tableHTML = theme('table', $header, $row);
	
	$sHtml .= $tableHTML;
	return $sHtml;
}



