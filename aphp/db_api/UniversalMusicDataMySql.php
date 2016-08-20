<?php

require_once ("db_api/SimpleTableDataMySql.php");

class UniversalMusicDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'xxx'
        , array(  'xxx' ) 
        , $dbPDO );
    }
	    
	/** Get all data needed to display get data from universal dump */
    public function getUniversalMusicDataFromLocalhost ()
    {
		$q = "SELECT trim(formated_name) AS artist_name, uuid, trim(product_title) AS universal_album_name FROM airplay_test.artists INNER JOIN airplay_test.artist_product ON airplay_test.artists.artist_id = airplay_test.artist_product.artist_id
		INNER JOIN airplay_test.albums ON airplay_test.albums.product_id = airplay_test.artist_product.product_id";
		return pdoQueryAssocRows($this->m_dbPDO, $q, array());
   }
		
    /** Get all data needed to display an artist page used in our facebook app */
    public function getUniversalMusicDataByItemType ($artist_id, $item_type, $currency_code)
    {
		// Album
		if ($item_type == 1) {
		$q = "
		SELECT artist.artist_id, artist_name, item_base_name, country_name, item_price_id, country.country_id,
		(price_local * currency_to_euro.to_euro * currency.from_euro) as price_local, media_format.media_format_name,
		item_price.buy_at_url, record_store.use_affiliate, item_price.media_format_id, record_store.affiliate_link,
		record_store.affiliate_encode_times, record_store.record_store_name, record_store.record_store_id
		FROM artist
		INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
		INNER JOIN item_price ON item_base.item_base_id = item_price.item_base_id 
		INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
		INNER JOIN currency ON currency.currency_name = ?
		INNER JOIN record_store ON item_price.record_store_id = record_store.record_store_id
		INNER JOIN country ON country.country_id = record_store.country_id
		INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id
		WHERE artist.artist_id = ? AND item_base.item_type = ? AND record_store.country_id = 45
		AND item_price.media_format_id IN (5, 7) AND item_used = 0 
		ORDER BY artist_name, item_base_name ASC";
		
		} else if ($item_type == 2) {
		/*$q = "
		SELECT artist.artist_id, artist_name, item_base_name, media_format_name
		FROM artist
		INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
		INNER JOIN item_price ON item_base.item_base_id = item_price.item_base_id 
		INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
		INNER JOIN currency ON currency.currency_name = ?
		WHERE artist.artist_id = ? AND item_base.item_type = ?
		ORDER BY artist_name, item_base_name ASC";*/
		// Merchandise
		} else if ($item_type == 3) {
		$q = "
			SELECT item_price_name, artist_name, country_name, cover_image_url, item_price_id, country.country_id,
			(price_local * currency_to_euro.to_euro * currency.from_euro) as price_local,   
			media_format.media_format_name, item_price.buy_at_url, record_store.use_affiliate, item_price.media_format_id,
			record_store.affiliate_link, record_store.affiliate_encode_times, record_store.record_store_name, record_store.record_store_id
			FROM artist
			INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
			INNER JOIN item_price ON item_base.item_base_id = item_price.item_base_id 
			INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
			INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id
			INNER JOIN record_store ON item_price.record_store_id = record_store.record_store_id
			INNER JOIN country ON country.country_id = record_store.country_id
			INNER JOIN currency ON currency.currency_name = ?
			WHERE artist.artist_id = ? AND item_base.item_type = ? AND record_store.country_id = 45 AND cover_image_url <> ''
			ORDER BY artist_name ASC, item_price_name ASC";
		// Concerts
		} else if ($item_type == 4) {
		$q = "
			SELECT item_price_id, artist_name, country_name, item_event_date, item_event_time, item_price_name, country.country_id,
			(price_local * currency_to_euro.to_euro * currency.from_euro) as price_local, item_price.media_format_id,
			media_format.media_format_name, item_price.buy_at_url, record_store.use_affiliate,
			record_store.affiliate_link, record_store.affiliate_encode_times, record_store.record_store_name, delivery_status, record_store.record_store_id
			FROM artist
			INNER JOIN item_base ON item_base.artist_id = artist.artist_id 
			INNER JOIN item_price ON item_base.item_base_id = item_price.item_base_id 
			INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
			INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id
			INNER JOIN record_store ON item_price.record_store_id = record_store.record_store_id
			INNER JOIN currency ON currency.currency_name = ?
			INNER JOIN country ON country.country_id = record_store.country_id
			INNER JOIN item_price_delivery_status ON item_price.item_price_delivery_status_id = item_price_delivery_status.item_price_delivery_status_id
			WHERE item_event_date >= CURDATE() AND artist.artist_id = ? AND item_base.item_type = ? 
			AND media_format.media_format_id = 128 AND record_store.country_id = 45"; 
			$q .= " GROUP BY item_price_name, item_event_date ORDER BY item_event_date ASC, item_event_time ASC";
		}
		return pdoQueryAssocRows($this->m_dbPDO, $q, array($currency_code, $artist_id, $item_type));
   }

   /*
	TODO : REMOVE NONE SQL FUNCTIONS FROM THIS FILE!!!
   */
   
   public function copyFileToBackup($file_path, $filename, $type) {
   
		if ($type == 1) {
			$new_dir_name = $file_path . "cd_vinyl/dk/" . date("Y");
		} else if ($type == 3) {
			$new_dir_name = $file_path . "merchandise/dk/" . date("Y");
		} else if ($type == 4) {
			$new_dir_name = $file_path . "concerts/dk/" . date("Y");
		}
		$new_file_name = date("Y") . "_" . date("m") . "_" . date("d") . "_" . date("H");

		@mkdir($new_dir_name, 0775, true);
		@mkdir($new_dir_name. "/". date("m"), 0775, true);

		@copy($file_path . $filename, $new_dir_name. "/". date("m") . "/" . $new_file_name . "_" . $filename);
   }
   
   public function downloadFile() {
		$fp = fopen($this->import_file, "w+");
		$ch = curl_init( $this->remote_server . $this->import_file );
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
	}
	
	public function getFileLines() {
		$handle = fopen($this->import_file, "r"); 
		if ($handle) { 
		   while (!feof($handle)) { 
			   $lines[] = fgets($handle, 4096);
		   } 
		   fclose($handle); 
		}
		return $lines;
	}

	public function GZipJSONFile($file_name) {
		$data = implode("", file($file_name));
		$gzdata = gzencode($data, 9);
		$fp = fopen( $file_name . ".gz", "w");
		fwrite($fp, $gzdata);
		fclose($fp);
	}
	
   public function airplay_format_price( $fPrice, $sCurrency = "" ) 
	{
		$fPrice = round( $fPrice*20 ) * 0.05;
		if ( $sCurrency == 'GBP' ) {
			return number_format  ( $fPrice , 2 , '.', ""  );
		} else {
			return number_format  ( $fPrice , 2 , ',', "."  );
		}
	}

	public function ap_replace_affiliate_link($buy_at_url, $affiliate_link, $affiliate_encode_times) {
		if ($affiliate_encode_times == 0) {
			return str_replace("[TARGET_URL]", $buy_at_url ,$affiliate_link);
		} else {
			for ($i=0;$i<=$affiliate_encode_times;$i++) {
				$buy_at_url = urlencode($buy_at_url);
			}
			return str_replace("[TARGET_URL]", $buy_at_url ,$affiliate_link);
		}
	}
	
	public function makeMediaFormatArray($media_format_id, $media_format_name) {
		global $aMedia_format_ids;
		global $media_format;
		
		if (!in_array($media_format_id, $aMedia_format_ids)) {
			$aMedia_format_ids[] = $media_format_id;
			$media_format[] = array( "category" => array("category_id" => $media_format_id, "category_name" => $media_format_name));
		}
	}

	public function makeRecordStoreArray($record_store_id, $record_store_name, $country_code, $country_name, $shipping_cost = 0, $shipping_cost_formatted = 0) {
		global $aRecord_store_ids;
		global $record_store;
		
		if (!in_array($record_store_id, $aRecord_store_ids)) {
			$aRecord_store_ids[] = $record_store_id;
			$record_store[] = array( "shop" => array("shop_id" => $record_store_id, "shop_name" => $record_store_name, "shop_country_name" => $country_name, "shop_country_code" => $country_code, "shipping_cost" => $shipping_cost, "shipping_cost_formatted" => $shipping_cost_formatted));
		}
	}

	function getArtistNameFromWebservice($char) {
		$url = $this->universal_server_url . "search/artist?name=" . $char;
		//  Initiate curl
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL,$url);
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		return $result;
		/*foreach($result["data"] AS $a) {
			$webservice_artist_names[] = $a;
		}*/
	}
	
	function getDiscographyFromWebservice($uuid) {
		$url = $this->universal_server_url . "artist/" . $uuid . "/releases";
		//  Initiate curl
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL,$url);
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		return $result;
		/*foreach($result["data"] AS $a) {
			$webservice_artist_names[] = $a;
		}*/
	}
	
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------	
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
	// TODO - remove from this obect - not MySQL variables.
    static       $m_aTblFields	= array( 'xxx' );
	//private	$import_file 		= "uni_file.csv";
	private	$remote_server = "http://public.airplaymusic.dk/partners/universalmusic/";
    //private 	$universal_server_url = "http://salty-brushlands-3881-965.herokuapp.com/i/";
	private 	$universal_server_url = "https://api.musik.dk/i/";
	public		$universal_char_list = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','Z','X','Y','0','1','2','3','4','5','6','7','8','9','Æ','Ø','Å','!');
}

?>