<?php
require_once ("redis_api/redis_utils.php");


class RecordStoreDataRedis
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
    /** Lookup record store ID from its name.
    \return One record_store ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function nameToID ($record_store_name)
    {
        $ln = toLookUpName($record_store_name); 
        return $this->m_r->get( $ln . ':record_store_id' );
    }
    
    // --------------------------
    // --- Get data functions --- 
    // --------------------------
    /** Get record_store base data. Eg. XX ... etc. */
    public function getBaseData ($record_store_id)
    {
        return $this->m_r->hgetall( $record_store_id . ':d' );
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
    public function createNew ( $record_store_name )
    {
        if ( skipDbWrite() ) return 0;
        $id = nextGlobalID($this->m_r );
        $this->m_r->lpush("record_store:all", $id);         // Push ID to list of all record_stores
        $this->m_r->set($id . ':type', 'record_store' );
        $ln = toLookUpName($record_store_name); 
        $this->m_r->lpush($ln . ':ids', $id );               // Add name to global lookup. Since we could have song names etc. with same name as a record_store we add to a list here
        $this->m_r->set( $ln . ':record_store_id', $id  );  // Add name to exact record_store lookup

        // --- BaseData hash (those from m_aDataTblFields) ---  
        $this->m_r->hset($id . ':d', 'record_store_id', $id );
        $this->m_r->hset($id . ':d', 'record_store_name', $record_store_name );
        return $id;
    }
    
    /**  Create new item with all fields
    \return ID of new item. */
    public function createNewFull ( $record_store_name, $record_store_url, $country_id )
    {
        if ( skipDbWrite() ) return 0;
        $id = $this->createNew ( $record_store_name );
        $this->m_r->hset($id . ':d', 'record_store_url', $record_store_url );
        $this->m_r->hset($id . ':d', 'country_id', $country_id );
        return $id;
    }
    
    
    /**  Update base data of existing item. */
    public function updateBaseData ($aData)
    {
        $record_store_id = $aData['record_store_id'];
        redisHashSetData ( $this->m_r, $record_store_id, $aData, RecordStoreDataRedis::$m_aDataTblFields );
     }
    
    /**  Update base data of existing item, but checking against the data already in DB and 
        only overwites non-empty values if new data has higher reliability (record_store_reliability). */
    public function updateBaseDataCheckOld ($aData)
    {
        $record_store_id = $aData['record_store_id'];
        $aDataOld = $this->getBaseData($record_store_id);
        redisHashSetData ( $this->m_r, $record_store_id, $aData, RecordStoreDataRedis::$m_aDataTblFields,  $aDataOld, 'record_store_reliability' );
     }
    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private         $m_r = null;
    public static   $m_aDataTblFields = array(    'record_store_name', 'record_store_url', 'country_id', 'use_affiliate'
                                                , 'affiliate_link', 'affiliate_encode_times', 'record_store_reliability' 
                                            );
    
}

?>