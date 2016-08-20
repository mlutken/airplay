<?php 
// $Id$
/**
\see Table theme function: http://www.group42.ca/theming_101_%E2%80%93_theme_table_function
\see http://api.drupal.org/api/function/theme_table/6 

*/
function prices_display_artist( $artist_name, $bSearchUrl )
{
    $sHtml = "";
    $sForm = "";
    $albums_show = 0;
    $songs_show = 0;
    $merchandise_show = 0;
    $concert_show = 0;
    
    $product = "album";
    $product_album_style = "style='display:block;'";
    $product_song_style = "style='display:none;'";
    $product_merchandise_style = "style='display:none;'";
    $product_concert_style = "style='display:none;'";

    if (!isset($_GET["product"])) {
        $product = "album";
    } else if (isset($_GET["product"]) && $_GET["product"] == "song") {
        $product = "song";
        $product_album_style = "style='display:none;'";
        $product_song_style = "style='display:block;'";
        $product_merchandise_style = "style='display:none;'";
        $product_concert_style = "style='display:none;'";
    } else if (isset($_GET["product"]) && $_GET["product"] == "merchandise") {
        $product = "merchandise";
        $product_album_style = "style='display:none;'";
        $product_song_style = "style='display:none;'";
        $product_concert_style = "style='display:none;'";
        $product_merchandise_style = "style='display:block;'";
    } else if (isset($_GET["product"]) && $_GET["product"] == "concert") {
        $product = "concert";
        $product_album_style = "style='display:none;'";
        $product_song_style = "style='display:none;'";
        $product_merchandise_style = "style='display:none;'";
        $product_concert_style = "style='display:block;'";
    }

    $to_frontpage_text = t("To Frontpage");
	$sPageTitle = t("{artist_name} - Compare prices Vinyl, CD, MP3, Streaming, Concert");
    $languageCode = ap_language_code();
    if ($artist_name != "") {
        drupal_set_title( str_replace("{artist_name}", $artist_name, $sPageTitle) );

        $artist_id = artistIdFromName($artist_name);
 
        // Get 404 status code for Search engines if needed
        get404StatusCodeForLowNumberOfPrices($artist_id, 0, "artist");
        
        /* Find the number of items - for the tabs to show. */ 
        $item_type_count = getItemTypeCountFromArtist ($artist_id);
		// Concert specific - for getting future concert and not just concerts.
		$item_type_concert_count = getItemTypeCountFromArtistByType($artist_id, 4);
		
        /*if (array_key_exists ('item_album_type', $item_type_count) && $item_type_count["item_album_type"]["item_type_count"] > 0) {
            $albums_show = 1;
        } else {
            $product_album_style = "style='display:none;'";
        }
        if (array_key_exists ('item_song_type', $item_type_count) && $item_type_count["item_song_type"]["item_type_count"] > 0) {
            $songs_show = 1;
        } else {
            $product_song_style = "style='display:none;'";
        }*/
        /*if (array_key_exists ('item_concert_type', $item_type_count) && $item_type_count["item_concert_type"]["item_type_count"] > 0) {
            $concert_show = 1;
        }*/

        if (array_key_exists ('item_merchandise_type', $item_type_count) && $item_type_count["item_merchandise_type"]["item_type_count"] > 0) {
            $merchandise_show = 1;
        }
		if ($item_type_concert_count > 0) {
            $concert_show = 1;
        }
        
		if ($artist_id != 0) {
            //debug("artist_id:" . $artist_id);
            $sArtistHeader = "<table cellpadding='0' cellspacing='0' class='list-price-intro'>";
            $sArtistHeader .= "<tr><td colspan='2'><div itemscope='' itemtype='http://data-vocabulary.org/Breadcrumb' style='float:left;'><a href='/' itemprop='url'><span itemprop='title'>$to_frontpage_text</span></a></div><div style='float:left;margin:0px 5px 0px 5px;'> &gt; </div><div itemscope='' itemtype='http://data-vocabulary.org/Breadcrumb'><span itemprop='title'>$artist_name</span></div></td></tr>";
            $sArtistHeader .= "<tr><td><h1 class='ap_item_headline1'>$artist_name</h1></td>";
			$sArtistHeader .= "<td id='addthis'>";
            if ($languageCode == "da") { $sArtistHeader .= ap_getAddThis(); } else { $sArtistHeader .= "&nbsp;"; }
            $sArtistHeader .= "</td>";
			$sArtistHeader .= "</tr><tr><td colspan='2'>&nbsp;</td></tr></table>";
		
			$currency = ap_user_locale_currency();

			$sHtml .= $sArtistHeader;

			$sHtml .= prices_display_artist_intro( $artist_name, $artist_id, $currency, $languageCode, $item_type_count, $item_type_concert_count );

            $sHtml .= prices_display_product_tabs( $artist_id, $artist_name, $currency, $product, $albums_show, $songs_show, $merchandise_show, $concert_show );

            $sHtml .= "<div id='product_albums' $product_album_style>" . prices_displayArtist_albums( $artist_id, $artist_name, $bSearchUrl, "album" ) ."</div>";

			$sHtml .= "<div id='product_songs' $product_song_style>" . prices_displayArtist_songs( $artist_id, $artist_name, $bSearchUrl, "song" ) ."</div>";
            
            $sHtml .= "<div id='product_merchandise' $product_merchandise_style>" . prices_displayArtist_merchandise( $artist_id, $artist_name, $bSearchUrl, "merchandise" ) ."</div>";
            
            $sHtml .= "<div id='product_concert' $product_concert_style>" . prices_displayArtist_concert( $artist_id, $artist_name, $bSearchUrl, "concert" ) ."</div>";

			$sHtml .= addArtistImage( 'artistImageID', $artist_name );
		}
    } else {
        $sHtml = "";
    }
    return $sHtml;
}

