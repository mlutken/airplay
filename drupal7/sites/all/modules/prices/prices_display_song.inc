<?php 
// $Id$

/**
\see Table theme function: http://www.group42.ca/theming_101_%E2%80%93_theme_table_function
\see http://api.drupal.org/api/function/theme_table/6 */
function prices_display_song( $song_simple_name, $artist_name="" )
{
    $nSongID = "";
    $sHtml  = "";
	$sPageTitle = t("{artist_name} - {song_name} - Compare prices MP3, FLAC, Streaming, Vinyl, CD");
    $languageCode = ap_language_code();
    $to_frontpage_text = t("To Frontpage");
    //$to_frontpage_text = t("Search music");
    
	drupal_set_title( str_replace("{song_name}", $song_simple_name, str_replace("{artist_name}", $artist_name, $sPageTitle)) );
    
    $currency           = ap_user_locale_currency();
    $media_format       = ap_user_media_format();
    $artist_id          = artistIdFromName( $artist_name );
    $song_simple_id     = itemSimpleIdFromNameExact( $artist_id, "song", $song_simple_name );
    
    // Get 404 status code for Search engines if needed
    get404StatusCodeForLowNumberOfPrices($artist_id, $song_simple_id, "song");
    
    $sSongHeader = "";
    if ( $song_simple_id ){
        $sArtistLink = "<a href='/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name) .  "' title='" . $artist_name . "'>" . $artist_name . "</a>";
        $url  = "/" . ap_artist_suburl() . "/" . airplay_name_to_url($artist_name);
		$artist_url = $url;
        $url .= "/" . ap_song_suburl() . "/" . airplay_name_to_url($song_simple_name);
        $sSongHeader = "<table cellpadding='0' cellspacing='0' class='list-price-intro'>";
        //$sSongHeader = "<tr><td colspan='2'><a href='/' title='$to_frontpage_text'>$to_frontpage_text</a> &gt; " . $sArtistLink . " &gt; $song_simple_name</td></tr>";
        $sSongHeader .= "<tr><td colspan='2'><div itemscope='' itemtype='http://data-vocabulary.org/Breadcrumb' style='float:left;'><a href='/' itemprop='url'><span itemprop='title'>$to_frontpage_text</span></a></div><div style='float:left;margin:0px 5px 0px 5px;'> &gt; </div><div itemscope='' itemtype='http://data-vocabulary.org/Breadcrumb' style='float:left;'><a href='" . $artist_url .  "' itemprop='url'><span itemprop='title'>$artist_name</span></a></div><div style='float:left;margin:0px 5px 0px 5px;'> &gt; </div><div itemscope='' itemtype='http://data-vocabulary.org/Breadcrumb'><span itemprop='title'>$song_simple_name</span></div></td></tr>";
        $sSongHeader .= "<tr>";
        $sSongHeader .= "<td><h1 class='ap_item_headline1'>{$artist_name}:  {$song_simple_name}</h1></td>";
        $sSongHeader .= "<td id='addthis'>";
        if ($languageCode == "da") { $sSongHeader .= ap_getAddThis(); } else { $sSongHeader .= "&nbsp;"; }
        $sSongHeader .= "</td>";
        $sSongHeader .= "</tr><tr><td colspan='2'>&nbsp;</td></tr>";
        $sSongHeader .= "</table>";
    }
    else {
        $url  = "/prices?search_for=song&lookup=" . urlencode($song_simple_name);
        $url .= "&artist_name=" . urlencode($artist_name);
    }


    if ($artist_id != 0 && $song_simple_id != 0) {

        $aAllRows = getBuyItems_songs($artist_id, $song_simple_id, $currency, $media_format);

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
		$cell['data'] = t('Song') . "<br/>" . $sArtistLink;
		$cell['class'] = 'header-song-song';
		$header[] = $cell;
		$cell['data'] = $selectMediaFormatHtml;
		$cell['class'] = 'header-song-price-format';
		$header[] = $cell;
		if ($media_format == "ALL") {
			$cell['data'] = "<div class='header'>&nbsp;</div><div style='float:left;line-height:15px;'><a href='javascript:void(0);' onClick='ap_ShowStreamingAnimation(\"item_page\");'>" . t("Compare Streaming services") . "</a></div>";
		} else {
				$cell['data'] = "";
		}
		$cell['class'] = 'header-song-buy-at-url';
        $header[] = $cell;
        $cell['data'] = "";
        $cell['class'] = 'header-song-price-value';
        $header[] = $cell;

		// ---------------------
		// --- Table content ---
		// ---------------------
		
        $aAllRows = ap_limit_results_by_media_format($aAllRows, $media_format);
        
		$rows = array();
		foreach ($aAllRows as $a ) {
			
			$nSongID = $a['item_base_id'];
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
			$cell['class'] = 'list-song-song';
			$cell_data[] = $cell;
			$sPriceText = "<div class='text'>" . $media_format_name . "</div><div class='price'>" . $sPrice;
			if ($languageCode == "da" && $a["freight_price_da"] != "" && $media_format_name != "MP3" && $media_format_name != "FLAC" && $media_format_name != "WMA") {
					$sPriceText .= "<span class='freight'><img src='http://static.airplay-music.dk/images/site/information_alert.png' border='0' title='" . $a["freight_price_da"] . "' height='12' width='12' align='middle' /></span>";
			} else if ($languageCode == "en" && $a["freight_price_en"] != "" && $media_format_name != "MP3" && $media_format_name != "FLAC" && $media_format_name != "WMA") {
					$sPriceText .= "<span class='freight'><img src='http://static.airplay-music.dk/images/site/information_alert.png' border='0' title='" . $a["freight_price_en"] . "' height='12' width='12' align='middle' /></span>";
			}
			$sPriceText .= "</div>";
            $cell['data'] = $sPriceText;
            //$cell['data'] = "<div class='text'>" . $media_format_name . "</div><div class='price'>" . $sPrice . "</div>";
            $cell['class'] = 'list-album-price-format';
            $cell_data[] = $cell;
            
			if ($a['use_affiliate'] == 1) {
				//_gaq.push([\"_trackEvent\", \"ResultPageSongs\", \"Click\", \"" . $a['record_store_name'] . "\"]);
				$google_events = "ga('send', 'event', 'ResultPageSongs', 'Click', '" . $a['record_store_name'] . "');";
                $buy_at_url = "<a href='{$buy_at_url}' onClick=\"{$google_events}\" target='_blank' title='" . $a['record_store_name'] . " - " . $a['item_price_name'] . " - " . $a['artist_name'] . "' rel='nofollow'>" . $a['record_store_name'] . "</a>";
                $cell['data'] = $buy_at_url;
			} else {
				//_gaq.push([\"_trackEvent\", \"ResultPageSongs\", \"Click\", \"" . $a['record_store_name'] . "\"]);
				$google_events = "ga('send', 'event', 'ResultPageSongs', 'Click', '" . $a['record_store_name'] . "');";
                $buy_at_url = "<a href='{$buy_at_url}' onClick=\"{$google_events}\" target='_blank' title='" . $a['record_store_name'] . " - " . $a['item_price_name'] . " - " . $a['artist_name'] . "'>" . $a['record_store_name'] . "</a>";
                $cell['data'] = $buy_at_url;
			}
			$cell['class'] = 'list-song-buy-at-url';
			$cell_data[] = $cell;
            
            $cell['data'] = str_replace(".", "", str_replace(",", "", $nPrice));
			$cell['class'] = 'list-song-price-value';
			$cell_data[] = $cell;
            
			$rows[] = $cell_data;
			$cell_data = "";
			$cell = "";
		}
		
		/* If no results - make empty tbody */
		if (count($aAllRows) == 0) {
			$cell['data'] = "";
			$cell['class'] = 'list-song-song';
			$cell_data[] = $cell;
			$cell['data'] = "";
			$cell['class'] = 'list-song-price-format';
			$cell_data[] = $cell;
			$cell['data'] = "";
			$cell['class'] = 'list-song-buy-at-url';
			$cell_data[] = $cell;
            $cell['data'] = 0;
			$cell['class'] = 'list-song-price-value';
			$cell_data[] = $cell;
			$rows[] = $cell_data;
			$cell_data = "";
			$cell = "";
		}
		
		$sSongHeader .= prices_display_song_intro( $artist_id, $artist_name, $song_simple_name, $nSongID, $song_simple_id, $currency, $languageCode, $artist_url, $url );
		// --- Render Table ---
		$tableHTML = theme('table', array( 'header' => $header,  'rows' => $rows,  'attributes' => array('class' => array('list-price-table'))));

		$sHtml .= $sSongHeader;
        $sHtml .= "<div id='tabs'><div id='tabs_currency'>"  .  ap_html_currencySelectTabs ( $url, $currency, "album" ) . "</div></div>";
		$sHtml .= $tableHTML;
		//$sHtml .= addYoutubePlayer( 'playerYoutube', "$artist_name, $song_simple_name", 8, 300, 200 );
        if ($media_format == "ALL") {
            $sHtml .= getCurrencyFromToJSArrays();
            $sHtml .= getDynamicMusicLookupSong($artist_name, $song_simple_name, $languageCode, $artist_id, $song_simple_id, "streaming");
            $sHtml .= getDynamicMusicLookupSong($artist_name, $song_simple_name, $languageCode, $artist_id, $song_simple_id, "MP3");
        } else if ($media_format == "MP3") {
            $sHtml .= getCurrencyFromToJSArrays();
            $sHtml .= getDynamicMusicLookupSong($artist_name, $song_simple_name, $languageCode, $artist_id, $song_simple_id, "MP3");
        }
	// Hide streaming icons if media format is not ALL
	if ($media_format != "ALL") {
$sHtml .=
<<<SCRIPT
    <script type='text/javascript'>ap_HideStreamingAnimation('item_page');</script>
SCRIPT;
	}
	}
    return $sHtml;
}

