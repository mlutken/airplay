<?php 
// $Id$
/**
\see Table theme function: http://www.group42.ca/theming_101_%E2%80%93_theme_table_function
\see http://api.drupal.org/api/function/theme_table/6 

*/
function prices_display_artist( $artist_name, $bSearchUrl )
{
        if ($artist_name != "") {
                $oArtist = airplay_base_lookup_artist_exact($artist_name);

                $sAddThisLink  = "http://www.airplaymusic.dk/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name);

                $sHtml = "";
                $sArtistHeader = "<table cellpadding='0' cellspacing='0' class='list-price-intro'>";
                $sArtistHeader .= "<tr><td><h1 class='ap_item_headline1'>$artist_name</h1></td>";
                $sArtistHeader .= "<td>" . ap_getAddThis() . "</td>";
                $sArtistHeader .= "</tr></table>";


	        $sHtml .= ap_autocomplete_offset(25);
	        $sHtml .= drupal_get_form('apms_large_form' );

	        $sHtml .= $sArtistHeader;
	
	        $sHtml .= prices_display_artist_albums( $artist_name, $bSearchUrl );
	        $sHtml .= prices_display_artist_songs( $artist_name, $bSearchUrl );

	        drupal_set_title( $artist_name . " - " . t("Sammenlign priser") );

	        return $sHtml;
        } else {
                $sHtml = "";
                $sHtml .= drupal_get_form('apms_large_form' );
                return $sHtml;
        }
}
function prices_display_artist_intro($artist_name, $nArtistID, $sCurrency, $sLanguage )
{
        $sAlbumLink = "<a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name) ."' title='" . $artist_name ."'>$artist_name</a>";
        if ($sLanguage == 'dk')
        {
                $first_line = "Guide til billigste priser og største udvalg af <b>" . $sAlbumLink . "</b> albums og sange.<br/>Vælg blandt alle musikformater - Mp3, WMA, FLAC, streaming...CD, SACD , vinyl, DVD...<br/>Og sammenlign udvalg, tilbud, priser blandt mere end 50 forskellige pladeforretninger - CDON, Amazon, Stereo Studio, T.P. Musik Marked, Gucca, Megastore... samt musik-/streamingtjenester som Spotify, Grooveshark, WiMP, TDC Play, Napster, Rdio...";
                $second_line = "Musiksøgemaskinen har fundet følgende priser for kunstneren <b>$artist_name</b> albums:";
                $third_line = "Musiksøgemaskinen har fundet følgende priser for kunstneren <b>$artist_name</b> sange:";
        } else {
                $first_line = "Find the best prices for <b>" . $sAlbumLink . "</b> albums and songs som music download, streaming, cd, vinyl...Compare prices from more than 50 Record stores";
                $second_line = "Music Search Engine found deals for the artist <b>$artist_name</b> albums";
                $third_line = "Music Search Engine found deals for the artist <b>$artist_name</b> songs:";
        }
		
		$sHtml .= "<table cellpadding='0' cellspacing='0' class='list-price-intro'><tr><td valign='top'>";
		
        $sHtml .= "<table cellpadding='0' cellspacing='0' class='list-price-intro-left'>";
		$sHtml .= "<tr><td colspan='3'>$first_line</td></tr>";
		if ($nArtistID != '') {
			$sHtml .= "<tr><td colspan='3'>$second_line</td></tr>";

			db_set_active('airplay_music');
			$sQuery = " SELECT MIN(price * from_euro * 0.01) AS price, media_format.media_format_name, currency_name, record_store.record_store_name
					FROM album_intro
					INNER JOIN media_format ON media_format.media_format_id = album_intro.media_format_id 
					INNER JOIN currency ON currency.currency_name = '$sCurrency'
					INNER JOIN record_store ON album_intro.record_store_id = record_store.record_store_id
				WHERE album_intro.artist_id = $nArtistID
				GROUP BY album_intro.media_format_id";

			$aArgs = "";
			$iResStart = 0;
			$iResPerPage = 3;

			$query_result = db_query_range($sQuery, $aArgs, $iResStart, $iResPerPage);

			$aAllRows = array();
			while ( $a = db_fetch_object($query_result) ) {
					$aAllRows[] = $a;
			} 
	  
			foreach ($aAllRows as $a ) {
					$fPrice  = $a->price;
					$fPrice  = airplay_format_price( $fPrice, '$a->currency_name' );
					$sHtml .= "<tr><td>".$a->media_format_name ."</td><td align='left'>".t('from')." ". $fPrice ." ". $a->currency_name . "</td><td>(".$a->record_store_name.")</td></tr>";
			}

			$sHtml .= "<tr><td colspan='3'>$third_line</td></tr>";

			$sQuery = "SELECT MIN(price * from_euro * 0.01) AS price, media_format.media_format_name, currency_name, record_store.record_store_name
				FROM song_intro 
				INNER JOIN artist ON artist.artist_id = song_intro.artist_id 
				INNER JOIN media_format ON media_format.media_format_id = song_intro.media_format_id 
				INNER JOIN currency ON currency.currency_name = '$sCurrency'
				INNER JOIN record_store ON song_intro.record_store_id = record_store.record_store_id
				WHERE song_intro.artist_id = $nArtistID
				GROUP BY song_intro.media_format_id";

			$aArgs = "";
			$iResStart = 0;
			$iResPerPage = 3;

			$query_result = db_query_range($sQuery, $aArgs, $iResStart, $iResPerPage);
		  
			$aAllRows = array();
			while ( $a = db_fetch_object($query_result) ) {
					$aAllRows[] = $a;
			} 
			db_set_active('default');
	  
			foreach ($aAllRows as $a ) {
				$fPrice  = $a->price;
				$fPrice  = airplay_format_price( $fPrice, '$a->currency_name' );
				$sHtml .= "<tr><td width='100px'>".$a->media_format_name ."</td><td align='left' width='100px'>".t('from')." ". $fPrice ." $a->currency_name</td><td>(".$a->record_store_name.")</td></tr>";
			}
		}
        $sHtml .= "</table></td><td valign='top'>";
		$sHtml .= get_artist_info_box($nArtistID, $artist_name);
		

		$sHtml .= "</td></tr></table>";
        return $sHtml;
}