function prices_display_product_tabs($artist_id, $artist_name, $currency, $product, $albums_show, $songs_show, $merchandise_show, $concert_show) {
    
    $search_for_word_in_title = t("Search for word in title");
    $media_format   = ap_user_media_format();
    $extra_function = "";
    $url = "/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name);
	$languageCode = ap_language_code();
	
    $sHtml = "";
    $sHtml .= "<div id='tabs'>";
    $sHtml .= "<div id='search_title_album_container'><input type='text' value='$search_for_word_in_title' class='search_title_album'></div>";
    $sHtml .= "<div id='search_title_song_container'><input type='text' value='$search_for_word_in_title' class='search_title_song'></div>";
    $sHtml .= "<div id='search_title_merchandise_container'><input type='text' value='$search_for_word_in_title' class='search_title_merchandise'></div>";
    $sHtml .= "<div id='product_tabs_album' onClick='ap_select_album_tab();'";
    /*if ($albums_show == 0) {
        $sHtml .= " style='display:none;'";
    }*/
    if ($product == "album") {
        $sHtml .= " class='selected'";
    } else {
		$sHtml .= " class='deselected'";
	}
    $sHtml .= "><h3><a href='javascript:void(0);'>" . t('Album') . "</a></h3></div>";
    //$sHtml .= " ><a href='$url'>" . t('Album') . "</a></div>";
    
    $sHtml .= "<div id='product_tabs_song'";
    /*if ($songs_show == 0) {
        $sHtml .= " style='display:none;'";
    }*/
    if ($product == "song") {
        $sHtml .= " class='selected'";
    } else {
		$sHtml .= " class='deselected'";
	}
    if ($media_format == "ALL" || $media_format == "MP3") { $extra_function .= "ap_getDynamicMusicLookupArtist();"; }
	if ($media_format != "ALL") { $extra_function .= "ap_HideStreamingAnimation(\"song\");"; } // Hide streaming icons if media format is not ALL
    $sHtml .= "  onClick='ap_select_song_tab();{$extra_function}'><h3><a href='javascript:void(0);'>" . t('Song') . "</a></h3></div>";
    
    $sHtml .= "<div id='product_tabs_merchandise'";
    if ($merchandise_show == 0) {
        $sHtml .= " style='display:none;'";
    }
    if ($product == "merchandise") {
        $sHtml .= " class='selected'";
    } else {
		$sHtml .= " class='deselected'";
	}
    $sHtml .= "  onClick='ap_select_merchandise_tab();'><h3><a href='javascript:void(0);'>" . t('Merchandise') . "</a></h3></div>";
    
    $sHtml .= "<div id='product_tabs_concert'";
    if ($concert_show == 0) {
        $sHtml .= " style='display:none;'";
    }
    if ($product == "concert") {
        $sHtml .= " class='selected'";
    } else {
		$sHtml .= " class='deselected'";
	}
    $sHtml .= "  onClick='ap_select_concert_tab();'><h3><a href='javascript:void(0);'>" . t('Concert') . "</a></h3></div>";

	// Show Agent link for all users on DK site
	if (ap_agent_is_user_logged_in() == true && ap_language_code() == "da") {
	$sHtml .= "<div id='price_agent_link_container'><div class='icon'>&nbsp;</div><a href='javascript:void(0);' title='Opret Koncert agent' onClick='ap_price_agent_create_concert_agent({$artist_id});' class='link'>Agent</a></div>";
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
		$sHtml .= "<div class='info_box_agent' onClick='ap_price_agent_create_concert_agent({$artist_id});jQuery(this).hide();'><div class='content'><strong>Opret Koncert Agent</strong><br>for {$artist_name} og<br>få besked om nye koncerter</div></div>";
	}
	$sHtml .= "<div id='tabs_currency'>" . ap_html_currencySelectTabs ( $url, $currency, $product ) . "</div>";
	
    $sHtml .= "</div>";

    if ($product == "song") {
        $sHtml .= "<script type='text/javascript'>jQuery(document).ready(function() {ap_getDynamicMusicLookupArtist();});</script>";
    } 
	if ($product == "concert") {
		$sHtml .= "<script type='text/javascript'>jQuery(document).ready(function() {ap_select_concert_tab();});</script>";
	}
    
    return $sHtml;
}


function prices_display_artist_intro($artist_name, $artist_id, $sCurrency, $languageCode, $item_type_count, $item_type_concert_count )
{
	$sHtml  = "";
    $html_album_count = "";
    $html_song_count = "";
    $html_merchandise_count = "";
	$html_concert_count = "";

	if ($languageCode == "da" && isBotIP() == false) {
		$video = lookupArtistVideo_data($artist_id);
		if (count($video)) {
			$s_video_Html = $video["embed_html"];
		} else {
			$s_video_Html = ap_getArtistEmbedVideoHTML($artist_name);
			insertArtistVideo_data($artist_id, $s_video_Html);
		}
	} else {
		$s_video_Html = "";
	}
	
    if (array_key_exists ('item_album_type', $item_type_count) && $item_type_count["item_album_type"]["item_type_count"] > 0) {
        $html_album_count = str_replace("[COUNT]", $item_type_count["item_album_type"]["item_type_count"], t("<b>Albums:</b> total [COUNT] prices."));
    }
    if (array_key_exists ('item_song_type', $item_type_count) && $item_type_count["item_song_type"]["item_type_count"] > 0) {
        $html_song_count = str_replace("[COUNT]", $item_type_count["item_song_type"]["item_type_count"], t("<b>Songs:</b> total [COUNT] prices."));
    }
    if (array_key_exists ('item_merchandise_type', $item_type_count) && $item_type_count["item_merchandise_type"]["item_type_count"] > 0) {
        $html_merchandise_count = str_replace("[COUNT]", $item_type_count["item_merchandise_type"]["item_type_count"], t("<b>Merchandise:</b> total [COUNT] prices."));
    }
	if ($item_type_concert_count > 0) {
        $html_concert_count = str_replace("[COUNT]", $item_type_concert_count,  t("<b>Concert:</b> total [COUNT] prices."));
    }

    $sBaseDesc      = lookupDescriptionArtist     ( $artist_id, $languageCode );
    $sDescription   = getDescriptionArtist        ( $sBaseDesc, array($artist_name) );
  
    $sHtml .= "<table cellpadding='0' cellspacing='0' class='list-price-intro'><tr><td valign='top'>";
    $sHtml .= "<table cellpadding='0' cellspacing='0' class='list-price-intro-left'>";
    $sHtml .= "<tr><td valign='top' id='artist_image'>" . get_artist_image($artist_name) . "</td>";
    $sHtml .= "<td valign='top' id='artist_text'>";
    if (strlen($sDescription) > 650) {
        $sDescription_short = getSubstringWithEndingHTMLTags($sDescription, 650);
        $sHtml .= "<div id='intro_text_less'>" . $sDescription_short . "...  <a class='show_more_link' href='javascript:void(0);' onClick='jQuery(\"#intro_text_less\").toggle();jQuery(\"#intro_text_more\").toggle();'>" . t('read more') . "</a></div>";
        $sHtml .= "<div id='intro_text_more' style='display:none;'>" . $sDescription . "  <a class='show_more_link' href='javascript:void(0);' onClick='jQuery(\"#intro_text_less\").toggle();jQuery(\"#intro_text_more\").toggle();'>" . t('hide intro text') . "</a></div>";
    } else {
        $sHtml .= "<div id='intro_text_more'>" . $sDescription . "</div>";
    }
    $sHtml .= "<div id='intro_text_item_type_text'>" . $html_album_count . " " . $html_song_count . " " . $html_merchandise_count . " " . $html_concert_count . "</div>";
    
    $sHtml .= "</td><td valign='top' id='artist_wiki'>";
	if ($s_video_Html != "") {
		$sHtml .= "<div class='tabs'>";
		$sHtml .= "<div id='tab_wiki' onClick='ap_ShowWikiFromTab(\"album\");'>" . ("Wiki") . "</div>";
		$sHtml .= "<div id='tab_video' onClick='ap_ShowVideo(\"album\");'>" . ("Video") . "</div>";
		$sHtml .= "<div id='tab_albums' class='selected' onClick='ap_ShowStreamingAnimation(\"album\");'>" . ("Streaming") . "</div>";
		$sHtml .= "</div>";
	}
    $sHtml .= "<div class='wiki'>";
	if ($s_video_Html != "") {
		$sHtml .= get_artist_info_box($artist_id, $artist_name, $languageCode, false);
	} else {
		$sHtml .= get_artist_info_box($artist_id, $artist_name, $languageCode, true);
	}
	$sHtml .= "</div>";

	if ($s_video_Html != "") {
		$sHtml .= "<div class='video'>";
		$sHtml .= $s_video_Html;
		$sHtml .= "</div>";
    }
	
    $sHtml .= "<div class='albums'>";
    $sHtml .= ap_html_streamingcountAsDropDown("#artist_wiki div.albums", $languageCode);;
    $sHtml .= "</div>";
    $sHtml .= "<div class='songs'>";
    $sHtml .= ap_html_streamingcountAsDropDown("#artist_wiki div.songs", $languageCode);;
    $sHtml .= "</div>";
    $sHtml .= "</td></tr>";
    $sHtml .= "</table>";
    $sHtml .= "</td></tr></table>";
    return $sHtml;
}

