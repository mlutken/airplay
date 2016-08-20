<?php 
// $Id$

function music_search_display_song_matches( $song_simple_name, &$iExactMatches  )
{
	$sHtml = music_search_display_song_simple_matches_exact( $song_simple_name, $iExactMatches  );
	if ( $sHtml == '' ) $sHtml = music_search_display_song_simple_matches_like ( $song_simple_name );
	return $sHtml;
}


function music_search_display_song_simple_matches_exact( $song_simple_name, &$iExactMatches  )
{
	global $G_DEFAULT_RESULTS_PER_PAGE;
	$iResStart = 0;
	$iExactMatches = 0;
	$iResPerPage = $G_DEFAULT_RESULTS_PER_PAGE;
	$tGetPrices = t('Get prices');
	
	db_set_active('airplay_music');

	$aArgs = array();
	
	$sQuery = "
	SELECT artist.artist_name, song_simple.song_simple_name 
	FROM artist
	INNER JOIN song_simple ON song_simple.artist_id=artist.artist_id 
	";
	
	
	if ( $song_simple_name != "" ) {
		$aArgs = array ($song_simple_name);
		$sQuery .= " WHERE song_simple.song_simple_name = '%s'"; 
	}
  	$sQuery .= " ORDER BY song_simple_name ASC";
	$query_result = db_query_range($sQuery, $aArgs, $iResStart, $iResPerPage);
	

	// ---------------------
	// --- Table content ---
	// ---------------------
 	$bEmptyQuery = true;
	while ($a = db_fetch_object($query_result)) {
		$iExactMatches++;
 		$bEmptyQuery = false;
        $sLookupSongPricesUrl  = "/" . ap_artist_suburl() . "/"  . airplay_name_to_url($a->artist_name);
        $sLookupSongPricesUrl .= "/" . ap_song_suburl() . "/" .  airplay_name_to_url($a->song_simple_name);

        $sArtistLink ="<a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($a->artist_name) .  "'>" . $a->artist_name . "</a>";
		$row[]  = array( 
  			"<a href='$sLookupSongPricesUrl'>" . $a->song_simple_name . "</a>",
			$sArtistLink,
 			"<a href='$sLookupSongPricesUrl'>$tGetPrices</a>",
			);
	}
	db_set_active('default');
	
	// --------------------
	// --- Table header ---
	// --------------------
	$header = array( 
		t('Song') . ' ( ' . t('Exact match') . ' )', 
		t('Artist'), 
		t('Buy'), 
		);
	
	// --- Render Table ---
	$tableHTML = "";
 	if ( !$bEmptyQuery) $tableHTML = theme('table', $header, $row);
	
	$sHtml .= $tableHTML;
	return $sHtml;
}


function music_search_display_song_simple_matches_like( $song_simple_name )
{
	global $G_DEFAULT_RESULTS_PER_PAGE;
	$iResStart = 0;
	$iResPerPage = $G_DEFAULT_RESULTS_PER_PAGE;
	$tGetPrices = t('Get prices');
	db_set_active('airplay_music');

	$aArgs = array();
	
	$sQuery = "
	SELECT artist.artist_name, song_simple.song_simple_name 
	FROM artist
	INNER JOIN song_simple ON song_simple.artist_id=artist.artist_id 
	";
	
// 	MATCH (handle) AGAINST ("xyz"); 
	
	if ( $song_simple_name != "" ) {
		$aArgs = array ($song_simple_name);
// 		$sQuery .= " WHERE song_simple.song_simple_name LIKE '%%%s%%'"; 
// 		$sQuery .= " WHERE MATCH (song_simple.song_simple_name) AGAINST ('%s')"; 
		$sQuery .= " WHERE song_simple.song_simple_name LIKE '%s%%'"; 
	}
  	$sQuery .= " ORDER BY song_simple_name ASC";
	$query_result = db_query_range($sQuery, $aArgs, $iResStart, $iResPerPage);
	

	// ---------------------
	// --- Table content ---
	// ---------------------
 	$bEmptyQuery = true;
	while ($a = db_fetch_object($query_result)) {
 		$bEmptyQuery = false;
        $sLookupSongPricesUrl  = "/" . ap_artist_suburl() . "/"  . airplay_name_to_url($a->artist_name);
        $sLookupSongPricesUrl .= "/" . ap_song_suburl() . "/" .  airplay_name_to_url($a->song_simple_name);
		$sArtistLink ="<a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($a->artist_name) .  "'>" . $a->artist_name . "</a>";
		$row[]  = array( 
  			"<a href='$sLookupSongPricesUrl'>" . $a->song_simple_name . "</a>",
 			$sArtistLink,
 			"<a href='$sLookupSongPricesUrl'>$tGetPrices</a>",
			);
	}
	db_set_active('default');
	
	// --------------------
	// --- Table header ---
	// --------------------
	$header = array( 
		t('Song') . ' ( ' . t('Similar matches') . ' )', 
		t('Artist'), 
		t('Buy'), 
		);
	
	// --- Render Table ---
	$tableHTML = "";
 	if ( !$bEmptyQuery) $tableHTML = theme('table', $header, $row);
	
	$sHtml .= $tableHTML;
	return $sHtml;
}



