<?php

require_once ("db_api/SimpleTableDataMySql.php");


class ItemDynamicMusicMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'live_search_cache'
        , array(  'live_search_cache_id', 'site', 'artist_id', 'item_type', 'search_for_type_id', 'timestamp_updated'
                , 'cache_tries', 'record_store_id', 'json_response', 'json_response_collapsed' ) 
        , $dbPDO );
    }

    
    // --------------------------
    // --- Get data functions --- 
    // --------------------------
    /** Get item base data,. Info like name, genre, wiki info etc. */
    public function getBaseData ($live_search_cache_id)
    {
        $q = "SELECT * FROM live_search_cache WHERE live_search_cache_id = ?";
        return pdoQueryAssocFirstRow($this->m_dbPDO, $q, array($live_search_cache_id)); 
    }
    

    
    // -----------------------------------------------
    // --- Get data functions needing an artist_id ---
    // -----------------------------------------------
    
    /** Get live_search_cache_id ID. */
    public function getAPILiveSearchID ($artist_id, $item_base_id, $record_store_id, $search_for_type_id, $site)
    {
        $q = "SELECT live_search_cache_id FROM live_search_cache WHERE artist_id = ? AND item_base_id = ? AND record_store_id = ? AND search_for_type_id = ? AND site = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($artist_id, $item_base_id, $record_store_id, $search_for_type_id, strtoupper($site))); 
    }

    /** Get datetime for added to cache. - Return diff not and added in hours */
    public function getAPILiveSearchCachedAgo ($live_search_cache_id)
    {
        $q = "SELECT TIMESTAMPDIFF(MINUTE, timestamp_updated, now()) AS TimeSpan FROM live_search_cache WHERE live_search_cache_id = ?";
        return pdoQueryAssocFirstRow($this->m_dbPDO, $q, array($live_search_cache_id)); 
    }
    
    /**  Create new item. \search_for_type_id must be (1 = artist_album, 2=album_song, 3=album, 4=song)
    \return ID of new item. */
    public function createNew ($artist_id, $item_base_id, $search_for_type_id, $record_store_id, $json_response, $json_response_collapsed, $item_count, $site)
    {
        $stmt = $this->m_dbPDO->prepare("INSERT INTO live_search_cache (artist_id, item_base_id, search_for_type_id, record_store_id, json_response, json_response_collapsed, item_count, site) VALUES (?, ?, ?, ?, ?, ?, ?, ?)" );
        $stmt->execute( array($artist_id, $item_base_id, $search_for_type_id, $record_store_id, $json_response, $json_response_collapsed, $item_count, strtoupper($site) ));
        $id = (int)$this->m_dbPDO->lastInsertId();
        return $id;
    }
    
    /**  Update base data of existing item. */
    public function updateBaseData ( $aData )
    {
        $result = 0;
        $stmt = $this->m_dbPDO->prepare("UPDATE live_search_cache SET timestamp_updated = now(), json_response = :json_response, json_response_collapsed = :json_response_collapsed WHERE live_search_cache_id = :live_search_cache_id");
        $stmt->execute( array( ":json_response" => $aData["json_response"], ":json_response_collapsed" => $aData["json_response_collapsed"], ":live_search_cache_id" => $aData["live_search_cache_id"]) );
        $result += $stmt->rowCount();
        return $result;
    }
 
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    static          $m_aTblFields = array( 'site', 'artist_id', 'item_type', 'search_for_type_id', 'timestamp_updated', 'cache_tries', 'record_store_id', 'json_response', 'json_response_collapsed' );
    
}


?>
