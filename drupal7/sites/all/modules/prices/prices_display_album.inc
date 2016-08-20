<?php 
// $Id$

/**
\see Table theme function: http://www.group42.ca/theming_101_%E2%80%93_theme_table_function
\see http://api.drupal.org/api/function/theme_table/6 */
function prices_display_album( $album_simple_name, $artist_name="" )
{
	$nAlbumID = "";
    $sHtml = "";
	$artist_url = "";
	$release_date = "0000-00-00";

	$sPageTitle = t("{artist_name} - {album_name} - Compare prices Vinyl, CD, MP3, FLAC, Streaming");
    $languageCode = ap_language_code();
    $to_frontpage_text = t("To Frontpage");
    
	drupal_set_title( str_replace("{album_name}", $album_simple_name, str_replace("{artist_name}", $artist_name, $sPageTitle)) );

	$currency 	        	= ap_user_locale_currency();
	$media_format 	    = ap_user_media_format();
	$artist_id          		= artistIdFromName( $artist_name );
	$album_simple_id   = itemSimpleIdFromNameExact( $artist_id, "album", $album_simple_name );

    // Get 404 status code for Search engines if needed
    get404StatusCodeForLowNumberOfPrices($artist_id, $album_simple_id, "album");
    
    $sAlbumHeader = "";
    if ( $album_simple_id ){
        $sArtistLink = "<a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name) .  "' title='" . $artist_name . "'>" . $artist_name . "</a>";
        $url  = "/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name);
		$artist_url = $url;
        $url .= "/" . ap_album_suburl() . "/" . airplay_name_to_url($album_simple_name);
        $sAlbumHeader = "<table cellpadding='0' cellspacing='0' class='list-price-intro'>";
        $sAlbumHeader .= "<tr><td colspan='2'><div itemscope='' itemtype='http://data-vocabulary.org/Breadcrumb' style='float:left;'><a href='/' itemprop='url'><span itemprop='title'>$to_frontpage_text</span></a></div><div style='float:left;margin:0px 5px 0px 5px;'> &gt; </div><div itemscope='' itemtype='http://data-vocabulary.org/Breadcrumb' style='float:left;'><a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name) .  "' itemprop='url'><span itemprop='title'>$artist_name</span></a></div><div style='float:left;margin:0px 5px 0px 5px;'> &gt; </div><div itemscope='' itemtype='http://data-vocabulary.org/Breadcrumb'><span itemprop='title'>$album_simple_name</span></div></td></tr>";
        $sAlbumHeader .= "<tr>";
        $sAlbumHeader .= "<td><h1 class='ap_item_headline1'>{$artist_name}:  {$album_simple_name}</h1></td>";
        $sAlbumHeader .= "<td id='addthis'>";

        if ($languageCode == "da") { $sAlbumHeader .= ap_getAddThis(); } else { $sAlbumHeader .= "&nbsp;"; }
        $sAlbumHeader .= "</td>";
        $sAlbumHeader .= "</tr><tr><td colspan='2'>&nbsp;</td></tr>";
        $sAlbumHeader .= "</table>";
    }
    else {
        $url  = "/prices?search_for=album&lookup=" . urlencode($album_simple_name);
        $url .= "&artist_name=" . urlencode($artist_name);
    }

    //$selectCurrencyHtml     = ap_html_currencySelect( $url, $currency );
    //$selectMediaFormatHtml  = ap_html_mediaFormatSelect ( $url, $media_format );
	if ($artist_id != 0 && $album_simple_id != 0) {
		$aAllRows = getBuyItems_albums($artist_id, $album_simple_id, $currency, $media_format);
        //$selectMediaFormatHtml  = ap_html_mediaFormatLinks     ( $url, $media_format, $aAllRows, null );
        $selectMediaFormatHtml = ap_html_mediaFormatLinksAsDropDown ( $url, $media_format, $aAllRows, null, ".list-price-table" );
        //$streamingCountHtml     = ap_html_streamingcountAsDropDown(".list-price-table", $languageCode);

        if (count($aAllRows) > 0) {
            $artist_name = $aAllRows[0]['artist_name'];
            $sArtistLink = "<a href=\"/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name) .  "\" title=\"" . $artist_name . "\">" . $artist_name . "</a>";
        }
		// --------------------
		// --- Table header ---
		// --------------------
		$header = array();
		$cell['data'] = t('Album') . "<br/>" . $sArtistLink;
		$cell['class'] = 'header-album-album';
		$header[] = $cell;
		$cell['data'] = $selectMediaFormatHtml;
		$cell['class'] = 'header-album-price-format';
		$header[] = $cell;
		if ($media_format == "ALL") {
			$cell['data'] = "<div class='header'>&nbsp;</div><div style='float:left;line-height:15px;'><a href='javascript:void(0);' onClick='ap_ShowStreamingAnimation(\"item_page\");'>" . t("Compare Streaming services") . "</a></div>";
		} else {
				$cell['data'] = "";
		}
		$cell['class'] = 'header-album-buy-at-url';
		$header[] = $cell;
        $cell['data'] = "";
		$cell['class'] = 'header-album-price-value';
		$header[] = $cell;
        
		// ---------------------
		// --- Table content ---
		// ---------------------
		
        $aAllRows = ap_limit_results_by_media_format($aAllRows, $media_format);
        
		$rows = array();
		foreach ($aAllRows as $a ) {

			$nAlbumID = $a['item_base_id'];
			$release_date = $a['release_date'];
            $media_format_name = $a['media_format_name'];
            if ($a['item_used'] == 1) {
                $item_grading_cover = $a['item_grading_cover'];
                $item_grading = $a['item_grading'];
                if ($item_grading == "" && $item_grading_cover == "")
                    $media_format_name .= " " . t("(used)");
                else {
                    $media_format_name .= " (" . t("used");
                    if ($item_grading != "" && $item_grading_cover != "") {
                        $media_format_name .= " " . $item_grading . "/" . $item_grading_cover;
                    } else {
                        $media_format_name .= " " . $item_grading . $item_grading_cover;
                    }
                    $media_format_name .= ")";
                }
            }
			$nPrice = airplay_format_price( $a['price_local'], $currency );
            //$sPrice = "{$nPrice}&nbsp;{$currency}";
			$sPrice = $nPrice;
			$buy_at_url = $a['buy_at_url'];
			if ($a['use_affiliate'] == 1) {
				$buy_at_url = ap_replace_affiliate_link($a['buy_at_url'], $a['affiliate_link'], $a['affiliate_encode_times']);
			}
			$cell['data'] = $a['item_price_name'];
			$cell['class'] = 'list-album-album';
			$cell_data[] = $cell;
			$sPriceText = "<div class='text'>" . $media_format_name . "</div><div class='price'>" . $sPrice;
			if ($languageCode == "da" && $a["freight_price_da"] != "" && $media_format_name != "MP3" && $media_format_name != "FLAC" && $media_format_name != "WMA") {
					$sPriceText .= "<span class='freight'><img src='http://static.airplay-music.dk/images/site/information_alert.png' border='0' title='" . $a["freight_price_da"] . "' height='12' width='12' align='middle' /></span>";
			} else if ($languageCode == "en" && $a["freight_price_en"] != "" && $media_format_name != "MP3" && $media_format_name != "FLAC" && $media_format_name != "WMA") {
					$sPriceText .= "<span class='freight'><img src='http://static.airplay-music.dk/images/site/information_alert.png' border='0' title='" . $a["freight_price_en"] . "' height='12' width='12' align='middle' /></span>";
			}
			$sPriceText .= "</div>";
            $cell['data'] = $sPriceText;
            $cell['class'] = 'list-album-price-format';
            $cell_data[] = $cell;

			if ($a['use_affiliate'] == 1) {
				//onClick='_gaq.push([\"_trackEvent\", \"ResultPageAlbums\", \"Click\", \"" . $a['record_store_name'] . "\"]);'
				$google_events = "ga('send', 'event', 'ResultPageAlbums', 'Click', '" . $a['record_store_name'] . "');";
                $buy_at_url = "<a href='{$buy_at_url}' onClick=\"{$google_events}\" target='_blank' title='" . $a['record_store_name'] . " - " . $a['item_base_name'] . " - " . $a['artist_name'] . "' rel='nofollow'>" . $a['record_store_name'] . "</a>";
                $cell['data'] = $buy_at_url;
			} else {
				//onClick='_gaq.push([\"_trackEvent\", \"ResultPageAlbums\", \"Click\", \"" . $a['record_store_name'] . "\"]);'
				$google_events = "ga('send', 'event', 'ResultPageAlbums', 'Click', '" . $a['record_store_name'] . "');";
				$buy_at_url = "<a href='{$buy_at_url}' onClick=\"{$google_events}\" target='_blank' title='" . $a['record_store_name'] . " - " . $a['item_base_name'] . " - " . $a['artist_name'] . "'>" . $a['record_store_name'] . "</a>";
                $cell['data'] = $buy_at_url;
            }
			$cell['class'] = 'list-album-buy-at-url';
			$cell_data[] = $cell;
            
            $cell['data'] = str_replace(".", "", str_replace(",", "", $nPrice));
			$cell['class'] = 'list-album-price-value';
			$cell_data[] = $cell;
            
			$rows[] = $cell_data;
			$cell_data = "";
			$cell = "";
		}
		
		/* If no results - make empty tbody */
		if (count($aAllRows) == 0) {
			$cell['data'] = "";
			$cell['class'] = 'list-album-album';
			$cell_data[] = $cell;
			$cell['data'] = "";
			$cell['class'] = 'list-album-price-format';
			$cell_data[] = $cell;
			$cell['data'] = "";
			$cell['class'] = 'list-album-buy-at-url';
			$cell_data[] = $cell;
            $cell['data'] = 0;
			$cell['class'] = 'list-album-price-value';
			$cell_data[] = $cell;
			$rows[] = $cell_data;
			$cell_data = "";
			$cell = "";
		}

		$sAlbumHeader .= prices_display_album_intro( $artist_id, $artist_name, $album_simple_name, $nAlbumID, $album_simple_id, $currency, $languageCode, $artist_url, $url, $release_date );
		
        // --- Render Table ---
		$tableHTML = theme('table', array( 'header' => $header,  'rows' => $rows,  'attributes' => array('class' => array('list-price-table'))));

		$sHtml .= $sAlbumHeader;
        
        $sHtml .= "<div id='tabs'>";

		// Only show agent for all users on DK site
		if (ap_agent_is_user_logged_in() == true && ap_language_code() == "da") {
		//$sHtml .= "<div id='price_agent_link_container'><div><a href='javascript:void(0);' onClick='ap_price_agent_create_album_agent({$artist_id}, {$album_simple_id});' class='link'>AGENT</a></div><div id='price_agent_container'>&nbsp;</div></div>";
		$sHtml .= "<div id='price_agent_link_container'><div class='icon'>&nbsp;</div><a href='javascript:void(0);' title='Opret Album agent' onClick='ap_price_agent_create_album_agent({$artist_id}, {$album_simple_id});' class='link'>Agent</a></div>";
		$sHtml .= "<div id='price_agent_container'>&nbsp;</div>";
		$sHtml .=
<<<SCRIPT
<script type="text/javascript">
	jQuery('#price_agent_link_container a.link,#price_agent_container').click(function(event) { jQuery('#price_agent_container').show(); event.stopPropagation(); }); 
	jQuery("html").click(function() { jQuery("#price_agent_container").hide(); });
</script>
SCRIPT;
		} else if (ap_agent_is_user_logged_in() != true && ap_language_code() == "da") {
			$sHtml .= ap_agent_user_not_logged_in_html();
		}
		if ($languageCode == "da") {
			$sHtml .= "<div class='info_box_agent' onClick='ap_price_agent_create_album_agent({$artist_id}, {$album_simple_id});jQuery(this).hide();'><div class='content'><strong>Opret Album Agent</strong><br>for {$album_simple_name} og få besked når billigere pris, andet musikformat ... findes</div></div>";
		}		
		$sHtml .= "<div id='tabs_currency'>"  .  ap_html_currencySelectTabs ( $url, $currency, "album" ) . "</div>";
		
		$sHtml .= "</div>";
		
		$sHtml .= $tableHTML;
        /* Show streaming */
        if ($media_format == "ALL") {
            $sHtml .= getCurrencyFromToJSArrays();
            $sHtml .= getDynamicMusicLookupAlbum($artist_name, $album_simple_name, $languageCode, $artist_id, $album_simple_id, "streaming");
            $sHtml .= getDynamicMusicLookupAlbum($artist_name, $album_simple_name, $languageCode, $artist_id, $album_simple_id, "MP3");
        } else if ($media_format == "MP3") {
            $sHtml .= getCurrencyFromToJSArrays();
            $sHtml .= getDynamicMusicLookupAlbum($artist_name, $album_simple_name, $languageCode, $artist_id, $album_simple_id, "MP3");
        }
	}
	// Hide streaming icons if media format is not ALL
	if ($media_format != "ALL") {
$sHtml .=
<<<SCRIPT
    <script type='text/javascript'>ap_HideStreamingAnimation('item_page');</script>
SCRIPT;
	}
	return $sHtml;
}

