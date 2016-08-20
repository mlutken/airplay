<?php
	mb_internal_encoding("UTF-8");
	date_default_timezone_set('Europe/Copenhagen');
	require_once ( 'aphp_fix_include_path.php' );
	require_once ('airplay_globals.php');

	require_once ('utils/general_utils.php');
	require_once ('utils/string_utils.php');
	require_once ('db_api/db_string_utils.php');
	require_once ('db_manip/AllDbTables.php');

	$dba = new AllDbTables();
	

	function createNewFull ( $record_store_name, $record_store_url, $country_id, $selling_type_id, $record_store_type_id )
    {
		global $g_MySqlPDO;
        $stmt = $g_MySqlPDO->prepare("INSERT INTO record_store (record_store_name, record_store_url, country_id, is_in_record_store_guide, selling_type_id, record_store_type_id) VALUES (?, ?, ?, 1, ?, ?)" );
        $stmt->execute( array($record_store_name, $record_store_url, $country_id, $selling_type_id, $record_store_type_id) );
        $id = (int)$g_MySqlPDO->lastInsertId();
        return $id;
    }
	
	function updateRecordStoreData($selling_type_id, $record_store_type_id, $record_store_id) {
		global $g_MySqlPDO;
		$stmt = $g_MySqlPDO->prepare( 'UPDATE record_store SET selling_type_id = ?, record_store_type_id = ? WHERE record_store_id = ?' );
		$stmt->execute( array($selling_type_id, $record_store_type_id, $record_store_id) );
	}

	function deleteMFRel($record_store_id) {
		global $g_MySqlPDO;
		$stmt = $g_MySqlPDO->prepare( 'DELETE FROM record_store_media_format_rel WHERE record_store_id = ?' );
		$stmt->execute( array($record_store_id) );
	}
	
	function insertMFRel ($record_store_id, $media_format_id) {
		global $g_MySqlPDO;
        $stmt = $g_MySqlPDO->prepare("INSERT INTO record_store_media_format_rel (record_store_id, media_format_id) VALUES (?, ?)" );
        $stmt->execute( array($record_store_id, $media_format_id) );
        $id = (int)$g_MySqlPDO->lastInsertId();
        return $id;
	}
	
	
	/*
		Get iitem_base items to retrieve images from
	*/
	function getRecordStoreIDFromName($record_store_name)
	{
		global $g_MySqlPDO;
		$q = "SELECT record_store_id FROM record_store WHERE record_store_name = :record_store_name";
		$a = pdoQueryAssocRows($g_MySqlPDO, $q, array(":record_store_name" => $record_store_name) );
		return $a;
	}
	
	$ap_imusic = new MusicImporter();
	$lines = $ap_imusic->getFileLines();
	$StoreData = $ap_imusic->getContent($lines);

	$ap_imusic->checkDB($StoreData);


class MusicImporter {
	
	function __construct() {
		$this->file_to_import = "Weblinks.csv";                    // File to download
		$this->file_imusic_output = "iMusic.xml";               // File for output our XML
		$this->file_imusic_output_diff = "diff_iMusic.csv";     // File with the diff from new file and file for last run
		$this->file_last_run = "music_LATEST.csv";              // File for last run
		$this->unkown_media_name = "unkown_media_name.txt";     // unknown media
		$this->unknown_genre_name = "unknown_genre_name.txt";   // unknown genre
		$this->products = 0;
		$this->upload_server	= "http://filesupload.airplaymusic.dk/upload.php";
		$this->download_server = "http://www.imusic.dk/update/export/deliverable/"; // - only valid from slagteriet and robot server
		//$this->download_server = "http://www.airplaymusic.dk/"; // - only valid from slagteriet and robot server
		
		$this->record_store_name = "iMusic (DK)";
		$this->record_store_url = "http://www.imusic.dk/";
	   

	}
	

	
	/*
		Get all the lines in the file as an array
	*/
	function getFileLines() {
		$handle = fopen($this->file_to_import, "r"); 

		if ($handle) { 
		   while (!feof($handle)) { 
			   $lines[] = fgets($handle, 4096);
		   } 
		   fclose($handle); 
		}
		return $lines;
	}
	
