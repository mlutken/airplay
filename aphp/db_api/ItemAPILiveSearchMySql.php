<?php

require_once ("db_api/SimpleTableDataMySql.php");


class ItemAPILiveSearchMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'live_search_cache'
        , array(  'live_search_cache_id', 'artist_id', 'item_type', 'timestamp_updated'
                , 'cache_tries', 'record_store_id', 'json_response', 'json_response_collapsed' ) 
        , $dbPDO );
    }

    // -------------------------------------
    // --- Erase/delete/merge functions ----
    // -------------------------------------
    /** Completely erase an entry. */
    public function erase ($live_search_cache_id)
    {
        $aArgs = array($live_search_cache_id);
        $stmt = $this->m_dbPDO->prepare( 'DELETE FROM live_search_cache WHERE live_search_cache_id = ?' );
        $stmt->execute( $aArgs );
    }

    
    // --------------------------
    // --- Get data functions --- 
    // --------------------------
    /** Get item base data,. Info like name, genre, wiki info etc. */
    public function getBaseData ($live_search_cache_id)
    {
        $q = "SELECT * FROM live_search_cache WHERE live_search_cache_id = $live_search_cache_id";
        return pdoQueryAssocFirstRow($this->m_dbPDO, $q); 
    }
    

    
    // -----------------------------------------------
    // --- Get data functions needing an artist_id ---
    // -----------------------------------------------
    
    /** Get artist base item IDs. */
    public function getAPILiveSearchID ($artist_id, $record_store_id, $item_type)
    {
        $q = "SELECT live_search_cache_id FROM live_search_cache WHERE artist_id = $artist_id AND record_store_id = $record_store_id AND item_type = $item_type";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($artist_id, $record_store_id, $item_type)); 
    }

   
    
    /**  Create new item. \item_type must be (1 = album, 2=song)
    \return ID of new item. */
    public function createNew ($artist_id, $item_type, $record_store_id, $json_response, $json_response_collapsed)
    {
        $stmt = $this->m_dbPDO->prepare("INSERT INTO live_search_cache (artist_id, item_type, record_store_id, json_response, json_response_collapsed) VALUES (?, ?, ?, ?, ?)" );
        $stmt->execute( array($artist_id, $item_type, $record_store_id, $json_response, $json_response_collapsed) );
        $id = (int)$this->m_dbPDO->lastInsertId();
        return $id;
    }
    /**  Update base data of existing item. */
    public function updateBaseData ($aData)
    {
    var_dump($aData);
        $result = 0;
        $stmt = $this->m_dbPDO->prepare("UPDATE live_search_cache SET timestamp_updated = now(), cache_tries = cache_tries + 1, json_response = ?, json_response_collapsed = ? WHERE live_search_cache_id = ?");
        $stmt->execute( array($aData["json_response"], $aData["json_response_collapsed"], $aData["live_search_cache_id"]) );
        $result += $stmt->rowCount();
        return $result;
    }
 
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    static          $m_aTblFields = array( 'artist_id', 'item_type', 'timestamp_updated', 'cache_tries', 'record_store_id', 'json_response', 'json_response_collapsed' );
    
}


?>