function get_artist_info_box($artist_id, $artist_name)
{
	$sHtml = " ";
	if ($artist_id != '' && $artist_name != '') {
		db_set_active('airplay_music');
		$sQuery = "SELECT artist_real_name, gender, artist_type, year_start, year_end, year_born, year_died, country_name, url_artist_official, url_fanpage, url_wikipedia, url_allmusic, url_musicbrainz, url_discogs FROM info_artist LEFT OUTER JOIN country ON country.country_id = info_artist.country_id WHERE artist_id = $artist_id";

		$query_result = db_query_range($sQuery, "", 0, 1);

		$aAllRows = array();
		while ( $o = db_fetch_object($query_result) ) {
				$aAllRows[] = $o;
		} 
		  
		foreach ($aAllRows as $o ) {
			
			$sHtml .= "<table cellpadding='0' cellspacing='0' class='list-price-intro-right'>";
			$sHtml .= "<tr><td colspan='2' class='header'>WIKI $artist_name</td></tr>";
			
			if ($o->artist_real_name != '') {
				$sHtml .= "<tr><td width='50%' class='label'>Fødenavn:</td><td width='50%'>$o->artist_real_name</td></tr>";
			}
			/*if ($o->gender != '') {
				$sHtml .= "<tr><td class='label'>Køn:</td><td>$o->gender</td></tr>";
			}*/
			if ($o->artist_type == 'G') {
				$sHtml .= "<tr><td>";
				if ($o->year_start != 0) {
					$sHtml .= "<span class='label'>Dannet:</span> $o->year_start";
				} else {
					$sHtml .= "&nbsp;";
				}
				$sHtml .= "</td><td>";
				if ($o->year_end != 0) {
					$sHtml .= "<span class='label'>Opløst:</span> $o->year_end";
				} else {
					$sHtml .= "&nbsp;";
				}
				$sHtml .= "</td></tr>";
			} else if ($o->artist_type == 'P') {
				$sHtml .= "<tr><td>";
				if ($o->year_born != 0) {
					$sHtml .= "<span class='label'>Født:</span> $o->year_born";
				} else {
					$sHtml .= "&nbsp;";
				}
				$sHtml .= "</td><td>";
				if ($o->year_died != 0) {
					$sHtml .= "<span class='label'>Død:</span> $o->year_died";
				} else {
					$sHtml .= "&nbsp;";
				}
				$sHtml .= "</td></tr>";
			}
			if ($o->country_name != '') {
				$sHtml .= "<tr><td class='label'>Hjemland:</td><td>$o->country_name</td></tr>";
			}
			
			$sHtml .= "<tr><td colspan='2'>";
			if ($o->url_artist_official != '') {
				$sHtml .= "<div class='official_link'><a href='$o->url_artist_official' title='Officiel - $artist_name'>Officiel website</a></div>";
			}
			if ($o->url_fanpage != '') {
				$sHtml .= "<div class='fan_link'><a href='$o->url_fanpage' title='Fan - $artist_name'>Fan website</a></div>";
			}
			if ($o->url_wikipedia != '') {
				$sHtml .= "<div class='wikipedia_link'><a href='$o->url_wikipedia' title='Wikipedia - $artist_name'>Wikipedia</a></div>";
			}
			if ($o->url_allmusic != '') {
				$sHtml .= "<div class='allmusic_link'><a href='$o->url_allmusic' title='Allmusic - $artist_name'>Allmusic</a></div>";
			}
			if ($o->url_musicbrainz != '') {
				$sHtml .= "<div class='musicbrainz_link'><a href='$o->url_musicbrainz' title='Musicbrainz - $artist_name'>Musicbrainz</a></div>";
			}
			if ($o->url_discogs != '') {
				$sHtml .= "<div class='discogs_link'><a href='$o->url_discogs' title='Discogs - $artist_name'>Discogs</a></div>";
			}
			$sHtml .= "</td></tr>";
			
			$sHtml .= "</table>";
		}
	}
	return $sHtml;
}