function get_artist_image($artist_name) {
    // Todo - make this cookieless with sep domain 
    // http://serverfault.com/questions/234891/how-to-configure-cookieless-virtual-host-in-apache2
    $artist = t("Artist");
    $chars_to_remove = array(",", "-", "_", "é", "æ", "ø", "å");
    $chars__remove_to = array("", "", "", "e", "a", "o", "a");
    $chars_to_remove_filename = array(",",".","é", "æ", "ø", "å");
    $chars__remove_to_filename = array("","", "e", "a", "o", "a");
    $image_path_name = str_replace($chars_to_remove, $chars__remove_to, airplay_name_to_url(strtolower($artist_name)));
    $image_name = airplay_name_to_url(strtolower(str_replace($chars_to_remove_filename, $chars__remove_to_filename, $artist_name)));
    $image_name_length = strlen($image_path_name);
    $image_path = "/images/artists/";
    /* Make sure that we get correct directory if artist_name lower then 3 chars */
    for ($i = 0; $i < 3; $i++) {
        if ($i < $image_name_length) {
            $image_path .= $image_path_name[$i] . "/";
        } else {
            break;
        }
    }
	$sHtml = "";
	$sHtml .= "<img id='artistImageID' onError='getArtistPicture();' src='" . $image_path . $image_name . ".png' max-height='150' alt='" . $artist_name . "' title='" . $artist_name . "' />"; // Retttet
	return $sHtml;
}

function get_artist_info_box($artist_id, $artist_name, $languageCode, $show_header)
{
    $sHtml = " ";

	$aAllRows = getArtistInfo($artist_id);
    
	foreach ($aAllRows as $a ) {
		
		$sHtml .= "<table cellpadding='0' cellspacing='0' class='list-price-intro-right'>";
		if ($show_header == true) {
			$sHtml .= "<tr><td colspan='2' class='header'><h3>WIKI {$artist_name}</h3></td></tr>";
		}
		
		if ( $a['artist_type'] == 'G') {
			$sHtml .= "<tr><td>";
			if ($a['year_start'] != 0)  $sHtml .= "<span class='label'>" . t("Founded") . ":</span> {$a['year_start']}"; 
			else                        $sHtml .= "&nbsp;";
			$sHtml .= "</td><td>";
			if ($a['year_end'] != 0)    $sHtml .= "<span class='label'>" . t("Dissolved") . ":</span> {$a['year_end']}";
			else                        $sHtml .= "&nbsp;";
			$sHtml .= "</td></tr>";
		} else if ($a['artist_type'] == 'P') {
			$sHtml .= "<tr><td>";
			if ($a['year_born'] != 0)   $sHtml .= "<span class='label'>" . t("Born") . ":</span> {$a['year_born']}";
			else                        $sHtml .= "&nbsp;";
			$sHtml .= "</td><td>";
			if ($a['year_died'] != 0)   $sHtml .= "<span class='label'>" . t("Died") . ":</span> {$a['year_died']}";
			else                        $sHtml .= "&nbsp;";
			$sHtml .= "</td></tr>";
		}
		if ($a['country_name'] != '' && $a['country_name'] != 'Unknown') {
			$sHtml .= "<tr><td class='label'>" . t("Country") . ":</td><td>{$a['country_name']}</td></tr>";
		}
		
		$sHtml .= "<tr><td colspan='2'>";
		if ($a['url_artist_official'] != '') {
			$sHtml .= "<div class='official_link'><a href=\"{$a['url_artist_official']}\" title=\"" . t("Officiel") . " - {$artist_name}\" target='_blank'>" . t("Official website") . "</a></div>";
		}
		if ($a['url_facebook'] != '') {
			$sHtml .= "<div class='facebook_link'><a href=\"{$a['url_facebook']}\" title=\"" . t("Facebook") . "- {$artist_name}\" target='_blank'>" . t("Facebook") . "</a></div>";
		}
        if ($a['url_fanpage'] != '') {
			$sHtml .= "<div class='fan_link'><a href=\"{$a['url_fanpage']}\" title=\"" . t("Fan") . " - {$artist_name}\" target='_blank'>" . t("Fan website") . "</a></div>";
		}
		if ($a['url_wikipedia'] != '') {
			$sHtml .= "<div class='wikipedia_link'><a href=\"{$a['url_wikipedia']}\" title=\"" . t("Wikipedia") . " - {$artist_name}\" target='_blank'>" . t("Wikipedia") . "</a></div>";
		}
		if ($a['url_allmusic'] != '') {
			$sHtml .= "<div class='allmusic_link'><a href=\"{$a['url_allmusic']}\" title=\"" . t("Allmusic") . " - {$artist_name}\" target='_blank'>" . t("Allmusic") . "</a></div>";
		}
		if ($a['url_musicbrainz'] != '') {
			$sHtml .= "<div class='musicbrainz_link'><a href=\"{$a['url_musicbrainz']}\" title=\"" . t("Musicbrainz") . "- {$artist_name}\" target='_blank'>" . t("Musicbrainz") . "</a></div>";
		}
		if ($a['url_discogs'] != '') {
			$sHtml .= "<div class='discogs_link'><a href=\"{$a['url_discogs']}\" title=\"" . t("Discogs") . " - {$artist_name}\" target='_blank'>" . t("Discogs") . "</a></div>";
		}
		$sHtml .= "</td></tr>";
		$sHtml .= "</table>";
	}
	
    return $sHtml;
}

