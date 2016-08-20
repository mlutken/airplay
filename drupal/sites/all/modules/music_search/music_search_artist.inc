<?php 
// $Id$

global $g_music_search_display_artist_BaseQuery;

$g_music_search_display_artist_BaseQuery = "
	SELECT artist_name, genre_name FROM 
	{artist} 
	LEFT JOIN {genre} ON artist.genre_id = genre.genre_id 
";

function music_search_display_artist_matches( $artist_name, &$iExactMatches )
{
	$sHtml = music_search_display_artist_matches_exact( $artist_name, $iExactMatches );
	if ( $sHtml == '' ) $sHtml = music_search_display_artist_matches_like ( $artist_name );
	return $sHtml;
}



function music_search_display_artist_matches_exact( $artist_name, &$iExactMatches )
{
	global $G_DEFAULT_RESULTS_PER_PAGE, $g_music_search_display_artist_BaseQuery;
	$iResStart = 0;
	$iExactMatches = 0;
	$iResPerPage = $G_DEFAULT_RESULTS_PER_PAGE;
	$tGetPrices = t('Get prices');
	db_set_active('airplay_music');

	$aArgs = array();
	
	$sQuery = $g_music_search_display_artist_BaseQuery;
	
	if ( $artist_name != "" ) {
		$aArgs = array ($artist_name);
 		$sQuery .= " WHERE artist.artist_name = '%s'"; 
	}
  	$sQuery .= " ORDER BY artist_name ASC";
	$query_result = db_query_range($sQuery, $aArgs, $iResStart, $iResPerPage);
	

	// ---------------------
	// --- Table content ---
	// ---------------------
 	$bEmptyQuery = true;
	while ($a = db_fetch_object($query_result)) {
		$iExactMatches++;
 		$bEmptyQuery = false;
		$sLookupArtistPricesUrl  = "/prices?search_for=artist";
		$sLookupArtistPricesUrl .= "&lookup=" . urlencode($a->artist_name);
		$sLookupArtistPricesUrl .= "&artist_name=" . urlencode($a->artist_name);
		$sArtistLink ="<a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($a->artist_name) .  "'>" . $a->artist_name;
		$sArtistLink .= "</a>";
		$row[]  = array( 
//  			$a->artist_name,
			$sArtistLink,
			$a->genre_name,
 			"<a href='$sLookupArtistPricesUrl'>$tGetPrices</a>",
			);
	}
	db_set_active('default');
	
	// --------------------
	// --- Table header ---
	// --------------------
	$header = array( 
		t('Artist') . ' ( ' . t('Exact match') . ' )', 
		t('Genre'), 
		t('Buy'), 
		);
	
	// --- Render Table ---
	$tableHTML = "";
 	if ( !$bEmptyQuery) $tableHTML = theme('table', $header, $row);
	
	$sHtml .= $tableHTML;
	return $sHtml;
}


function music_search_display_artist_matches_like( $artist_name )
{
	global $G_DEFAULT_RESULTS_PER_PAGE, $g_music_search_display_artist_BaseQuery;
	$iResStart = 0;
	$iResPerPage = $G_DEFAULT_RESULTS_PER_PAGE;
	$tGetPrices = t('Get prices');
	db_set_active('airplay_music');

	$aArgs = array();
	
	$sQuery = $g_music_search_display_artist_BaseQuery;
	
	
	if ( $artist_name != "" ) {
		$aArgs = array ($artist_name);
		$sQuery .= " WHERE artist.artist_name LIKE '%%%s%%'"; 
	}
  	$sQuery .= " ORDER BY artist_name ASC";
	$query_result = db_query_range($sQuery, $aArgs, $iResStart, $iResPerPage);
	

	// ---------------------
	// --- Table content ---
	// ---------------------
 	$bEmptyQuery = true;
	while ($a = db_fetch_object($query_result)) {
 		$bEmptyQuery = false;
// // 		$sLookupArtistPricesUrl  = "/prices?search_for=artist";
// // 		$sLookupArtistPricesUrl .= "&lookup=" . urlencode($a->artist_name);
// // 		$sLookupArtistPricesUrl .= "&artist_name=" . urlencode($a->artist_name);
		$sArtistLink ="<a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($a->artist_name) .  "'>" . $a->artist_name . "</a>";
		$row[]  = array( 
			 $sArtistLink
			,$a->genre_name
// //  			,"<a href='$sLookupArtistPricesUrl'>$tGetPrices</a>",
			);
	}
	db_set_active('default');
	
	// --------------------
	// --- Table header ---
	// --------------------
	$header = array( 
		 t('Artist') . ' ( ' . t('Similar matches') . ' )' 
		,t('Genre')
// // 		,t('Buy') 
		);
	
	// --- Render Table ---
	$tableHTML = "";
 	if ( !$bEmptyQuery) $tableHTML = theme('table', $header, $row);
	
	$sHtml .= $tableHTML;
	return $sHtml;
}