function prices_display_album_intro( $artist_id, $artist_name, $album_name, $nAlbumID, $album_simple_id, $currency, $languageCode, $artist_url, $url, $release_date )
{
    $sHtml  = "";
	$sImage = "";
    $reviews_wiki_html = "";
	$reviews_intro_text = "";
	$image_url = "";
	$image_width = 0;
	$image_height = 0;
	$intro_tracklist_class = "tracklist_full"; // Variables to test if we need to output element with or without full width.
	$intro_text_class = "text_full"; // Variables to test if we need to output element with or without full width.
    //$aMinPricesperFormat_albums = getMinPricesPerFormat_albums($album_simple_id, $currency );

	$media_format 	    = ap_user_media_format();
	//print $media_format;
	$aCoverImage_albums = getCoverImageInfo_albums($album_simple_id);
	foreach ($aCoverImage_albums as $a ) {
		$image_url = $a["image_url"];
		$image_width = $a["image_width"];
		$image_height = $a["image_height"];
	}
    
    $sBaseDesc      = lookupDescription_album ( $artist_id, $album_simple_id, $languageCode );
    $sDescription    = getDescription_album    ( $sBaseDesc, array($artist_name, $artist_url, $album_name, $url) );

	// Get tracklist
	$tracklist = lookupTracklist_album($album_simple_id);
	// Get HTML for tracklist - empty if not list.
	$tracklist_html = getTracklistHTML($tracklist, $artist_name, $album_name);

	$reviews_data = lookupReview_data ( $album_simple_id, $languageCode );
	$reviews_wiki_html = lookupReview_wiki_html ( $reviews_data );
	
    $sHtml .= "<table cellpadding='0' cellspacing='0' class='list-price-intro'>";
    $sHtml .= "<tr>";

	// Item have cover image
	$sHtml .= "<td valign='top' id='album_text'>";
	if ($image_url != "") {
		$sHtml .= "<div class='cover_image'><img src='{$image_url}' border='0' width='{$image_width}' height='{$image_height}' alt='{$artist_name} {$album_name}' title='{$artist_name} {$album_name}' /></div>";
		$intro_tracklist_class = "tracklist"; // tracklist does not need to be full width.
	}
	if ($tracklist_html != "") {
		$sHtml .= "<div class='{$intro_tracklist_class}'>{$tracklist_html}</div>";
		if ($image_url != "") {
			$intro_text_class = "text";
		}
	} else {
		$intro_text_class = "text";
	}
	// Test full width output
	if (($image_url != "" && $tracklist_html != "") || ($image_url == "" && $tracklist_html == "")) {
		$intro_text_class = "text_full";	
	}
	$sHtml .= "<div class='{$intro_text_class}'>{$sDescription}</div>";
	$sHtml .= "</td>";
	
	$sHtml .= "<td valign='top' id='album_wiki'>";
    $sHtml .= "<div class='wiki'>";
    // Reviews tab
    $sHtml .= "<table cellspacing='0' cellpadding='0' class='list-price-intro-right'>";
    $sHtml .= "<tr><td class='header'><h3>". substr($album_name, 0 , 28) . "</h3></td></tr>";
	if ($release_date != "" && $release_date != "0000-00-00") {
        $sHtml .= "<tr><td><span class='label'>" . t("Release date:") . "</span> " . ap_format_date($release_date, $languageCode) . "</td></tr>";
    }
    if ($reviews_wiki_html != "") {
        $sHtml .= "<tr><td>" . $reviews_wiki_html . "</td></tr>";
    }
    $sHtml .= "</table>";
    $sHtml .= "</div>";
    // album tab
    $sHtml .= "<div class='albums'>";
    $sHtml .= ap_html_streamingcountAsDropDown("#album_wiki div.albums", $languageCode);;
    $sHtml .= "</div>";
    
    $sHtml .= "</td></tr>";
    /*if ( count($aMinPricesperFormat_albums) > 0 ) {
        foreach ( $aMinPricesperFormat_albums as $media_format_name => $a ) {
            $fPrice  = $a['price_MIN'];
            $fPrice  = airplay_format_price( $fPrice, $currency );
            $sHtml .= "<tr style='display:none;'><td>$media_format_name</td><td align='left'>" . t('from') . " $fPrice $currency</td><td>{$a['record_store_name']}</td></tr>";
        }
    }*/
    $sHtml .= "</table>";
	
	if ($languageCode == "en") {
		$jsArray = 'var aStreamingToComplete = [["spotify", 0, false], ["rdio", 0, false], ["deezer", 0, false], ["napster", 0, false]];';
	} else {
		$jsArray = 'var aStreamingToComplete = [["spotify", 0, false], ["rdio", 0, false], ["deezer", 0, false], ["napster", 0, false], ["wimp", 0, false]];';
	}
$sHtml .=
<<<SCRIPT
    <script type='text/javascript'>
        $jsArray
    </script>
SCRIPT;
	
    return $sHtml;
}

