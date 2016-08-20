<?php
/*
	Facebook tab page:
	https://developers.facebook.com/docs/reference/dialogs/add_to_page/
	Ex link https://www.facebook.com/dialog/pagetab?app_id=229261137230663&redirect_uri=https://facebook.airplaymusic.dk/kunstner/Agnes_Obel
*/


	/*************************************
	Rewrite rule is in apache conf file - due to performance loss in .htaccess files.
	http://httpd.apache.org/docs/2.2/howto/htaccess.html (When not to use .....)
	*************************************/
	require_once ( '../../../aphp/aphp_fix_include_path.php' );
	require_once ( '../../../aphp/airplay_globals.php' );
	require_once ( '../../../aphp/db_api/SimpleTableDataMySql.php' );
	require_once ( '../../../aphp/db_api/ArtistDataMySql.php' );
	require_once ( '../../../aphp/utils/string_utils.php' );
	require_once ( '../../../aphp/db_api/ItemDataMySql.php' );
	require_once ( '../../../aphp/db_api/CurrencyDataMySql.php' );
	require_once ( '../../../aphp/db_api/FacebookAppDataMySql.php' );
	require_once ( '../functions/FacebookFunctions.php' );


	$artist_id = 0;
	$item_base_id = 0;

	$facebook = new FacebookApplication();

	$facebook->setDefaultValues();
	$facebook->setCurrencyCode();
	$facebook->setTokenValues();
	$facebook->setFacebookAppSettings();
	
	// Get Items pr item type - used for showing tabs.
	$item_type_count = $facebook->getItemTypeCountForArtist();
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title></title>
		<link href='//fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
		<link type="text/css" rel="stylesheet" href="/default_style.css" media="all" />
		<script type="text/javascript" src="/js/jquery.js"></script>
		<meta property="fb:app_id" content="<?php print $facebook->fb_app_id; ?>"/>
	</head>
	<?php print "<body id='section_{$facebook->page_to_show}' class='page-{$facebook->page_to_show}-{$facebook->language_code}'>"; ?>
	<?php // Facebook resize iframe ?>
	<script type="text/javascript" src="//connect.facebook.net/en_US/all.js"></script><script type="text/javascript"> var fb_timeout_ms = 100;FB.init({appId: '{<?php print $facebook->fb_app_id; ?>}',status: true,cookie: true,xfbml: true});FB.Canvas.setSize({height: 600});setTimeout("FB.Canvas.setAutoGrow()", fb_timeout_ms); </script>
	<?php 
		if ($facebook->fb_comments == true) {
			print "<div id='fb-root'></div>";
			print
<<<SCRIPT
<script type="text/javascript">(function(d, s, id) {  var js, fjs = d.getElementsByTagName(s)[0];   if (d.getElementById(id)) return;  js = d.createElement(s); js.id = id;  js.src = "//connect.facebook.net/da_DK/all.js#xfbml=1&appId={<?php print $facebook->fb_app_id; ?>}";  fjs.parentNode.insertBefore(js, fjs);}(document, 'script', 'facebook-jssdk'));</script>
SCRIPT;
		}
	?>
		<div class="page-wrapper">
		<?php 
			print $facebook->getLanguageChangeHTML();
			/*print "media_format_id: " . $facebook->media_format_id . "<br>";
			print "page_to_show: " . $facebook->page_to_show . "<br>";
			print "language_code: " . $facebook->language_code . "<br>";
			print "currency_code: " . $facebook->currency_code . "<br>";
			print "product_name: " . $facebook->product_name . "<br>";
			print "item_type: " . $facebook->item_type . "<br>";*/
			if ($facebook->artist_id == 85023 || $facebook->artist_id == 966) {
				print $facebook->getHTMLHeader($facebook->artist_id);
			} else if ($facebook->artist_id == 11407 || $facebook->artist_id == 2) {
				print $facebook->getHTMLBFSHeader($facebook->artist_id);
			}
		?>
			<div class="spacer"></div>

			<?php if ($facebook->artist_id == 11407 || $facebook->artist_id == 2) { ?>
			<div class="record-store-top">
				<div class="text"><?php print str_replace("[ARTIST_NAME]", $facebook->artist_name, $facebook->token_record_store_top_text); ?></div>
				<div class="icons">
					<div class="text"><?php print $facebook->token_record_store_top_icon_text; ?></div>
					<div class="icon"><img src="/images/icons/itunes_icon.png" width="55" height="20" alt="" /></div>
					<div class="icon"><img src="/images/icons/amazon_icon.png" width="55" height="20" alt="" /></div>
					<div class="icon"><img src="/images/icons/cdon_icon.png" width="55" height="20" alt="" /></div>
					<div class="icon"><img src="/images/icons/stereostudio_icon.png" width="55" height="20" alt="" /></div>
					<div class="icon"><img src="/images/icons/imusic_icon.png" width="55" height="20" alt="" /></div>
				</div>
			</div>
			<div class="spacer"></div>
			<?php } ?>
						
			<?php
				// Container for facebook div.
				if ($facebook->fb_comments == true) {
					print "<div class='fb-comments' data-href='{$facebook->fb_comments_data_href}' data-width='800' data-numposts='2' data-colorscheme='light'></div>";
					print "<div class='spacer'></div>";
				}
			?>
			<?php
				$url = "/" . $facebook->getURLArtistString()  . "/" . nameToUrl($facebook->artist_name);
				if ($facebook->item_type == 1) { $album_class = "album selected"; } else { $album_class = "album"; }
				if ($facebook->item_type == 2) { $song_class = "song selected"; } else { $song_class = "song"; }
				if ($facebook->item_type == 3) { $merchandise_class = "merchandise selected"; } else { $merchandise_class = "merchandise"; }
				if ($facebook->item_type == 4) { $concert_class = "concert selected"; } else { $concert_class = "concert"; }
				$s = "<div id='tabs'>";
				if ($item_type_count[0]["item_type_count"] > 0 && $item_type_count[0]["item_type"] == 1) {
					$s .= "<div class='$album_class'><a href='{$url}/product=album/media_format={$facebook->media_format_id}/currency={$facebook->currency_code}/language={$facebook->language_code}'>{$facebook->token_tab_album}</a></div>";
				}
				if ($item_type_count[1]["item_type_count"] > 0 && $item_type_count[1]["item_type"] == 2) {
					$s .= "<div class='$song_class'><a href='{$url}/product=song/media_format={$facebook->media_format_id}/currency={$facebook->currency_code}/language={$facebook->language_code}'>{$facebook->token_tab_song}</a></div>";
				}
				if ($item_type_count[2]["item_type_count"] > 0 && $item_type_count[2]["item_type"] == 3) {
					$s .= "<div class='$merchandise_class'><a href='{$url}/product=merchandise/media_format={$facebook->media_format_id}/currency={$facebook->currency_code}/language={$facebook->language_code}'>{$facebook->token_tab_merchandise}</a></div>";
				}
				/* Disabled - not enough data
				if ($item_type_count[3]["item_type_count"] > 0 && $item_type_count[3]["item_type"] == 4) {
					$s .= "<div class='$concert_class'><a href='{$url}/product=concert/media_format={$facebook->media_format_id}/currency={$facebook->currency_code}/language={$facebook->language_code}'>{$facebook->token_tab_concert}</a></div>";
				}*/
				$s .= "</div>";
				print $s;
			?>

			<div class="spacer"></div>
			
			<div class="content">
			<?php
			if ($facebook->page_to_show == "artist") {
				if ($facebook->artist_id != 0) {
					$ap_artist_data = new FacebookAppDataMySql( $m_dbAll );
					$aData = $ap_artist_data->getFacebookArtistPageData($facebook->artist_id, $facebook->item_type, $facebook->media_format_id, $facebook->currency_code);
					if ($facebook->item_type == 1 || $facebook->item_type == 2) {
						print $facebook->getArtistPageAlbumSongHTML($aData);
					} else if ($facebook->item_type == 3) {
						print $facebook->getArtistPageMerchandiseHTML($aData);
					} else if ($facebook->item_type == 4) {
						// Disabled - not enough data
						//print $facebook->getArtistPageConcertHTML($aData);
					}
				}
			} else if ($facebook->page_to_show == "album" || $facebook->page_to_show == "song") {
				if ($facebook->page_to_show == "album") {
					$facebook->item_type = 1;
				} else if ($facebook->page_to_show == "song") {
					$facebook->item_type = 2;
				}
				$ap_item_data = new FacebookAppDataMySql( $m_dbAll );
				if ($facebook->item_base_id != 0) {
					$aData = $ap_item_data->getFacebookItemPageData($facebook->item_base_id, $facebook->media_format_id, $facebook->currency_code);
					print $facebook->getAlbumSongPageHTML($aData);
				}
			}

			if ($facebook->artist_id != 0) {
				$sHtml .= $facebook->getCurrencyFromToJSArrays();
				$sHtml .= "<script type='text/javascript'>";
				$sHtml .= $facebook->getDynamicMusicLookupArtistJavascriptVariables("streaming");
				$sHtml .= $facebook->getDynamicMusicLookupArtistJavascriptVariables("MP3");
				if ($facebook->item_type == 1 && $facebook->ap_show_streaming == true) {
					$sHtml .= "\nfunction ap_getDynamicMusicLookupArtistAlbums() {";
					$sHtml .= $facebook->getDynamicMusicLookupArtistAlbums("streaming");
					$sHtml .= $facebook->getDynamicMusicLookupArtistAlbums("MP3");
					$sHtml .= "};";
				}
				if ($facebook->item_type == 2 && $facebook->ap_show_streaming == true) {
					$sHtml .= "\nfunction ap_getDynamicMusicLookupArtistSongs() {";
					$sHtml .= $facebook->getDynamicMusicLookupArtistSongs("streaming");
					$sHtml .= $facebook->getDynamicMusicLookupArtistSongs("MP3");
					$sHtml .= "};";
				}
				if ($facebook->item_base_id != 0 && $facebook->ap_show_streaming == true) {
					if ($facebook->page_to_show == "album") {
						$sHtml .= "\nfunction ap_getDynamicMusicLookupAlbums() {";
						if ($facebook->media_format_id == 0) {
							$sHtml .= $facebook->getDynamicMusicLookupAlbum("streaming");
							$sHtml .= $facebook->getDynamicMusicLookupAlbum("MP3");
						} else if ($facebook->media_format_id == 3) {
							$sHtml .= $facebook->getDynamicMusicLookupAlbum("MP3");
						}
						$sHtml .= "};";
					}
					if ($facebook->page_to_show == "song") {
						$sHtml .= "\nfunction ap_getDynamicMusicLookupSongs() {";
						if ($facebook->media_format_id == 0) {
							$sHtml .= $facebook->getDynamicMusicLookupSong("streaming");
							$sHtml .= $facebook->getDynamicMusicLookupSong("MP3");
						} else if ($facebook->media_format_id == 3) {
							$sHtml .= $facebook->getDynamicMusicLookupSong("MP3");
						}
						$sHtml .= "};";
					}
				}
				$sHtml .= "</script>";
			}
			print $sHtml;
			
