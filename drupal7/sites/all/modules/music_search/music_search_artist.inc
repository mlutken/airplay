<?php 
// $Id$

global $g_music_search_display_artist_BaseQuery;

$g_music_search_display_artist_BaseQuery = "
	SELECT artist_name, genre_name, item_price_count FROM 
	artist
	LEFT JOIN genre ON artist.genre_id = genre.genre_id";
    
function music_search_display_artist_matches( $artist_name, &$iExactMatches )
{
	$sHtml = "";
	$sHtml_Top_Artists = "";
	$sHtml_Exact_Artists = "";
	$sHtml_Exact_Artists .= music_search_display_artist_matches_exact( $artist_name, $iExactMatches );
	$sHtml_Top_Artists .= music_search_display_artist_matches_like_top_artists ( $artist_name );
	if ( $sHtml_Exact_Artists == '' ) {
		$sHtml .= music_search_display_artist_matches_like ( $artist_name );
	}
	return $sHtml_Exact_Artists . $sHtml_Top_Artists . $sHtml;
}



function music_search_display_artist_matches_exact( $artist_name, &$iExactMatches )
{
	global $G_DEFAULT_RESULTS_PER_PAGE, $g_music_search_display_artist_BaseQuery;
	$sHtml = "";
	$iResStart = 0;
	$iExactMatches = 0;
	$iResPerPage = $G_DEFAULT_RESULTS_PER_PAGE;
	$tGetPrices = t('Get prices');
	
	$aArgs = array();
	
	$sQuery = $g_music_search_display_artist_BaseQuery;
	
	if ( $artist_name != "" ) {
		$aArgs = array ( ':artist_name' => $artist_name);
 		$sQuery .= " WHERE artist.artist_name = :artist_name"; 
	}
  	$sQuery .= " ORDER BY artist_name ASC";
	
	db_set_active('airplay_music_v1');
	$query_result = db_query_range($sQuery, $iResStart, $iResPerPage, $aArgs);
	db_set_active('default');
	
	// ---------------------
	// --- Table content ---
	// ---------------------
 	$bEmptyQuery = true;
	while ($a = $query_result->fetchObject()) {
		$iExactMatches++;
 		$bEmptyQuery = false;
		$sLookupArtistPricesUrl  = "/prices?search_for=artist";
		$sLookupArtistPricesUrl .= "&lookup=" . urlencode($a->artist_name);
		$sLookupArtistPricesUrl .= "&artist_name=" . urlencode($a->artist_name);
		$sArtistLink = "<a href=\"/" . ap_artist_suburl() . "/" . airplay_name_to_url($a->artist_name) .  "\">" . $a->artist_name . "</a>";
		$sArtistLinkPrices = "<a href=\"/" . ap_artist_suburl() . "/" . airplay_name_to_url($a->artist_name) .  "\">" . $tGetPrices . "</a>";
		
		$cell['data'] = $sArtistLink;
        $cell['class'] = 'list-artist-artist-search-result';
        $cell_data[] = $cell;
		if ($a->genre_name == "Unknown") {
			$cell['data'] = t('Unknown');
		} else {
			$cell['data'] = $a->genre_name;
		}
        $cell['class'] = 'list-artist-genre-search-result';
        $cell_data[] = $cell;
		/*$cell['data'] = $sArtistLinkPrices;
        $cell['class'] = 'list-artist-buy-at-url-search-result';
		$cell_data[] = $cell;*/
        $rows[] = $cell_data;
        $cell_data = "";
        $cell = "";
	}

    $cell['data'] = "";
    $cell['class'] = 'list-artist-artist-search-result';
    $cell_data[] = $cell;
    $cell['data'] = "";
    $cell['class'] = 'list-artist-genre-search-result';
    $cell_data[] = $cell;
    /*$cell['data'] = "";
    $cell['class'] = 'list-artist-buy-at-url-search-result';
    $cell_data[] = $cell;*/
    //$rows[] = $cell_data;
    $rows[] = array( 'data' => $cell_data, 'class' => array('search-result-table-spacer') );
    $cell_data = "";
    $cell = "";
   
	
	// --------------------
	// --- Table header ---
	// --------------------
	$header = array();
    $cell['data'] = t('Artist') . ' (' . t('Exact match') . ')';
    $cell['class'] = 'header-artist-artist-search-result';
    $header[] = $cell;
    $cell['data'] = t('Genre');
    $cell['class'] = 'header-artist-genre-search-result';
    $header[] = $cell;
    /*$cell['data'] = t('Buy');
    $cell['class'] = 'header-artist-buy-at-url-search-result';
    $header[] = $cell;*/
	// --- Render Table ---
	$tableHTML = "";

	if ( !$bEmptyQuery) $tableHTML = theme('table', array(  'header' => $header,  'rows' => $rows,  'attributes' => array('class' => array('list-price-table'))));
	
	$sHtml .= $tableHTML;
	return $sHtml;
}