function prices_displayArtist_albums( $artist_id, $artist_name, $bSearchUrl, $product )
{
    global $G_RESULTS_PER_PAGE_PAGING;
    $sHtml = "";
    $bEmptyQuery = true;
    $rows = array(); // Table rows
    $header = array(); // Table headers
    $aArtistAlbumsDiscography = array();
    $items_count = 0; // Albums outputed to tabel
	$item_master = 0;
    $page_count = 0;
    $item_price_count = 0;
    $search_for_word_in_title = t("Search for word in title");
    $artist_album_outputted = array();
    $array_page_letters = array();

    $tUnknown = t('Unknown');
    $currency = ap_user_locale_currency();
    $media_format   = ap_user_media_format();
    $languageCode = ap_language_code();
    
    $url = "/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name);

    $aAllRows = getArtist_albums($artist_id, $currency, $media_format);
    /* Get media_format_counts */
    $aAllMediaFormatCounts = getArtist_media_format_count ( $artist_id, 1 );

    $selectMediaFormatHtml = ap_html_mediaFormatLinksAsDropDown ( $url, $media_format, $aAllMediaFormatCounts, $product, "#product_albums" );
    //$streamingCountHtml = ap_html_streamingcountAsDropDown("#product_albums", $languageCode);
    //$aAllRows = ap_limit_results_by_media_format_artist ( $aAllRows, $media_format );

	// --------------------
	// --- Table header ---
	// --------------------
    $cell['data'] = "";
	$cell['class'] = 'header-artist-album';
	$header[] = $cell;
	$cell['data'] = ""; //t("Year");
	$cell['class'] = 'header-artist-item-year';
	$header[] = $cell;
    $cell['data'] = $selectMediaFormatHtml;
	$cell['class'] = 'header-artist-price-format';
	$header[] = $cell;
	if ($media_format == "ALL") {
		$cell['data'] = "<div class='header'>" . t("Streaming") . "</div><div style='float:left;line-height:15px;'><a href='javascript:void(0);' onClick='ap_ShowStreamingAnimation(\"album\");'>" . t("Compare Streaming services") . "</a></div>";
	} else {
			$cell['data'] = "";
	}
	$cell['class'] = 'header-artist-streaming';
	$header[] = $cell;
    
	// ---------------------
	// --- Table content ---
	// ---------------------
    foreach ($aAllRows as $a) {
		$item_master = $a["item_master"];
	
        $page_letter = strtoupper(substr($a['item_base_name'], 0, 1));  // Used for paging.
        if (!preg_match("/^[a-zA-Z]$/", $page_letter)) {
            $page_letter = "other";
        }
        if (!in_array($page_letter,$array_page_letters)) {
            $array_page_letters[] = $page_letter;
        }
        $page_count = floor($items_count/$G_RESULTS_PER_PAGE_PAGING);
        $bEmptyQuery = false;

        $fPriceMin  = $a["price_MIN"];
        $fPriceMax  = $a["price_MAX"];
        $iPriceMin  = airplay_format_price( $fPriceMin, $currency );
        $iPriceMax  = airplay_format_price( $fPriceMax, $currency );
        $item_price_count = $a["album_prices_count"];
        if ($a["price_MIN"] == "") {
            $item_price_count = 0;
        }
        $sPricesText = $item_price_count > 1 ? t('prices') : t('price'); 
        if ($item_price_count < 2) {
            $no_follow_text = "rel='nofollow'";
        } else {
            $no_follow_text = "";
        }
        $sViewPricesUrl  = "/" . ap_artist_suburl() . "/" . airplay_name_to_url( $a["artist_name"] );
        $sViewPricesUrl .= "/" . ap_album_suburl() . "/" . airplay_name_to_url( $a["item_base_name"] );
		$image_url = $a["image_url"];
		
		if ($a["image_url"] != "") {
			$image_width = $a["image_width"];
			$image_height = $a["image_height"];
			$cell['data'] = "<div class='album_cover_icon' onMouseover='ap_ShowAlbumCoverArtistPage(" . $a['item_base_id'] . ", \"{$image_url}\", {$image_width}, {$image_height});' onMouseout='ap_HideAlbumCoverArtistPage(" . $a['item_base_id'] . ");'>&nbsp;</div><div class='album_cover' id='album_cover_" . $a['item_base_id'] . "'>&nbsp;</div><div class='title'><a href=\"$sViewPricesUrl\" title=\"" . $a["item_base_name"] . "\" " . $no_follow_text . ">" . $a['item_base_name'] . "</a></div>";
		} else {
			$cell['data'] = "<div class='album_cover_no_icon'>&nbsp;</div><div class='title'><a href=\"$sViewPricesUrl\" title=\"" . $a["item_base_name"] . "\" " . $no_follow_text . ">" . $a['item_base_name'] . "</a></div>";
		}

        $cell['class'] = 'list-artist-album';
        $cell_data[] = $cell;
		if ($a['item_year'] == 0) {
			$cell['data'] = "";
		} else {
			$cell['data'] = $a['item_year'];
		}

		$cell['class'] = 'list-artist-item-year';
		$cell_data[] = $cell;
        // <span class='currency'>$currency</span>
        $price_html = "<div class='price'><span class='min-price'>$iPriceMin</span> - <span class='max-price'>$iPriceMax</span></div>";
        //$prices_cell_text = "<div class='text'><a href=\"$sViewPricesUrl\" title=\"" . $a["item_base_name"] . "\" " . $no_follow_text . ">" . t('View') . " <span class='price_count'>" . $item_price_count . "</span> " . $sPricesText . "</a></div>";
		$prices_cell_text = "<div class='text'><span class='price_count'>{$item_price_count}</span>&nbsp;<span class='price_text'>{$sPricesText}</span></div>";
        $cell['data'] = $prices_cell_text . $price_html;
        $cell['class'] = 'list-artist-price-format';
        $cell_data[] = $cell;

        $prices_cell_text = ap_get_small_streaming_icons($product, urlencode($a["artist_name"] . " " . $a["item_base_name"]));
        $cell['data'] = $prices_cell_text;
        $cell['class'] = 'list-artist-streaming';
        $cell_data[] = $cell;

        //$rows[] = array('data' => $cell_data, 'class' => array('album-page-' .$page_count, 'album-page-' .$page_letter, 'album-master-' . $item_master), 'item_count' => array($item_price_count) );
		$rows[] = array('data' => $cell_data, 'class' => array('album-page-' .$page_letter, 'album-master-' . $item_master), 'item_count' => array($item_price_count) );

        $cell_data = "";
        $cell = "";
        $items_count++;
    }

	/* If no results - make empty tbody */
	if (count($aAllRows) == 0 && count($aArtistAlbumsDiscography) == 0) {
		$cell['data'] = "";
		$cell['class'] = 'list-artist-album';
		$cell_data[] = $cell;
		$cell['data'] = "";
		$cell['class'] = 'list-artist-item-year';
		$cell_data[] = $cell;
		$cell['data'] = "";
		$cell['class'] = 'list-artist-price-format';
		$cell_data[] = $cell;
		$cell['data'] = "";
		$cell['class'] = 'list-artist-streaming';
		$cell_data[] = $cell;
		$rows[] = $cell_data;
		$cell_data = "";
		$cell = "";
	}
	// --- Render Table ---
	$tableHTML = "";
	$tableHTML = theme('table', array( 'header' => $header,  'rows' => $rows,  'attributes' => array('class' => array('list-price-table'))));
	$sHtml .= $tableHTML;

    if ($page_count > 0) {
        //$sHtml .= "<div class='page-navigator'><div class='page-item-label'>" . t("Page:") . "</div>";
		$sHtml .= "<div class='page-navigator'>";
        $sHtml .= "<div class='page-item-first'><a href='javascript:void(0);' onClick='PageArtistAlbumsFrontpage();'>" . t("Artist frontpage") . "</a>&nbsp;|</div>";
		$sHtml .= "<div class='page-item-first'><a href='javascript:void(0);' onClick='PageArtistAlbumsAll();'>" . t("Show all") . "</a>&nbsp;|</div>";
        $paging_chars = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
        for ( $i = 0; $i < count($paging_chars); $i++) {
            $page_char = $paging_chars[$i];
            if (in_array($page_char, $array_page_letters)) {
                $sHtml .= "<div class='page-item'><a href='javascript:void(0);' onClick='PageArtistAlbumsByChars(\"" . $page_char . "\");'>" . $page_char . "</a></div>";
            } else {
                $sHtml .= "<div class='page-item'>" . $page_char . "</div>";
            }
        }
        $sHtml .= "<div class='page-item-last'><a href='javascript:void(0);' onClick='PageArtistAlbumsByChars(\"other\");'>" . t("Other") . "</a></div>";
        $sHtml .= "</div>";
    }
	
	if ($languageCode == "en") {
		$jsArray = 'var aStreamingToCompleteAlbums = [["spotify", 0, false], ["rdio", 0, false], ["deezer", 0, false], ["napster", 0, false]];';
		$jsArray .= 'var aStreamingToCompleteSongs = [["spotify", 0, false], ["rdio", 0, false], ["deezer", 0, false], ["napster", 0, false]];';
		$token_prices = "prices";
	} else {
		$jsArray = 'var aStreamingToCompleteAlbums = [["spotify", 0, false], ["rdio", 0, false], ["deezer", 0, false], ["napster", 0, false], ["wimp", 0, false]];';
        $jsArray .= 'var aStreamingToCompleteSongs = [["spotify", 0, false], ["rdio", 0, false], ["deezer", 0, false], ["napster", 0, false], ["wimp", 0, false]];';
		$token_prices = "priser";
	}
        $sHtml .=
<<<SCRIPT
    <script type='text/javascript'>
		$jsArray;
		var token_prices = "$token_prices";
        var ArtistAlbumPage = 0;
        var ArtistAlbumPageCount = $page_count;
		var index = 0;
		var ArtistPageAlbumItemsOnPage = 0;
		var ArtistPageAlbumCount = jQuery('#product_albums table.list-price-table > tbody > tr').length;
		var ArtistPageAlbumItemPrPage = {$G_RESULTS_PER_PAGE_PAGING};
		
		ap_ArtistPageAlbumShowMasters();
		
        jQuery("input.search_title_album").keyup(function() {
            ap_filter_titles(jQuery(this).val(), 1);
        });
        jQuery("input.search_title_album").focus(function() {
            if (jQuery(this).val() == '{$search_for_word_in_title}') {
                jQuery(this).val('');
            }
        });
        jQuery("input.search_title_album").blur(function() {
            if (jQuery(this).val() == "") {
                jQuery(this).val('{$search_for_word_in_title}');
            }
        });
        jQuery("input.search_title_song").keyup(function() {
            ap_filter_titles(jQuery(this).val(), 2);
        });
        jQuery("input.search_title_song").focus(function() {
            if (jQuery(this).val() == '{$search_for_word_in_title}') {
                jQuery(this).val('');
            }
        });
        jQuery("input.search_title_song").blur(function() {
            if (jQuery(this).val() == "") {
                jQuery(this).val('{$search_for_word_in_title}');
            }
        });
        jQuery("input.search_title_merchandise").keyup(function() {
            ap_filter_titles(jQuery(this).val(), 3);
        });
        jQuery("input.search_title_merchandise").focus(function() {
            if (jQuery(this).val() == '{$search_for_word_in_title}') {
                jQuery(this).val('');
            }
        });
        jQuery("input.search_title_merchandise").blur(function() {
            if (jQuery(this).val() == "") {
                jQuery(this).val('{$search_for_word_in_title}');
            }
        });
    </script>
SCRIPT;
	// Hide streaming icons if media format is not ALL
	if ($media_format != "ALL") {
$sHtml .=
<<<SCRIPT
    <script type='text/javascript'>ap_HideStreamingAnimation('album');</script>
SCRIPT;
	}
    return $sHtml;
}

