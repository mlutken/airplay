<?php

class FacebookApplication
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( )
    {
		if (isset($_REQUEST["aid"]) && $_REQUEST["aid"] != "") {
			$this->artist_id = $_REQUEST["aid"];
		}
		if (isset($_REQUEST["c"]) && $_REQUEST["c"] != "") {
			$this->country_code = $_REQUEST["c"];
		}
		if (isset($_REQUEST["mf"]) && $_REQUEST["mf"] != "") {
			$this->media_format_id = $_REQUEST["mf"];
		}
		if (isset($_REQUEST["t"]) && $_REQUEST["t"] != "") {
			$this->lookup_type = $_REQUEST["t"];
			if ($this->lookup_type == "artist_album") {
				$this->item_type = 1;
			} else if ($this->lookup_type == "artist_song") {
				$this->item_type = 2;
			}
		}
    }

	
	/***********
		Set functions
	***********/

	public function setDefaultValues() {
		if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != "") {
			$url_array = explode("/",$_SERVER["QUERY_STRING"]);
			$url_count = count($url_array);
//print $url_count;
//print $url_array[5];
			// Site is called with /artist/Artist_Name or like that
			if (($url_array[1] == "kunstner" || $url_array[1] == "artist") && $url_count > 2) {
				$this->artist_name = urlToName($url_array[2]);
				if ($this->artist_name != "") {
					$ap_artist_data = new ArtistDataMySql( $m_dbAll );
					$this->artist_id = $ap_artist_data->nameToID($this->artist_name);
				}
				
				if ($this->artist_id != 0) {
					// Site is called with artist only.
					// Artist page called or artist page called with product or media formats.
					if (($url_array[2] != "" && $url_count == 3) || substr($url_array[3], 0, 8) == "product=" || substr($url_array[4], 0, 13) == "media_format=") {
						if (substr($url_array[4], 13) != "") {
							$this->media_format_id = substr($url_array[4], 13);
						}
						if (substr($url_array[6], 9) != "") {
							$this->language_code = substr($url_array[6], 9);
						}
						if ($url_array[3] == "product=song") {
							$this->item_type = 2;
						} else if ($url_array[3] == "product=merchandise") {
							$this->item_type = 3;
						} else if ($url_array[3] == "product=concert") {
							$this->item_type = 4;
						} else {
							$this->item_type = 1;
						}
						$this->page_to_show = "artist";
					} else if (($url_array[3] == "album" && $url_array[4] != "" && $url_count == 5) || ($url_array[3] == "album" && $url_array[4] != "" && $url_count >= 7 && substr($url_array[5], 0, 8) == "product=" && substr($url_array[6], 0, 13) == "media_format=")) {
						if ($url_count == 5) {
							$this->media_format_id = 0;
						} else {
							$this->media_format_id = substr($url_array[6], 13);
						}
						if (substr($url_array[8], 9) != "") {
							$this->language_code = substr($url_array[8], 9);
						}
						$ap_item_data = new ItemDataMySql( $m_dbAll );
						$this->item_base_name = urlToName($url_array[4]);
						$this->item_base_id = $ap_item_data->nameToID ($this->artist_id, $this->item_base_name, 1);
						if ($this->item_base_id != 0) {
							$this->page_to_show = "album"; 
						}
					} else if ((($url_array[3] == "song" || $url_array[3] == "sang") && $url_array[4] != "" && $url_count == 5) || (($url_array[3] == "song" || $url_array[3] == "sang") && $url_array[4] != "" && $url_count >= 7 && substr($url_array[5], 0, 8) == "product=" && substr($url_array[6], 0, 13) == "media_format=")) {
						$this->media_format_id = substr($url_array[6], 13);
						if (substr($url_array[8], 9) != "") {
							$this->language_code = substr($url_array[8], 9);
						}
						$ap_item_data = new ItemDataMySql( $m_dbAll );
						$this->item_base_name = urlToName($url_array[4]);
						$this->item_base_id = $ap_item_data->nameToID ($this->artist_id, $this->item_base_name, 2);
						if ($this->item_base_id != 0) {
							$this->page_to_show = "song";
						}
					}
				}
			}
		}
	}
	
	public function setTokenValues() {
		if ($this->language_code == "DK") {
			$this->token_tab_album 			= "Album";
			$this->token_tab_song 				= "Sang";
			$this->token_tab_merchandise 	= "Merchandise";
			$this->token_tab_concert		 	= "Koncert";
			$this->token_title						= "Titel";
			$this->token_buy						= "KØB";
			$this->token_price						= "PRIS";
			$this->token_venue					= "SPILLESTED";
			$this->token_streaming				= "STREAMING";
			$this->token_media_format		= "MEDIA FORMAT";
			$this->token_start_time				= "Tidspunkt";
			$this->token_view_price				= "Se <span class='price_count'>[ITEM_COUNT]</span> pris";
			$this->token_view_prices			= "Se <span class='price_count'>[ITEM_COUNT]</span> priser";
			$this->token_all							= "Alle";
			$this->token_show_more_media_format = "Flere formater";
			$this->token_show_currency		= "Vælg valuta";
			$this->token_compare_prices		= "Sammenlign priser";
			$this->token_record_store_top_icon_text = "Musiksøgemaskinen søger blandt 100+ pladeforretninger bl.a. ";
			$this->token_record_store_top_text = "<h2>Guide til [ARTIST_NAME] udgivelser</h2>Her kan du finde al den musik, [ARTIST_NAME] har udgivet gennem tiden - der er masser at vælge imellem, og du kan sammenligne priser og formater på en let og overskuelig måde. Vi håber, at I vil tage godt imod denne service!";
		} else {
			$this->token_tab_album 			= "Album";
			$this->token_tab_song 				= "Song";
			$this->token_tab_merchandise 	= "Merchandise";
			$this->token_tab_concert		 	= "Concert";
			$this->token_title						= "Title";
			$this->token_buy						= "BUY";
			$this->token_price						= "PRICE";
			$this->token_venue					= "VENUE";
			$this->token_streaming				= "STREAMING";
			$this->token_media_format		= "MEDIA FORMAT";
			$this->token_start_time				= "Starttime";
			$this->token_view_price				= "See <span class='price_count'>[ITEM_COUNT]</span> price";
			$this->token_view_prices			= "See <span class='price_count'>[ITEM_COUNT]</span> prices";
			$this->token_all							= "All";
			$this->token_show_more_media_format = "More formats";
			$this->token_show_currency		= "Choose currency";
			$this->token_compare_prices		= "Compare prices";
			$this->token_record_store_top_icon_text = "Musiksøgemaskinen søger blandt 100+ pladeforretninger bl.a. ";
			$this->token_record_store_top_text = "<h2>Guide to [ARTIST_NAME] releases</h2>Here you can find all the music, [ARTIST_NAME] has released - there are plenty to choose from, and you can compare prices and formats in an easy and straightforward manner. We hope that you will welcome this service!";
		}
	}
	

	/* Set currency code */
	public function setCurrencyCode() {
		if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != "") {
			$url_array = explode("/",$_SERVER["QUERY_STRING"]);
			$url_count = count($url_array);
			if ($url_count > 0) {
				if ($url_array[2] != "" && ($url_count == 3 || $url_count == 4 || $url_count == 5)) {
					$this->currency_code = "DKK";
				} else if ($url_array[2] != "" && $url_count == 7  && substr($url_array[5], 0, 9) == "currency=") {
					$this->currency_code = substr($url_array[5], 9);
				} else if ($url_array[2] != "" && $url_count == 9  && substr($url_array[7], 0, 9) == "currency=") {
					$this->currency_code = substr($url_array[7], 9);
				}
			}
		}
	}

	public function setFacebookAppSettings() {
		// Agnes Obel
		if ($this->artist_id == 85023 || $this->artist_id == 966) {
			$this->fb_app_id = 226608287511353;
			$this->fb_comments = false;
			$this->fb_comments_data_href = "";
			$this->ap_show_streaming = true;
		// One Direction
		} else if ($this->artist_id == 84771 || $this->artist_id == 1) {
			$this->fb_app_id = 226608287511353;
			$this->fb_comments = false;
			$this->fb_comments_data_href = "";
			$this->ap_show_streaming = true;
		} else if ($this->artist_id == 11407 || $this->artist_id == 2) {
			$this->fb_app_id = 578039368958275;
			$this->fb_comments = true;
			$this->fb_comments_data_href = "http://www.airplaymusic.dk/kunstner/Big_Fat_Snake";
			$this->ap_show_streaming = false;
		}
	}
	
	
	/****************
		Get functions 
	****************/
	public function getLanguageChangeHTML() {
		$html = "";
		$url_danish = "";
		$url_english = "";
		$product = "";
		if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != "") {
			$url_array = explode("/",$_SERVER["QUERY_STRING"]);
			$url_danish .= "/kunstner/" . $url_array[2];
			$url_english .= "/artist/" . $url_array[2];
			if ($this->item_type == 1) {
				$product = "album"; 
			} else if ($this->item_type == 2) {
				$product = "song"; 
			} else if ($this->item_type == 3) {
				$product = "merchandise"; 
			}
			if (count($url_array) == 9) {
				if ($this->page_to_show == "album") {
					$url_english .= "/album/" .  $url_array[4];
					$url_danish .= "/album/" .  $url_array[4];
					$product = "album"; 
				} else if ($this->page_to_show == "song") {
					$url_english .= "/song/" .  $url_array[4];
					$url_danish .= "/sang/" .  $url_array[4];
					$product = "song"; 
				}
			}
			$url_english .= "/product={$product}/media_format={$this->media_format_id}/currency={$this->currency_code}/language=UK";
			$url_danish .= "/product={$product}/media_format={$this->media_format_id}/currency={$this->currency_code}/language=DK";
		}
		$html .= "<div style='width:100%;float:left;text-align:right;'><b><a href='{$url_danish}'>Dansk</a></b>&nbsp;&nbsp;<b><a href='{$url_english}'>English</a></b></div>";
		return $html;
	}
	
	public function getHTMLBFSHeader($artist_id) {
		$s = "";
		// page type i class name
		$s = "<table class='header-intro-table' border='0' cellspacing='0' cellpadding='0'>";
		$s .= "<tr>";
		$s .= "<td class='picture'><img src='/images/artist/big_fat_snake.png' border='0' width='800' height='330' /></td>";
		$s .= "</tr>";
		$s .= "</table>";
		return $s;
	}	
	
	public function getHTMLHeader($artist_id) {
		$s = "";
		// page type i class name
		$s = "<table class='header-intro-table' border='0' cellspacing='0' cellpadding='0'>";
		
		$s .= "<tr>";
		$s .= "<td class='picture'><img src='/images/artist/agnes_obel.jpg' border='0' width='200' /></td>";
		$s .= "<td class='text'>";
		$s .= "<div class='item_name'>Aventine</div>";
		$s .= "<div class='item_text'>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</div>";
		$s .= "<div class='streaming_icons'><div class='label'>Streaming:</div><span class='spotify'><a target='_blank' href='https://play.spotify.com/album/3eVwN9Qju2pxBERkOQQv1Q'><img width='25' height='25' border='0' alt='Spotify' title='Spotify' src='/images/icons/spotify_25x25_icon.png'></a></span><span class='wimp'><a target='_blank' href='http://wimp.dk/album/22757081'><img width='25' height='25' border='0' alt='WiMP' title='WiMP' src='/images/icons/wimp_25x25_icon.png'></a></span><span class='deezer'><a target='_blank' href='http://www.deezer.com/en/album/6982745?app_id=116135'><img width='45' height='25' border='0' alt='Deezer' title='Deezer' src='/images/icons/deezer_25x57_icon.png'></a></span><span class='rdio'><a target='_blank' href='http://click.linksynergy.com/fs-bin/click?id=YhwuE0trgIw&amp;subid=&amp;offerid=221756.1&amp;type=10&amp;tmpid=7950&amp;RD_PARM1=http%253A%252F%252Fwww.rdio.com%252Fartist%252FAgnes_Obel%252Falbum%252FAventine%252F'><img width='24' height='25' border='0' alt='Rdio' title='Rdio' src='/images/icons/rdio_25x25_icon.png'></a></span><span class='napster'><a target='_blank' href='http://www.napster.com/artist/agnes-obel/album/aventine-play-it-again-sam'><img width='25' height='25' border='0' alt='Napster' title='Napster' src='/images/icons/napster_25x25_icon.png'></a></span><span class='spacer'>&nbsp;</span></div>";
		$s .= "<div class='record_store_icons'>";
		

		$s .= "<div class='icons'><div class='label'>MP3:</div><div class='icons'>";
		$s .= "<div class='icon'><a href='http://www.dpbolvw.net/click-7265513-10364616?url=http://www.emusic.com/album/agnes-obel/aventine/14422200/' target='_blank' title='emusic'><img src='/images/icons/emusic_icon.png' border='0' /></a></div>";
		$s .= "<div class='icon'><a href='http://www.7digital.com/artist/agnes-obel/release/aventine/?partner=5898' target='_blank' title='7digital'><img src='/images/icons/7digital_icon.png' border='0' /></a></div>";
		$s .= "<div class='icon'><a href='http://clk.tradedoubler.com/click?p(24375)a(1933478)g(11696696)url(https%3A%2F%2Fitunes.apple.com%2Fdk%2Falbum%2Faventine%2Fid681187053%3Fuo%3D4%26partnerId%3D2003)' target='_blank' title='iTunes'><img src='/images/icons/itunes_icon.png'  border='0' /></a></div>";
		$s .= "<div class='link'><a href='/kunstner/Agnes_Obel/album/Aventine/product=album/media_format=3/currency={$this->currency_code}'  title='{$this->token_compare_prices}' target='_self'>+ 3 priser</a></div>";
		$s .= "</div></div>";

		$s .= "<div class='icons'><div class='label'>CD:</div><div class='icons'>";
		$s .= "<div class='icon'><a href='http://www.amazon.co.uk/Aventine-Agnes-Obel/dp/B00E6OQL68%3FSubscriptionId%3DAKIAI4XC3QUSC45AT7BQ%26tag%3Dairpmusi-21%26linkCode%3Dsp1%26camp%3D2025%26creative%3D165953' target='_blank' title='Amazon'><img src='/images/icons/amazon_icon.png' border='0' /></a></div>";
		$s .= "<div class='icon'><a href='http://clk.tradedoubler.com/click?p(120)a(1933478)g(16263494)url(http://cdon.dk/musik/agnes_obel/aventine_(danish_edition_)-23986002)' target='_blank' title='CdON'><img src='/images/icons/cdon_icon.png' border='0' /></a></div>";
		$s .= "<div class='icon'><a href='http://www.imusic.dk/cd/5414939563324/' target='_blank' title='iMusic'><img src='/images/icons/imusic_icon.png' border='0' /></a></div>";
		$s .= "<div class='icon'><a href='http://www.stereostudio.dk/cd-lp-obel-agnes-aventine-limited-deluxe-box.html' target='_blank' title='Stereo Studio'><img src='/images/icons/stereostudio_icon.png' border='0' /></a></div>";
		$s .= "<div class='link'><a href='/kunstner/Agnes_Obel/album/Aventine/product=album/media_format=5/currency={$this->currency_code}'  title='{$this->token_compare_prices}' target='_self'>+ 24 priser</a></div>";
		$s .= "</div></div>";

		$s .= "<div class='icons'><div class='label'>Vinyl:</div><div class='icons'>";
		$s .= "<div class='icon'><a href='http://www.amazon.co.uk/Aventine-VINYL-Agnes-Obel/dp/B00E6P2EZE%3FSubscriptionId%3DAKIAI4XC3QUSC45AT7BQ%26tag%3Dairpmusi-21%26linkCode%3Dsp1%26camp%3D2025%26creative%3D165953' target='_blank' title='Amazon'><img src='/images/icons/amazon_icon.png' border='0' /></a></div>";
		$s .= "<div class='icon'><a href='http://clk.tradedoubler.com/click?p(216473)a(2284745)g(20292054)url(http%253A%252F%252Fwww.platekompaniet.no%252F%252FMusikk.aspx%252FLP%252FAgnes_Obel%252FAventine_VINYL%252F%253Fid%253DPIASR615LP)' target='_blank' title='Platekompaniet'><img src='/images/icons/platekompaniet_icon.png' border='0' /></a></div>";
		$s .= "<div class='icon'><a href='http://www.stereostudio.dk/lp-obel-agnes-aventine-lp-inkl-cd.html' target='_blank' title='Stereo Studio'><img src='/images/icons/stereostudio_icon.png' border='0' /></a></div>";
		$s .= "<div class='icon'><a href='http://www.imusic.dk/lp/5414939567919/' target='_blank' title='iMusic'><img src='/images/icons/imusic_icon.png' border='0' /></a></div>";
		$s .= "<div class='link'><a href='/kunstner/Agnes_Obel/album/Aventine/product=album/media_format=7/currency={$this->currency_code}'  title='{$this->token_compare_prices}' target='_self'>+ 19 priser</a></div>";
		$s .= "</div></div>";
		
		$s .= "</div>";
		$s .= "</td>";
		$s .= "</tr>";
		$s .= "</table>";
		return $s;
	}
	
	public function getURLArtistString() {
		if ($this->language_code == "DK") {
			return "kunstner";
		} else if ($this->language_code == "UK") {
			return "artist";
		}
	}
	
	private function getURLItemTypeString() {
		if ($this->item_type == 1) {
			return "album";
		} else if ($this->item_type == 2) {
			if ($this->language_code == "DK") {
				return "sang";
			} else if ($this->language_code == "UK") {
				return "song";
			}
		} else if ($this->item_type == 3) {
			return "merchandise";
		} else if ($this->item_type == 4) {
			return "concert";
		}
	}
	
	public function getArtistPageAlbumSongHTML($aData) {
		$url = "";
		if ($this->item_type == 1) {
			$item_type_name = "album";
		} else {
			$item_type_name = "song";
		}
		
		$s = "<table class='list-price-table' border='0' cellspacing='0' cellpadding='0'>";
		$s .= "<thead>";
		$s .= "<tr>";
		//$s .= "<td class='title'>{$this->token_title}</td>";
		$s .= "<td class='title'>&nbsp;</td>";
		$s .= "<td class='buy_at'>{$this->token_buy}<br>" . $this->getMediaFormatLinksArtistPage() . "</td>";
		if ($this->ap_show_streaming == true) {
			$s .= "<td class='streaming'>{$this->token_streaming}<br/> " . $this->getCurrencyLinks() . "</td>";
		} else {
			$s .= "<td class='streaming'>&nbsp;<br/>" . $this->getCurrencyLinks() . "</td>";
		}
		$s .= "</tr>";
		$s .= "</thead>";

		$record_store_icons = $this->ap_get_small_recordstore_icons();
		$streaming_icons = $this->ap_get_small_streaming_icons();
		
		foreach ($aData as $a) {

			$page_letter = strtoupper(substr($a['item_base_name'], 0, 1));  // Used for paging.
			if (!preg_match("/^[a-zA-Z]$/", $page_letter)) {
				$page_letter = "other";
			}
			$page_letter = "A";
			/*if (!in_array($page_letter,$array_page_letters)) {
				$array_page_letters[] = $page_letter;
			}*/
		
			if ($count%2 == 0) {
				$class = "odd";
			} else {
				$class = "even";
			}
			if ($a["item_prices_count"] == 1) {
				$text_prices_count = str_replace("[ITEM_COUNT]", $a["item_prices_count"], $this->token_view_price);
			} else {
				$text_prices_count = str_replace("[ITEM_COUNT]", $a["item_prices_count"], $this->token_view_prices);
			}
			$url = "<a href='/" . $this->getURLArtistString() . "/" . nameToUrl($a["artist_name"]) . "/" . $this->getURLItemTypeString() . "/" . nameToUrl($a["item_base_name"]) . "/product=/media_format={$this->media_format_id}/currency={$this->currency_code}/language={$this->language_code}'  title='{$this->token_compare_prices}'>{$text_prices_count}</a>";

			$s .= "<tr class='$class {$item_type_name}-page-{$page_letter}'>";
			$s .= "<td class='title'>" . $a["item_base_name"] .  "</td>";
			//$a["release_date"] 
			$s .= "<td class='buy_at'>";
			$s .= "<div class='text'>" . $url . "</div>";
			//$s .= "<div class='icons'>";
			//$s .= $record_store_icons;
			$s .= "<div class='price'><span class='min-price'>" . $this->airplay_format_price($a["min_price_local"]) . "</span> - <span class='max-price'>" . $this->airplay_format_price($a["max_price_local"]) . "</span> <span class='currency'>" . $this->currency_code . "</span></div>";
			//$s .= "</div>";
			//$s .= "<div class='text'>";
			//$s .= $url;
			//$s .= "</div>";
			$s .= "</td>";
			$s .= "<td class='streaming'>";
			$s .= $streaming_icons;
			$s .= "</td>";
			$s .= "</tr>";
			$count++;
		}
		$s .= "</table>";

		return $s;	
	}
	
	
	public function getArtistPageMerchandiseHTML($aData) {
		$url = "";
		
		$s = "<table class='list-price-table' border='0' cellspacing='0' cellpadding='0'>";
		$s .= "<thead>";
		$s .= "<tr>";
		//$s .= "<td class='title'>{$this->token_title}</td>";
		$s .= "<td class='title'>&nbsp;</td>";
		$s .= "<td class='media_format'>{$this->token_media_format} " . $this->getMediaFormatLinksArtistPage() . "</td>";
		$s .= "<td class='price'>{$this->token_price}</td>";
		$s .= "<td class='buy_at'>&nbsp;<br/> " . $this->getCurrencyLinks() . "</td>";
		$s .= "</tr>";
		$s .= "</thead>";

		// Items
		foreach ($aData as $a) {
			if ($count%2 == 0) {
				$class = "odd";
			} else {
				$class = "even";
			}
			if ($a['use_affiliate'] == 1) {
				$buy_at_url = $this->ap_replace_affiliate_link($a['buy_at_url'], $a['affiliate_link'], $a['affiliate_encode_times']);
			} else {
				$buy_at_url = $a["buy_at_url"];
			}
			
			$link = "<a href='" . $buy_at_url . "' target='_blank' title='" . $a['record_store_name'] . "'>" . $a['record_store_name'] . "</a>";
			
			$s .= "<tr class='$class'>";
			$s .= "<td class='title'>" . $a["item_price_name"] . "</td>";
			$s .= "<td class='media_format'>" . $a["media_format_name"] . "</td>";
			$s .= "<td class='price'>" . $this->airplay_format_price($a["price_local"]) . "&nbsp;<span class='currency'>" . $this->currency_code . "</span></td>";
			$s .= "<td class='buy_at'>" . $link . "</td>";
			$s .= "</tr>";
			$count++;
		}
		$s .= "</table>";
		return $s;
	}
	
	public function getArtistPageConcertHTML($aData) {
		$url = "";
		
		$s = "<table class='list-price-table' border='0' cellspacing='0' cellpadding='0'>";
		$s .= "<thead>";
		$s .= "<tr>";
		//$s .= "<td class='title'>{$this->token_title}</td>";
		$s .= "<td class='title'>&nbsp;</td>";
		$s .= "<td class='date_time'>{$this->token_start_time}</td>";
		$s .= "<td class='price'>{$this->token_price}</td>";
		$s .= "<td class='venue'>{$this->token_venue}<br/> " . $this->getCurrencyLinks() . "</td>";
		$s .= "</tr>";
		$s .= "</thead>";

		// Items
		foreach ($aData as $a) {
			if ($count%2 == 0) {
				$class = "odd";
			} else {
				$class = "even";
			}
			if ($a['use_affiliate'] == 1) {
				$buy_at_url = $this->ap_replace_affiliate_link($a['buy_at_url'], $a['affiliate_link'], $a['affiliate_encode_times']);
			} else {
				$buy_at_url = $a["buy_at_url"];
			}
			$link = "<a href='" . $buy_at_url . "' target='_blank' title='" . $a['record_store_name'] . "'>" . $a['record_store_name'] . "</a>";

			$s .= "<tr class='$class'>";
			$s .= "<td class='title'>" . $a["item_price_name"] . "</td>";
			$s .= "<td class='date_time'>" . $this->ap_format_date_time($a["item_date_time"] ) . "</td>";
			$s .= "<td class='price'>" . $this->airplay_format_price($a["price_local"]) . "&nbsp;<span class='currency'>" . $this->currency_code . "</span></td>";
			$s .= "<td class='venue'>" . $link . "</td>";
			$s .= "</tr>";
			$count++;
		}
		$s .= "</table>";
		return $s;
	}
	
	
	public function getAlbumSongPageHTML($aData) {
		$url = "";

		$s .= "<table class='list-price-table' border='0' cellspacing='0' cellpadding='0'>";
		$s .= "<thead>";
		$s .= "<tr class='item'>";
		//$s .= "<td class='title'>{$this->token_title}</td>";
		$s .= "<td class='title'>&nbsp;</td>";
		$s .= "<td class='media_format'>{$this->token_media_format}<br/>" . $this->getMediaFormatLinksItemPage() . "</td>";
		$s .= "<td class='price'>{$this->token_price}</td>";
		$s .= "<td class='buy_at'>{$this->token_buy}<br/>" . $this->getCurrencyLinks() . "</td>";
		if ($this->ap_show_streaming == true) {
			$s .= "<td class='list-song-price-value'>{$this->token_streaming}</td>";
		} else {
			$s .= "<td class='list-song-price-value'>&nbsp;</td>";
		}
		$s .= "</tr>";
		$s .= "</thead>";
		$s .= "<tbody>";
		
		foreach ($aData as $a) {
			if ($count%2 == 0) {
				$class = "odd";
			} else {
				$class = "even";
			}
			if ($a['use_affiliate'] == 1) {
				$buy_at_url = $this->ap_replace_affiliate_link($a['buy_at_url'], $a['affiliate_link'], $a['affiliate_encode_times']);
			} else {
				$buy_at_url = $a["buy_at_url"];
			}
			$link = "<a href='" . $buy_at_url . "' target='_blank' title='" . $a['record_store_name'] . "'>" . $a['record_store_name'] . "</a>";
			
			$s .= "<tr class='item $class'>";
			$s .= "<td class='title'>" . $a["item_price_name"] . "</td>";
			$s .= "<td class='media_format'>" . $a["media_format_name"] . "</td>";
			$s .= "<td class='price'>" . $this->airplay_format_price($a["price_local"]) . " {$this->currency_code}</td>";
			$s .= "<td class='buy_at'>" . $link . "</td>";
			$s .= "<td class='list-song-price-value'>" . str_replace(".", "", str_replace(",", "", $this->airplay_format_price($a["price_local"]))) . "</td>";
			$s .= "</tr>";

			$count++;
		}
		$s .= "</tbody>";
		$s .= "</table>";
		return $s;
	}
	
	private function getCurrencyLinks() {
		$s = "";
		$url_artist_string = $this->getURLArtistString();
		$url_artist_name = nameToUrl($this->artist_name);
		$url_item_type_string = $this->getURLItemTypeString();
		if ($url_item_type_string == "sang") { $url_item_type_string = "song"; }
		$url_item_name_string = nameToUrl($this->item_base_name);
		$aData = array ( array ("currency_name" => "DKK"), array ("currency_name" => "EUR"), array ("currency_name" => "SEK"), array ("currency_name" => "NOK"), array ("currency_name" => "GBP"), array ("currency_name" => "USD") );

		$s .= "<div id='currency_codes_container'>";
		
		foreach ($aData as $a) {
			if ($a["currency_name"] == "DKK" || $a["currency_name"] == "EUR") {
				if ($this->currency_code == $a["currency_name"]) { $class = "selected"; } else { $class = ""; }
				$s .= "<a class='$class' href='/" . $url_artist_string . "/" . $url_artist_name;
				if ($this->item_base_name != "") {
					$s .= "/" . $url_item_type_string . "/" . $url_item_name_string;
				}
				$s .= "/product={$url_item_type_string}";
				$s .= "/media_format={$this->media_format_id}/currency=" . $a["currency_name"] . "/language={$this->language_code}'>" . $a["currency_name"] . "</a>&nbsp;&nbsp;";
			}
		}
		
		$s .= "<a href='javascript:void(0);' id='currency_header_link'>{$this->token_show_currency}</a>";
		$s .= "<div id='currency_codes'>";
		
		foreach ($aData as $a) {
			if ($this->currency_code == $a["currency_name"]) { $class = "selected"; } else { $class = ""; }
			
			$s .= "<a class='$class' href='/" . $url_artist_string . "/" . $url_artist_name;
			if ($this->item_base_name != "") {
				$s .= "/" . $url_item_type_string . "/" . $url_item_name_string;
			}
			$s .= "/product={$url_item_type_string}";
			$s .= "/media_format={$this->media_format_id}/currency=" . $a["currency_name"] . "/language={$this->language_code}'>" . $a["currency_name"] . "</a>&nbsp;&nbsp;";
		}
		$s .= "</div>";
		$s .= "</div>";
$s .=
<<<SCRIPT
<script type="text/javascript">
	jQuery("#currency_codes").hide(); jQuery('#currency_header_link,#currency_codes').click(function(event) { jQuery('#currency_codes').show(); event.stopPropagation(); }); jQuery("html").click(function() { jQuery("#currency_codes").hide(); });
</script>
SCRIPT;
		return $s;
	}
	
	
	private function getMediaFormatLinksArtistPage() {
		$s = "";
		$aUniqueMediaFormats = array();
		$selected_media_format_name = "";
		$url_artist_string = $this->getURLArtistString();
		$url_artist_name = nameToUrl($this->artist_name);
		$url_item_type_string = $this->getURLItemTypeString();
		if ($url_item_type_string == "sang") { $url_item_type_string = "song"; }
		$url_item_name_string = nameToUrl($this->item_base_name);
		$aData = $this->getPriceCountPrMediaFormatForArtist();
		$media_format_count = count($aData);
		$count = 1;
		
		$s .= "<div id='media_formats_container'>";
		if (count($aData) > 1) {
			$s .= "<a class='$class' href='/" . $url_artist_string . "/" . $url_artist_name;
			if ($this->item_base_name != "") {
				$s .= "/" . $this->getURLItemTypeString() . "/" . $url_item_name_string;
			}
			$s .= "/product={$url_item_type_string}/media_format=0/currency={$this->currency_code}/language={$this->language_code}'>{$this->token_all}</a>";
			$s .= "&nbsp;&nbsp;-&nbsp;&nbsp;";
		}
		foreach ($aData as $a) {
			if ($a["media_format_name"] == "MP3" || $a["media_format_name"] == "Vinyl" || $a["media_format_name"] == "CD") {
				if ($this->media_format_id == $a["media_format_id"]) { $class = "selected"; $selected_media_format_name = $a["media_format_name"]; } else { $class = ""; }
				$s .= "<a class='$class' href='/" . $url_artist_string . "/" . $url_artist_name;
				if ($this->item_base_name != "") {
					$s .= "/" . $this->getURLItemTypeString() . "/" . $url_item_name_string;
				}
				$s .= "/product={$url_item_type_string}/media_format=" . $a["media_format_id"] . "/currency={$this->currency_code}/language={$this->language_code}'>" . $a["media_format_name"] . "</a>";
				if ($count != $media_format_count) {
					$s .= "&nbsp;&nbsp;-&nbsp;&nbsp;";
				}
				if (!in_array($a["media_format_name"], $aUniqueMediaFormats)) {
					$aUniqueMediaFormats[] = $a["media_format_name"];
				}
			}
			$count++;
		}
		if ($media_format_count != count($aUniqueMediaFormats)) {
			$s .= "<a href='javascript:void(0);' id='media_format_header_link'>{$this->token_show_more_media_format}</a>";
			
			if ($this->media_format_id == 0) { $class = "selected"; } else { $class = ""; }
			
			$s .= "<div id='media_formats'>";
			$s .= "<a class='$class' href='/" . $url_artist_string . "/" . $url_artist_name;
			if ($this->item_base_name != "") {
				$s .= "/" . $this->getURLItemTypeString() . "/" . $url_item_name_string;
			}
			$s .= "/product={$url_item_type_string}/media_format=0/currency={$this->currency_code}/language={$this->language_code}'>" . $this->token_all . "</a>&nbsp;&nbsp;";
			
			foreach ($aData as $a) {
				if ($this->media_format_id == $a["media_format_id"]) { $class = "selected"; $selected_media_format_name = $a["media_format_name"]; } else { $class = ""; }
				$s .= "<a class='$class' href='/" . $url_artist_string . "/" . $url_artist_name;
				if ($this->item_base_name != "") {
					$s .= "/" . $this->getURLItemTypeString() . "/" . $url_item_name_string;
				}
				$s .= "/product={$url_item_type_string}/media_format=" . $a["media_format_id"] . "/currency={$this->currency_code}/language={$this->language_code}'>" . $a["media_format_name"] . "</a>&nbsp;&nbsp;";
			}
			$s .= "</div>";
		}
		$s .= "</div>";

$s .=
<<<SCRIPT
<script type="text/javascript">
	var selected_media_format_name = '{$selected_media_format_name}';
	jQuery("#media_formats").hide(); jQuery('#media_format_header_link,#media_formats').click(function(event) { jQuery('#media_formats').show(); event.stopPropagation(); }); jQuery("html").click(function() { jQuery("#media_formats").hide(); });
</script>
SCRIPT;
		return $s;
	}
	
	private function getMediaFormatLinksItemPage() {
		$s = "";
		$aUniqueMediaFormats = array();
		$url_artist_string = $this->getURLArtistString();
		$url_artist_name = nameToUrl($this->artist_name);
		$url_item_type_string = $this->getURLItemTypeString();
		/* JHACK */ 
		if ($url_item_type_string == "sang") { $url_item_type_string = "song"; }
		$url_item_name_string = nameToUrl($this->item_base_name);
		$aData = $this->getPriceCountPrMediaFormatForItem();
		$media_format_count = count($aData);
		$count = 1;
		
		$s .= "<div id='media_formats_container'>";
		if (count($aData) > 1) {
			$s .= "<a class='$class' href='/" . $url_artist_string . "/" . $url_artist_name;
			if ($this->item_base_name != "") {
				$s .= "/" . $this->getURLItemTypeString() . "/" . $url_item_name_string;
			}
			$s .= "/product={$url_item_type_string}/media_format=0/currency={$this->currency_code}/language={$this->language_code}'>{$this->token_all}</a>";
			$s .= "&nbsp;&nbsp;-&nbsp;&nbsp;";
		}
		foreach ($aData as $a) {
			if ($a["media_format_name"] == "MP3" || $a["media_format_name"] == "Vinyl" || $a["media_format_name"] == "CD") {
				if ($this->media_format_id == $a["media_format_id"]) { $class = "selected"; } else { $class = ""; }
				$s .= "<a class='$class' href='/" . $url_artist_string . "/" . $url_artist_name;
				if ($this->item_base_name != "") {
					$s .= "/" . $this->getURLItemTypeString() . "/" . $url_item_name_string;
				}
				$s .= "/product={$url_item_type_string}/media_format=" . $a["media_format_id"] . "/currency={$this->currency_code}/language={$this->language_code}'>" . $a["media_format_name"] . "</a>";
				if ($count != $media_format_count) {
					$s .= "&nbsp;&nbsp;-&nbsp;&nbsp;";
				}
				if (!in_array($a["media_format_name"], $aUniqueMediaFormats)) {
					$aUniqueMediaFormats[] = $a["media_format_name"];
				}
			}
			$count++;
		}
		if ($media_format_count != count($aUniqueMediaFormats)) {
			$s .= "<a href='javascript:void(0);' id='media_format_header_link'>{$this->token_show_more_media_format}</a>";
			
			if ($this->media_format_id == 0) { $class = "selected"; } else { $class = ""; }
			
			$s .= "<div id='media_formats'>";
			$s .= "<a class='$class' href='/" . $url_artist_string . "/" . $url_artist_name;
			if ($this->item_base_name != "") {
				$s .= "/" . $this->getURLItemTypeString() . "/" . $url_item_name_string;
			}
			$s .= "/product={$url_item_type_string}/media_format=0/currency={$this->currency_code}/language={$this->language_code}'>" . $this->token_all . "</a>&nbsp;&nbsp;";

			foreach ($aData as $a) {
				if ($this->media_format_id == $a["media_format_id"]) { $class = "selected"; } else { $class = ""; }
				$s .= "<a class='$class' href='/" . $url_artist_string . "/" . $url_artist_name;
				if ($this->item_base_name != "") {
					$s .= "/" . $this->getURLItemTypeString() . "/" . $url_item_name_string;
				}
				$s .= "/product={$url_item_type_string}/media_format=" . $a["media_format_id"] . "/currency={$this->currency_code}/language={$this->language_code}'>" . $a["media_format_name"] . "</a>&nbsp;&nbsp;";
			}
			$s .= "</div>";
		}
		$s .= "</div>";
		$s .=
<<<SCRIPT
<script type="text/javascript">
	jQuery("#media_formats").hide(); jQuery('#media_format_header_link,#media_formats').click(function(event) { jQuery('#media_formats').show(); event.stopPropagation(); }); jQuery("html").click(function() { jQuery("#media_formats").hide(); });
</script>
SCRIPT;
		return $s;
	}	
	
	private function ap_replace_affiliate_link($buy_at_url, $affiliate_link, $affiliate_encode_times) {
		if ($affiliate_encode_times == 0) {
			return str_replace("[TARGET_URL]", $buy_at_url ,$affiliate_link);
		} else {
			for ($i=0;$i<=$affiliate_encode_times;$i++) {
				$buy_at_url = urlencode($buy_at_url);
			}
			return str_replace("[TARGET_URL]", $buy_at_url ,$affiliate_link);
		}
	}
	
	
	private function airplay_format_price( $fPrice ) {
		$fPrice = round( $fPrice*20 ) * 0.05;
		return number_format  ( $fPrice , 2 , ',', "."  );
	}
	
	private function ap_format_date_time($date_time) {

		$date_time = explode(" ", $date_time);
        $date_time_date = explode("-", $date_time[0]);
        $date_time_time = $date_time[1];
        $date_time_time = substr($date_time_time, 0, 5);
        if ($this->language_code == "DK") {
            return $date_time_date[2] . "." . $date_time_date[1] . "." . $date_time_date[0] . "&nbsp;&nbsp;kl. " . $date_time_time;
        } else {
            return $date_time_date[0] . "." . $date_time_date[1] . "." . $date_time_date[2] . "&nbsp;&nbsp;at " . $date_time_time;
        }
	}
	
	/*
		Make an array with names of valid record_stores to use in the dynamic music lookup. This way we can make sure that WiMP does not start on ap.co.uk.
		Returns an array.
	*/
	private function getDynamicMusicLookupValidRecordstores($lookup_type) {
		$oRecord_stores_streaming = array();
		$oRecord_stores_other = array();
		
		if ($lookup_type == "streaming") {
			$oRecord_stores_streaming = array("spotify", "rdio", "deezer", "napster", "wimp");
			if ($this->language_code == "DK") {
				$oRecord_stores_streaming[] = "wimp";
			}
		} else if ($lookup_type == "MP3") {
			$oRecord_stores_other = array("itunes", "7digital");
		}
		$oRecord_stores = array_merge($oRecord_stores_streaming, $oRecord_stores_other);
		return $oRecord_stores;
	}
	
	
	public function getDynamicMusicLookupArtistJavascriptVariables($lookup_type) {
			$sHTML = "";
			$sHTML .= "var token_view_price = \"{$this->token_view_price}\";";
			$sHTML .= "var token_view_prices = \"{$this->token_view_prices}\";";
			$sHTML .= "var currency_code = \"{$this->currency_code}\";";
			$oRecord_stores = $this->getDynamicMusicLookupValidRecordstores($lookup_type);
			foreach ($oRecord_stores AS $record_stores) {
				$sHTML .= "var oAlbumJSON" . $record_stores ." = '';var oSongJSON" . $record_stores ." = '';";
			}
			return $sHTML;
		}
	
	/*
	   Get the Javascript HTML for using on "artist" page.
	*/
	public function getDynamicMusicLookupArtistAlbums($lookup_type) {
		$sHTML = "";
		$artist_name = str_replace("'", "\'", $this->artist_name);
		$oRecord_stores = $this->getDynamicMusicLookupValidRecordstores($lookup_type);
		
		foreach ($oRecord_stores AS $record_stores) {
			$sHTML .= "jQuery.ajax({type: 'POST', url: '/dynamic_music_lookup.php', data: { s: '". $record_stores ."', aid: {$this->artist_id}, q: '', qa: '{$artist_name}', t: 'artist_album', c: '{$this->language_code}', timestamp: '" . microtime(true) . "'}, dataType: 'json', success: parseJSONItemsForArtistPageAlbums });";
		}
		return $sHTML;
	}

	public function getDynamicMusicLookupArtistSongs($lookup_type) {
		$sHTML = "";
		$artist_name = str_replace("'", "\'", $this->artist_name);
		$oRecord_stores = $this->getDynamicMusicLookupValidRecordstores($lookup_type);
		foreach ($oRecord_stores AS $record_stores) {
			$sHTML .= "jQuery.ajax({type: 'POST', url: '/dynamic_music_lookup.php', data: { s: '". $record_stores ."', aid: {$this->artist_id}, q: '', qa: '{$artist_name}', t: 'artist_song', c: '{$this->language_code}', timestamp: '" . microtime(true) . "'}, dataType: 'json', success: parseJSONItemsForArtistPageSongs });";
		}
		return $sHTML;
	}

	
	/*
	   Get the Javascript HTML for using on "album" page.
	*/
	public function getDynamicMusicLookupAlbum($lookup_type) {

		$artist_name = str_replace("'", "\'", $this->artist_name);
		$item_base_name = str_replace("'", "\'", $this->item_base_name);
		
		$oRecord_stores = $this->getDynamicMusicLookupValidRecordstores($lookup_type);

		foreach ($oRecord_stores AS $record_stores) {
			$sHTML .= "jQuery.ajax({type: 'POST', url: '/dynamic_music_lookup.php', data: { s: '". $record_stores ."', aid: {$this->artist_id}, iid: {$this->item_base_id}, q: '{$item_base_name}', qa: '{$artist_name}', t: 'album', c: '{$this->language_code}'}, dataType: 'json', success: parseJSONItemsForAlbumPage });";
		}
		return $sHTML;
	}
	
		

	/*
	   Get the Javascript HTML for using on "song" page.
	*/
	public function getDynamicMusicLookupSong($lookup_type) {
		$artist_name = str_replace("'", "\'", $this->artist_name);
		$item_base_name = str_replace("'", "\'", $this->item_base_name);
		$oRecord_stores = $this->getDynamicMusicLookupValidRecordstores($lookup_type);
		foreach ($oRecord_stores AS $record_stores) {
			$sHTML .= "jQuery.ajax({type: 'POST', url: '/dynamic_music_lookup.php', data: { s: '". $record_stores ."', aid: {$this->artist_id}, iid: {$this->item_base_id}, q: '{$item_base_name}', qa: '{$artist_name}', t: 'song', c: '{$this->language_code}'}, dataType: 'json', success: parseJSONItemsForSongPage });";
		}
		return $sHTML;
	}
	
	private function ap_get_small_recordstore_icons() {
		$s = "<div class='icons'>";
		$s .= "<span class='itunes'><a href='javascript:void(0);' target='_blank'><img src='/images/icons/itunes_icon.png' width='55' height='20' title='iTunes' alt='iTunes' border='0' /></a></span>";
		$s .= "<span class='amazon'><a href='javascript:void(0);' target='_blank'><img src='/images/icons/amazon_icon.png' width='55' height='20' title='Amazon' alt='Amazon' border='0' /></a></span>";
		$s .= "<span class='cdon'><a href='javascript:void(0);' target='_blank'><img src='/images/icons/cdon_icon.png' width='55' height='20' title='CDON' alt='CDON' border='0' /></a></span>";
		$s .= "<span class='sevendigital'><a href='javascript:void(0);' target='_blank'><img src='/images/icons/7digital_icon.png' width='55' height='20' title='7digital' alt='7digital' border='0' /></a></span>";
		$s .= "<span class='emusic'><a href='javascript:void(0);' target='_blank'><img src='/images/icons/emusic_icon.png' width='55' height='20' title='emusic' alt='emusic' border='0' /></a></span>";
		$s .= "<span class='imusic'><a href='javascript:void(0);' target='_blank'><img src='/images/icons/imusic_icon.png' width='55' height='20' title='iMusic' alt='iMusic' border='0' /></a></span>";
		$s .= "<span class='platekompaniet'><a href='javascript:void(0);' target='_blank'><img src='/images/icons/platekompaniet_icon.png' width='55' height='20' title='Platekompaniet' alt='Platekompaniet' border='0' /></a></span>";
		$s .= "<span class='stereostudio'><a href='javascript:void(0);' target='_blank'><img src='/images/icons/stereostudio_icon.png' width='55' height='20' title='Stereo Studio' alt='Stereo Studio' border='0' /></a></span>";
		$s .= "<span class='spacer'>&nbsp;</span>";
		$s .= "</div>";
		return $s;
	}
	
	private function ap_get_small_streaming_icons() {
		$s = "<div class='icons'>";
		$s .= "<span class='spotify'><a href='javascript:void(0);' target='_blank'><img src='/images/icons/spotify_icon.png' width='18' height='18' title='Spotify' alt='Spotify' border='0' /></a></span>";
		$s .= "<span class='wimp'><a href='javascript:void(0);' target='_blank'><img src='/images/icons/wimp_icon.png' width='18' height='18' title='WiMP' alt='WiMP' border='0' /></a></span>";
		$s .= "<span class='deezer'><a href='javascript:void(0);' target='_blank'><img src='/images/icons/deezer_icon.png' width='32' height='18' title='Deezer' alt='Deezer' border='0' /></a></span>";
		$s .= "<span class='rdio'><a href='javascript:void(0);' target='_blank'><img src='/images/icons/rdio_icon.png' width='18' height='18' title='Rdio' alt='Rdio' border='0' /></a></span>";
		$s .= "<span class='napster'><a href='javascript:void(0);' target='_blank'><img src='/images/icons/napster_icon.png' width='18' height='18' title='Napster' alt='Napster' border='0' /></a></span>";
		$s .= "</div>";
		return $s;
	}
	
	/*
		Get number of items pr item_type for an artist
	*/
	public function getItemTypeCountForArtist() {
		$ap_artist_data = new ArtistDataMySql( $m_dbAll );
		return $ap_artist_data->getItemTypeCountForArtist($this->artist_id);
	}
	
	/*
		Get number of media format pr item_type for an artist
	*/
	public function getPriceCountPrMediaFormatForArtist() {
		$ap_artist_data = new ArtistDataMySql( $m_dbAll );
		return $ap_artist_data->getPriceCountPrMediaFormatForArtist($this->artist_id, $this->item_type);
	}
	
	/*
		Get number of media format pr item_type for an artist
	*/
	public function getPriceCountPrMediaFormatForItem() {
		$ap_item_data = new ItemDataMySql( $m_dbAll );
		return $ap_item_data->getPriceCountPrMediaFormatForItem($this->item_base_id);
	}

	public function getCurrencyCodesFromToValues() {
		$ap_artist_data = new CurrencyDataMySql( $m_dbAll );
		$aData = $ap_artist_data->getBaseDataRows(0,20);
		foreach ($aData as $a) {
			$aAllRows[] = array("currency_name" => $a["currency_name"], "from_euro" => $a["from_euro"], "to_euro" => $a["to_euro"]);
		}
		return $aData;
	}

	public function getCurrencyFromToJSArrays () {
		$sHTML = "";
		$currencies = $this->getCurrencyCodesFromToValues();
		$sHTML .= "<script type='text/javascript'>";
		$sHTML .= "var oCurrencyToEuro = new Array();var oCurrencyFromEuro = new Array();";
		foreach ($currencies AS $currency) {
			$sHTML .= "oCurrencyToEuro['" . $currency["currency_name"] . "'] = " . $currency["to_euro"] . "; oCurrencyFromEuro['" . $currency["currency_name"] . "'] = " . $currency["from_euro"] . ";";
		}
		$sHTML .= "</script>";
		return $sHTML;
	}
	
	public function formatRecordStoreName($record_store_name) {
		$s = $record_store_name;
		$str_pos = strpos($s, " (");
		$s = substr($s, 0, $str_pos);
		$s = str_replace(" ", "", $s);
		$s = str_ireplace(".com", "", $s);
		$s = str_replace("7", "seven", $s);
		$s = strtolower($s);
		return $s;
	}
	
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
	public		$item_type					= "";
	public		$artist_id						= 0;
	public		$artist_name					= "";
	public		$item_base_id				= 0;
	public		$item_base_name			= "";
	public		$product_name 				= "";
    public		$currency_code 			= "DKK";	// Default danish currency
	public		$language_code 			= "DK";	// Default Danish language
	public 		$page_to_show				= "";
	public		$media_format_id 			= 0;
	public		$fb_app_id					= 0;
	public		$fb_comments				= false;
	public		$fb_comments_data_href = "";
	public 		$ap_show_streaming		= false;
	
	public		$token_tab_album 				= "";
	public		$token_tab_song 					= "";
	public		$token_tab_merchandise 		= "";
	public		$token_tab_concert 				= "";
	public		$token_title			 				= "";
	public		$token_buy				 			= "";
	public		$token_price			 				= "";
	public		$token_venue						= "";
	public		$token_streaming					= "";
	public		$token_media_format			= "";
	public		$token_start_time					= "";
	public		$token_show_more_media_format = "";
	public		$token_show_currency			= "";
	public 		$token_compare_prices 			= "";
	public		$token_record_store_top_text = "";

}
?>