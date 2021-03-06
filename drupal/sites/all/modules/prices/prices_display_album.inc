<?php 
// $Id$

/**
<!-- AddThis Button BEGIN -->
<!-- AddThis Button END -->

\see Table theme function: http://www.group42.ca/theming_101_%E2%80%93_theme_table_function
\see http://api.drupal.org/api/function/theme_table/6 */
function prices_display_album( $album_simple_name, $artist_name="" )
{
	global $G_DEFAULT_RESULTS_PER_PAGE;
	$nAlbumID = "";
	////dfb (" prices_display_album($album_simple_name, $artist_name)" );
	$artist_name_title = " : " . $artist_name;
	if ( $artist_name == "" ) $artist_name_title = "";
        drupal_set_title( $album_simple_name . $artist_name_title  . " - " . t("Sammenlign priser") );

	
	$iResStart = 0;
	$iResPerPage  = $G_DEFAULT_RESULTS_PER_PAGE;
	$currency 	= ap_user_locale_currency();
	$media_format 	= ap_user_media_format();
	
	$o_album = ap_lookup_album_simple_exact( $album_simple_name, $artist_name );
	
        $sAlbumHeader = "";
        if ( $o_album !== false ){
                
                $sArtistLink ="<a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name) .  "' title='" . $artist_name . "'>" . $artist_name . "</a>";
                $url  = "/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name);
                $url .= "/" . ap_album_suburl() . "/" . airplay_name_to_url($album_simple_name);
                $sAlbumHeader = "<table cellpadding='0' cellspacing='0' class='list-price-intro'>";
                $sAlbumHeader .= "<tr>";
                $sAlbumHeader .= "<td><h1 class='ap_item_headline1'>$album_simple_name</h1></td>";
                $sAlbumHeader .= "<td>" . ap_getAddThis() . "</td>";
                $sAlbumHeader .= "</tr>";
                $sAlbumHeader .= "</table>";
        }
        else {
                $url  = "/prices?search_for=album&lookup=" . urlencode($album_simple_name);
                $url .= "&artist_name=" . urlencode($artist_name);
        }

        $selectCurrencyHtml     = ap_html_currencySelect( $url, $currency );
        $selectMediaFormatHtml  = ap_html_mediaFormatSelect ( $url, $media_format );
	
	db_set_active('airplay_music');

	$aArgs = array();
	$aArgs[] = $currency;
	$sQuery = airplay_view_buy_album();
	if ( $album_simple_name != "" ) {
		if ( $artist_name != "" ) 	{
			$aArgs[] = $album_simple_name;
			$aArgs[] = $artist_name;
			$sQuery .= " WHERE album_simple.album_simple_name = '%s' AND artist.artist_name = '%s'";
		}
		else {
			$aArgs[] = $album_simple_name;
			$sQuery .= " WHERE album_simple.album_simple_name = '%s'"; 
		}
	}
 	if ( $media_format != "" && $media_format != 'ALL' ) {
		$aArgs[] = $media_format;
		$sQuery .= " AND media_format_name = '%s'"; 
	}
	
 	$sQuery .= " ORDER BY album_simple_name ASC, price ASC";
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
        $cell['data'] = t('Album');
        $cell['class'] = 'header-album-album';
        $header[] = $cell;
        $cell['data'] = t('Artist');
        $cell['class'] = 'header-album-artist';
        $header[] = $cell;
        $cell['data'] = t('Price') . "&nbsp;{$selectCurrencyHtml}";
        $cell['class'] = 'header-album-price';
        $header[] = $cell;
        $cell['data'] = t('Format') . "&nbsp;{$selectMediaFormatHtml}";
        $cell['class'] = 'header-album-format';
        $header[] = $cell;
        $cell['data'] = t('Buy');
        $cell['class'] = 'header-album-buy-at-url';
        $header[] = $cell;
	// ---------------------
	// --- Table content ---
	// ---------------------
	
	foreach ($aAllRows as $o ) {
                $nAlbumID = $o->album_id;
		$sPrice = airplay_format_price( $o->price, $currency );
		$sPrice ="<div style='float:left' >{$sPrice}&nbsp;{$currency}</div>";
		$sArtistLink ="<a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($o->artist_name) .  "' title='" . $o->artist_name . "'>" . $o->artist_name . "</a>";
        	$buy_at_url = $o->buy_at_url;
		if ($o->use_affiliate == 1) {
			$buy_at_url = ap_replace_affiliate_link($o->buy_at_url, $o->affiliate_link, $o->affiliate_encode_times);
		}
                $cell['data'] = $o->album_name;
                $cell['class'] = 'list-album-album';
                $cell_data[] = $cell;
                $cell['data'] = $sArtistLink;
                $cell['class'] = 'list-album-artist';
                $cell_data[] = $cell;
                $cell['data'] = $sPrice;
                $cell['class'] = 'list-album-price';
                $cell_data[] = $cell;
                $cell['data'] = $o->media_format_name;
                $cell['class'] = 'list-album-format';
                $cell_data[] = $cell;
                if ($o->use_affiliate == 1) {
                        $cell['data'] = "<a href='" . $buy_at_url . "' onClick='_gaq.push([\"_trackEvent\", \"ResultPageAlbums\", \"Click\", \"" . $o->record_store_name . "\"]);' target='_blank' title='" . $o->record_store_name . " - " . $o->album_name . " - " . $o->artist_name . "' rel='nofollow'>" . $o->record_store_name . "</a>";
                } else {
                        $cell['data'] = "<a href='" . $buy_at_url . "' onClick='_gaq.push([\"_trackEvent\", \"ResultPageAlbums\", \"Click\", \"" . $o->record_store_name . "\"]);' target='_blank' title='" . $o->record_store_name . " - " . $o->album_name . " - " . $o->artist_name . "'>" . $o->record_store_name . "</a>";
				}
                $cell['class'] = 'list-album-buy-at-url';
                $cell_data[] = $cell;
                $row[] = $cell_data;
                $cell_data = "";
                $cell = "";
	}
        if ($_SERVER["HTTP_HOST"] == 'www.airplaymusic.dk' || $_SERVER["HTTP_HOST"] == 'airplaymusic.dk.localhost')  {
			$sAlbumHeader .= prices_display_album_intro( $artist_name, $album_simple_name, $nAlbumID, $currency, 'dk');
	}
        if ($_SERVER["HTTP_HOST"] == 'www.airplaymusic.co.uk' || $_SERVER["HTTP_HOST"] == 'airplaymusic.co.uk.localhost')  {
			$sAlbumHeader .= prices_display_album_intro($artist_name, $album_simple_name, $nAlbumID, $currency, 'uk');
	}
	// --- Render Table ---
 	$tableHTML = theme('table', $header, $row, array('class' => 'list-price-table'));

        $sHtml .= ap_autocomplete_offset(25);
        $sHtml .= drupal_get_form('apms_large_form' );
	$sHtml .= $sAlbumHeader;
	$sHtml .= $tableHTML;
	return $sHtml;
}

