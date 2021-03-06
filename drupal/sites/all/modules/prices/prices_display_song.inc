<?php 
// $Id$

/**
\see Table theme function: http://www.group42.ca/theming_101_%E2%80%93_theme_table_function
\see http://api.drupal.org/api/function/theme_table/6 */
function prices_display_song( $song_simple_name, $artist_name="" )
{
        global $G_DEFAULT_RESULTS_PER_PAGE;
        $nSongID = "";

        $artist_name_title = " : " . $artist_name;
        if ( $artist_name == "" ) $artist_name_title = "";
        drupal_set_title( $song_simple_name . $artist_name_title  . " - " . t("Sammenlign priser") );
        ////dfb (" prices_display_song($song_simple_name, $artist_name)" );
    
        $iResStart = 0;
        $iResPerPage    = $G_DEFAULT_RESULTS_PER_PAGE;
        $currency       = ap_user_locale_currency();
        $media_format   = ap_user_media_format();

        $o_song = ap_lookup_song_simple_exact( $song_simple_name, $artist_name );

        $sSongHeader = "";
        $paramsStartChar="?";
        if ( $o_song !== false ){

                $sSongHeader = "<table cellpadding='0' cellspacing='0' class='list-price-intro'>";
                $sSongHeader .= "<tr><td colspan='2'><h1 class='ap_item_headline1'>$song_simple_name</h1>";
                $sSongHeader .= "<td><h2 class=ap_item_headline2 style='float:left' >$sArtistLink</h2></td>";
                $sSongHeader .= "<td>" . ap_getAddThis() . "</td>";
                $sSongHeader .= "</tr>";
                $sSongHeader .= "</table>";
        }
        else {
                $url  = "/prices?search_for=song&lookup=" . urlencode($song_simple_name);
                $url .= "&artist_name=" . urlencode($artist_name);
        }

        $selectCurrencyHtml     = ap_html_currencySelect( $url, $currency );
        $selectMediaFormatHtml  = ap_html_mediaFormatSelect ( $url, $media_format );

        db_set_active('airplay_music');

        $aArgs = array();
        $aArgs[] = $currency;
        $sQuery = airplay_view_buy_song();
        if ( $song_simple_name != "" ) {
                if ( $artist_name != "" )   {
                        $aArgs[] = $song_simple_name;
                        $aArgs[] = $artist_name;
                        $sQuery .= " WHERE song_simple.song_simple_name = '%s' AND artist.artist_name = '%s'";
                }
                else {
                        $aArgs[] = $song_simple_name;
                        $sQuery .= " WHERE song_simple.song_simple_name = '%s'"; 
                }
        }
        if ( $media_format != "" && $media_format != 'ALL' ) {
                $aArgs[] = $media_format;
                $sQuery .= " AND media_format_name = '%s'"; 
        }
    
        $sQuery .= " ORDER BY song_simple_name ASC, price ASC";
        $query_result = db_query_range($sQuery, $aArgs, $iResStart, $iResPerPage);


        $aAllRows = array();
        while ( $o = db_fetch_object($query_result) ) {
                $aAllRows[] = $o;
        } 
        db_set_active('default');

        // --------------------
        // --- Table header ---
        // --------------------

        $header = array();
        $cell['data'] = t('Song');
        $cell['class'] = 'header-song-song';
        $header[] = $cell;
        $cell['data'] = t('Artist');
        $cell['class'] = 'header-song-artist';
        $header[] = $cell;
        $cell['data'] = t('Price') . "&nbsp;{$selectCurrencyHtml}";
        $cell['class'] = 'header-song-price';
        $header[] = $cell;
        $cell['data'] = t('Format') . "&nbsp;{$selectMediaFormatHtml}";
        $cell['class'] = 'header-song-format';
        $header[] = $cell;
        $cell['data'] = t('Buy');
        $cell['class'] = 'header-song-buy-at-url';
        $header[] = $cell;
        // ---------------------
        // --- Table content ---
        // ---------------------

        foreach ($aAllRows as $o ) {
                $nSongID = $o->song_id;
                $sPrice = airplay_format_price( $o->price, $currency );
                $sPrice ="<div style='float:left' >{$sPrice}&nbsp;{$currency}</div>";
                $sArtistLink ="<a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($o->artist_name) .  "' title='" . $o->artist_name . "'>" . $o->artist_name . "</a>";
                $buy_at_url = $o->buy_at_url;
                if ($o->use_affiliate == 1) {
                    $buy_at_url = ap_replace_affiliate_link($o->buy_at_url, $o->affiliate_link, $o->affiliate_encode_times);
                }

                $cell['data'] = $o->song_name;
                $cell['class'] = 'list-song-song';
                $cell_data[] = $cell;
                $cell['data'] = $sArtistLink;
                $cell['class'] = 'list-song-artist';
                $cell_data[] = $cell;
                $cell['data'] = $sPrice;
                $cell['class'] = 'list-song-price';
                $cell_data[] = $cell;
                $cell['data'] = $o->media_format_name;
                $cell['class'] = 'list-song-format';
                $cell_data[] = $cell;
                if ($o->use_affiliate == 1) {
                        $cell['data'] = "<a href='" . $buy_at_url . "' onClick='_gaq.push([\"_trackEvent\", \"ResultPageSongs\", \"Click\", \"" . $o->record_store_name . "\"]);' target='_blank' rel='nofollow' title='" . $o->record_store_name . " - " . $o->artist_name . " - " . $o->song_name . "'>" . $o->record_store_name . "</a>";
                } else {
                         $cell['data'] = "<a href='" . $buy_at_url . "' onClick='_gaq.push([\"_trackEvent\", \"ResultPageSongs\", \"Click\", \"" . $o->record_store_name . "\"]);' target='_blank' title='" . $o->record_store_name . " - " . $o->artist_name . " - " . $o->song_name . "'>" . $o->record_store_name . "</a>";
                }
                $cell['class'] = 'list-song-buy-at-url';
                $cell_data[] = $cell;
                $row[] = $cell_data;
                $cell_data = "";
                $cell = "";
        }

        if ($_SERVER["HTTP_HOST"] == 'www.airplaymusic.dk' || $_SERVER["HTTP_HOST"] == 'airplaymusic.dk.localhost')  {
                $sSongHeader .= prices_display_song_intro( $artist_name,  $song_simple_name, $nSongID, $currency, 'dk');
        }
        if ($_SERVER["HTTP_HOST"] == 'www.airplaymusic.co.uk' || $_SERVER["HTTP_HOST"] == 'airplaymusic.co.uk.localhost')  {
                $sSongHeader .= prices_display_song_intro( $artist_name, $song_simple_name, $nSongID, $currency, 'uk');
        }
        // --- Render Table ---
        $tableHTML = theme('table', $header, $row, array('class' => 'list-price-table'));

        $sHtml .= ap_autocomplete_offset(25);
        $sHtml .= drupal_get_form('apms_large_form' );
        $sHtml .= $sSongHeader;
        $sHtml .= $tableHTML;
        return $sHtml;
}

