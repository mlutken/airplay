<?php
	require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
	require_once ('airplay_globals.php');

	require_once ('utils/general_utils.php');
	require_once ('utils/string_utils.php');
	require_once ('db_api/db_string_utils.php');
	require_once ('db_manip/AllDbTables.php');

	$baseDir		= "/home/sleipner/airplay/drupal7/images/albums";
	$max_item_from_mysql = 1000;
	$record_stores_valid_for_images = "42, 23, 3, 9, 43, 162, 168, 62"; 
	// 3, 9, 45, 25, 162 ; Stereo Studio (DK), Gaffa, Townsend (UK), TPMusik, HIGHRES
	// "42, 23"; // Amazon, CDON,  - Done
	// 168  - base.com (UK) 62 - RecordStore.co.uk (UK)
	//Platekompaniet kan ikke bruges da denne har "forkerte" billede hvis de ikke har nogen billeder. http://www.platekompaniet.no/Search.aspx?q=%22Agnes+Obel%22

	$CoverURLInfo = array();
	
	$dba = new AllDbTables();
	@mkdir( $baseDir, 0755, true ); // The @ suppresses php warnings. Here we particularly want to disregard the dir exists warning when creating

	
	// OPTIMIZE - just one MySQL call
	$aItemBase = getItemBaseFromMySql(0, $max_item_from_mysql);
	
	$CoverURLs = array();
	
	foreach ($aItemBase AS $data) {
		$Covers = getCoverURLFromMySql($data["item_base_id"]);
		if (count($Covers) > 0) {
			foreach ($Covers AS $a) {
				$artist_id = (int)$a["artist_id"];
				$artist_name = (string)$a["artist_name"];
				$item_base_id = (int)$a["item_base_id"];
				$item_base_name = (string)$a["item_base_name"];
				$cover_image_url = (string)$a["cover_image_url"];
				$record_store_id = (int)$a["record_store_id"];
				$CoverURLInfo[] = array("artist_id" => $artist_id, "artist_name" => $artist_name, "item_base_id" => $item_base_id, "item_base_name" => $item_base_name, "cover_image_url" => $cover_image_url, "record_store_id" => $record_store_id );
			}
		} else {
			updateImageProcessedInfo( $data["item_base_id"], '', 0, 0, 0 );
		}
	}

	print "Getting images from " . count($CoverURLInfo) . " items.\n";
	
	$i = 0;
	foreach ($CoverURLInfo AS $data) {
		$artist_name = writeImage($dba, $baseDir, $data["artist_id"], $data["cover_image_url"], $data["item_base_id"], $data["record_store_id"] );
		if ( $i % 10 == 0 ) {
			print "$i: " . $data["artist_name"] . " - " . $data["item_base_name"] . "\n";
		}
		$i++;
	}

	function getCoverURLFromMySql($item_base_id)
	{
		global $g_MySqlPDO;
		global $record_stores_valid_for_images;
		$q = "SELECT cover_image_url, item_price.artist_id, artist_name, item_base.item_base_id, item_base_name, item_price.record_store_id
				FROM item_price
				INNER JOIN artist ON artist.artist_id = item_price.artist_id
				INNER JOIN item_base ON item_base.item_base_id = item_price.item_base_id
				WHERE item_base.item_base_id = ? AND item_price.cover_image_url <> '' AND record_store_id IN ( $record_stores_valid_for_images )
				ORDER BY record_store_id 
				LIMIT 0, 1";
		$aCoverInfo = pdoQueryAssocRows($g_MySqlPDO, $q, array( $item_base_id ));
		return $aCoverInfo;
	}

	/*
		Function used to update info about "item_base" image.
	*/
	function updateImageProcessedInfo( $item_base_id, $cover_url, $img_width, $img_height, $record_store_id )
	{
		global $g_MySqlPDO;
		$stmt = $g_MySqlPDO->prepare( 'UPDATE item_base SET  image_url = ?, image_width = ?, image_height = ?, image_from_record_store_id = ?, image_processed = 1 WHERE item_base_id = ?' );
		$stmt->execute( array($cover_url, $img_width, $img_height, $record_store_id, $item_base_id) );
	}

	/*
		Get iitem_base items to retrieve images from
	*/
	function getItemBaseFromMySql( $start, $count )
	{
		global $g_MySqlPDO;
		$q = "SELECT item_base_id, item_base_name FROM item_base WHERE image_processed = 0 AND item_type = 1 LIMIT $start, $count";
		$aItemBaseNames = pdoQueryAssocRows($g_MySqlPDO, $q, array() );
		return $aItemBaseNames;
	}
	
	function writeImage( $dba, $baseDir, $artist_id, $cover_url, $item_base_id, $record_store_id )
	{
		$artistModuloDir    = moduloDirFromID($artist_id);
		$img_width_new = 150;
		$img_height_new = 0;
		$image_url = "";
		$file_name_ap = ""; // Internal filename after saving.
		
		$artist_dir      	= "{$baseDir}/artist/{$artistModuloDir}/{$artist_id}/album";	// File system path
		$image_url     	= "/images/albums/artist/{$artistModuloDir}/{$artist_id}/album"; // URL path

		@mkdir( $artist_dir, 0755, true ); // The @ suppresses php warnings. Here we particularly want to disregard the dir exists warning when creating
		
		$file_name = $artist_dir . "/{$item_base_id}";
		$image_url .= "/{$item_base_id}_w{$img_width_new}.png";
		$file_name_ap = $file_name . "_w{$img_width_new}.png";

		
		getImageFromRemoteServer($cover_url, $file_name);
		
		$img_attributes = getImageAttributes($file_name);
		
		if (count($img_attributes) == 7) {
			$img_width = $img_attributes[0]; // Width
			$img_height = $img_attributes[1]; // Height
			$img_ratio = ($img_width / $img_height);
			
			$img_height_new = (int)($img_width_new / $img_ratio);
			$img_mime = $img_attributes["mime"]; // type

			if ($img_mime == "image/jpeg") {
				$image = imagecreatefromjpeg ($file_name);
			} else if ($img_mime == "image/gif") {
				$image = imagecreatefromgif ($file_name);
			} else if ($img_mime == "image/png") { // needed ??
				$image = imagecreatefrompng ($file_name);
			}
			
			if ($img_width > $img_width_new) {
				$img_new = imagecreatetruecolor($img_width_new, $img_height_new);
				imagecopyresampled($img_new, $image, 0, 0, 0, 0, $img_width_new, $img_height_new, $img_width, $img_height);
				imagepng($img_new, $file_name_ap, 9);
				imagedestroy($image);
				imagedestroy($img_new);
			} else {
				imagepng($image, $file_name_ap, 9);
				imagedestroy($image);
			}
			unlink( $file_name );
			updateImageProcessedInfo( $item_base_id, $image_url, $img_width_new, $img_height_new, $record_store_id );
		
		// If width, height and mime is wrong then make sure to save it is processed
		} else {
			updateImageProcessedInfo( $item_base_id, '', 0, 0, 0 );
		}
		
		
		
		
		return $artist_dir;
	}
	
	function getImageFromRemoteServer($cover_url, $file_name) {
	
		$ch = curl_init($cover_url);
		$fp = fopen($file_name, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);

	}
	
	function getImageAttributes($file_name) {
		return getimagesize($file_name);
	}
	
	
?>