function prices_display_album_intro( $artist_name, $album_name, $nAlbumID, $sCurrency, $sLanguage )
{
        $sAlbumLink = "<a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name) . "/" . ap_album_suburl() . "/" . airplay_name_to_url($album_name) . "' title='" . $artist_name ." - ". $album_name . "'>" . $album_name . "</a>";
        if ($sLanguage == 'dk')
        {
                $first_line = "Guide til den billigste pris på albummet <b>" . $sAlbumLink. "</b> af kunstneren <b>" . $artist_name . "</b>. Vælg blandt alle musikformater - Mp3, WMA, FLAC, streaming...CD, SACD , vinyl, DVD...";
                $second_line = "Musiksøgemaskinen har sammenlignet mere end 50 forskellige pladeforretninger - CDON, Amazon, Stereo Studio, T.P. Musik Marked, Gucca, Megastore... samt musik-/streamingtjenester som Spotify, Grooveshark, WiMP, TDC Play, Napster, Rdio... for billigste priser på albummet <b>$album_name </b> af kunstneren <b>$artist_name</b>:";
        } else {
                $first_line = "Comparison shopping for the best prices for the album <b>" . $sAlbumLink . "</b> of the artist <b>" . $artist_name . "</b> as music download, streaming, cd, vinyl...";
                $second_line = "Music Search Engine has compared more than 50 Record stores for best deals on the album <b>$album_name</b> of the artist <b>$artist_name</b>:";
         }

        $sHtml = "<table cellpadding='0' cellspacing='0' class='list-price-intro'>";
        $sHtml .= "<tr><td colspan='3'>$first_line</td></tr>";
        $sHtml .= "<tr><td colspan='3'>$second_line</td></tr>";
		if ($nAlbumID != '') {
			db_set_active('airplay_music');
			$sQuery = "SELECT MIN(price * from_euro * 0.01) AS price, media_format.media_format_name, currency.currency_name, record_store.record_store_name
					FROM album_intro
					INNER JOIN media_format ON media_format.media_format_id = album_intro.media_format_id
					INNER JOIN currency ON currency.currency_name = '$sCurrency'
					INNER JOIN record_store ON album_intro.record_store_id = record_store.record_store_id
					WHERE album_intro.album_id = $nAlbumID 
					GROUP BY album_intro.media_format_id";

			$aArgs = "";
			$iResStart = 0;
			$iResPerPage = 3;

			$query_result = db_query_range($sQuery, $aArgs, $iResStart, $iResPerPage);

			$aAllRows = array();

			while ( $a = db_fetch_object($query_result) ) {
					$aAllRows[] = $a;
			} 
			db_set_active('default');
	  
			foreach ($aAllRows as $a ) 
			{
					$fPrice  = $a->price;
					$fPrice  = airplay_format_price( $fPrice, '$a->currency_name' );
					$sHtml .= "<tr><td>".$a->media_format_name ."</td><td align='left'>".t('from')." ". $fPrice ." $a->currency_name</td><td>(".$a->record_store_name.")</td></tr>";
			}
		}
        $sHtml .= "</table>";
        return $sHtml;
}
