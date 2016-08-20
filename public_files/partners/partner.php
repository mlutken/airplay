<?php

	$start = microtime(true);
	
	global $partner_name;
	global $partner_market;
	global $search_for;
	global $search_for_type;
	global $search_for_media;
	global $search_for_currency;
	global $search_for_type_limit;
	global $item_array_to_show;
	global $search_for_media_reverse;
	global $ap_domain_base;
	global $ap_link_artist;
	global $ap_link_album;
	global $ap_link_song;
	
	global $G_URL_TO_NORMAL_fromUrl;
	global $G_URL_TO_NORMAL_toNormal;	

	$G_URL_TO_NORMAL_fromUrl    =  array("_"   , "-AND-", "-SLASH-", "-QMARK-" );
	$G_URL_TO_NORMAL_toNormal   =  array(" "   , "&"    , "/"      , "?"	   );
	
	
	/* Setting default values */
	$partner_name = "airplaymusic_dk";
	$partner_market = "DK";
	$search_for_type = "album"; /* Album */
	$search_for_type_limit = 10;
	$search_for = 'Pink_Floyd';
	$search_for_media = '';
	$search_for_media_reverse = '';
	$search_for_currency = 'DKK'; /*  Valid are DKK, EUR, GBP */
	
	/* Get values if defined and correct type */
	if (isset($_GET["partner_name"])) {
		$partner_name = base64_encode($_GET["partner_name"]);
	}
	
	if (isset($_GET["partner_market"])) {
		if ($_GET["partner_market"] == 'DK' OR $_GET["partner_market"] = 'UK') {
			$partner_market = $_GET["partner_market"];
		}
	}
	
	if (isset($_GET["search_for"])) {
		$search_for = $_GET["search_for"];
	}
	
	if (isset($_GET["search_for_type"])) {
		if ($_GET["search_for_type"] == 'album'/* OR $_GET["search_for_type"] = 'song' OR $_GET["search_for_type"] = 'album_song'*/) {
			$search_for_type = $_GET["search_for_type"];
		}
	}
	
	if (isset($_GET["search_for_media"])) {
		if ($_GET["search_for_media"] == 'CD' OR $_GET["search_for_media"] = 'MP3' OR $_GET["search_for_media"] = 'Vinyl') {
			$search_for_media = $_GET["search_for_media"];
		}
	}
	
	if (isset($_GET["search_for_media_reverse"])) {
		if ($_GET["search_for_media"] == 'CD' OR $_GET["search_for_media"] = 'MP3' OR $_GET["search_for_media"] = 'Vinyl') {
			$search_for_media_reverse = $_GET["search_for_media_reverse"];
		}
	}
	
	if (isset($_GET["search_for_currency"])) {
		if ($_GET["search_for_currency"] == 'DKK' OR $_GET["search_for_currency"] = 'EUR' OR $_GET["search_for_currency"] = 'GBP') {
			$search_for_currency = $_GET["search_for_currency"];
		}
	}
	
	/*if (isset($_GET["search_for_type_limit"])) {
		if (is_numeric($_GET["search_for_type_limit"]) && $_GET["search_for_type_limit"] <= 50) {
			$search_for_type_limit = $_GET["search_for_type_limit"];
		}
	}*/

	/* Print out the correct CSS for the partner */
	function get_layout_settings() {
		global $partner_name;
		$css_file_name = "css/airplay_". $partner_name . ".css";
		if (file_exists($css_file_name) && substr($css_file_name,-4) == ".css") {
			return "<link href='/partners/css/airplay_" . $partner_name . ".css' type='text/css' rel='stylesheet'>";
		} else {
			return "<link href='/partners/css/airplay_default.css' type='text/css' rel='stylesheet'>";
		}
	}

	/* Print out the correct CSS for the partner */
	function set_link_settings() {
		global $ap_domain_base;
		global $ap_link_artist;
		global $ap_link_album;
		global $ap_link_song;
		global $partner_market;
        global $partner_name;
		if ($partner_market == 'UK') {
			$ap_domain_base = "http://www.airplaymusic.co.uk/";
			$ap_link_artist = "artist/";
			$ap_link_album = "/album/";
			$ap_link_song = "/song/";
		} else {
			$ap_domain_base = "http://www.airplaymusic.dk/";
			$ap_link_artist = "kunstner";
			$ap_link_album = "/album/";
			$ap_link_song = "/sang/";
		}
		/*$css_file_name = "css/airplay_". $partner_name . ".css";
		if (file_exists($css_file_name) && substr($css_file_name,-4) == ".css") {
			return "<link href='/partners/css/airplay_" . $partner_name . ".css' type='text/css' rel='stylesheet'>";
		} else {
			return "<link href='/partners/css/airplay_default.css' type='text/css' rel='stylesheet'>";
		}*/
	}
	
	$dbconnection = open_db();
	/*
	$sql = "SELECT artist_name, album_simple_name AS item_name, MIN(price_local * currency_to_euro.to_euro * currency.from_euro * 0.01) as price, currency.currency_name, COUNT(price_local) AS count_price_local
FROM airplay_music.artist 
INNER JOIN airplay_music.album ON airplay_music.artist.artist_id = airplay_music.album.artist_id
INNER JOIN airplay_music.buy_album ON airplay_music.album.album_id = airplay_music.buy_album.album_id
INNER JOIN album_simple ON album_simple.album_simple_id = album.album_simple_id
INNER JOIN airplay_music.media_format ON airplay_music.media_format.media_format_id = airplay_music.buy_album.media_format_id
INNER JOIN currency_to_euro ON currency_to_euro.currency_id = buy_album.currency_id
INNER JOIN currency ON currency.currency_name = '$search_for_currency'
WHERE artist_name = '". mysql_real_escape_string(airplay_url_to_name($search_for)). "'";
if ($search_for_media != '') {
	$sql .= "AND media_format_name = '$search_for_media'";
}
if ($search_for_media_reverse != '') {
	$sql .= "AND media_format_name <> '$search_for_media_reverse'";
}

$sql .= " GROUP BY artist_name, lower(album_simple_name), currency.currency_name
ORDER BY price ASC, album_simple_name DESC LIMIT $search_for_type_limit ;";*/
    $sql = "SELECT artist.artist_name, item_base.item_base_name, currency.currency_name,
    MIN(price_local * currency_to_euro.to_euro * currency.from_euro) as price
    FROM artist
    INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
    INNER JOIN item_price ON item_base.item_base_id = item_price.item_base_id 
    INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
    INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id   
    INNER JOIN currency ON currency.currency_name = :search_for_currency
    WHERE artist_name = :search_for AND item_base.item_type = 1
    GROUP BY artist_name, item_base_name, currency.currency_name
    ORDER BY price ASC, item_base_name DESC
    LIMIT $search_for_type_limit";
    
	$item_array_to_show = sql_return_query($dbconnection, $sql, array(":search_for_currency" => $search_for_currency, ":search_for" => airplay_url_to_name($search_for) ));
	
	$str_return = "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>";
	$str_return .= "<html><head><title></title><meta http-equiv='Content-Type' content='text/html; charset=UTF-8' /></head>";
	
	$str_return .= get_layout_settings();
	
	$str_return .= "<body>";
	
	if (is_array($item_array_to_show)) {
		$item_array_size = sizeof($item_array_to_show);
		if ($item_array_size > 0) {
			
			set_link_settings(); /* Set setting for building the AP links */
			
			for ($i = 0; $i < $item_array_size; $i++)
			{
				extract($item_array_to_show[$i]);
				$ap_link = $ap_domain_base . $ap_link_artist . "/" . airplay_name_to_url($artist_name) . $ap_link_album . $item_base_name;
				$ap_link = str_replace(" ", "_",$ap_link);

				if ($i == 0) {
					$str_return .= "<div id='ap_container'>";
					$str_return .= "<div class='ap_artist_name'>" . htmlentities($artist_name,ENT_QUOTES, "UTF-8") . "</div>";
				}
				
				$str_return .= "<div class='ap_items'>";
				$str_return .= "<div class='ap_item'>";
				$str_return .= "<div class='item_base_name'><a href='$ap_link' target='_blank' title='$item_base_name'>" . htmlentities($item_base_name,ENT_QUOTES, "UTF-8") . "</a></div>";
				$str_return .= "<div class='item_price'><a href='$ap_link' target='_blank' title='$item_base_name'>" . number_format($price) . " " . $currency_name . "</a></div>";
				$str_return .= "</div>";
				$str_return .= "</div>";

				if ($i == $item_array_size) { $str_return .= "</div>"; }
			}
		}
	}
	$str_return .= "</body></html>";
	
	echo $str_return;

	close_db($dbconnection);

	function airplay_name_to_url( $sName ) 
{
		global $G_URL_TO_NORMAL_fromUrl;
		global $G_URL_TO_NORMAL_toNormal;	
		$sUrl = str_replace  ( $G_URL_TO_NORMAL_toNormal, $G_URL_TO_NORMAL_fromUrl, $sName );	
		return $sUrl;
	}
	
	function airplay_url_to_name( $sName ) 
{
		global $G_URL_TO_NORMAL_fromUrl;
		global $G_URL_TO_NORMAL_toNormal;	
		$sUrl = str_replace  ( $G_URL_TO_NORMAL_fromUrl, $G_URL_TO_NORMAL_toNormal, $sName );	
		return $sUrl;
	}
	
	 // Function used to open a connection to our database server.
 	function open_db() {
		try {
			$dbconnection = new PDO('mysql:host=localhost;dbname=airplay_music_v1;charset=utf8', 'airplay_user', 'Deeyl1819');
			return $dbconnection;
		} catch (PDOException $e) {
			die();
		}
	}
    
	// Function used to close our database connection.
	function close_db($dbconnection) {
		$dbconnection = null;
	}

	// Function used for return some queries.
	function sql_return_query($dbconnection, $query, $array) {
		try {
			$statement = $dbconnection->prepare($query);
			$statement->execute($array);
			$results = $statement->fetchAll(PDO::FETCH_ASSOC);
			return $results;
		} catch (PDOException $e) {
			echo $e->getMessage();
			die();
		}
	}
//printf("<div>Total time page: %.6fs\n", microtime(true) - $start . "</div>");
?>
