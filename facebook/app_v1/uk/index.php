<?php

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
	require_once ( '../functions/FacebookFunctions.php' );

	$artist_id = 0;
	$item_base_id = 0;

	$facebook = new FacebookApplication();

	$facebook->setLanguageCode();
	$facebook->setCurrencyCode();
	$facebook->setDefaultValues();
	$facebook->setTokenValues();

	// Get Items pr item type - used for showing tabs.
	$item_type_count = $facebook->getItemTypeCountForArtist();
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<link href='//fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
		<link type="text/css" rel="stylesheet" href="/default_style.css" media="all" />
		<script type="text/javascript" src="/js/jquery.js"></script>
	</head>
	<?php print "<body id='section_{$facebook->page_to_show}' class='page-{$facebook->page_to_show}-{$facebook->language_code}'>"; ?>
		<div class="page-wrapper">
			<?php
				$url = "/" . $facebook->getURLArtistString()  . "/" . nameToUrl($facebook->artist_name);
				if ($facebook->item_type == 1) { $album_class = "album selected"; } else { $album_class = "album"; }
				if ($facebook->item_type == 2) { $song_class = "song selected"; } else { $song_class = "song"; }
				if ($facebook->item_type == 3) { $merchandise_class = "merchandise selected"; } else { $merchandise_class = "merchandise"; }
				if ($facebook->item_type == 4) { $concert_class = "concert selected"; } else { $concert_class = "concert"; }
				$s = "<div id='tabs'>";
				if ($item_type_count[0]["item_type_count"] > 0 && $item_type_count[0]["item_type"] == 1) {
					$s .= "<div class='$album_class'><a href='{$url}/product=album'>{$facebook->token_tab_album}</a></div>";
				}
				if ($item_type_count[1]["item_type_count"] > 0 && $item_type_count[1]["item_type"] == 2) {
					$s .= "<div class='$song_class'><a href='{$url}/product=song'>{$facebook->token_tab_song}</a></div>";
				}
				if ($item_type_count[2]["item_type_count"] > 0 && $item_type_count[2]["item_type"] == 3) {
					$s .= "<div class='$merchandise_class'><a href='{$url}/product=merchandise'>{$facebook->token_tab_merchandise}</a></div>";
				}
				if ($item_type_count[3]["item_type_count"] > 0 && $item_type_count[3]["item_type"] == 4) {
					$s .= "<div class='$concert_class'><a href='{$url}/product=concert'>{$facebook->token_tab_concert}</a></div>";
				}
				$s .= "</div>";
				print $s;
			?>
			<div class="content">
			<?php
				if ($facebook->page_to_show == "artist") {
					if ($facebook->artist_id != 0) {
						$ap_artist_data = new ArtistDataMySql( $m_dbAll );
						$aData = $ap_artist_data->getFacebookPageData($facebook->artist_id, $facebook->item_type, $facebook->media_format_id);
						if ($facebook->item_type == 1 || $facebook->item_type == 2) {
							print $facebook->getArtistPageAlbumSongHTML($aData);
						} else if ($facebook->item_type == 3) {
							print $facebook->getArtistPageMerchandiseHTML($aData);
						} else if ($facebook->item_type == 4) {
							print $facebook->getArtistPageConcertHTML($aData);
						}
					}
				} else if ($facebook->page_to_show == "album" || $facebook->page_to_show == "song") {
					if ($facebook->page_to_show == "album") {
						$facebook->item_type = 1;
					} else if ($facebook->page_to_show == "song") {
						$facebook->item_type = 2;
					}
					$ap_item_data = new ItemDataMySql( $m_dbAll );
					if ($facebook->item_base_id != 0) {
						$aData = $ap_item_data->getFacebookPageData($facebook->item_base_id, $facebook->media_format_id);
						print $facebook->getAlbumSongPageHTML($aData);
					}
				}

				if ($facebook->artist_id != 0) {
					$sHtml .= $facebook->getCurrencyFromToJSArrays();
					$sHtml .= "<script type='text/javascript'>";
					$sHtml .= $facebook->getDynamicMusicLookupArtistJavascriptVariables("streaming");
					$sHtml .= $facebook->getDynamicMusicLookupArtistJavascriptVariables("MP3");
					if ($facebook->item_type == 1) {
						$sHtml .= "\nfunction ap_getDynamicMusicLookupArtistAlbums() {";
						$sHtml .= $facebook->getDynamicMusicLookupArtistAlbums("streaming");
						$sHtml .= $facebook->getDynamicMusicLookupArtistAlbums("MP3");
						$sHtml .= "};";
					}
					if ($facebook->item_type == 2) {
						$sHtml .= "\nfunction ap_getDynamicMusicLookupArtistSongs() {";
						$sHtml .= $facebook->getDynamicMusicLookupArtistSongs("streaming");
						$sHtml .= $facebook->getDynamicMusicLookupArtistSongs("MP3");
						$sHtml .= "};";
					}
					
					if ($facebook->item_base_id != 0) {
						if ($facebook->page_to_show == "album") {
							$sHtml .= "\nfunction ap_getDynamicMusicLookupAlbums() {";
							$sHtml .= $facebook->getDynamicMusicLookupAlbum("streaming");
							$sHtml .= $facebook->getDynamicMusicLookupAlbum("MP3");
							$sHtml .= "};";
						}
						
						if ($facebook->page_to_show == "song") {
							$sHtml .= "\nfunction ap_getDynamicMusicLookupSongs() {";
							$sHtml .= $facebook->getDynamicMusicLookupSong("streaming");
							$sHtml .= $facebook->getDynamicMusicLookupSong("MP3");
							$sHtml .= "};";
						}
					}
					$sHtml .= "</script>";
				}
				print $sHtml;
			?>
			<?php //print "facebook->page_to_show" . $facebook->page_to_show ?>
			<?php //print "facebook->item_type" . $facebook->item_type ?>
			<?php //print "facebook->artist_id" . $facebook->artist_id ?>
			<?php //print "facebook->item_base_id" . $facebook->item_base_id ?>
			<?php //print "facebook->media_format_id" . $facebook->media_format_id ?>
			<?php if ($facebook->page_to_show == "artist" && $facebook->item_type == 1 && $facebook->artist_id != 0) { ?>
				<script type='text/javascript'>jQuery("table.list-price-table").ready(function() {ap_getDynamicMusicLookupArtistAlbums();});var ArtistAlbumPage = "A";</script>
			<?php } else if ($facebook->page_to_show == "artist" && $facebook->item_type == 2 && $facebook->artist_id != 0) {  ?>
				<script type='text/javascript'>jQuery("table.list-price-table").ready(function() {ap_getDynamicMusicLookupArtistSongs();});var ArtistSongPage = "A";</script>
			<?php } else if ($facebook->page_to_show == "album" && $facebook->item_base_id != 0) { ?>
				<script type='text/javascript'>jQuery("table.list-price-table").ready(function() {ap_getDynamicMusicLookupAlbums();});</script>
			<?php } else if ($facebook->page_to_show == "song" && $facebook->item_base_id != 0) { ?>
				<script type='text/javascript'>jQuery("table.list-price-table").ready(function() {ap_getDynamicMusicLookupSongs();});</script>
			<?php } ?>
			</div>
		</div>
		TODO:<br/>
		- Valuta<br/>
		- Google Analytics<br/>
		- Paging<br/>
		<script type="text/javascript" src="/js/prices.js"></script>
		<script type="text/javascript" src="/js/jquery.tablesorter.js"></script>
	</body>
</html>