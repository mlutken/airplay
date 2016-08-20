<?php

require_once ("db_api/SimpleTableDataMySql.php");


class PriceDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'item_price'
        , array(  'item_base_id', 'item_type', 'item_price_name', 'record_store_id'
                , 'artist_id', 'media_format_id', 'media_type_id', 'item_genre_id'
                , 'price_local', 'currency_id', 'buy_at_url', 'cover_image_url'
                , 'release_date', 'item_event_date', 'item_event_time', 'item_year', 'item_time', 'track_number'
                , 'item_used', 'timestamp_updated', 'timestamp_added', 'parent_item', 'child_items'
                , 'item_grading_cover', 'item_grading', 'item_price_delivery_status_id' ) 
        , $dbPDO );
    }

    // -------------------------------------
    // --- Erase/delete/merge functions ----
    // -------------------------------------
 
    public function mergeArtist ($into_artist_id, $from_artist_id)
    {
        $stmt = $this->m_dbPDO->prepare( 'UPDATE item_price SET artist_id=? WHERE artist_id = ?' );
        $stmt->execute( array($into_artist_id, $from_artist_id ) );
    }
// // 
// //     public function mergeItemBase ($into_item_base_id, $from_item_base_id)
// //     {
// //         $stmt = $this->m_dbPDO->prepare( 'UPDATE item_price SET item_base_id=? WHERE item_base_id = ?' );
// //         $stmt->execute( array($into_item_base_id, $from_item_base_id ) );
// //     }

    /** Move item_price to a artist "owner". I.e. change the artist to which the \a $from_item_price_id belongs */
    public function moveToArtist( $into_artist_id, $from_item_price_id )
    {
        if ( (int)$into_artist_id !=0 && (int)$from_item_price_id != 0 ) {
            $stmt = $this->m_dbPDO->prepare( 'UPDATE item_price SET artist_id=? WHERE item_price_id = ?' );
            $stmt->execute( array($into_artist_id, $from_item_price_id) );
        }
    }

    /** Move item_price to a new item_base "owner". I.e. change the item_base to which the \a $from_item_price_id belongs */
    public function moveToItemBase( $into_artist_id, $into_item_base_id, $from_item_price_id )
    {
        if ( (int)$into_artist_id !=0 && (int)$into_item_base_id !=  0 && (int)$from_item_price_id != 0 ) {
            $stmt = $this->m_dbPDO->prepare( 'UPDATE item_price SET artist_id=?, item_base_id=? WHERE item_price_id = ?' );
            $stmt->execute( array($into_artist_id, $into_item_base_id, $from_item_price_id) );
        }
    }
    
    // --------------------------------
    // --- Item ID lookup functions --- 
    // --------------------------------

    
    /** Lookup item_price ID from item_base_id and "full" item_price_name.
    \return one item ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function priceNameToID ($item_base_id, $item_price_name)
    {
        $q = 'SELECT item_price_id FROM item_price WHERE item_base_id = ? AND item_price_name = ?';
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($item_base_id, $item_price_name) );
    }
    

    // --------------------------
    // --- Get data functions --- 
    // --------------------------

    /**  */
    public function getItemsForArtist ( $artist_id )
    {
        $q = "SELECT * FROM item_price WHERE artist_id = ?";
        $a = array($artist_id);
        return pdoQueryAssocRows($this->m_dbPDO, $q, $a );
    }
    
    
    /** Get a list of all item_price_id's that belongs to the given \a $item_base_id. */
    public function getItemPriceIDs ($item_base_id)
    {
        $q = 'SELECT item_price_id FROM item_price WHERE item_base_id = ?';
        return pdoQueryAllRowsFirstElem($this->m_dbPDO, $q, array($item_base_id) );
    }
    
    /** Get a list of all item_price data that belongs to the given \a $item_base_id. */
    public function getItemPrices ($item_base_id)
    {
        $q = 'SELECT * FROM item_price WHERE item_base_id = ?';
        return pdoQueryAssocRows($this->m_dbPDO, $q, array($item_base_id) );
    }

    /** Get a item_price_id's that belongs to the given \a $artist_id, $item_price_name, $media_format_id, $record_store_id. */
    public function lookupItemPriceID ($artist_id, $item_price_name, $media_format_id, $record_store_id, $item_used, $item_type, $item_grading, $item_grading_cover, $item_event_date )
    {
        $q = 'SELECT item_price_id FROM item_price WHERE artist_id = ? AND item_price_name = ? AND media_format_id = ? AND record_store_id = ? AND item_used = ? AND item_type = ? AND item_grading = ? AND item_grading_cover = ? AND item_event_date = ?';
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($artist_id, $item_price_name, $media_format_id, $record_store_id, $item_used, $item_type, $item_grading, $item_grading_cover, $item_event_date));
    }
    
	/** Get all item_price_id's that belongs to the given \a $artist_id, $item_price_name, $media_format_id, $record_store_id. */
    public function lookupItemPriceIDWithOutEventDate ($artist_id, $item_price_name, $media_format_id, $record_store_id, $item_used, $item_type, $item_grading, $item_grading_cover )
    {
        $q = 'SELECT item_price_id, item_event_date FROM item_price WHERE artist_id = ? AND item_price_name = ? AND media_format_id = ? AND record_store_id = ? AND item_used = ? AND item_type = ? AND item_grading = ? AND item_grading_cover = ?';
        return pdoQueryAssocRows($this->m_dbPDO, $q, array($artist_id, $item_price_name, $media_format_id, $record_store_id, $item_used, $item_type, $item_grading, $item_grading_cover));
    }
	
    /** Lookup ID from $aData.
    \return One ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function toID ($aData)
    {
        return $this->lookupItemPriceID ($aData['artist_id'], $aData['item_price_name'], $aData['media_format_id'], 
                                        $aData['record_store_id'], $aData['item_used'], $aData['item_type'], $aData['item_grading'], $aData['item_grading_cover'], $aData['item_event_date'] );
    }

 	/** Get all item_price_id's that belongs to the given \a $artist_id, $item_price_name, $media_format_id, $record_store_id. */
    public function toIDWithoutEventDate ($aData)
    {
        return $this->lookupItemPriceIDWithOutEventDate ($aData['artist_id'], $aData['item_price_name'], $aData['media_format_id'], 
                                        $aData['record_store_id'], $aData['item_used'], $aData['item_type'], $aData['item_grading'], $aData['item_grading_cover'] );
    }
    
    /**  Create new item. 
    \return ID of new item. */
    public function createNew ($item_base_id, $item_price_name, $record_store_id, $media_format_id, $media_type_id, $price_local, $currency_id, $buy_at_url, $artist_id, $item_used, $item_type, $item_grading, $item_grading_cover)
    {
        $stmt = $this->m_dbPDO->prepare("
        INSERT INTO item_price (item_base_id, item_price_name, record_store_id, media_format_id, media_type_id, price_local, currency_id, buy_at_url, artist_id, item_used, item_type, item_grading, item_grading_cover) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)" );
        $stmt->execute( array($item_base_id, $item_price_name, $record_store_id, $media_format_id, $media_type_id, $price_local, $currency_id, $buy_at_url, $artist_id, $item_used, $item_type, $item_grading, $item_grading_cover) );
        $id = (int)$this->m_dbPDO->lastInsertId();
        return $id;
    }
    
    /**  Create new item  with all fields
    \return ID of new item. */
    public function createNewFull ($item_base_id, $item_price_name, $record_store_id, $media_format_id, $media_type_id, $price_local, $currency_id, $buy_at_url, $artist_id, $item_used, $item_type, $cover_image_url, $release_date, $item_event_date, $item_event_time, $item_year, $item_time, $track_number, $parent_item, $item_genre_id, $item_grading, $item_grading_cover, $item_price_delivery_status_id)
    {
        //printf("DBG: createNewFull ($item_base_id, $item_price_name, $record_store_id, $media_format_id, $media_type_id, $price_local, $currency_id, $buy_at_url, $artist_id, $item_used, $item_type, $cover_image_url, $release_date, $item_event_date, $item_event_time, $item_year, $item_time, $track_number, $parent_item, $item_genre_id, $item_grading, $item_grading_cover, $item_price_delivery_status_id)\n");
        $stmt = $this->m_dbPDO->prepare("
        INSERT INTO item_price (item_base_id, item_price_name, record_store_id, media_format_id, media_type_id, price_local, currency_id, buy_at_url, artist_id, item_used, item_type, cover_image_url, release_date, item_event_date, item_event_time, item_year, item_time, track_number, timestamp_updated, parent_item, item_genre_id, item_grading, item_grading_cover, item_price_delivery_status_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now(), ?, ?, ?, ?, ?)" );
        $stmt->execute( array($item_base_id, $item_price_name, $record_store_id, $media_format_id, $media_type_id, $price_local, $currency_id, $buy_at_url, $artist_id, $item_used, $item_type, $cover_image_url, $release_date, $item_event_date, $item_event_time, $item_year, $item_time, $track_number, $parent_item, $item_genre_id, $item_grading, $item_grading_cover, $item_price_delivery_status_id) );
        $id = (int)$this->m_dbPDO->lastInsertId();
        return $id;
    }
    
    
}

?>