function music_search_display_artist_matches_like( $artist_name )
{
	global $G_DEFAULT_RESULTS_PER_PAGE, $g_music_search_display_artist_BaseQuery;
    $sHtml = "";
	$show_more_link = "";
	$iResStart = 0;
	$iResPerPage = $G_DEFAULT_RESULTS_PER_PAGE;
	$sQuery = $g_music_search_display_artist_BaseQuery;
	
	if ( $artist_name != "" ) {
		$sQuery .= " WHERE artist.artist_name LIKE :artist_name"; 
	}
  	$sQuery .= " ORDER BY artist_name ASC";
	
	db_set_active('airplay_music_v1');
	$query_result = db_query_range($sQuery, $iResStart, $iResPerPage, array(':artist_name' => "".$artist_name."%") );
	db_set_active('default');

	// ---------------------
	// --- Table content ---
	// ---------------------
 	$bEmptyQuery = true;
 	$rows = array();
	while ($a = $query_result->fetchObject()) {
 		$bEmptyQuery = false;
		$sArtistLink = "<a href=\"/" . ap_artist_suburl() . "/" . airplay_name_to_url($a->artist_name) .  "\">" . $a->artist_name . "</a>";
        $sPriceLink = "<a href=\"/" . ap_artist_suburl() . "/" . airplay_name_to_url($a->artist_name) .  "\">" . t('Get prices') . "</a>";
		$cell['data'] = $sArtistLink;
        $cell['class'] = 'list-artist-artist-search-result';
        $cell_data[] = $cell;
		if ($a->genre_name == "Unknown") {
			$cell['data'] = t('Unknown');
		} else {
			$cell['data'] = $a->genre_name;
		}
        $cell['class'] = 'list-artist-genre-search-result';
        $cell_data[] = $cell;
        /*$cell['data'] = $sPriceLink;
        $cell['class'] = 'list-artist-buy-at-url-search-result';
        $cell_data[] = $cell;*/
        $rows[] = $cell_data;
        $cell_data = "";
        $cell = "";
	}
    
    $cell['data'] = "&nbsp;";
    $cell['class'] = 'list-artist-artist-search-result';
    $cell_data[] = $cell;
    $cell['data'] = "&nbsp;";
    $cell['class'] = 'list-artist-genre-search-result';
    $cell_data[] = $cell;
    /*$cell['data'] = "&nbsp;";
    $cell['class'] = 'list-artist-buy-at-url-search-result';
    $cell_data[] = $cell;*/
    $rows[] = array( 'data' => $cell_data, 'class' => array('search-result-table-spacer') );
    $cell_data = "";
    $cell = "";
    

	
	// --------------------
	// --- Table header ---
	// --------------------
	$header = array();
    $cell['data'] = t('Alternative artists');
    $cell['class'] = 'header-artist-artist-search-result';
    $header[] = $cell;
    $cell['data'] = t('Genre');
    $cell['class'] = 'header-artist-genre-search-result';
    $header[] = $cell;
    /*$cell['data'] = t('Buy');
    $cell['class'] = 'header-artist-buy-at-url-search-result';
    $header[] = $cell;*/
	// --- Render Table ---
	$tableHTML = "";
    if ( !$bEmptyQuery) $tableHTML = theme('table', array(  'header' => $header,  'rows' => $rows,  'attributes' => array('class' => array('list-price-table'), 'id' => array('alternative_artists'), 'style' => array('display:none;'))));
	
	if (!$bEmptyQuery) {
		$show_more_link = "<div style='width:100%;float:left;padding:0px 0px 20px 0px;'><a href='javascript:void(0);' onClick='jQuery(\"#alternative_artists\").toggle();'><strong>" . t("See more alternatives") . "<strong></a></div>";
	}
	
	$sHtml .= $show_more_link . $tableHTML;
	return $sHtml;
}

