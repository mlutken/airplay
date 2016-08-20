<?php

require_once ('db_api/GenreLookup.php');

require_once ('db_api/ItemBaseCorrectionDataMySql.php');
require_once ('db_api/ArtistDataMySql.php');
require_once ('db_api/ItemDataMySql.php');

require_once ('redis_api/ItemBaseCorrectionDataRedis.php');
require_once ('redis_api/ArtistDataRedis.php');
require_once ('redis_api/ItemBaseDataRedis.php');

require_once ('db_manip/MusicDatabaseFactory.php');

/** Insert item_base records into the DB. */
class DbInserter_item_base 
{

    public  function    __construct( $dbAll )
    {
        $this->m_dbAll = $dbAll;
    }


    public function insertToDB ( $aData )
    {
//        var_dump($aData);
		$artist_id = $this->getSimpleIDs($aData);
		$item_type = 0;
		$album_name = "";
        
        if ( $artist_id <= 0 )  return false;
        $item_name_raw = '';
        
		if ($aData['data_record_type'] == 'album_base') {
			$item_type = 1;
			$item_name_raw = $aData['album_name'];
            $aData['item_year'] = (int)$aData['album_year'];
            $aData['item_time'] = (int)$aData['album_time'];
		} else if ($aData["data_record_type"] == "song_base") {
			$item_type = 2;
            $item_name_raw = $aData["song_name"];
            $aData["item_year"] = (int)$aData["song_year"];
            $aData['item_time'] = (int)$aData['song_time'];
            $album_name = $aData['album_name'];
            // Get parent album id
            if ( $album_name != '' ) {
                $aData['parent_item'] = $this->m_dbAll->m_dbItemBaseData->fuzzyFindID ($artist_id, $album_name, 1, $this->m_dbAll->m_dbItemBaseCorrection );
            }
        } else if ($aData['data_record_type'] == 'merchandise_base') {
			$item_type = 3;
			$item_name_raw = $aData['merchandise_name'];
            $aData["item_year"] = 0;
            $aData['item_time'] = 0;
		} else if ($aData['data_record_type'] == 'concert_base') {
			$item_type = 4;
			$item_name_raw = $aData['concert_name'];
            $aData["item_year"] = 0;
            $aData['item_time'] = 0;
		}

		if ($item_type != 0 && $item_name_raw != '' ) {
			$item_base_id = $this->m_dbAll->m_dbItemBaseData->fuzzyFindID ($artist_id, $item_name_raw, $item_type, $this->m_dbAll->m_dbItemBaseCorrection );

            if ( $item_base_id == 0 ) {
                $item_base_name = cleanItemNameFull($item_name_raw, $this->m_dbAll->m_dbItemBaseCorrection );
				//$item_base_id = $this->m_dbAll->m_dbItemBaseData->createNew ($artist_id, $item_base_name, $item_type);
                $track_number = 0;
                $parent_item = 0;
				$item_master = 0;
                $release_date = "0000-00-00";
				
                if ( array_key_exists ('track_number', $aData) )  { $track_number = $aData["track_number"]; }
                if ( array_key_exists ('parent_item', $aData) )  { $parent_item = $aData["parent_item"]; }
                if ( array_key_exists ('release_date', $aData) )  { $release_date = $aData["release_date"]; }
				if ( array_key_exists ('item_master', $aData) )  { $item_master = $aData["item_master"]; }

                $item_time = (int)$aData["item_time"];
                /* Make sure that we have a valid item_base_name */
                if ( $item_base_name != "" ) {
                    $item_base_id = $this->m_dbAll->m_dbItemBaseData->createNewFull ($artist_id, $item_base_name, $item_type, $aData['item_genre_id'], $aData['item_year'], $release_date, $parent_item, $item_time, $track_number, $item_master);
					if ($item_base_id == 0) {
						printf("ERROR: DbInserter_item_base CreateNew: $artist_id, $item_base_name\n");
					}
                }
			} else {
                $aData['item_type']         = $item_type;
                $aData['item_base_id']      = $item_base_id;
                $aData['item_base_name']    = $this->m_dbAll->m_dbItemBaseData->IDToName($item_base_id);
                $aData['item_base_soundex'] = calcSoundex($aData['item_base_name']); // Make sure we update soundex
                $this->m_dbAll->m_dbItemBaseData->updateBaseDataCheckOld($aData);
            }
            // Update Review
			$aData['item_base_id'] = $item_base_id;
            if ( $aData['review_rating'] != '' && $aData['review_rating'] > 0) {
                $item_base_review_id = $this->m_dbAll->m_dbItemBaseReviewData->toID($aData);
                if ( $item_base_review_id == 0 ) {
                    $item_base_review_id = $this->m_dbAll->m_dbItemBaseReviewData->newItem($aData);
                }
                $aData['item_base_review_id'] = $item_base_review_id;
                $this->m_dbAll->m_dbItemBaseReviewData->updateBaseData($aData);
			}
		}
		else {
            printf("Skipping: {$aData['album_name']} - {$item_name_raw}, item_type: ${item_type}\n");
		}
		

        return true;
    }