function prices_displayArtist_songs( $artist_id, $artist_name, $bSearchUrl, $product )
{
    $sHtml = "";
    global $G_RESULTS_PER_PAGE_PAGING;
    $bEmptyQuery = true;
    $rows = array(); // Table rows
    $header = array(); // Table headers
    $aArtistSongsDiscography  = array();
    $items_count = 0; // Songs outputed to tabel
    $page_count = 0;
    $item_price_count = 0;
    $array_page_letters = array();
    $artist_song_outputted = array();
    
    $tUnknown = t('Unknown');
    $currency = ap_user_locale_currency();
    $title_name_year_sort = ap_user_title_name_year_sort();
    $media_format   = ap_user_media_format();
    $languageCode = ap_language_code();
    
    $url = "/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name);
    
    $aAllRows = getArtist_songs($artist_id, $currency, $media_format);
    /* Get media_format_counts */
    $aAllMediaFormatCounts = getArtist_media_format_count ( $artist_id, 2 );
    $selectMediaFormatHtml = ap_html_mediaFormatLinksAsDropDown ( $url, $media_format, $aAllMediaFormatCounts, $product, "#product_songs" );
    //$streamingCountHtml = ap_html_streamingcountAsDropDown("#product_songs", $languageCode);
    
    $aAllRows = ap_limit_results_by_media_format_artist($aAllRows, $media_format);
    
	// --------------------
	// --- Table header ---
	// --------------------
	$cell['data'] = ""; // t('Song');
	$cell['class'] = 'header-artist-song';
	$header[] = $cell;
	$cell['data'] = "";
	$cell['class'] = 'header-artist-item-year';
	$header[] = $cell;
    $cell['data'] = $selectMediaFormatHtml;
	$cell['class'] = 'header-artist-price-format';
	$header[] = $cell;
	if ($media_format == "ALL") {
		$cell['data'] = "<div class='header'>" . t("Streaming") . "</div><div style='float:left;line-height:15px;'><a href='javascript:void(0);' onClick='ap_ShowStreamingAnimation(\"song\");'>" . t("Compare Streaming services") . "</a></div>";
	} else {
			$cell['data'] = "";
	}
	$cell['class'] = 'header-artist-streaming';
	$header[] = $cell;
    
    
	// ---------------------
	// --- Table content ---
	// ---------------------
    foreach ($aAllRows as $a ) {
        $page_letter = strtoupper(substr($a['item_base_name'], 0, 1));  // Used for paging.
        if (!preg_match("/^[a-zA-Z]$/", $page_letter)) {
            $page_letter = "other";
        }
        if (!in_array($page_letter,$array_page_letters)) {
            $array_page_letters[] = $page_letter;
        }
        $page_count = floor($items_count/$G_RESULTS_PER_PAGE_PAGING);
        $bEmptyQuery = false;
        
        
        $fPriceMin  = $a["price_MIN"];
        $fPriceMax  = $a["price_MAX"];
        $iPriceMin  = airplay_format_price( $fPriceMin, $currency );
        $iPriceMax  = airplay_format_price( $fPriceMax, $currency );
        
        $sPricesText = $item_price_count > 1 ? t('prices') : t('price');
        $item_price_count = $a["song_prices_count"];
        
        if ($a["price_MIN"] == "") {
            $item_price_count = 0;
        }
        if ($a["song_prices_count"] < 2) {
            $no_follow_text = "rel='nofollow'";
        } else {
            $no_follow_text = "";
        }
        $sViewPricesUrl  = "/" . ap_artist_suburl() . "/" . airplay_name_to_url( $a["artist_name"] );
        $sViewPricesUrl .= "/" . ap_song_suburl() . "/" . airplay_name_to_url( $a["item_base_name"] );

        $cell['data'] = "<div class='title'><a href=\"$sViewPricesUrl\" title=\"" . $a["item_base_name"] . "\" " . $no_follow_text . ">" . $a['item_base_name'] . "</a></div>";
        $cell['class'] = 'list-artist-song';
        $cell_data[] = $cell;

		$cell['data'] = "";
		$cell['class'] = 'list-artist-item-year';
		$cell_data[] = $cell;
		// <span class='currency'>$currency</span>
        $price_html = "<div class='price'><span class='min-price'>$iPriceMin</span> - <span class='max-price'>$iPriceMax</span></div>";
        //$prices_cell = "<div class='text'><a href=\"$sViewPricesUrl\" title=\"" . $a["item_base_name"] . "\" " . $no_follow_text . ">" . t('View') . " <span class='price_count'>" . $item_price_count . "</span> " . $sPricesText . "</a></div>";
		$prices_cell = "<div class='text'><span class='price_count'>{$item_price_count}</span>&nbsp;<span class='price_text'>{$sPricesText}</span></div>";
        $cell['data'] = $prices_cell . $price_html;
        $cell['class'] = 'list-artist-price-format';
        $cell_data[] = $cell;
        $prices_cell_text = ap_get_small_streaming_icons($product, urlencode($a["artist_name"] . " " . $a["item_base_name"]));
        $cell['data'] = $prices_cell_text;
        $cell['class'] = 'list-artist-streaming';
        $cell_data[] = $cell;

        $rows[] = array('data' => $cell_data, 'class' => array('song-page-' .$page_count, 'song-page-' .$page_letter) );
        $cell_data = "";
        $cell = "";
        $items_count++;
    }

	/* If no results - make empty tbody */
	if (count($aAllRows) == 0 && count($aArtistSongsDiscography) == 0) {
		$cell['data'] = "";
		$cell['class'] = 'list-artist-song';
		$cell_data[] = $cell;
		$cell['data'] = "";
		$cell['class'] = 'list-artist-item-year';
		$cell_data[] = $cell;
		$cell['data'] = "";
		$cell['class'] = 'list-artist-price-format';
		$cell_data[] = $cell;
		$cell['data'] = "";
		$cell['class'] = 'list-artist-streaming';
		$cell_data[] = $cell;
		$rows[] = $cell_data;
		$cell_data = "";
		$cell = "";
	}
	
	// --- Render Table ---
	$tableHTML = "";
	$tableHTML = theme('table', array(  'header' => $header,  'rows' => $rows,  'attributes' => array('class' => array('list-price-table'))));
	$sHtml .= $tableHTML;

    if ($page_count > 0) {
        //$sHtml .= "<div class='page-navigator'><div class='page-item-label'>" . t("Page:") . "</div>";
		$sHtml .= "<div class='page-navigator'>";
        $paging_chars = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
		$sHtml .= "<div class='page-item-first'><a href='javascript:void(0);' onClick='PageArtistSongsFrontpage(0);'>" . t("Artist frontpage") . "</a>&nbsp;|</div>"; // Show all items on page number 0.
		
        for ( $i = 0; $i < count($paging_chars); $i++) {
            $page_char = $paging_chars[$i];
            
            if (in_array($page_char, $array_page_letters)) {
                $sHtml .= "<div class='page-item'><a href='javascript:void(0);' onClick='PageArtistSongsByChars(\"" . $page_char . "\");'>" . $page_char . "</a></div>";
            } else {
                $sHtml .= "<div class='page-item'>" . $page_char . "</div>";
            }
            
        }
        $sHtml .= "<div class='page-item-last'><a href='javascript:void(0);' onClick='PageArtistSongsByChars(\"other\");'>" . t("Other") . "</a></div>";
        $sHtml .= "</div>";
    }
    
    $sHtml .=
<<<SCRIPT
    <script type='text/javascript'>
        var ArtistSongPage = 0;
        var ArtistSongPageCount = $page_count;
        PageArtistSongsFrontpage(0);
    </script>
SCRIPT;
    if ($media_format == "ALL") {
        $sHtml .= getCurrencyFromToJSArrays();
        $sHtml .= "<script type='text/javascript'>";
        $sHtml .= getDynamicMusicLookupArtistAlbums($artist_name, $languageCode, $artist_id, "streaming");
        $sHtml .= getDynamicMusicLookupArtistAlbums($artist_name, $languageCode, $artist_id, "MP3");
        $sHtml .= "function ap_getDynamicMusicLookupArtist() {";
        $sHtml .= getDynamicMusicLookupArtistSongs($artist_name, $languageCode, $artist_id, "streaming");
        $sHtml .= getDynamicMusicLookupArtistSongs($artist_name, $languageCode, $artist_id, "MP3");
        $sHtml .= "};</script>";

	} else if ($media_format == "MP3") {
        $sHtml .= getCurrencyFromToJSArrays();
        $sHtml .= "<script type='text/javascript'>";
        $sHtml .= getDynamicMusicLookupArtistAlbums($artist_name, $languageCode, $artist_id, "MP3");
        $sHtml .= "function ap_getDynamicMusicLookupArtist() {";
        $sHtml .= getDynamicMusicLookupArtistSongs($artist_name, $languageCode, $artist_id, "MP3");
        $sHtml .= "};</script>";
    }
	// Hide streaming icons if media format is not ALL
	if ($media_format != "ALL") {
$sHtml .=
<<<SCRIPT
    <script type='text/javascript'>ap_HideStreamingAnimation('song');</script>
SCRIPT;
	}
	
    return $sHtml;
}

