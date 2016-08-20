<?php

require_once ('db_api/MediaFormatLookup.php');
require_once ('db_api/MediaTypeLookup.php');
require_once ('db_api/GenreLookup.php');

require_once ('db_api/RecordStoreDataMySql.php');
require_once ('db_api/ArtistDataMySql.php');
require_once ('db_api/ItemDataMySql.php');
require_once ('db_api/PriceDataMySql.php');
require_once ('db_api/ItemBaseCorrectionDataMySql.php');
require_once ('db_api/CurrencyDataMySql.php');

require_once ('redis_api/RecordStoreDataRedis.php');
require_once ('redis_api/ArtistDataRedis.php');
require_once ('redis_api/ItemBaseDataRedis.php');
require_once ('redis_api/ItemPriceDataRedis.php');
require_once ('redis_api/ItemBaseCorrectionDataRedis.php');

require_once ('db_manip/MusicDatabaseFactory.php');


class RecordSplitter_item_price
{
    public  function    __construct( $dbAll )
    {
        $this->m_dbAll = $dbAll;
    }

    public function splitRecord ( $aData )
    {
        $aRecords = array();
        
        $drt = $aData['data_record_type'];
        $aRecordStore = $aData;
        $aRecordStore['data_record_type'] = 'record_store';

        $aArtistData = $aData;
        $aArtistData['data_record_type'] = 'artist_data';
        
        $aItemBase = $aData;
        
        if      ( 'album' == $drt || 'album_price' == $drt )   {
            $aItemBase['data_record_type']  = 'album_base';
            $aData['data_record_type']      = 'album_price';
        }
        else if ( 'song' == $drt  || 'song_price'  == $drt ) {
            $aItemBase['data_record_type']  = 'song_base';
            $aData['data_record_type']      = 'song_price';
        }
        else if ( 'merchandise' == $drt  || 'merchandise_price'  == $drt ) {
            $aItemBase['data_record_type']  = 'merchandise_base';
            $aData['data_record_type']      = 'merchandise_price';
        }
        else if ( 'concert' == $drt  || 'concert_price'  == $drt ) {
            $aItemBase['data_record_type']  = 'concert_base';
            $aData['data_record_type']      = 'concert_price';
        }
        else   {
            $aItemBase['data_record_type'] = '';
        }
        
        $aRecords[] = $aRecordStore;
        $aRecords[] = $aArtistData;
        $aRecords[] = $aItemBase;
        $aRecords[] = $aData;
       
        return $aRecords;
    }

    // ----------------------------
    // --- PRIVATE: Member data --- 
    // ----------------------------
    private         $m_dbAll = null;
}


class DbInserter_item_price 
{

    public  function    __construct( $dbAll )
    {
        $this->m_dbAll = $dbAll;
    }


	/*
		Function used to make sure that grading are 
		S, EX, NM, M, VG, G, P or F - with ++,+,--,-
	*/
	private function validateItemGrading($item_grading) {
		$ap_grading_rating = "";
		// This order to make sure we get ++ before +
		/*if (stristr($item_grading, "++") || stristr($item_grading, "plus plus")) {
			$ap_grading_rating = "++";
		} else if (stristr($item_grading, "+") || stristr($item_grading, "plus")) {
			$ap_grading_rating = "+";
		} else if (stristr($item_grading, "--") || stristr($item_grading, "minus minus")) {
			$ap_grading_rating = "--";
		} else if (stristr($item_grading, "-") || stristr($item_grading, "minus")) {
			$ap_grading_rating = "-";
		}*/
		// This order for gettting near mint before mint, very good before good.
		if ($item_grading == "EX-" || $item_grading == "EX+" || $item_grading == "EX--" || $item_grading == "EX++" || $item_grading == "EX" || $item_grading == "NM+" || $item_grading == "NM-" || $item_grading == "NM++" || $item_grading == "NM--" || $item_grading == "NM" ||
		$item_grading == "M-" || $item_grading == "M+" || $item_grading == "M--" || $item_grading == "M++" || $item_grading == "M" || $item_grading == "VG+" || $item_grading == "VG-" || $item_grading == "VG++" || $item_grading == "VG--" || $item_grading == "VG" ||
		$item_grading == "G-" || $item_grading == "G+" || $item_grading == "G--" || $item_grading == "G++" || $item_grading == "G" || $item_grading == "P" || $item_grading == "F" || $item_grading == "SS" || $item_grading == "S") {
			$ap_grading = $item_grading;
		} else if (stristr($item_grading, "SEAL")) {
			$ap_grading = "SS";
		} else if (stristr($item_grading, "EX")) {
			$ap_grading = "EX";
		} else if (stristr($item_grading, "NEAR MINT") || stristr($item_grading, "NM")) {
			$ap_grading = "NM";
		} else if (stristr($item_grading, "MINT")) {
			$ap_grading = "M";
		} else if (stristr($item_grading, "VERY GOOD") || stristr($item_grading, "VG")) {
			$ap_grading = "VG";
		} else if (stristr($item_grading, "GOOD")) {
			$ap_grading = "G";
		} else if (stristr($item_grading, "POOR")) {
			$ap_grading = "P";
		} else if (stristr($item_grading, "FAIR")) {
			$ap_grading = "F";
		} else {
			$ap_grading = "";
		}
		
		if ($ap_grading != "") {
			//$ap_grading = $ap_grading . $ap_grading_rating;
			$ap_grading = $ap_grading;
		} else {
			$ap_grading = "";
		}
print $ap_grading;
		return $ap_grading;
	}
	
