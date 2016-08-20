<?php

require_once ("db_api/SimpleTableDataMySql.php");


class ItemDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'item_base'
        , array(  'item_type', 'artist_id', 'item_base_name', 'record_label_id'
                , 'item_genre_id', 'item_subgenre_ids', 'item_year', 'release_date'
                , 'parent_item', 'item_time', 'track_number', 'child_items'
                , 'item_master', 'item_base_reliability', 'item_base_soundex'
                , 'image_url', 'image_width', 'image_height', 'image_processed'
                , 'image_from_record_store_id' 
                ) 
        , $dbPDO );
    }

    // -------------------------------------
    // --- Erase/delete/merge functions ----
    // -------------------------------------
    /** Completely erase an entry. */
    public function erase ($item_base_id)
    {
        $aArgs = array($item_base_id);
        $stmt = $this->m_dbPDO->prepare( 'DELETE FROM item_base WHERE item_base_id = ?' );
        $stmt->execute( $aArgs );
        
        $stmt = $this->m_dbPDO->prepare('DELETE FROM item_base_text WHERE item_base_id = ?' );
        $stmt->execute( $aArgs );
    }

    /** . */
    public function merge ($into_item_base_id, $from_item_base_id)
    {
        if ( $into_item_base_id == $from_item_base_id ) return;
        
        $aBaseDataInto = $this->getBaseData($into_item_base_id);
        $aBaseDataFrom = $this->getBaseData($from_item_base_id);
        $aBaseDataFrom['artist_id']             = $aBaseDataInto['artist_id'];
        $aBaseDataFrom['item_base_name']        = $aBaseDataInto['item_base_name'];
        $aBaseDataFrom['item_base_id']          = $into_item_base_id;
        $aBaseDataFrom['item_base_reliability'] = $aBaseDataInto['item_base_reliability'];

        $this->updateBaseDataCheckOld($aBaseDataFrom);
    }
    
    /** Move item_base to a new artist "owner". I.e. change the artist to which the \a $from_item_base_id belongs */
    public function moveToArtist ($into_artist_id, $from_item_base_id)
    {
         $stmt = $this->m_dbPDO->prepare( 'UPDATE item_base SET artist_id=? WHERE item_base_id = ?' );
         $stmt->execute( array($into_artist_id, $from_item_base_id ) );
    }

   
    // ----------------------------------
    // --- Item ID lookup functions --- 
    // ----------------------------------

    /** Lookup item ID from item "base/real/official" name.
    \return one item ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function nameToID ($artist_id, $item_base_name, $item_type)
    {
        $q = "SELECT item_base_id FROM item_base WHERE artist_id = ? AND item_type = ? AND item_base_name = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($artist_id, $item_type, $item_base_name) );
    }
    
    /** Lookup ID from $aData.
    \return One ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function toID ($aData)
    {
        return $this->nameToID ($aData['artist_id'], $aData['item_base_name'], $aData['item_type'] );
    }
    
    /** Lookup item ID from item "base/real/official" name.
    \deprecated Use nameToID
    \return one item ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function baseNameToID ($artist_id, $item_base_name, $item_type)
    {
        $q = "SELECT item_base_id FROM item_base WHERE artist_id = ? AND item_type = ? AND item_base_name = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($artist_id, $item_type, $item_base_name) );
    }
    
    /** TODO: Doc finish
    Exact match using soundex strings . */
    public function findID ( $artist_id, $item_name_raw, $item_type, $dbItemBaseCorrection )
    {
        if ( $item_name_raw == '' ) return 0;
        $cleanedItemName = cleanItemNameFull($item_name_raw, $dbItemBaseCorrection, $artist_id );
        $item_base_id = $this->nameToID( $artist_id, $cleanedItemName, $item_type );
        if ( $item_base_id > 0 ) return $item_base_id;
        $aCandidates = $this->getItemsForArtist ( $artist_id, $item_type );
        $item_base_id = findIdFromSoundex($aCandidates, 'item_base', $cleanedItemName );
        return $item_base_id;
    }

    /** TODO: Doc finish
    Fuzzy match using soundex strings. We consider it a match if the string are at least "90% alike"
    - Which in this context is computed from the if levenshtein distance between the cleanedItemName 
    and each candidate item name. We get a factor like this:
        $levDistSoundex = levenshtein ( $soundexToFind , $soundexCandidate );
        $fac  = 1.0 - ($levDistSoundex / $iLenSoundexToFind);
        The best matching is returned, but only if is greater than .90 (90% match).
    */
    public function fuzzyFindID ( $artist_id, $item_name_raw, $item_type, $dbItemBaseCorrection )
    {
        if ( $item_name_raw == '' ) return 0;
        $cleanedItemName = cleanItemNameFull($item_name_raw, $dbItemBaseCorrection, $artist_id );
        $item_base_id = $this->nameToID( $artist_id, $cleanedItemName, $item_type );
        if ( $item_base_id > 0 ) return $item_base_id;
        $aCandidates = $this->getItemsForArtist ( $artist_id, $item_type );
        $item_base_id = fuzzyFindIdFromSoundex($aCandidates, 'item_base', $cleanedItemName, 0.90 );
        return $item_base_id;
    }

    // ------------------------------
    // --- Names lookup functions --- 
    // ------------------------------
    public function lookupSimilarNames ( $name )
    {
        $q = "
        SELECT artist.artist_id as artist_id, artist_name, item_base_id, item_base_name, item_type 
        FROM item_base 
        INNER JOIN artist ON item_base.artist_id = artist.artist_id
        WHERE item_base_name LIKE ?
        ";
        
        return pdoQueryAssocRows( $this->m_dbPDO, $q, array("{$name}%") );
    }

    
    // --------------------------
    // --- Get data functions --- 
    // --------------------------
    /** Get item base data,. Info like name, genre, wiki info etc. */
    public function getBaseData ($item_base_id)
    {
        $q = "SELECT * FROM item_base WHERE item_base_id = $item_base_id";
        return pdoQueryAssocFirstRow($this->m_dbPDO, $q); 
    }
    
    /** Get item_base official name from ID. 
    \return Official AP name. */
    public function IDToName ($item_base_id)
    {
        $aData = $this->getBaseData ($item_base_id);
        return $aData['item_base_name'];
    }

       
    /** Get child song base_item IDs. The \a $item_base_id should be an album, otherwise an emty list is returned. */
    public function getChildSongIDs ($item_base_id)
    {
        $q = "SELECT item_base_id FROM item_base WHERE parent_item = ?";
        $a = pdoQueryAllRowsFirstElem($this->m_dbPDO, $q, array($item_base_id) ); 
        return $a;
    }

    
    // -----------------------------------------------
    // --- Get data functions needing an artist_id ---
    // -----------------------------------------------
    
    /** Get artist base item IDs. */
    public function getItemBaseIDs ($artist_id, $item_type)
    {
        if ( $item_type == 0 ) $q = "SELECT item_base_id FROM item_base WHERE artist_id = $artist_id";
        else                   $q = "SELECT item_base_id FROM item_base WHERE artist_id = $artist_id AND item_type = $item_type";
        return pdoQueryAllRowsFirstElem($this->m_dbPDO, $q); 
    }

    /** Get artist base album IDs. */
    public function getBaseAlbumIDs ($artist_id)
    {
        return $this->getItemBaseIDs($artist_id, 1 );
    }
    
    /** Get artist base song IDs. */
    public function getBaseSongIDs ($artist_id)
    {
        return $this->getItemBaseIDs($artist_id, 2 );
    }
    
    
    /**  */
    public function getItemsForArtist ( $artist_id, $item_type )
    {
        $q = "SELECT * FROM item_base WHERE artist_id = ?";
        $a = array($artist_id);
        if ( $item_type != 0 ) { 
            $q .= ' AND item_type = ?';
            $a[] = $item_type;
        }
        return pdoQueryAssocRows($this->m_dbPDO, $q, $a );
    }
    
    public function getItemNamesForArtist ( $artist_id, $item_type )
    {
        $q = "SELECT item_base_name FROM item_base WHERE artist_id = ?";
        $a = array($artist_id);
        if ( $item_type != 0 ) { 
            $q .= ' AND item_type = ?';
            $a[] = $item_type;
        }
        return pdoQueryAssocRows($this->m_dbPDO, $q, $a );
    }
    
	/*
		Get number of prices pr item_type for an item
	*/
	function getPriceCountPrMediaFormatForItem ($item_base_id) {
		$q = "SELECT COUNT(item_price.media_format_id) As media_format_count, media_format_name, media_format.media_format_id 
		FROM item_price INNER JOIN media_format ON media_format.media_format_id = item_price.media_format_id
		INNER JOIN item_base ON item_base.item_base_id = item_price.item_base_id 
		WHERE item_base.item_base_id = ? GROUP BY item_price.media_format_id;";
		$a = array($item_base_id);
        return pdoQueryAssocRows($this->m_dbPDO, $q, $a );
	}
	
    /** Get all data needed to display an item page */
    public function getPageData ($item_base_id)
    {
        // TODO: Implement this!
    }
    
    // --------------------------
    // --- Set data functions --- 
    // --------------------------
    /**  Set base data of item. Creates new item if name not found. */
    public function setBaseData ($aData)
    {

    }
    
         
    /**  Create new item. \item_type must be (1 = album, 2=song)
    \return ID of new item. */