function music_search_display_artist_matches_like_top_artists( $artist_name )
{
	global $G_SEARCH_RESULTS_TOP_SEARCH_PER_PAGE, $g_music_search_display_artist_BaseQuery;
    $sHtml = "";
	$soundex_string = "";
	$sQuery = "";
	$artist_name_written_from_alias = "";
	
	$artist_words = explode(" ", $artist_name);
	$artist_word_count = count($artist_words);
	$iResStart = 0;
	$iResPerPage = $G_SEARCH_RESULTS_TOP_SEARCH_PER_PAGE;
	$aArgs = array();
	
	$sQuery = $g_music_search_display_artist_BaseQuery;

	if ($artist_word_count > 1) {
		for ($i = 0; $i < $artist_word_count; $i++) {
			$soundex_string .= " " . soundex($artist_words[$i]);
		}
	} else {
		$soundex_string = soundex($artist_name);
	}

	$soundex_string = trim($soundex_string);
	
	if ( $artist_name != "" ) {
		$sQuery .= " WHERE (artist_name LIKE :artist_name_like OR artist_soundex LIKE :artist_name_soundex) AND item_price_count > 9"; 
	}
  	$sQuery .= " ORDER BY item_price_count DESC";
	
	db_set_active('airplay_music_v1');
	//$query_result = db_query_range($sQuery, $iResStart, $iResPerPage, array(':artist_name_like' => "%". $artist_name. "%", ':artist_name_soundex' => "%" . $soundex_string . "%" ) );
	$query_result = db_query_range($sQuery, $iResStart, $iResPerPage, array(':artist_name_like' => "". $artist_name. "%", ':artist_name_soundex' => "" . $soundex_string . "%" ) );
	db_set_active('default');

	/* Is in alias aka most likely "best result" */
	$sAliasQuery = "SELECT artist_name, genre_name FROM artist ";
	$sAliasQuery .= "INNER JOIN artist_synonym ON artist.artist_id = artist_synonym.artist_id ";
	$sAliasQuery .= "LEFT JOIN genre ON artist.genre_id = genre.genre_id ";
	$sAliasQuery .= "WHERE artist_synonym_name = :artist_name ";
	
	db_set_active('airplay_music_v1');
	$query_alias_result = db_query_range($sAliasQuery, $iResStart, 1, array(':artist_name' => $artist_name) );
	db_set_active('default');
	
	
	// ---------------------
	// --- Table content ---
	// ---------------------
 	$bEmptyQuery = true;
 	$rows = array();
    
	/* Is in alias aka most likely "best result" */
	while ($a = $query_alias_result->fetchObject()) {
 		$bEmptyQuery = false;
		$artist_name_written_from_alias = $a->artist_name;
		$sArtistLink = "<a href=\"/" . ap_artist_suburl() . "/" . airplay_name_to_url($a->artist_name) .  "\">" . $a->artist_name . "</a>";
        $sPriceLink = "<a href=\"/" . ap_artist_suburl() . "/" . airplay_name_to_url($a->artist_name) .  "\">" . t('Get prices') . "</a>";
		$cell['data'] = $sArtistLink;
        $cell['class'] = 'list-artist-artist-search-result';
        $cell_data[] = $cell;
		if ($a->genre_name == "Unknown") {
			$cell['data'] = t('Unknown');
		} else {
			$cell['data'] = $a->genre_name;
		}
        $cell['class'] = 'list-artist-genre-search-result';
        $cell_data[] = $cell;
        /*$cell['data'] = $sPriceLink;
        $cell['class'] = 'list-artist-buy-at-url-search-result';
        $cell_data[] = $cell;*/
        $rows[] = $cell_data;
        $cell_data = "";
        $cell = "";
	}

	
	while ($a = $query_result->fetchObject()) {
		if ($artist_name_written_from_alias != $a->artist_name) {
			$bEmptyQuery = false;
			$sArtistLink = "<a href=\"/" . ap_artist_suburl() . "/" . airplay_name_to_url($a->artist_name) .  "\">" . $a->artist_name . "</a>";
			$sPriceLink = "<a href=\"/" . ap_artist_suburl() . "/" . airplay_name_to_url($a->artist_name) .  "\">" . t('Get prices') . "</a>";
			$cell['data'] = $sArtistLink;
			$cell['class'] = 'list-artist-artist-search-result';
			$cell_data[] = $cell;
			if ($a->genre_name == "Unknown") {
				$cell['data'] = t('Unknown');
			} else {
				$cell['data'] = $a->genre_name;
			}
			$cell['class'] = 'list-artist-genre-search-result';
			$cell_data[] = $cell;
			/*$cell['data'] = $sPriceLink;
			$cell['class'] = 'list-artist-buy-at-url-search-result';
			$cell_data[] = $cell;*/
			$rows[] = $cell_data;
			$cell_data = "";
			$cell = "";
		}
	}


	/* Ekstra table row */
    $cell['data'] = "&nbsp;";
    $cell['class'] = 'list-artist-artist-search-result';
    $cell_data[] = $cell;
    $cell['data'] = "&nbsp;";
    $cell['class'] = 'list-artist-genre-search-result';
    $cell_data[] = $cell;
    /*$cell['data'] = "&nbsp;";
    $cell['class'] = 'list-artist-buy-at-url-search-result';
    $cell_data[] = $cell;*/
    $rows[] = array( 'data' => $cell_data, 'class' => array('search-result-table-spacer') );
    $cell_data = "";
    $cell = "";
    

	// --------------------
	// --- Table header ---
	// --------------------
	$header = array();
	$cell['data'] = t('Artist (did you mean?)');
    $cell['class'] = 'header-artist-artist-search-result';
    $header[] = $cell;
    $cell['data'] = t('Genre');
    $cell['class'] = 'header-artist-genre-search-result';
    $header[] = $cell;
    /*$cell['data'] = t('Buy');
    $cell['class'] = 'header-artist-buy-at-url-search-result';
    $header[] = $cell;*/
	// --- Render Table ---
	$tableHTML = "";
    if ( !$bEmptyQuery) $tableHTML = theme('table', array(  'header' => $header,  'rows' => $rows,  'attributes' => array('class' => array('list-price-table'))));
	
	$sHtml .= $tableHTML;
	return $sHtml;
}

