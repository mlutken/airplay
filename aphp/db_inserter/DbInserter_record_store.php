<?php

require_once ('db_api/RecordStoreDataMySql.php');
require_once ('redis_api/RecordStoreDataRedis.php');
require_once ('db_api/CountryLookup.php');

require_once ('db_manip/MusicDatabaseFactory.php');

class DbInserter_record_store 
{

    public  function    __construct( $dbAll )
    {
        $this->m_dbAll = $dbAll;
    }


    public function insertToDB ( $aData )
    {
        $record_store_name      = $aData['record_store_name'];
        $aData['country_id']    = 0;
        $aData['country_id'] = $this->m_dbAll->m_dbCountryLookup->lookupID($aData['country_name']);
        
        $record_store_id = $this->m_dbAll->m_dbRecordStore->nameToIDSimple( $record_store_name );
        
        $aData['record_store_id']   = $record_store_id;
        
        if ( $record_store_id == 0 ) {
            $record_store_id = $this->m_dbAll->m_dbRecordStore->createNewFull ( $record_store_name, $aData['record_store_url'], $aData['country_id'] );
        } else {
            $aData['record_store_id']   = $record_store_id;
            $this->m_dbAll->m_dbRecordStore->updateBaseDataCheckOld($aData);
        }
    }

    // ----------------------------
    // --- PRIVATE: Member data --- 
    // ----------------------------
    private         $m_dbAll = null;
}


?>