function prices_display_artist_albums( $artist_name, $bSearchUrl )
{
	global $G_DEFAULT_RESULTS_PER_PAGE;
	$iResStart = 0;
	$iResPerPage = $G_DEFAULT_RESULTS_PER_PAGE;
    $nArtistID = "";

	$tUnknown = t('Unknown');
	$currency = ap_user_locale_currency();
	$title_name_year_sort = ap_user_title_name_year_sort();
        $media_format   = ap_user_media_format();
	
	$url = "/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name);
	
	$selectCurrencyHtml         = ap_html_currencySelect        ( $url, $currency );
	$selectTitleNameSortHtml    = ap_html_titleNameSortSelect   ( $url, $title_name_year_sort );
	$selectTitleYearSortHtml    = ap_html_titleYearSortSelect   ( $url, $title_name_year_sort );
	$selectMediaFormatHtml      = ap_html_mediaFormatSelect     ( $url, $media_format );
	
	db_set_active('airplay_music');
	
	$aArgs = array();
	$aArgs[] = $currency;
	$sQuery = airplay_view_buy_artist_album( );
	$aArgs[] = $artist_name;
	$sQuery .= " WHERE artist.artist_name = '%s'";
	if ( $media_format != "" && $media_format != 'ALL' ) {
	    $aArgs[] = $media_format;
	    $sQuery .= " AND media_format_name = '%s'"; 
	}
	
	$sQuery .= " GROUP BY album_simple.album_simple_name";
	
	if ( $title_name_year_sort == "name_ASC" ) {
		$aArgs[] = 'ASC';
		$sQuery .= " ORDER BY album_simple_name %s"; 
	}
	else if ( $title_name_year_sort == "name_DESC" ) {
		$aArgs[] = 'DESC';
		$sQuery .= " ORDER BY album_simple_name %s"; 
	}
	else if ( $title_name_year_sort == "year_ASC" ) {
		$aArgs[] = 'ASC';
		$sQuery .= " ORDER BY album_year %s"; 
	}
	else if ( $title_name_year_sort == "year_DESC" ) {
		$aArgs[] = 'DESC';
		$sQuery .= " ORDER BY album_year %s"; 
	}

	$query_result = db_query_range($sQuery, $aArgs, $iResStart, $iResPerPage);
	
	$aAllRows = array();
	while ( $a = db_fetch_array($query_result) ) {
		$aAllRows[] = $a;
	} 
	$aAllRows = getArtistAlbums($artist_name, $currency, $media_format);
	
    if ( $title_name_year_sort == "name_ASC" ) {
        usort( $aAllRows,'name_ASC' );
    }
    else if ( $title_name_year_sort == "name_DESC" ) {
        usort( $aAllRows,'name_DESC' );
    }
    else if ( $title_name_year_sort == "year_ASC" ) {
        usort( $aAllRows,'year_ASC' );
    }
    else if ( $title_name_year_sort == "year_DESC" ) {
        usort( $aAllRows,'year_DESC' );
    }

	
	db_set_active('default');
	
    // --------------------
	// --- Table header ---
	// --------------------
        $header = array();
        $cell['data'] = t('Album') . "&nbsp;{$selectTitleNameSortHtml}";
        $cell['class'] = 'header-artist-album';
        $header[] = $cell;
        $cell['data'] = t('Year') . "&nbsp;{$selectTitleYearSortHtml}";
        $cell['class'] = 'header-artist-year';
        $header[] = $cell;
        $cell['data'] = t('Price range') . "&nbsp;{$selectCurrencyHtml}";
        $cell['class'] = 'header-artist-price';
        $header[] = $cell;
        $cell['data'] = t('Format') . "&nbsp;{$selectMediaFormatHtml}";
        $cell['class'] = 'header-artist-format';
        $header[] = $cell;
	// ---------------------
	// --- Table content ---
	// ---------------------
 	
 	$bEmptyQuery = true;
	
	foreach ($aAllRows as $a ) {
        $nArtistID = $a["artist_id"];
 		$bEmptyQuery = false;
		$fPriceMin  = $a["price_MIN"];
		$fPriceMax  = $a["price_MAX"];
		$iPriceMin  = airplay_format_price( $fPriceMin, $currency );
		$iPriceMax  = airplay_format_price( $fPriceMax, $currency );
		$sPricesText = $a["album_prices_count"] > 1 ? t('prices') : t('price'); 

        $sViewPricesUrl  = "/" . ap_artist_suburl() . "/" . airplay_name_to_url( $a["artist_name"] );
        $sViewPricesUrl .= "/" . ap_album_suburl() . "/" . airplay_name_to_url( $a["album_simple_name"] );
		$album_year = $a["album_year"] != 0 ? $a["album_year"] : $tUnknown;

	        //$cell['data'] = "<a href='$sViewPricesUrl' title='{$a->album_simple_name}'>{$a->album_simple_name}</a>";
			$cell['data'] = $a['album_simple_name'];
			$cell['class'] = 'list-artist-album';
			$cell_data[] = $cell;
			$cell['data'] = $album_year;
			$cell['class'] = 'list-artist-year';
			$cell_data[] = $cell;
			$cell['data'] = "$iPriceMin - $iPriceMax $currency";
			$cell['class'] = 'list-artist-price';
			$cell_data[] = $cell;
			$cell['data'] = "<a href='$sViewPricesUrl' title='" . $a["album_simple_name"] . "'>" . t('View') . " <b>" . $a["album_prices_count"] . "</b> " . $sPricesText . "</a>";
			$cell['class'] = 'list-artist-format';
			$cell_data[] = $cell;
			$row[] = $cell_data;
			$cell_data = "";
			$cell = "";
	}

	// --- Render Table ---
	$tableHTML = "";

        if ($_SERVER["HTTP_HOST"] == 'www.airplaymusic.dk' || $_SERVER["HTTP_HOST"] == 'airplaymusic.dk.localhost')  {
		 $sHtml .= prices_display_artist_intro( $artist_name, $nArtistID, $currency, 'dk' );
	}
        if ($_SERVER["HTTP_HOST"] == 'www.airplaymusic.co.uk' || $_SERVER["HTTP_HOST"] == 'airplaymusic.co.uk.localhost')  {
        	 $sHtml .= prices_display_artist_intro( $artist_name, $nArtistID, $currency, 'uk' );
	}

        $tableHTML = theme('table', $header, $row, array('class' => 'list-price-table'));
	
	$sHtml .= $tableHTML;
	return $sHtml;
}