	/*
		Function used to validate media format vs item_type - to make sure we dont import albums as T-shirts and merchandise as Vinyl.
	*/
	private function validateItemTypeVSMediaFormat($item_type, $media_format_id) {
		// Album and songs.
		if (($item_type == 1 || $item_type == 2) && $media_format_id < 64 && $media_format_id > 0) {
			return $media_format_id;
		// Merchandise.
		} else if ($item_type == 3 && $media_format_id < 128 && $media_format_id > 63) {
			return $media_format_id;
		// Concerts
		} else if ($item_type == 4 && $media_format_id < 130 && $media_format_id > 127) {
			return $media_format_id;
		} else {
			return 0;
		}
	}
	
	private function validateItemDeliveryStatusID($item_price_delivery_status_id) {
		$item_price_delivery_status_id = (int)$item_price_delivery_status_id;
		if ($item_price_delivery_status_id == 0) {
			return 1;
		} else if ($item_price_delivery_status_id >= 1 && $item_price_delivery_status_id < 10) {
			return $item_price_delivery_status_id;
		} else {
			return 1;
		}
	}
	
    public function insertToDB ( $aData )
    {
		$artist_id = $this->getSimpleIDs($aData);
		$media_format_id = 0;
		$media_type_id = 0;
		$currency_id = 0;
		$release_date = "0000-00-00";
		$item_event_date = "0000-00-00"; // Only Concerts are using this variable - item_type = 4 - other are using '0000-00-00'
		$item_event_time = "00:00:00"; // Only Concerts are using this variable - item_type = 4 - other are using '00:00:00'
		$price_local = 0.0;
		$cover_image_url = "";
		$album_name = "";
		$song_name = "";
		$item_used = 0;
		$record_store_id = 0;
		$track_number = 0;
        $parent_item = 0;
        $item_grading = "";
        $item_grading_cover = "";
        
		if ( $artist_id <= 0 )  {
            return false;
        }
		
		if ( array_key_exists ('release_date', $aData) ) {
            $release_date = $aData["release_date"];
		}
        if ( array_key_exists ('track_number', $aData) ) {
            $track_number = $aData["track_number"];
		}
        if ( array_key_exists ('item_grading', $aData) ) {
            $item_grading = $aData["item_grading"];
			$item_grading = $this->validateItemGrading($item_grading);
			$aData["item_grading"] = $item_grading;
		}
        if ( array_key_exists ('item_grading_cover', $aData) ) {
            $item_grading_cover = $aData["item_grading_cover"];
			$item_grading_cover = $this->validateItemGrading($item_grading_cover);
			$aData["item_grading_cover"] = $item_grading_cover;
		}
		$price_local = (float)$aData["price_local"];
		$record_store_id = $this->m_dbAll->m_dbRecordStore->nameToIDSimple($aData["record_store_name"]);
		$aData["record_store_id"] = $record_store_id;
		
		$media_format_id = $this->m_dbAll->m_MediaFormatLookup->nameToIDSimple($aData["media_format_name"]);
		$aData["media_format_id"] = $media_format_id;
        if ( 0 == $media_format_id ) {
            $this->m_dbAll->m_MediaFormatLookup->latestUnknownSave ($aData);
        }
		$item_price_delivery_status_id = $this->validateItemDeliveryStatusID($aData["item_delivery_status"]);
		$aData["item_price_delivery_status_id"] = $item_price_delivery_status_id;
		
		$media_type_id = $this->m_dbAll->m_MediaTypeLookup->nameToIDSimple($aData["media_type_name"]);
		$aData["media_type_id"] = $media_type_id;
        if ( 0 == $media_type_id ) {
            $this->m_dbAll->m_MediaTypeLookup->latestUnknownSave ($aData);
        }

		$currency_id = $this->m_dbAll->m_dbCurrencyData->nameToIDSimple($aData["currency_name"]);
		$aData['currency_id'] = $currency_id;

		$item_genre_id = $this->m_dbAll->m_dbGenreLookup->nameToIDSimple($aData["genre_name"]);
		$aData['item_genre_id'] = $item_genre_id;

		$album_name = $aData["album_name"];

		$song_name = $aData["song_name"];

		/* Make sure that we use the best posible image */
		if ( array_key_exists ('small_cover_image_url', $aData) ) {
			$aData['cover_image_url'] = $aData["small_cover_image_url"];
			$cover_image_url = $aData['cover_image_url'];
		}
		if ( array_key_exists ('medium_cover_image_url', $aData) ) {
			$aData['cover_image_url'] = $aData["medium_cover_image_url"];
			$cover_image_url = $aData['cover_image_url'];
		}
		if ( array_key_exists ('large_cover_image_url', $aData) ) {
			$aData['cover_image_url'] = $aData["large_cover_image_url"];
			$cover_image_url = $aData['cover_image_url'];
		}
        /* Make sure that item_used is not empty */
        $item_used = (int)$aData['item_used'];
        if ( $aData["data_record_type"] == "album_price" ) {
			$item_type = 1;
			$item_price_name = $album_name;
			$aData["item_time"] = (int)$aData["album_time"];
			$aData["item_year"] = (int)$aData["album_year"];
			$aData["item_event_date"] = $item_event_date;
			$aData["item_event_time"] = $item_event_time;
		} else if ($aData["data_record_type"] == "song_price") {
			$item_type = 2;
			$item_price_name = $song_name;
			$aData["item_time"] = (int)$aData["song_time"];
			$aData["item_year"] = (int)$aData["song_year"];
			$aData["item_event_date"] = $item_event_date;
			$aData["item_event_time"] = $item_event_time;
        } else if ($aData["data_record_type"] == "merchandise_price") {
			$item_type = 3;
			$item_price_name = $aData["merchandise_name"];
            $aData["item_time"] = 0;
			$aData["item_year"] = 0;
			$aData["item_event_date"] = $item_event_date;
			$aData["item_event_time"] = $item_event_time;
		} else if ($aData["data_record_type"] == "concert_price") {
			$item_type = 4;
			$item_price_name = $aData["concert_name"];
            $aData["item_time"] = 0;
			$aData["item_year"] = 0;
			$item_event_date_time_split = explode(" " , $aData["concert_date_time"]);
			if (count($item_event_date_time_split) == 2) {
				$item_event_date = $item_event_date_time_split[0];
				$item_event_time = $item_event_date_time_split[1];
				$aData["item_event_date"] = $item_event_date;
				$aData["item_event_time"] = $item_event_time;
			}
		}
        
		// Test if media_format is valid for the item_type.
		$media_format_id = $this->validateItemTypeVSMediaFormat($item_type, $media_format_id);
		$aData["media_format_id"] = $media_format_id;
		
		$item_base_id = $this->m_dbAll->m_dbItemBaseData->fuzzyFindID($artist_id, $item_price_name, $item_type, $this->m_dbAll->m_dbItemBaseCorrection);

		/*
			Make sure that we dont insert or update prices that does not have:
			- a valid item_base_id, artist_id, record_store_id, media_format_id, media_type_id, item_price_name, buy_at_url, price_local, currency_id
		*/
		if ($item_base_id > 0 && $artist_id > 0 && $record_store_id > 0 && $item_price_name != "" && $media_format_id > 0 && $price_local > 0 && $currency_id > 0) {
			// Concert specific code.
			if ($item_type == 4 && $aData["item_event_date"] != "" && $aData["item_event_time"] != "") {
				$item_prices = $this->m_dbAll->m_dbItemPriceData->lookupItemPriceIDWithOutEventDate($artist_id, $item_price_name, $media_format_id, $record_store_id, $item_used, $item_type, $item_grading, $item_grading_cover);
				// Concerts for this artist and record_store.
				if (count($item_prices) != 0) {
					$item_price_id = 0;
					// Find any concerts with data '0000-00-00' and get item_price_id for updating.
					foreach ($item_prices AS $a) {
						if ($a["item_event_date"] == '0000-00-00') {
							$item_price_id = $a["item_price_id"];
						}
					}
					// Make sure that we don't add an new concert with the same date previous added and get item_price_id for updating.
					foreach ($item_prices AS $a) {
						if ($a["item_event_date"] == $aData["item_event_date"]) {
							$item_price_id = $a["item_price_id"];
						}
					}
				} else {
					$item_price_id = 0;
				}
			// All other - no change
			} else {
				/* Get parent name if song has an album */
				if ( $album_name != '' && $song_name != '' ) {
					/* Parent is always an album - therefore last parameter is one (1) */
					$aData['parent_item'] = $this->m_dbAll->m_dbItemPriceData->lookupItemPriceID ($artist_id, $album_name, $media_format_id, $record_store_id, $item_used, 1, $item_grading, $item_grading_cover, $item_event_date);
				}
				$item_price_id = $this->m_dbAll->m_dbItemPriceData->lookupItemPriceID            ($artist_id, $item_price_name, $media_format_id, $record_store_id, $item_used, $item_type, $item_grading, $item_grading_cover, $item_event_date);
			}
			/* Do nothing we found duplicates */
			if ($item_price_id < 0) {
				//print "Duplicate found....\n";
            } else if ($item_price_id == 0) {
                $parent_item = 0;
                $release_date = "0000-00-00";
                if ( array_key_exists ('track_number', $aData) )   $track_number = $aData["track_number"];
                if ( array_key_exists ('parent_item', $aData) )   $parent_item = $aData["parent_item"];
                if ( array_key_exists ('release_date', $aData) )  { $release_date = $aData["release_date"]; }
				
                $item_time = (int)$aData["item_time"];
                $item_year = (int)$aData["item_year"];
             
				$item_price_id = $this->m_dbAll->m_dbItemPriceData->createNewFull ($item_base_id, $item_price_name, $record_store_id, $media_format_id, $media_type_id, $price_local, $currency_id, $aData["buy_at_url"], $artist_id, $item_used, $item_type, $cover_image_url, $release_date, $item_event_date, $item_event_time, $item_year, $item_time, $track_number, $parent_item, $aData['item_genre_id'], $item_grading, $item_grading_cover, $item_price_delivery_status_id);
				if ( $item_price_id == 0 ) {
                    printf("ERROR CRITICAL: Could not create new price in DB: '%s'\n", $item_price_name );
                    printf("      Params: createNewFull (item_base_id, item_price_name, record_store_id, media_format_id, media_type_id, price_local, currency_id, aData['buy_at_url'], artist_id, item_used, item_type, cover_image_url, release_date, item_event_date, item_event_time, item_year, item_time, track_number, parent_item, {aData['item_genre_id']}, item_grading, item_grading_cover, item_price_delivery_status_id);\n" );
		            printf("      Data  : createNewFull ($item_base_id, $item_price_name, $record_store_id, $media_format_id, $media_type_id, $price_local, $currency_id, {$aData['buy_at_url']}, $artist_id, $item_used, $item_type, $cover_image_url, $release_date, $item_event_date, $item_event_time, $item_year, $item_time, $track_number, $parent_item, {$aData['item_genre_id']}, $item_grading, $item_grading_cover, $item_price_delivery_status_id);\n" );
				}
			} else {
                $aData['item_price_id'] = $item_price_id;
                //$this->m_dbAll->m_dbItemPriceData->updateBaseDataCheckOld($aData);
                $this->m_dbAll->m_dbItemPriceData->updateBaseData($aData);
            }
			return true;
		} 
		else {
            // Please just uncomment this when done debugging. We might need some other time :)
              //print("DBG: NO price insert:\n" );
              //printf("item_base_id: $item_base_id\n");
              //printf("artist_id: $artist_id\n");
              //printf("record_store_id: $record_store_id\n");
              //printf("item_price_name: $item_price_name\n");
              //printf("media_format_id: $media_format_id\n");
              //printf("price_local: $price_local\n");
              //printf("currency_id: $currency_id\n\n");
			 return false;
		}
	}
	// ---------------------------------
    // --- PRIVATE: Helper functions --- 
    // ---------------------------------
    private function getSimpleIDs ( &$aData )
    {
        
        // --- Get artist_id ---
        $artist_id      = $this->m_dbAll->m_dbArtistData->lookupID( $aData['artist_name'] );
        if ( $artist_id <= 0 )  {
            logDbInsertWarning("[item_price] Could not find '{$aData['artist_name']}', id returned '$artist_id'");
        }
        $aData['artist_id'] = $artist_id;
        
        // item_genre_id: We prefer to use item_genre_name, but can fallback to the old genre_name
        if ( array_key_exists ('item_genre_name', $aData ) ) {
			$item_genre_name = $aData['item_genre_name'];
			$aData['item_genre_id'] = $this->m_dbAll->m_dbGenreLookup->lookupID($item_genre_name);
        }
        return $artist_id;
    }
    // ----------------------------
    // --- PRIVATE: Member data --- 
    // ----------------------------
    private         $m_dbAll = null;
}

?>