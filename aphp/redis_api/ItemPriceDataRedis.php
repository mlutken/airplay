<?php
require_once ('redis_api/redis_utils.php');
require_once ('db_api/db_string_utils.php');


class ItemPriceDataRedis
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $redis = null )
    {
        global $g_redis;
        $this->m_r = $redis;
        if ( $redis == null ) $this->m_r = $g_redis;      
    }

    // ----------------------------------
    // --- Item ID lookup functions --- 
    // ----------------------------------
    /** Lookup item price IDs from its name.
    \return Array of all matching IDs.  */
    public function nameToIDs ($item_price_name)
    {
        $ln = toLookUpName($item_price_name); 
        return $this->m_r->lrange( $ln . ':item_price_ids', 0, -1 );
    }

 
 
 
    // --------------------------
    // --- Get data functions --- 
    // --------------------------
    /** Get item_price price data. Eg. XX ... etc. */
    public function getBaseData ($item_price_id)
    {
        return $this->m_r->hgetall( $item_price_id . ':d' );
    }

    /** Get a list of all item_price_id's that belongs to the given \a $item_base_id. */
    public function getItemPriceIDs ($item_base_id)
    {
        return $this->m_r->lrange( $item_base_id . ':all_prices', 0, -1 ); 
    }
    
    /** Get child song price_item IDs. The \a $item_price_id should be an album, otherwise an emty list is returned. 
    \sa Same as getChildIDs. */
    public function getChildSongIDs ($item_price_id)
    {
        return $this->getChildIDs($item_price_id);
    }
    
    /** Get child (song) price_item IDs. The \a $item_price_id should be an album, otherwise an emty list is returned. 
    \sa The getChildSongIDs is an alias (backwards compatible) with this. */
    public function getChildIDs ($item_price_id)
    {
        return $this->m_r->zrangebyscore( $item_price_id . ':child_ids', '-inf', '+inf' );
    }

    
    
    /** Get a item_price_id's that belongs to the given \a $artist_id, $item_price_name, $media_format_id, $record_store_id. */
    public function lookupItemPriceID ( $artist_id, $item_price_name, $media_format_id, $record_store_id, $item_used, $item_type )
    {
        $ln = toLookUpName($item_price_name); 
        return $this->m_r->get( "$ln:$artist_id:$media_format_id:$record_store_id:$item_used:$item_type:item_price_id" ); 
    }
    
    // --------------------------
    // --- Set data functions --- 
    // --------------------------
    /**  Set price data of item. Creates new item if name not found. */
    public function setBaseData ($aData)
    {

    }

 
    /**  Create new item. 
    \return ID of new item. */
    public function createNew (   $item_base_id, $item_price_name, $record_store_id, $media_format_id
                                , $media_type_id, $price_local, $currency_id, $buy_at_url
                                , $artist_id, $item_used, $item_type)
    {
        if ( skipDbWrite() ) return 0;
        $id = nextGlobalID($this->m_r );
        $this->m_r->lpush("item_price:all", $id);         // Push ID to list of all item_prices
        $this->m_r->set($id . ':type', 'item_price' );
        $ln = toLookUpName($item_price_name); 
        $this->m_r->lpush($ln . ':ids', $id  );              // Add name to global lookup. Since we could have song names etc. with same name as a item_price we add to a list here
        $this->m_r->lpush($ln . ':item_price_ids', $id  );   // Add name to item_price lookup. We use a list since we can have many item with same name 
        $this->m_r->set("$ln:$artist_id:$media_format_id:$record_store_id:$item_used:$item_type:item_price_id", $id  );    // Add name to exact item_price lookup
        $this->m_r->lpush( $item_base_id . ':all_prices', $id  );       // Add price to base_item's list of all it's prices 
        
        // --- BaseData hash (those from m_aDataTblFields) ---  
        $this->m_r->hset($id . ':d', 'item_price_id', $id );
        $this->m_r->hset($id . ':d', 'item_base_id', $item_base_id );
        $this->m_r->hset($id . ':d', 'item_price_name', $item_price_name );
        $this->m_r->hset($id . ':d', 'record_store_id', $record_store_id );
        $this->m_r->hset($id . ':d', 'media_format_id', $media_format_id );
        $this->m_r->hset($id . ':d', 'media_type_id', $media_type_id );
        $this->m_r->hset($id . ':d', 'price_local', $price_local );
        $this->m_r->hset($id . ':d', 'currency_id', $currency_id );
        $this->m_r->hset($id . ':d', 'buy_at_url', $buy_at_url );
        $this->m_r->hset($id . ':d', 'artist_id', $artist_id );
        $this->m_r->hset($id . ':d', 'item_used', $item_used );
        $this->m_r->hset($id . ':d', 'item_type', $item_type );
        return $id;
    }
    
    /**  Create new item  with all fields
    \return ID of new item. */
    public function createNewFull (   $item_base_id, $item_price_name, $record_store_id, $media_format_id
                                    , $media_type_id, $price_local, $currency_id, $buy_at_url
                                    , $artist_id , $item_used, $item_type, $cover_image_url
                                    , $release_date , $item_year, $item_time, $track_number
                                    , $parent_item, $item_genre_id )
    {
        if ( skipDbWrite() ) return 0;
        $id = $this->createNew (  $item_base_id, $item_price_name, $record_store_id, $media_format_id
                                , $media_type_id, $price_local, $currency_id, $buy_at_url
                                , $artist_id, $item_used, $item_type);

        $this->m_r->hset($id . ':d', 'cover_image_url', $cover_image_url );
        $this->m_r->hset($id . ':d', 'release_date',  $release_date);
        $this->m_r->hset($id . ':d', 'item_year', $item_year );
        $this->m_r->hset($id . ':d', 'item_time', $item_time );
        $this->m_r->hset($id . ':d', 'track_number', $track_number );
        $this->m_r->hset($id . ':d', 'parent_item', $parent_item );
        $this->m_r->hset($id . ':d', 'item_genre_id', $item_genre_id );
         
        $this->addItemToParent( $parent_item, $id, $track_number );
        return $id;
    }
 
    
    /**  Add item (song) to parent (album).
    \return void. */
    public function addItemToParent ( $parent_item_price_id, $child_item_price_id, $weight )
    {
        if ( $parent_item_price_id == 0 || $child_item_price_id == 0 || $weight == 0 ) return;
        $this->m_r->zadd( $parent_item_price_id . ':child_ids', $weight, $child_item_price_id );
    }
    
    /**  Update base data of existing item. */
    public function updateBaseData ($aData)
    {
        $item_price_id = $aData['item_price_id'];
        redisHashSetData ( $this->m_r, $item_price_id, $aData, ItemPriceDataRedis::$m_aDataTblFields );
        $this->addItemToParent( $aData['parent_item'], $item_price_id, $aData['track_number'] );
     }
    
    /**  Update price data of existing item, but checking against the data already in DB and 
        only overwites non-empty values if new data has higher reliability (item_price_reliability). */
    public function updateBaseDataCheckOld ($aData)
    {
        $item_price_id = $aData['item_price_id'];
        $aDataOld = $this->getBaseData($item_price_id);
        $bNewDataBetter = redisHashSetData ( $this->m_r, $item_price_id, $aData, ItemPriceDataRedis::$m_aDataTblFields,  $aDataOld, 'item_price_reliability' );
        
        // TODO: This check is too simple! We need to read the old sorted set and see if the key we are 
        //       adding perhaps does not exist. In this case we would want to add it regardless of 
        //       reliability
        if ( $bNewDataBetter ) {
            $this->addItemToParent( $aData['parent_item'], $item_price_id, $aData['track_number'] );
        }
     }
    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private         $m_r = null;
                                            
    public static   $m_aDataTblFields = array(    'item_base_id', 'item_price_name', 'record_store_id', 'media_format_id'
                                                , 'media_type_id', 'item_genre_id', 'price_local', 'currency_id'
                                                , 'buy_at_url', 'cover_image_url', 'release_date', 'track_number'
                                                , 'item_time', 'item_year', 'timestamp_updated', 'parent_item'
                                                , 'cover_image_url', 'item_used' 
                                             );
    
}

?>