function prices_displayArtist_merchandise( $artist_id, $artist_name, $bSearchUrl, $product )
{
    global $G_RESULTS_PER_PAGE_PAGING;
    $items_count = 0; // Albums outputed to tabel
    $page_count = 0;
	$bEmptyQuery = true;
	$rows = array();
    $sHtml = "";
    
    $tUnknown = t('Unknown');
    $currency = ap_user_locale_currency();
    $title_name_year_sort = ap_user_title_name_year_sort();
    $media_format   = ap_user_media_format();
    $languageCode = ap_language_code();
    
    $url = "/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name);
    
    $aAllRows = getArtist_merchandise($artist_id, $currency, $media_format);

    /* Get media_format_counts */
    $aAllMediaFormatItemCounts = getArtist_media_format_count ( $artist_id, 3 );
    
    $selectMediaFormatHtml = ap_html_mediaFormatLinksAsDropDown ( $url, $media_format, $aAllMediaFormatItemCounts, $product, "#product_merchandise" );

    
    $aAllRows = ap_limit_results_by_media_format_artist($aAllRows, $media_format);
    /*if ( $title_name_year_sort == "name_ASC" ) {
		usort( $aAllRows, 'item_base_name_ASC' );
	}
    else if ( $title_name_year_sort == "year_DESC" ) {
		usort( $aAllRows,'item_year_DESC' );
	}*/

	// --------------------
	// --- Table header ---
	// --------------------
	$header = array();

	$cell['data'] = ""; // t('Merchandise');
	$cell['class'] = 'header-artist-merchandise';
	$header[] = $cell;
	$cell['data'] = "";
	$cell['class'] = 'header-artist-item-year';
	$header[] = $cell;
    $cell['data'] = $selectMediaFormatHtml;
	$cell['class'] = 'header-artist-price-format';
	$header[] = $cell;
	$cell['data'] = "";
	$cell['class'] = 'header-artist-streaming';
	$header[] = $cell;
    
	// ---------------------
	// --- Table content ---
	// ---------------------
	foreach ($aAllRows as $a ) {
		$bEmptyQuery = false;
        
        $page_count = floor($items_count/$G_RESULTS_PER_PAGE_PAGING);
		
        $fPriceMin  = $a["price_MIN"];
		$iPriceMin  = airplay_format_price( $fPriceMin, $currency );

        $buy_at_url = $a['buy_at_url'];
        if ($a['use_affiliate'] == 1) {
            $buy_at_url = ap_replace_affiliate_link($buy_at_url, $a['affiliate_link'], $a['affiliate_encode_times']);
        }
		$image_url = $a["cover_image_url"];
		if ($image_url != "") {
			$image_width = 150;
			$image_height = 150;
			$cell['data'] = "<div class='merchandise_image_icon' onMouseover='ap_ShowMerchandiseArtistPage(" . $a['item_price_id'] . ", \"{$image_url}\", {$image_width}, {$image_height});' onMouseout='ap_HideMerchandiseArtistPage(" . $a['item_price_id'] . ");'>&nbsp;</div><div class='merchandise_image' id='merchandise_image_" . $a['item_price_id'] . "'>&nbsp;</div><div class='title'>" . $a['item_price_name'] . "</div>";
		} else {
			$cell['data'] = "<div class='merchandise_image_no_icon'>&nbsp;</div><div class='title'>" . $a['item_price_name']. "</div>";
		}
		$cell['class'] = 'list-artist-merchandise';
		$cell_data[] = $cell;
		$cell['data'] = "";
		$cell['class'] = 'list-artist-item-year';
		$cell_data[] = $cell;
		//$currency
		$cell['data'] = $iPriceMin;
		$cell['class'] = 'list-artist-price-format';
		$cell_data[] = $cell;
        if ($a['use_affiliate'] == 1) {
			//onClick='_gaq.push([\"_trackEvent\", \"ResultPageMerchandise\", \"Click\", \"" . $a['record_store_name'] . "\"]);'
			$google_events = "ga('send', 'event', 'ResultPageMerchandise', 'Click', '" . $a['record_store_name'] . "');";
            $buy_at_url = "<a href='{$buy_at_url}' onClick=\"{$google_events}\" target='_blank' title='" . $a['record_store_name'] . " - " . str_replace("'", "", $a['item_base_name']) . " - " . str_replace("'", "", $a['artist_name']) . "' rel='nofollow'>" . $a['record_store_name'] . "</a>";
        } else {
			//onClick='_gaq.push([\"_trackEvent\", \"ResultPageMerchandise\", \"Click\", \"" . $a['record_store_name'] . "\"]);'
			$google_events = "ga('send', 'event', 'ResultPageMerchandise', 'Click', '" . $a['record_store_name'] . "');";
            $buy_at_url = "<a href='{$buy_at_url}' onClick=\"{$google_events}\" target='_blank' title='" . $a['record_store_name'] . " - " . str_replace("'", "", $a['item_base_name']) . " - " . str_replace("'", "", $a['artist_name']) . "'>" . $a['record_store_name'] . "</a>";
        }
		$cell['data'] = $buy_at_url;
		$cell['class'] = 'list-artist-streaming';
		$cell_data[] = $cell;
        $rows[] = array('data' => $cell_data, 'class' => array('merchandise-page-' .$page_count) );
		$cell_data = "";
		$cell = "";
        $items_count++;
	}
	
	/* If no results - make empty tbody */
	if (count($aAllRows) == 0) {
		$cell['data'] = "";
		$cell['class'] = 'list-artist-merchandise';
		$cell_data[] = $cell;
		$cell['data'] = "";
		$cell['class'] = 'list-artist-item-year';
		$cell_data[] = $cell;
		$cell['data'] = "";
		$cell['class'] = 'list-artist-price-format';
		$cell_data[] = $cell;
		$cell['data'] = "";
		$cell['class'] = 'list-artist-streaming';
		$cell_data[] = $cell;
		$rows[] = $cell_data;
		$cell_data = "";
		$cell = "";
	}
	
	// --- Render Table ---
	$tableHTML = "";
	$tableHTML = theme('table', array(  'header' => $header,  'rows' => $rows,  'attributes' => array('class' => array('list-price-table'))));
	$sHtml .= $tableHTML;
    
    if ($page_count > 0) {
        //$sHtml .= "<div class='page-navigator'><div class='page-item-label'>" . t("Page:") . "</div>";
		$sHtml .= "<div class='page-navigator'>";
        for ($i = 0; $i <= $page_count; $i++) {
            if ($i == 0) { $selected = "selected"; } else { $selected = ""; }
            $sHtml .= "<div class='page-item $selected'><a href='javascript:void(0);' onClick='PageArtistMerchandise($i);'>" . ($i+1) . "</a></div>";
        }
        $sHtml .= "</div>";
    }
    $sHtml .=
<<<SCRIPT
    <script type='text/javascript'>
        var ArtistMerchandisePage = 0;
        var ArtistMerchandisePageCount = $page_count;
        for (var i = 1; i <= ArtistMerchandisePageCount; i++ ) {
            jQuery(".merchandise-page-" + i).hide();
        }
    </script>
SCRIPT;
    
    
    return $sHtml;
}