if ($facebook->page_to_show == "artist" && $facebook->item_type == 1 && $facebook->artist_id != 0 && $facebook->ap_show_streaming == true) {
print
<<<SCRIPT
	<script type='text/javascript'>jQuery("table.list-price-table").ready(function() {ap_getDynamicMusicLookupArtistAlbums();/*ap_getIconsForArtistAlbum(<?php print $facebook->artist_id; ?>, '<?php print $facebook->language_code; ?>', '<?php print $facebook->media_format_id; ?>');*/});var ArtistAlbumPage = "A";</script>
SCRIPT;
} else if ($facebook->page_to_show == "artist" && $facebook->item_type == 2 && $facebook->artist_id != 0 && $facebook->ap_show_streaming == true) {
print
<<<SCRIPT
	<script type='text/javascript'>jQuery("table.list-price-table").ready(function() {ap_getDynamicMusicLookupArtistSongs();/*ap_getIconsForArtistSong(<?php print $facebook->artist_id; ?>, '<?php print $facebook->language_code; ?>', '<?php print $facebook->media_format_id; ?>');*/});var ArtistSongPage = "A";</script>
SCRIPT;
} else if ($facebook->page_to_show == "album" && $facebook->item_base_id != 0 && $facebook->ap_show_streaming == true) { 
print
<<<SCRIPT
	<script type='text/javascript'>jQuery("table.list-price-table").ready(function() {ap_getDynamicMusicLookupAlbums();});</script>
SCRIPT;
} else if ($facebook->page_to_show == "song" && $facebook->item_base_id != 0 && $facebook->ap_show_streaming == true) {
print
<<<SCRIPT
	<script type='text/javascript'>jQuery("table.list-price-table").ready(function() {ap_getDynamicMusicLookupSongs();});</script>
SCRIPT;
			} ?>
			
			</div>
			<div class="powered_by"><a href="http://www.airplaymusic.dk/" target="_blank" title="Airplay Music"><span class="text">Powered by</span><span class="image"><img src="/images/airplay_music_logo.png" width="80" height="13" border="0" /></span></a></div>
		</div>		
		<script type="text/javascript" src="/js/prices.js"></script>
		<script type="text/javascript" src="/js/jquery.tablesorter.js"></script>
	</body>
</html>