//     public function createNew ($artist_id, $item_base_name, $item_type)
//     {
//         try {
//             $this->m_dbPDO->beginTransaction();
//             $stmt = $this->m_dbPDO->prepare("INSERT INTO item_base (artist_id, item_base_name, item_type) VALUES (?, ?, ?)" );
//             $stmt->execute( array($artist_id, $item_base_name, $item_type) );
//             $id = (int)$this->m_dbPDO->lastInsertId();  // IMPORTANT: Call BEFORE commit!
//             $this->m_dbPDO->commit();
//             return $id;
//         } 
//         catch(PDOException $e) {
//             $this->m_dbPDO->rollBack();
//             return 0;
//         }
//     }
    
    /**  Create new item. \item_type must be (1 = album, 2=song)
    \return ID of new item. */
    public function createNew ($artist_id, $item_base_name, $item_type)
    {
        $stmt = $this->m_dbPDO->prepare("INSERT INTO item_base (artist_id, item_base_name, item_type) VALUES (?, ?, ?)" );
        $stmt->execute( array($artist_id, $item_base_name, $item_type) );
        $id = (int)$this->m_dbPDO->lastInsertId();
        return $id;
    }
    
    /**  Create new item. \item_type must be (1 = album, 2=song)  with all fields
    \return ID of new item. */
    public function createNewFull ($artist_id, $item_base_name, $item_type, $item_genre_id, $item_year, $release_date, $parent_item, $item_time, $track_number, $item_master)
    {
        $item_base_soundex = calcSoundex($item_base_name);
        $stmt = $this->m_dbPDO->prepare("INSERT INTO item_base (artist_id, item_base_name, item_type, item_genre_id, item_year, release_date, parent_item, item_time, track_number, item_base_soundex, item_master) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)" );
        $stmt->execute( array($artist_id, $item_base_name, $item_type, $item_genre_id, $item_year, $release_date, $parent_item, $item_time, $track_number, $item_base_soundex, $item_master) );
        $id = (int)$this->m_dbPDO->lastInsertId();
        return $id;
    }
    
    /**  Update base data of existing item. */
    public function updateBaseData ($aData)
    {
        $result = 0;
        $item_base_id = $aData['item_base_id'];
        
        $aUpd = pdoGetUpdate ($aData, ItemDataMySql::$m_aTblFields );
        if ($aUpd[0] != "" ) {
            $q = 'UPDATE item_base SET ' . $aUpd[0] . ' WHERE item_base_id = ?';
            $aUpd[1][] = $item_base_id;
            $stmt = $this->m_dbPDO->prepare($q);
            $stmt->execute($aUpd[1]);
            $result += $stmt->rowCount();
        }
        
        // Note: This $aData['item_base_article'] != '' check is for efficiency reasons only!
        if ( $aData['item_base_article'] != '' ) {
            // Since we do not create item_base_text entries for every item_base,language combination and since 
            // we want updateBaseData to work transparently in this situation we add the following check 
            // and textCreateNewFull call if needed.
            $language_code = $aData['language_code'];
            if ( !$this->textExists ($item_base_id, $language_code) ) {
                $id = $this->textCreateNewFull ($item_base_id, $language_code, $aData['item_base_article'], $aData['item_base_text_reliability']);
                $result += 0 == $id ? 0 : 1;
            }
            else {
                $aUpd = pdoGetUpdate ($aData, ItemDataMySql::$m_aTextTblFields );
                if ($aUpd[0] != "" && $item_base_id != 0 && $language_code != '' ) {
                    $q = 'UPDATE item_base_text SET ' . $aUpd[0] . ' WHERE item_base_id = ? AND language_code = ?';
                    $aUpd[1][] = $item_base_id;
                    $aUpd[1][] = $language_code;
                    $stmt = $this->m_dbPDO->prepare($q);
                    $stmt->execute($aUpd[1]);
                    $result += $stmt->rowCount();
                }
            }
        }
        return $result;
    }
    

    /**  Update base data of existing item, but checking against the data already in DB and 
        only overwites non-empty values if new data has higher reliability (item_base_reliability). */
    public function updateBaseDataCheckOld ($aData)
    {
        $result = 0;
        $item_base_id = $aData['item_base_id'];

        //var_dump($aData);
        $aDataOld = $this->getBaseData($item_base_id);

        $aUpd = pdoGetUpdate ($aData, ItemDataMySql::$m_aTblFields, $aDataOld, 'item_base_reliability' );
		//var_dump($aDataOld);
        if ($aUpd[0] != "" ) {
            $q = 'UPDATE item_base SET ' . $aUpd[0] . ' WHERE item_base_id = ?';
            $aUpd[1][] = $item_base_id;
            $stmt = $this->m_dbPDO->prepare($q);
            $stmt->execute($aUpd[1]);
            $result += $stmt->rowCount();
        }
        
        // Note: This $aData['item_base_article'] != '' check is for efficiency reasons only!
        if ( $aData['item_base_article'] != '' ) {
            // Since we do not create item_base_text entries for every item_base,language combination and since 
            // we want updateBaseData to work transparently in this situation we add the following check 
            // and textCreateNewFull call if needed.
            $language_code = $aData['language_code'];
            if ( !$this->textExists ($item_base_id, $language_code) ) {
                $id = $this->textCreateNewFull ($item_base_id, $language_code, $aData['item_base_article'], $aData['item_base_text_reliability']);
                $result += 0 == $id ? 0 : 1;
            }
            else {
                $aUpd = pdoGetUpdate ($aData, ItemDataMySql::$m_aTextTblFields, $aDataOld, 'item_base_text_reliability' );
                if ($aUpd[0] != "" && $item_base_id != 0 && $language_code != '' ) {
                    $q = 'UPDATE item_base_text SET ' . $aUpd[0] . ' WHERE item_base_id = ? AND language_code = ?';
                    $aUpd[1][] = $item_base_id;
                    $aUpd[1][] = $language_code;
                    $stmt = $this->m_dbPDO->prepare($q);
                    $stmt->execute($aUpd[1]);
                    $result += $stmt->rowCount();
                }
            }
        }
        
        return $result;
    }

    // --------------------------------------
    // --- ItemBase text/article functions ----
    // --------------------------------------

     /**  Create new item_base_text. 
    \return ID of new item_base_text. */
    public function textCreateNew ($item_base_id, $language_code)
    {
        $stmt = $this->m_dbPDO->prepare("INSERT INTO item_base_text (item_base_id, language_code) VALUES (?,?)" );
        $stmt->execute( array($item_base_id, $language_code) );
        $item_base_text_id = (int)$this->m_dbPDO->lastInsertId();
        return $item_base_text_id;
    }
    
     /**  Create new item_base with all fields
    \return ID of new item_base. */
    public function textCreateNewFull ($item_base_id, $language_code, $item_base_article, $item_base_text_reliability)
    {
        $stmt = $this->m_dbPDO->prepare("INSERT INTO item_base_text (item_base_id, language_code, item_base_article, item_base_text_reliability) VALUES (?,?,?,?)" );
        $stmt->execute( array($item_base_id, $language_code, $item_base_article, $item_base_text_reliability) );
        $item_base_text_id = (int)$this->m_dbPDO->lastInsertId();
        return $item_base_text_id;
    }
    
     /**  Check if a given alias exists for item_base. 
    \return ID of item_base_text or zero if not found.. */
    public function textExists ($item_base_id, $language_code)
    {
        if ( $item_base_id == 0 || $language_code == '' ) return false;
        $q = "SELECT item_base_text_id FROM item_base_text WHERE item_base_id = ? AND language_code = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($item_base_id, $language_code) );
    }
    

     /**  Get item_base text/article. 
    \return ItemBase article text. */
    public function textDataGet ($item_base_id, $language_code)
    {
        if ( $item_base_id == 0 || $language_code == '' ) return '';
        $q = "SELECT * FROM item_base_text WHERE item_base_id = ? AND language_code = ?";
        return pdoQueryAssocFirstRow($this->m_dbPDO, $q, array($item_base_id, $language_code) );
    }

     /**  Get all item_base's texts for saving as new json format. 
    \note This is not a general useable function as such since we dont return all data, but only what we need
            for our new json/disk/redis DB format.. */
    public function textDataGetAllForJson ( $item_base_id )
    {
        if ( $item_base_id == 0 ) return array();
        $q = "SELECT language_code, created_date, item_base_article, item_base_text_reliability FROM item_base_text WHERE item_base_id = ?";
        return pdoQueryAssocRows($this->m_dbPDO, $q, array($item_base_id) );
    }
    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    static          $m_aTblFields = array( 'item_type', 'artist_id', 'item_base_name', 'record_label_id', 'item_genre_id', 'item_subgenre_ids', 'item_year', 'release_date', 'parent_item', 'item_time', 'track_number', 'child_items', 'item_master', 'item_base_reliability', 'item_base_soundex', 'image_width', 'image_height', 'image_processed', 'image_from_record_store_id' );
    static          $m_aTextTblFields = array( 'language_code', 'item_base_id', 'item_base_article', 'item_base_text_reliability' );
    
}


?>