function prices_display_artist_songs( $artist_name, $bSearchUrl )
{
        global $G_DEFAULT_RESULTS_PER_PAGE;
        $iResStart = 0;
        $iResPerPage = $G_DEFAULT_RESULTS_PER_PAGE;

        $tUnknown = t('Unknown');
        $currency = ap_user_locale_currency();
        $title_name_year_sort = ap_user_title_name_year_sort();
        $media_format   = ap_user_media_format();

        $url = "/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name);
    
        $selectCurrencyHtml         = ap_html_currencySelect        ( $url, $currency );
        $selectTitleNameSortHtml    = ap_html_titleNameSortSelect   ( $url, $title_name_year_sort );
        $selectTitleYearSortHtml    = ap_html_titleYearSortSelect   ( $url, $title_name_year_sort );
        $selectMediaFormatHtml      = ap_html_mediaFormatSelect     ( $url, $media_format );
    

        db_set_active('airplay_music');

        $aArgs = array();
        $aArgs[] = $currency;
        $sQuery = airplay_view_buy_artist_song( );

        $aArgs[] = $artist_name;
        $sQuery .= " WHERE artist.artist_name = '%s'";

        if ( $media_format != "" && $media_format != 'ALL' ) {
                $aArgs[] = $media_format;
                $sQuery .= " AND media_format_name = '%s'"; 
        }

        $sQuery .= " GROUP BY song_simple.song_simple_name";

        if ( $title_name_year_sort == "name_ASC" ) {
                $aArgs[] = 'ASC';
                $sQuery .= " ORDER BY song_simple_name %s"; 
        }
        else if ( $title_name_year_sort == "name_DESC" ) {
                $aArgs[] = 'DESC';
                $sQuery .= " ORDER BY song_simple_name %s"; 
        }
        else if ( $title_name_year_sort == "year_ASC" ) {
                $aArgs[] = 'ASC';
                $sQuery .= " ORDER BY song_year %s"; 
        }
        else if ( $title_name_year_sort == "year_DESC" ) {
                $aArgs[] = 'DESC';
                $sQuery .= " ORDER BY song_year %s"; 
        }

        $query_result = db_query_range($sQuery, $aArgs, $iResStart, $iResPerPage);

        $aAllRows = array();
        while ( $a = db_fetch_object($query_result) ) {
                $aAllRows[] = $a;
        } 
        db_set_active('default');
    
        // --------------------
        // --- Table header ---
        // --------------------

        $header = array();
        $cell['data'] = t('Song') . "&nbsp;{$selectTitleNameSortHtml}";
        $cell['class'] = 'header-artist-song';
        $header[] = $cell;
        $cell['data'] = t('Year') . "&nbsp;{$selectTitleYearSortHtml}";
        $cell['class'] = 'header-artist-year';
        $header[] = $cell;
        $cell['data'] = t('Price range') . "&nbsp;{$selectCurrencyHtml}";
        $cell['class'] = 'header-artist-price';
        $header[] = $cell;
        $cell['data'] = t('Format') . "&nbsp;{$selectMediaFormatHtml}";
        $cell['class'] = 'header-artist-format';
        $header[] = $cell;

        // ---------------------
        // --- Table content ---
        // ---------------------

        $bEmptyQuery = true;

        foreach ($aAllRows as $a ) {
                $bEmptyQuery = false;
                $fPriceMin  = $a->price_MIN;
                $fPriceMax  = $a->price_MAX;
                $iPriceMin  = airplay_format_price( $fPriceMin, $currency );
                $iPriceMax  = airplay_format_price( $fPriceMax, $currency );
                $sPricesText = $a->song_prices_count > 1 ? t('prices') : t('price'); 

                $sViewPricesUrl  = "/" . ap_artist_suburl() . "/" . airplay_name_to_url($a->artist_name);
                $sViewPricesUrl .= "/" . ap_song_suburl() . "/" . airplay_name_to_url($a->song_simple_name);
                $song_year = $a->song_year != 0 ? $a->song_year : $tUnknown;

                //$cell['data'] = "<a href='$sViewPricesUrl' title='{$a->song_simple_name}'>{$a->song_simple_name}</a>";
				$cell['data'] = "{$a->song_simple_name}";
                $cell['class'] = 'list-artist-song';
                $cell_data[] = $cell;
                $cell['data'] = $song_year;
                $cell['class'] = 'list-artist-year';
                $cell_data[] = $cell;
                $cell['data'] = "$iPriceMin - $iPriceMax $currency";
                $cell['class'] = 'list-artist-price';
                $cell_data[] = $cell;
                $cell['data'] = "<a href='$sViewPricesUrl' title='{$a->song_simple_name}'>" . t('View') . " <b>" . $a->song_prices_count . "</b> " . $sPricesText . "</a>";
                $cell['class'] = 'list-artist-format';
                $cell_data[] = $cell;
                $row[] = $cell_data;
                $cell_data = "";
                $cell = "";
        }

        // --- Render Table ---
        $tableHTML = "";
        $tableHTML = theme('table', $header, $row, array('class' => 'list-price-table'));
        $sHtml .= $tableHTML;

        return $sHtml;
}
