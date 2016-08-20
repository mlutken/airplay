<?php
require_once ('redis_api/redis_utils.php');
require_once ('db_api/db_string_utils.php');


class ItemBaseDataRedis
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
    /** Lookup item base IDs from its name.
    \return Array of all matching IDs.  */
    public function nameToIDs ($item_base_name)
    {
        $ln = toLookUpName($item_base_name); 
        return $this->m_r->lrange( $ln . ':item_base_ids', 0, -1 );
    }

    /** Lookup item base ID from its name.
    \return One item_base ID if found one and only one matching. Zero if not found any and -N if N matching found.  */
    public function nameToID ($artist_id, $item_base_name, $item_type)
    {
        $ln = toLookUpName($item_base_name); 
        return $this->m_r->get($ln . ':' . $artist_id . ':' . $item_type . ':item_base_id' ); 
    }
 
 
    /** TODO: Doc finish
    Exact match using soundex strings . */
    public function findID ( $artist_id, $item_name_raw, $item_type, $dbItemBaseCorrection )
    {
        if ( $item_name_raw == '' ) return 0;
        $cleanedItemName = cleanItemNameFull($item_name_raw, $dbItemBaseCorrection );
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
        $cleanedItemName = cleanItemNameFull($item_name_raw, $dbItemBaseCorrection );
        $item_base_id = $this->nameToID( $artist_id, $cleanedItemName, $item_type );
        if ( $item_base_id > 0 ) return $item_base_id;
        $aCandidates = $this->getItemsForArtist ( $artist_id, $item_type );
        $item_base_id = fuzzyFindIdFromSoundex($aCandidates, 'item_base', $cleanedItemName, 0.90 );
        return $item_base_id;
    }

    
    // --------------------------
    // --- Get data functions --- 
    // --------------------------
    /** Get item_base base data. Eg. XX ... etc. */
    public function getBaseData ($item_base_id)
    {
        return $this->m_r->hgetall( $item_base_id . ':d' );
    }

    /** Get item_base official name from ID. 
    \return Official AP name. */
    public function IDToName ($item_base_id)
    {
        return $this->m_r->hget( $item_base_id . ':d', 'item_base_name' );
    }
    
    /** Get child song base_item IDs. The \a $item_base_id should be an album, otherwise an emty list is returned. 
    \deprecated Use getChildIDs instead */
    public function getChildSongIDs ($item_base_id)
    {
        return $this->getChildIDs($item_base_id);
    }
    
    /** Get child (song) base_item IDs. The \a $item_base_id should be an album, otherwise an emty list is returned. 
    \sa The getChildSongIDs is an alias (backwards compatible) with this. */
    public function getChildIDs ($item_base_id)
    {
        return $this->m_r->zrangebyscore( $item_base_id . ':child_ids', '-inf', '+inf' );
    }

    
    // -----------------------------------------------
    // --- Get data functions needing an artist_id ---
    // -----------------------------------------------

    /** Get artist base item IDs. */
    public function getItemBaseIDs ($artist_id, $item_type)
    {
        $a = array();
        if ( $item_type == 0 ) {
            $a = $this->m_r->lrange( $artist_id . ':all_items', 0, -1 );
        }
        else {
            $a = $this->m_r->lrange( $artist_id . ':' . $item_type . ':all_items', 0, -1 );
        }
        return $a; 
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
    
    /** Get all base_items of a given artist. */
    public function getItemsForArtist ( $artist_id, $item_type )
    {
        $aAllBaseItemIDs = array();
        if ( $item_type == 0 )  $aAllBaseItemIDs = $this->m_r->lrange( $artist_id . ':all_items', 0, -1 );
        else                    $aAllBaseItemIDs = $this->m_r->lrange( $artist_id . ':' . $item_type . ':all_items', 0, -1 );
        
        $aAllBaseItems = array();
        foreach ( $aAllBaseItemIDs as $item_base_id ) {
            $aAllBaseItems[] = $this->getBaseData($item_base_id);
        }
        return $aAllBaseItems;
    }
    
    // --------------------------
    // --- Set data functions --- 
    // --------------------------
    /**  Set base data of item. Creates new item if name not found. */
    public function setBaseData ($aData)
    {

    }

    
    
    /**  Create new item. 
    \return ID of new item. */
    public function createNew ( $artist_id, $item_base_name, $item_type )
    {
        if ( skipDbWrite() ) return 0;
        $id = nextGlobalID($this->m_r );
        $this->m_r->lpush("item_base:all", $id);         // Push ID to list of all item_bases
        $this->m_r->set($id . ':type', 'item_base' );
        $ln = toLookUpName($item_base_name); 
        $this->m_r->lpush($ln . ':ids', $id  );           // Add name to global lookup. Since we could have song names etc. with same name as a item_base we add to a list here
        $this->m_r->lpush($ln . ':item_base_ids', $id  ); // Add name to item_base lookup. We use a list since we can have many item with same name 
        $this->m_r->set($ln . ':' . $artist_id . ':' . $item_type . ':item_base_id', $id  );    // Add name to exact item_base lookup
        $this->m_r->lpush( $artist_id . ':all_items', $id );                    // Add item to artist list of all albums and songs
        $this->m_r->lpush( $artist_id . ':' . $item_type . ':all_items', $id ); // Add item to artist list of all albums or all songs
        
        // --- BaseData hash (those from m_aDataTblFields) ---  
        $this->m_r->hset($id . ':d', 'item_base_id', $id );
        $this->m_r->hset($id . ':d', 'item_base_name', $item_base_name );
        $this->m_r->hset($id . ':d', 'artist_id', $artist_id );
        $this->m_r->hset($id . ':d', 'item_type', $item_type );
        return $id;
    }
    
    /**  Create new item with all fields
    \return ID of new item. */
    public function createNewFull (   $artist_id, $item_base_name, $item_type, $item_genre_id, $item_year
                                    , $release_date, $parent_item, $item_time, $track_number)
    {
        if ( skipDbWrite() ) return 0;
        $id = $this->createNew ( $artist_id, $item_base_name, $item_type );
        $this->m_r->hset($id . ':d', 'item_genre_id', $item_genre_id );
        $this->m_r->hset($id . ':d', 'item_year', $item_year );
        $this->m_r->hset($id . ':d', 'release_date', $release_date );
        $this->m_r->hset($id . ':d', 'parent_item', $parent_item );
        $this->m_r->hset($id . ':d', 'item_time', $item_time );
        $this->m_r->hset($id . ':d', 'track_number', $track_number );
        
        $this->addItemToParent( $parent_item, $id, $track_number );
        return $id;
    }
    
    /**  Add item (song) to parent (album).
    \return void. */
    public function addItemToParent ( $parent_item_base_id, $child_item_base_id, $weight )
    {
        if ( $parent_item_base_id == 0 || $child_item_base_id == 0 || $weight == 0 ) return;
        $this->m_r->zadd( $parent_item_base_id . ':child_ids', $weight, $child_item_base_id );
    }
    
    /**  Update base data of existing item. */
    public function updateBaseData ($aData)
    {
        $item_base_id = $aData['item_base_id'];
        redisHashSetData ( $this->m_r, $item_base_id, $aData, ItemBaseDataRedis::$m_aDataTblFields );
        $this->addItemToParent( $aData['parent_item'], $item_base_id, $aData['track_number'] );
     }
    
    /**  Update base data of existing item, but checking against the data already in DB and 
        only overwites non-empty values if new data has higher reliability (item_base_reliability). */
    public function updateBaseDataCheckOld ($aData)
    {
        $item_base_id = $aData['item_base_id'];
        $aDataOld = $this->getBaseData($item_base_id);
        $bNewDataBetter = redisHashSetData ( $this->m_r, $item_base_id, $aData, ItemBaseDataRedis::$m_aDataTblFields,  $aDataOld, 'item_base_reliability' );
        
        // TODO: This check is too simple! We need to read the old sorted set and see if the key we are 
        //       adding perhaps does not exist. In this case we would want to add it regardless of 
        //       reliability
        if ( $bNewDataBetter ) {
            $this->addItemToParent( $aData['parent_item'], $item_base_id, $aData['track_number'] );
        }
     }
    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private         $m_r = null;
    public static   $m_aDataTblFields = array(    'item_type', 'artist_id', 'item_base_name', 'record_label_id'
                                                , 'item_genre_id', 'item_subgenre_ids', 'item_year', 'release_date'
                                                , 'parent_item', 'item_time', 'track_number', 'child_items'
                                                , 'item_base_reliability' 
                                             );
    
}

?>