function prices_display_song_intro( $artist_id, $artist_name, $song_name, $nSongID, $song_simple_id, $currency, $languageCode, $artist_url, $url )
{
    $sHtml  = "";
    $aMinPricesperFormat_songs = getMinPricesPerFormat_songs($song_simple_id, $currency );
    
    $sBaseDesc      = lookupDescription_song ( $artist_id, $song_simple_id, $languageCode );
    $sDescription   = getDescription_song    ( $sBaseDesc, array($artist_name, $artist_url, $song_name, $url) );

	if ($languageCode == "da" && isBotIP() == false) {
		$video = lookupItemVideo_data($song_simple_id);
		if (count($video)) {
			$s_video_Html = $video["embed_html"];
		} else {
			$s_video_Html = ap_getArtistSongEmbedVideoHTML($artist_name, $song_name);
			insertItemVideo_data($song_simple_id, $s_video_Html);
		}
	} else {
		$s_video_Html = "";
	}
	
	// Get parent from song - aka album from a song
	$album_from_song = lookupParent_song($song_simple_id);
	// Get HTML for tracklist - empty if not list.
	$album_from_track_html = getSongParentHTML($album_from_song, $artist_name);
	
    $sHtml .= "<table cellpadding='0' cellspacing='0' class='list-price-intro'>";
    $sHtml .= "<tr><td id='song_text' valign='top'>";
    $sHtml .= "<table cellspacing='0' cellpadding='0' class='list-price-intro-left'>";
    $sHtml .= "<tr><td><div>{$sDescription}</div>{$album_from_track_html}</td></tr>";
    $sHtml .= "</table>";
    $sHtml .= "</td><td id='song_wiki' valign='top'>";
    
	if ($s_video_Html != "") {
		$sHtml .= "<div class='tabs'>";
		$sHtml .= "<div id='tab_wiki' onClick='ap_ShowVideo(\"item_page\");'>" . ("Video") . "</div>";
		$sHtml .= "<div id='tab_songs' class='selected' onClick='ap_ShowStreamingAnimation(\"item_page\");'>" . ("Streaming") . "</div>";
		$sHtml .= "</div>";
	}
    $sHtml .= "<div class='wiki'>";
    // Youtube link tab
    $sHtml .= "<table cellspacing='0' cellpadding='0' class='list-price-intro-right'>";
	if ($s_video_Html == "") {
		$sHtml .= "<tr><td class='header'><h3>". substr($song_name, 0 , 28) . "</h3></td></tr>";
	}
	// Video tab
	if ($s_video_Html != "") {
		$sHtml .= "<tr><td><div class='video'>";
		$sHtml .= $s_video_Html;
		$sHtml .= "</div></td></tr>";
	} else {
		$sHtml .= "<tr><td>" . addYoutubeLink( "$artist_name $song_name") . "</td></tr>";
	}
    $sHtml .= "</table>";
    $sHtml .= "</div>";
	
    // Songs tab
    $sHtml .= "<div class='songs'>";
    $sHtml .= ap_html_streamingcountAsDropDown("#song_wiki div.songs", $languageCode);
    $sHtml .= "</div>";
    
    $sHtml .= "</td></tr>";

	
    foreach ( $aMinPricesperFormat_songs as $media_format_name => $a ) {
        $fPrice  = $a['price_MIN'];
        $fPrice  = airplay_format_price( $fPrice, $currency );
        $sHtml .= "<tr style='display:none;'><td>{$media_format_name}</td><td align='left'>" . t('from') . " {$fPrice} {$currency}</td><td> " . $a['record_store_name'] . "</td></tr>";
    }
        
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
