<?php 
// $Id$

function music_search_display_song_matches( $song_simple_name, &$iExactMatches  )
{
    $sHtml = music_search_display_song_simple_matches_exact( $song_simple_name, $iExactMatches  );
    if ( $sHtml == '' ) $sHtml = music_search_display_song_simple_matches_like ( $song_simple_name );
    return $sHtml;
}


function music_search_display_song_simple_matches_exact ( $song_simple_name, &$iExactMatches  )
{
    $sHtml = "";
    $iExactMatches = 0;
    //$tGetPrices = t('Get prices');

    $aAllRows = lookupExact_songs($song_simple_name);

    // ---------------------
    // --- Table content ---
    // ---------------------
    $bEmptyQuery = true;
    $rows = array();
    foreach ($aAllRows as $a ) {
        $iExactMatches++;
        $bEmptyQuery = false;
        $sLookupSongPricesUrl  = "/" . ap_artist_suburl() . "/"  . airplay_name_to_url($a['artist_name']);
        $sLookupSongPricesUrl .= "/" . ap_song_suburl() . "/" .  airplay_name_to_url($a['item_base_name']);

        $sArtistLink ="<a href=\"/" . ap_artist_suburl() . "/" . airplay_name_to_url($a['artist_name']) .  "\">" . $a['artist_name'] . "</a>";

		$cell['data'] = "<a href=\"{$sLookupSongPricesUrl}\">" . $a['item_base_name'] . "</a>";
        $cell['class'] = 'list-song-song-search-result';
        $cell_data[] = $cell;
		$cell['data'] = $sArtistLink;
        $cell['class'] = 'list-song-artist-search-result';
        $cell_data[] = $cell;
		/*$cell['data'] = "<a href=\"$sLookupSongPricesUrl\">$tGetPrices</a>";
        $cell['class'] = 'list-song-buy-at-url-search-result';
		$cell_data[] = $cell;*/
        $rows[] = $cell_data;
        $cell_data = "";
        $cell = "";
    }
    
    $cell['data'] = "&nbsp;";
    $cell['class'] = 'list-song-song-search-result';
    $cell_data[] = $cell;
    $cell['data'] = "&nbsp;";
    $cell['class'] = 'list-song-artist-search-result';
    $cell_data[] = $cell;
    /*$cell['data'] = "&nbsp;";
    $cell['class'] = 'list-song-buy-at-url-search-result';
    $cell_data[] = $cell;*/
    //$rows[] = $cell_data;
    $rows[] = array( 'data' => $cell_data, 'class' => array('search-result-table-spacer') );
    $cell_data = "";
    $cell = "";
    
    // --------------------
    // --- Table header ---
    // --------------------
	$header = array();
    $cell['data'] = t('Song'); // . ' ( ' . t('Exact match') . ' )';
    $cell['class'] = 'header-song-song-search-result';
    $header[] = $cell;
    $cell['data'] = t('Artist');
    $cell['class'] = 'header-song-artist-search-result';
    $header[] = $cell;
    /*$cell['data'] = t('Buy');
    $cell['class'] = 'header-song-buy-at-url-search-result';
    $header[] = $cell;*/

    // --- Render Table ---
    $tableHTML = "";
    if ( !$bEmptyQuery) $tableHTML = theme('table', array(  'header' => $header,  'rows' => $rows,  'attributes' => array('class' => array('list-price-table'))));
    
    $sHtml .= $tableHTML;
    return $sHtml;
}


function music_search_display_song_simple_matches_like ( $song_simple_name )
{
    $sHtml = "";
    //$tGetPrices = t('Get prices');
    $aAllRows = lookupSimilar_songs($song_simple_name);
    
    // ---------------------
    // --- Table content ---
    // ---------------------
    $bEmptyQuery = true;
    $rows = array();
    foreach ($aAllRows as $a ) {
        $bEmptyQuery = false;
        $sLookupSongPricesUrl  = "/" . ap_artist_suburl() . "/"  . airplay_name_to_url($a['artist_name']);
        $sLookupSongPricesUrl .= "/" . ap_song_suburl() . "/" .  airplay_name_to_url($a['item_base_name']);

        $sArtistLink ="<a href=\"/" . ap_artist_suburl() . "/" . airplay_name_to_url($a['artist_name']) .  "\">" . $a['artist_name'] . "</a>";

		$cell['data'] = "<a href=\"{$sLookupSongPricesUrl}\">" . $a['item_base_name'] . "</a>";
        $cell['class'] = 'list-song-song-search-result';
        $cell_data[] = $cell;
		$cell['data'] = $sArtistLink;
        $cell['class'] = 'list-song-artist-search-result';
        $cell_data[] = $cell;
		/*$cell['data'] = "<a href=\"$sLookupSongPricesUrl\">$tGetPrices</a>";
        $cell['class'] = 'list-song-buy-at-url-search-result';
		$cell_data[] = $cell;*/
        $rows[] = $cell_data;
        $cell_data = "";
        $cell = "";
    }
    
    $cell['data'] = "&nbsp;";
    $cell['class'] = 'list-song-song-search-result';
    $cell_data[] = $cell;
    $cell['data'] = "&nbsp;";
    $cell['class'] = 'list-song-artist-search-result';
    $cell_data[] = $cell;
    /*$cell['data'] = "&nbsp;";
    $cell['class'] = 'list-song-buy-at-url-search-result';
    $cell_data[] = $cell;*/
    //$rows[] = $cell_data;
    $rows[] = array( 'data' => $cell_data, 'class' => array('search-result-table-spacer') );
    $cell_data = "";
    $cell = "";
    
    // --------------------
    // --- Table header ---
    // --------------------
    $header = array();
    $cell['data'] = t('Song'); // . ' ( ' . t('Similar matches') . ' )';
    $cell['class'] = 'header-song-song-search-result';
    $header[] = $cell;
    $cell['data'] = t('Artist');
    $cell['class'] = 'header-song-artist-search-result';
    $header[] = $cell;
    /*$cell['data'] = t('Buy');
    $cell['class'] = 'header-song-buy-at-url-search-result';
    $header[] = $cell;*/

    // --- Render Table ---
    $tableHTML = "";
    if ( !$bEmptyQuery) $tableHTML = theme('table', array(  'header' => $header,  'rows' => $rows,  'attributes' => array('class' => array('list-price-table'))));
    
    $sHtml .= $tableHTML;
    return $sHtml;
}



