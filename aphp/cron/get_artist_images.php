<?php
	require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
	require_once ('airplay_globals.php');

	require_once ('utils/general_utils.php');
	require_once ('utils/string_utils.php');
	require_once ('db_api/db_string_utils.php');
	require_once ('db_manip/AllDbTables.php');
	require_once ('db_api/ArtistDataMySql.php');
	require_once('../../drupal7/sites/all/modules/airplay_base/airplay_base_url_functions.inc');
	
	$dbg = true; // Show debug - true false,
	$base_dir		= str_replace("aphp/cron", "drupal7/images/artists", __DIR__);
	$work_dir 		= "/tmp/";
	$temp_work_file_name = "temp_discogs_image.jpeg"; // Temp file name for working file (the file downloaded)
	$max_item_from_mysql = 0;
	$img_width_new = 150;
	$i_image_processed = 1;

	
	// Make sure that we have "root" directory.
	if ($dbg == true) { print "Work directory: {$work_dir}\n"; }
	if ($dbg == true) { print "Creating directory: {$base_dir}\n"; }
	@mkdir( $base_dir, 0755, true ); // The @ suppresses php warnings. Here we particularly want to disregard the dir exists warning when creating

	$dba = new AllDbTables();
	$aArtistData = new ArtistDataMySql( );
	$aDicsogsImages = getDicsogsImagesFromMySql( $max_item_from_mysql );

	if ($dbg == true) { print "Getting images for " . count($aDicsogsImages) . " artists.\n"; }
	
	foreach ($aDicsogsImages AS $a) {
		$artist_name = $a["name"];
		$image_url_source = replaceDiscogsURL($a["image"]);
		$discogs_artist_id = $a["artist_id"];
		$artist_name_reversed = "";
		$artist_id = 0;

		// The artist_name is in our Various Artist table - then we do not need to import it - Just bail out.
		// OHHH no no break here ....
       // if (count(getVariousArtist($artist_name)) > 0) {
        //    break;
        //} else {
			
		$artist_id = $aArtistData->lookupID( $artist_name );
		// Try reversed lookup for two word names with comma between the words (ex. 'Turner, Tina')
		if ( $artist_id <= 0 ) {
			$artist_name_reversed = reverseArtistNameWithComma($artist_name);
			if ( $artist_name_reversed != $artist_name ) {
				$artist_id = $aArtistData->nameToID( $artist_name_reversed );
				if ( $artist_id > 0 ) {
					$artist_name = $artist_name_reversed;
				}
			}
		}
		// Try reversed lookup for two word names without comma (ex. 'Turner Tina')
		if ( $artist_id <= 0 ) {
			$artist_name_reversed = reverseArtistName($artist_name);
			if ( $artist_name_reversed != $artist_name ) {
				$artist_id = $aArtistData->nameToID( $artist_name_reversed );
				if ( $artist_id > 0 ) {
					$artist_name = $artist_name_reversed;
				}
			}
		}
		writeImage($base_dir, $image_url_source, $artist_name, $discogs_artist_id);
		if ( $i_image_processed % 10 == 0 && $dbg == true) {
			print "#{$i_image_processed}: Artist: {$artist_name} - Image: {$image_url_source} \n";
		}
		$i_image_processed++;
		//}
		//sleep (1);
	}

	/*
		Get discogs path to retrieve images from
	*/
	function getDicsogsImagesFromMySql( $max_item_from_mysql )
	{
		global $g_MySqlPDO;
		$q = "SELECT name, image, artist_id FROM discogs_artists WHERE image <> '' AND image_processed = 0 ORDER BY artist_id ASC";
		if ($max_item_from_mysql != 0) {
			$q .= " LIMIT 0, {$max_item_from_mysql}";
		}
		$aDicsogsImages = pdoQueryAssocRows($g_MySqlPDO, $q, array( ) );
		return $aDicsogsImages;
	}
	
	 /** Get all base data rows from table, obeying the limits given. */
    function getVariousArtist ( $artist_name )
    {
		global $g_MySqlPDO;
        $q = "SELECT * FROM artist_various WHERE artist_various_name = ?";
        return pdoQueryAssocFirstRow($g_MySqlPDO, $q, array($artist_name) ); 
    }

	/*
		Function used to update info about "item_base" image.
	*/
	function updateImageProcessedInfo( $artist_id )
	{
		global $g_MySqlPDO;
		$stmt = $g_MySqlPDO->prepare( 'UPDATE discogs_artists SET image_processed = 1 WHERE artist_id = ?' );
		$stmt->execute( array($artist_id) );
	}
		
	/*
		Remove and change specifik chars from image name.
	*/
	function getValidNameFromArtistName($artist_name) {
		$chars_to_remove = array(",", ".", " ", "é", "æ", "ø", "å");
		$chars_remove_to = array("","","_", "e", "a", "o", "a");
		$image_path_name = str_replace($chars_to_remove, $chars_remove_to, airplay_name_to_url(strtolower($artist_name)));
		return $image_path_name;
	}
	
	/*
		Get sub folders from artist_name
	*/
	function getImageFolderFromArtistName($artist_name) {
		//$image_path_name =getValidNameFromArtistName($artist_name);
		$chars_to_remove = array(",", "-", "_", "é", "æ", "ø", "å");
		$chars__remove_to = array("", "", "", "e", "a", "o", "a");
		$image_path_name = str_replace($chars_to_remove, $chars__remove_to, airplay_name_to_url(strtolower($artist_name)));
		$image_name_length = strlen($image_path_name);
		$image_folder = "";
		/* Make sure that we get correct directory if artist_name lower then 3 chars */
		for ($i = 0; $i < 3; $i++) {
			if ($i < $image_name_length) {
				$image_folder .= $image_path_name[$i] . "/";
			} else {
				break;
			}
		}
		return $image_folder;
	}
	
	
	function writeImage( $base_dir, $cover_url, $artist_name, $artist_id )
	{
		global $work_dir;
		global $temp_work_file_name;
		global $img_width_new;
		$img_height_new = 0;
		$temp_work_path = $work_dir . $temp_work_file_name; // Temp file name + path for working file (the file downloaded)
		
		$image_folder = getImageFolderFromArtistName($artist_name);
		$image_path_name = getValidNameFromArtistName($artist_name);
		
		$artist_dir = "{$base_dir}/{$image_folder}";
		$file_name_ap  = "{$artist_dir}{$image_path_name}.png";
		
		getImageFromRemoteServer( $cover_url, $temp_work_path );		
		
		$img_attributes = getImageAttributes($temp_work_path);

		if (count($img_attributes) == 7) {
		
			// Only create a directory if we have a valid image....
			@mkdir( $artist_dir, 0755, true ); // The @ suppresses php warnings. Here we particularly want to disregard the dir exists warning when creating
		
			$img_width = $img_attributes[0]; // Width
			$img_height = $img_attributes[1]; // Height
			$img_ratio = ($img_width / $img_height);
			
			$img_height_new = (int)($img_width_new / $img_ratio);
			$img_mime = $img_attributes["mime"]; // type

			if ($img_mime == "image/jpeg") {
				$image = imagecreatefromjpeg ($temp_work_path);
			} else if ($img_mime == "image/gif") {
				$image = imagecreatefromgif ($temp_work_path);
			} else if ($img_mime == "image/png") { // needed ??
				$image = imagecreatefrompng ($temp_work_path);
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
			updateImageProcessedInfo( $artist_id );
			//print "OK in {$artist_name} file {$file_name_ap} \n";
		// If width, height and mime is wrong then make sure to save it is processed
		} else {
			print "Error in {$artist_name} file {$file_name_ap} \n";
			//updateImageProcessedInfo( $item_base_id, '', 0, 0, 0 );
		}
		@unlink( $temp_work_path );
	}
	
	/*
		Replace domain for Discogs.
	*/
	function replaceDiscogsURL($cover_url) {
		return str_ireplace("http://api.discogs.com/", "http://s.pixogs.com/", $cover_url);
	}
	
	
	/*
		Download and save to file.
	*/
	function getImageFromRemoteServer($cover_url, $file_name) {
		$ch = curl_init($cover_url);
		$fp = fopen($file_name, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:29.0) Gecko/20100101 Firefox/29.0 ');
		curl_setopt($ch, CURLOPT_VERBOSE, FALSE);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
	}

	/*
		Get image attr from file.
	*/
	function getImageAttributes($file_name) {
		return getimagesize($file_name);
	}

?>