	/*
		Logic to parse the lines.
	*/
	function getContent($lines) {
		$aRS = array();
		foreach ($lines as $line) {
			if ($line != "") {
				// Split each file into array
				$product = explode( ';', $line );

				$record_store_name = $product[0];
				$record_store_link = $product[1];
				//$record_store_type = $product[2]; // Not used
				$record_store_country = $product[3];
				$record_store_media_format = $product[5];

				$record_store_shop_type = $product[6];
				$record_store_shop_type2 = $product[7];

				if ($record_store_name != "" && $record_store_link != "" && $record_store_country != "" && strlen($record_store_country) == 2 &&  $record_store_media_format != "") {
					$media_formats = array();
					$selling_type = 1;
					
					if (stristr($record_store_media_format, "brugt")) {
						$selling_type = 3;
					}

					$types = explode(",", $record_store_media_format );
					if (count($types)) {
						foreach ($types AS $thetype) {
							$media_formats[] = $thetype;
						}
					} else {
						$media_formats[] = $record_store_media_format;
					}

					if ($record_store_shop_type == "" && $record_store_shop_type2 == "") {
						$shop_type = 2;
					} else if ($record_store_shop_type == "" && $record_store_shop_type2 == "butik") {
						$shop_type = 8;
					} else {
						$shop_type = 4;
					}
					
					$media_formats = $this->cleanUpMediaFormatNames($media_formats);
					$country_id = $this->getCountryIDFromCode($record_store_country);
					
					$aRS[] = array(	"record_store_name" => $record_store_name . " (" . $record_store_country . ")" , 
							"record_store_url" => $record_store_link,
							"country_id" => $country_id, 
							"media_formats" => array($media_formats),
							"selling_type" => $selling_type,
							"shop_type" => $shop_type
					);
				}
			}
		}
		//var_dump($aRS);
		return $aRS;
	}
 
	public function checkDB($data) {
		foreach ($data AS $a) {
			$record_store_name = $a["record_store_name"];
			$aRS = getRecordStoreIDFromName($a["record_store_name"]);
			//var_dump($aRS);
			$record_store_id = (int)$aRS[0]["record_store_id"];
			$record_store_url = $a["record_store_url"];
			$country_id = $a["country_id"];
			$selling_type_id = $a["selling_type"];
			$record_store_type_id = $a["shop_type"];

			if ( $record_store_id == 0 ) {
				print "Added new .... {$record_store_name} {$record_store_url} {$country_id} {$selling_type_id} {$record_store_type_id} \n";
		        $record_store_id = createNewFull ( $record_store_name, $record_store_url, $country_id, $selling_type_id, $record_store_type_id );
			} else {
				print "Update .... {$selling_type_id} {$record_store_type_id} {$record_store_id} \n";
				updateRecordStoreData($selling_type_id, $record_store_type_id, $record_store_id);
			}
			
			deleteMFRel($record_store_id);
			$aMFS = $a["media_formats"][0];
			if ( $record_store_id != 0 ) {
				foreach($aMFS AS $aMF) {
					insertMFRel($record_store_id, $aMF);
				}
			}
		}
	}
 
	private function cleanUpMediaFormatNames($media_formats) {
		$aNew = array();
		$aTemp = array();
		foreach ($media_formats AS $media_format) {
			$aTemp[] = str_ireplace(" ", "", str_ireplace("brygt", "", str_ireplace("brugt", "", $media_format)));
		}
		$aTemp = array_unique($aTemp);
		
		foreach ($aTemp AS $media_format) {
			$format = $media_format;
			if ($format == "mp3") {
				$format_id = 3;
			} else if ($format == "cd") {
				$format_id = 5;
			} else if ($format == "vinyl") {
				$format_id = 7;
			} else if ($format == "sacd") {
				$format_id = 12;
			} else if ($format == "flac") {
				$format_id = 16;
			} else if ($format == "dvd") {
				$format_id = 8;
			} else if ($format == "wav") {
				$format_id = 17;
			} else if ($format == "merchandise") { // just some random merchandise id
				$format_id = 64;
			} else {
				print "her" . $format . "\n";
			}
			$aNew[] = $format_id;
		}
		return $aNew;
	}
 
 
	private function getCountryIDFromCode ($code) {
		$lang_id = 0;
		if ($code == "UK") {
			$lang_id = 44;
		} else if ($code == "IS") {
			$lang_id = 354;
		} else if ($code == "DE") {
			$lang_id = 49;
		} else if ($code == "US") {
			$lang_id = 1;
		} else if ($code == "DK") {
			$lang_id = 45;
		} else if ($code == "NO") {
			$lang_id = 47;
		} else if ($code == "SE") {
			$lang_id = 46;
		} else if ($code == "CA") {
			$lang_id = 2;
		} else if ($code == "AU") {
			$lang_id = 61;
		} else if ($code == "CH") {
	$lang_id = 45;
		} else if ($code == "IE") {
	$lang_id = 45;
		} else if ($code == "AR") {
	$lang_id = 45;
		} else if ($code == "JP") {
			$lang_id = 81;
		} else if ($code == "NL") {
			$lang_id = 31;
		} else if ($code == "ES") {
			$lang_id = 34;
		} else if ($code == "FI") {
			$lang_id = 358;
		} else if ($code == "ZA") {
	$lang_id = 45;
		} else if ($code == "KR") {
	$lang_id = 45;
		} else if ($code == "MX") {
			$lang_id = 52;
		} else if ($code == "HK") {
			$lang_id = 852;			
		}
		return $lang_id;
	}
 
}
?>