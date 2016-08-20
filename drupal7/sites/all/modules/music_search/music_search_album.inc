<?php 
// $Id$

function music_search_display_album_matches( $album_simple_name, &$iExactMatches  )
{
	$sHtml = music_search_display_album_simple_matches_exact( $album_simple_name, $iExactMatches  );
	if ( $sHtml == '' ) $sHtml = music_search_display_album_simple_matches_like ( $album_simple_name );
	return $sHtml;
}


function music_search_display_album_simple_matches_exact ( $album_simple_name, &$iExactMatches  )
{
	$sHtml = "";
	$iExactMatches = 0;
	$tGetPrices = t('Get prices');

    $aAllRows = lookupExact_albums($album_simple_name);

	// ---------------------
	// --- Table content ---
	// ---------------------
 	$bEmptyQuery = true;
    $rows = array();
    foreach ($aAllRows as $a ) {
		$iExactMatches++;
 		$bEmptyQuery = false;
        $sLookupAlbumPricesUrl  = "/" . ap_artist_suburl() . "/"  . airplay_name_to_url($a['artist_name']);
        $sLookupAlbumPricesUrl .= "/" . ap_album_suburl() . "/" .  airplay_name_to_url($a['item_base_name']);

        $sArtistLink ="<a href=\"/" . ap_artist_suburl() . "/" . airplay_name_to_url($a['artist_name']) .  "\">" . $a['artist_name'] . "</a>";
		$cell['data'] = "<a href=\"{$sLookupAlbumPricesUrl}\">" . $a['item_base_name'] . "</a>";
        $cell['class'] = 'list-album-album-search-result';
        $cell_data[] = $cell;
		$cell['data'] = $sArtistLink;
        $cell['class'] = 'list-album-artist-search-result';
        $cell_data[] = $cell;
		/*$cell['data'] = "<a href=\"$sLookupAlbumPricesUrl\">$tGetPrices</a>";
        $cell['class'] = 'list-album-buy-at-url-search-result';
		$cell_data[] = $cell;*/
        $rows[] = $cell_data;
        $cell_data = "";
        $cell = "";
	}
	
    $cell['data'] = "&nbsp;";
    $cell['class'] = 'list-album-album-search-result';
    $cell_data[] = $cell;
    $cell['data'] = "&nbsp;";
    $cell['class'] = 'list-album-artist-search-result';
    $cell_data[] = $cell;
    /*$cell['data'] = "&nbsp;";
    $cell['class'] = 'list-album-buy-at-url-search-result';
    $cell_data[] = $cell;*/
    //$rows[] = $cell_data;
    $rows[] = array( 'data' => $cell_data, 'class' => array('search-result-table-spacer') );
    $cell_data = "";
    $cell = "";
    
	// --------------------
	// --- Table header ---
	// --------------------
	$header = array();
    $cell['data'] = t('Album'); // . ' ( ' . t('Exact match') . ' )';
    $cell['class'] = 'header-album-album-search-result';
    $header[] = $cell;
    $cell['data'] = t('Artist');
    $cell['class'] = 'header-album-artist-search-result';
    $header[] = $cell;
    /*$cell['data'] = t('Buy');
    $cell['class'] = 'header-album-buy-at-url-search-result';
    $header[] = $cell;*/


	// --- Render Table ---
	$tableHTML = "";
    if ( !$bEmptyQuery) $tableHTML = theme('table', array(  'header' => $header,  'rows' => $rows,  'attributes' => array('class' => array('list-price-table'))));
	
	$sHtml = $tableHTML;
	return $sHtml;
}


function music_search_display_album_simple_matches_like ( $album_simple_name )
{
	$sHtml = "";
	$tGetPrices = t('Get prices');
	$aAllRows = lookupSimilar_albums($album_simple_name);
	
	// ---------------------
	// --- Table content ---
	// ---------------------
 	$bEmptyQuery = true;
    $rows = array();
    foreach ($aAllRows as $a ) {
 		$bEmptyQuery = false;
        $sLookupAlbumPricesUrl  = "/" . ap_artist_suburl() . "/"  . airplay_name_to_url($a['artist_name']);
        $sLookupAlbumPricesUrl .= "/" . ap_album_suburl() . "/" .  airplay_name_to_url($a['item_base_name']);

        $sArtistLink ="<a href=\"/" . ap_artist_suburl() . "/" . airplay_name_to_url($a['artist_name']) .  "\">" . $a['artist_name'] . "</a>";

		$cell['data'] = "<a href=\"{$sLookupAlbumPricesUrl}\">" . $a['item_base_name'] . "</a>";
        $cell['class'] = 'list-album-album-search-result';
        $cell_data[] = $cell;
		$cell['data'] = $sArtistLink;
        $cell['class'] = 'list-album-artist-search-result';
        $cell_data[] = $cell;
		/*$cell['data'] = "<a href=\"$sLookupAlbumPricesUrl\"'>$tGetPrices</a>";
        $cell['class'] = 'list-album-buy-at-url-search-result';
		$cell_data[] = $cell;*/
        $rows[] = $cell_data;
        $cell_data = "";
        $cell = "";
	}
	
    $cell['data'] = "&nbsp;";
    $cell['class'] = 'list-album-album-search-result';
    $cell_data[] = $cell;
    $cell['data'] = "&nbsp;";
    $cell['class'] = 'list-album-artist-search-result';
    $cell_data[] = $cell;
    /*$cell['data'] = "&nbsp;";
    $cell['class'] = 'list-album-buy-at-url-search-result';
    $cell_data[] = $cell;*/
    //$rows[] = $cell_data;
    $rows[] = array( 'data' => $cell_data, 'class' => array('search-result-table-spacer') );
    $cell_data = "";
    $cell = "";
    
	// --------------------
	// --- Table header ---
	// --------------------

	$header = array();
    $cell['data'] = t('Album'); // . ' ( ' . t('Similar matches') . ' )';
    $cell['class'] = 'header-album-album-search-result';
    $header[] = $cell;
    $cell['data'] = t('Artist');
    $cell['class'] = 'header-album-artist-search-result';
    $header[] = $cell;
    /*$cell['data'] = t('Buy');
    $cell['class'] = 'header-album-buy-at-url-search-result';
    $header[] = $cell;*/

	// --- Render Table ---
	$tableHTML = "";
    if ( !$bEmptyQuery) $tableHTML = theme('table', array(  'header' => $header,  'rows' => $rows,  'attributes' => array('class' => array('list-price-table'))));
	
	$sHtml .= $tableHTML;
	return $sHtml;
}