function prices_displayArtist_concert( $artist_id, $artist_name, $bSearchUrl, $product )
{
	$bEmptyQuery = true;
	$rows = array();
    $sHtml = "";
    
    $tUnknown = t('Unknown');
    $currency = ap_user_locale_currency();
    $media_format   = ap_user_media_format();
    $languageCode = ap_language_code();
    
    $url = "/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name);
    
    $aAllRows = getArtist_concerts($artist_id, $currency, $media_format);

    /* Get media_format_counts */
    $aAllMediaFormatItemCounts = getArtist_media_format_count ( $artist_id, 4 );
	//$selectMediaFormatHtml = ap_html_mediaFormatLinksAsDropDown ( $url, $media_format, $aAllMediaFormatItemCounts, $product, "#product_concert" );

	// --------------------
	// --- Table header ---
	// --------------------
	$header = array();

	$cell['data'] = "<div class='header'>" . t("Venue") . "</div>";
	$cell['class'] = 'header-artist-concert';
	$header[] = $cell;
	$cell['data'] = "";
	$cell['class'] = 'header-artist-item-year';
	$header[] = $cell;
    $cell['data'] = "<div class='header'>" . t("Time") . "</div>";
	$cell['class'] = 'header-artist-concert-start-time';
	$header[] = $cell;
    $cell['data'] = "<div class='header'>" . t("Price") . "</div>";
	$cell['class'] = 'header-artist-concert-price-format';
	$header[] = $cell;
	$cell['data'] = "";
	$cell['class'] = 'header-artist-concert';
	$header[] = $cell;
    $cell['data'] = "";
	$cell['class'] = 'header-artist-concert-sort-value';
	$header[] = $cell;
	// ---------------------
	// --- Table content ---
	// ---------------------
	foreach ($aAllRows as $a ) {
		$bEmptyQuery = false;
		// Do not show prices for festivals - only show text
		if ($a["media_format_id"] == 129) {
			$price_currency_text = t("See Festival");
		} else {
			// If saved price is 1 - then is not a real price - show text.
			if ($a["price_local"] == 1) {
				$price_currency_text = t("See Venue");
			} else {
				$fPriceMin  = $a["price_MIN"];
				$iPriceMin  = airplay_format_price( $fPriceMin, $currency );
				$price_currency_text = $iPriceMin; // . " " . $currency;
			}
		}
		$buy_at_url = $a['buy_at_url'];
		if ($a['use_affiliate'] == 1) {
			$buy_at_url = ap_replace_affiliate_link($buy_at_url, $a['affiliate_link'], $a['affiliate_encode_times']);
		}
		
		$cell['data'] = $a['item_price_name'];
		$cell['class'] = 'list-artist-concert';
		$cell_data[] = $cell;
		$cell['data'] = "";
		$cell['class'] = 'list-artist-item-year';
		$cell_data[] = $cell;
		// event date is not set 
		if ($a['item_event_date'] == '0000-00-00') {
			// Use manuel value from recordstore
			if ($a["record_store_event_date_text"] != "")  {
				$concert_date_splitted = explode(" ", $a["record_store_event_date_text"]);
				if (count($concert_date_splitted) == 2) {
					$date_time_text = ap_format_date($concert_date_splitted[0], $languageCode) . " - " . ap_format_date($concert_date_splitted[1], $languageCode);
				} else {
					$date_time_text = t("To be announced");
				}
			} else {
				$date_time_text = t("To be announced");
			}
		} else {
			if ($languageCode == "da") {
				$date_time_text = ap_format_date($a['item_event_date'], $languageCode) . "&nbsp;kl. " . $a['item_event_time'];
			} else {
				$date_time_text = ap_format_date($a['item_event_date'], $languageCode) . "&nbsp;at " . $a['item_event_time'];
			}
		}
		if ($a["item_price_delivery_status_id"] == 3 || $a["item_price_delivery_status_id"] == 4) {
			$date_time_text = "<del>{$date_time_text}</del>";
		}
		$cell['data'] = $date_time_text;
		$cell['class'] = 'list-artist-concert-start-time';
		$cell_data[] = $cell;
		if ($a["item_price_delivery_status_id"] == 3 || $a["item_price_delivery_status_id"] == 4) {
			$price_currency_text = "<del>{$price_currency_text}</del>";
		}			
		$cell['data'] = $price_currency_text;
		$cell['class'] = 'list-artist-price-format';
		$cell_data[] = $cell;
		if ($a['use_affiliate'] == 1) {
			//onClick='_gaq.push([\"_trackEvent\", \"ResultPageConcert\", \"Click\", \"" . $a['record_store_name'] . "\"]);'
			$google_events = "ga('send', 'event', 'ResultPageConcert', 'Click', '" . $a['record_store_name'] . "');";
			$buy_at_url = "<a href='{$buy_at_url}' onClick=\"{$google_events}\" target='_blank' title='" . $a['artist_name'] . " - " . str_replace("'", "", $a['record_store_name']) . "' rel='nofollow'>" . $a['record_store_name'] . "</a>";
		} else {
			//onClick='_gaq.push([\"_trackEvent\", \"ResultPageConcert\", \"Click\", \"" . $a['record_store_name'] . "\"]);'
			$google_events = "ga('send', 'event', 'ResultPageConcert', 'Click', '" . $a['record_store_name'] . "');";
			$buy_at_url = "<a href='{$buy_at_url}' onClick=\"{$google_events}\" target='_blank' title='" . $a['artist_name'] . " - " . str_replace("'", "", $a['record_store_name']) . "'>" . $a['record_store_name'] . "</a>";
		}
		if ($a["item_price_delivery_status_id"] == 2) {
			$buy_at_url .= "&nbsp;&nbsp;<strong>(" . t("Few") . ")</strong>";
		} else if ($a["item_price_delivery_status_id"] == 3) {
			$buy_at_url .= "&nbsp;&nbsp;<strong>(" . t("Sold out") . ")</strong>";
		} else if ($a["item_price_delivery_status_id"] == 4) {
			$buy_at_url .= "&nbsp;&nbsp;<strong>(" . t("Cancelled") . ")</strong>";
		} else if ($a["item_price_delivery_status_id"] == 5) {
			$buy_at_url .= "&nbsp;&nbsp;<strong>(" . t("Waiting list") . ")</strong>";
		}
		
		$cell['data'] = $buy_at_url;
		$cell['class'] = 'list-artist-concert';
		$cell_data[] = $cell;
		
		// event date is not set 
		if ($a['item_event_date'] == '0000-00-00') {
			// Use manuel value from recordstore
			if ( $a["record_store_event_date_text"] != "")  {
				$cell['data'] = str_replace("-", "", $concert_date_splitted[0]);
			} else {
				$cell['data'] = "20380119";
			}
		} else {
			$cell['data'] = str_replace("-", "", $a['item_event_date']);
		}
		$cell['class'] = 'list-artist-concert-sort-value';
		$cell_data[] = $cell;
		
		$rows[] = array('data' => $cell_data, 'class' => array() );
		$cell_data = "";
		$cell = "";
	}
	
	/* If no results - make empty tbody */
	if (count($aAllRows) == 0) {
		$cell['data'] = "";
		$cell['class'] = 'list-artist-concert';
		$cell_data[] = $cell;
		$cell['data'] = "";
		$cell['class'] = 'list-artist-item-year';
		$cell_data[] = $cell;
		$cell['data'] = "";
		$cell['class'] = 'list-artist-concert-start-time';
		$cell_data[] = $cell;
        $cell['data'] = "";
		$cell['class'] = 'list-artist-price-format';
		$cell_data[] = $cell;
		$cell['data'] = "";
		$cell['class'] = 'list-artist-concert';
		$cell_data[] = $cell;
		$cell['data'] = "";
		$cell['class'] = 'list-artist-concert-sort-value';
		$cell_data[] = $cell;
		$rows[] = $cell_data;
		$cell_data = "";
		$cell = "";
	}
	
	// --- Render Table ---
	$tableHTML = "";
	$tableHTML = theme('table', array(  'header' => $header,  'rows' => $rows,  'attributes' => array('class' => array('list-price-table'))));
	$sHtml .= $tableHTML;
    
    return $sHtml;
}


function addArtistImage($artistImageID, $sSearchString)
{
$sSearchString = str_replace("'", "\'", $sSearchString);
$s=
<<<SCRIPT
<script type="text/javascript">
    var g_allArtistImages           = [];
    var g_currentArtistImageIndex   = -1;
    var g_sSearchString = '$sSearchString';
    google.load('search', '1');
</script>
SCRIPT;
    return $s;
}