function prices_display_song_intro( $artist_name, $song_simple_name, $nSongID, $sCurrency, $sLanguage )
{
        $sSongLink = "<a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name) . "/" . ap_song_suburl() . "/" . airplay_name_to_url($song_simple_name) . "' title='" . $artist_name ." - ". $song_simple_name . "'>" . $song_simple_name . "</a>";
        $sArtistLink = "<a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name) . "'  title='". $artist_name ."'>" . $artist_name . "</a>";
        if ($sLanguage == 'dk')
        {
                $first_line = "Find den billigste pris på musik, download, streaming, cd, vinyl... for sangen <b>" . $sSongLink. "</b> af kunstneren <b>" . $sArtistLink . "</b>. Vælg blandt alle musikformater - Mp3, WMA, FLAC, streaming...CD, SACD , vinyl, DVD...";
                $second_line = "Musiksøgemaskinen har sammenlignet mere end 50 forskellige pladeforretninger - CDON, Amazon, Stereo Studio, T.P. Musik Marked, Gucca, Megastore... samt musik-/streamingtjenester som Spotify, Grooveshark, WiMP, TDC Play, Napster, Rdio... for billigste priser på sangen  <b>$song_simple_name</b> af kunstneren <b>$artist_name</b>:";
        } else {
                $first_line = "Find the best prices for music, download, streaming, cd, vinyl...for the song <b>" . $sSongLink. "</b> of the artist <b>" . $sArtistLink . "</b>.";
                $second_line = "Music Search Engine found deals for the song <b>$song_simple_name</b> by the artist <b>" . $artist_name . "</b> songs:";

        }

        $sHtml = "<table cellpadding='0' cellspacing='0' class='list-price-intro'>";
        $sHtml .= "<tr><td colspan='3'>$first_line</td></tr>";
        $sHtml .= "<tr><td colspan='3'>$second_line</td></tr>";
		if ($nSongID != "") {
			db_set_active('airplay_music');
			$sQuery = "SELECT (price * from_euro * 0.01) AS price, media_format.media_format_name, currency.currency_name, record_store_name
					FROM song_intro 
				INNER JOIN media_format ON media_format.media_format_id = song_intro.media_format_id 
					INNER JOIN currency ON currency.currency_name = '$sCurrency'
					INNER JOIN record_store ON song_intro.record_store_id = record_store.record_store_id
					WHERE song_intro.song_id = $nSongID
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
					$sHtml .= "<tr><td>".$a->media_format_name ."</td><td align='left'>".t('from')." ". $fPrice ." $a->currency_name</td><td>(".$a->record_store_name.")</td></tr>";
			}
		}
        $sHtml .= "</table>";

        return $sHtml;
}