    // ---------------------------------
    // --- PRIVATE: Helper functions --- 
    // ---------------------------------
    private function getSimpleIDs ( &$aData )
    {
        $this->artistNameCorrectWhenSpecificItemBase( $aData );
        // --- Get artist_id ---
        $artist_id      = $this->m_dbAll->m_dbArtistData->lookupID( $aData['artist_name'] );
        if ( $artist_id <= 0 )  {
            logDbInsertWarning("[item_base] Could not find '{$aData['artist_name']}', id returned '$artist_id'");
        }

        // item_genre_id: We prefer to use item_genre_name, but can fallback to the old genre_name
        $item_genre_name = '';
        if      ( array_key_exists ('item_genre_name', $aData   ) ) $item_genre_name = $aData['item_genre_name'];
        else if ( array_key_exists ('genre_name', $aData        ) ) $item_genre_name = $aData['genre_name'];
        $item_genre_id = $this->m_dbAll->m_dbGenreLookup->lookupID($item_genre_name);
        
        // If genre name is not found write it into the UnknownGenres table.
        // We do this here and not in the price inserter, since it then also works 
        // if we create scripts to purely update base data with no prices.
        // Note: We need to lookup the record_store_id in this case.
        if ( 0 == $item_genre_id ) {
            $aData['record_store_id'] = $this->m_dbAll->m_dbRecordStore->nameToIDSimple($aData['record_store_name']);
            $this->m_dbAll->m_dbGenreLookup->latestUnknownSave ($aData);
        }
        
        // Insert to $aData, so we are ready for the updateBaseData later
        $aData['artist_id']     = $artist_id;   // Insert to $aData, so we are ready for the updateBaseData later
        $aData['item_genre_id'] = $item_genre_id;   
        return $artist_id;  // For convenience we also return artist_id since it's crucial
    }

    private function getItemBaseNameRaw($aData)
    {
        $item_name_raw = '';
        if ($aData['data_record_type'] == 'album_base') {
            $item_name_raw = $aData['album_name'];
        } else if ($aData["data_record_type"] == "song_base") {
            $item_name_raw = $aData["song_name"];
        } else if ($aData['data_record_type'] == 'merchandise_base') {
            $item_name_raw = $aData['merchandise_name'];
        } else if ($aData['data_record_type'] == 'concert_base') {
            $item_name_raw = $aData['concert_name'];
        }
        return $item_name_raw;
    }
    
    private function artistNameCorrectWhenSpecificItemBase( &$aData)
    {
        // Add corrections here. Note Only works if the corrected to artist_name is in DB already
        static  $aCorrect = array (
            'Kim Larsen'  => array ( 
                '7-9-13' => 'Kim Larsen & Kjukken',
                '7 9 13' => 'Kim Larsen & Kjukken'
                ),
            'XXXXXXX'  => array ( 
                'XXXXXXXX' => 'XXXXXXXXX'
                )
        );
        
        $artist_name = $aData['artist_name'];
        if ( array_key_exists( $artist_name, $aCorrect ) ) {
            $item_name_raw = $this->getItemBaseNameRaw($aData);
            $item_base_name = cleanItemNameFull($item_name_raw, $this->m_dbAll->m_dbItemBaseCorrection );
            $a = $aCorrect[$artist_name];
            $artistCorrectionName = $aCorrect[$artist_name][$item_base_name];
            if ( $artistCorrectionName != '' ) {
                $aData['artist_name'] = $artistCorrectionName;
            }
        }
        
    }
    
    
    // ----------------------------
    // --- PRIVATE: Member data --- 
    // ----------------------------
    private         $m_dbAll = null;

}




?>