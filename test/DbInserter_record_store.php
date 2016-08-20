<?php

require_once ('RecordStoreDataMySql.php');
require_once ('CountryLookup.php');

class DbInserter_record_store 
{

    public  function    __construct(  $dbPDO = null )
    {
        global $g_MySqlPDO;
        $this->m_dbPDO = $dbPDO;
        if ( $dbPDO == null ) $this->m_dbPDO = $g_MySqlPDO; 
        
        $this->m_dbRecordStore      = new RecordStoreDataMySql  ( $this->m_dbPDO );
        $this->m_dbCountryLookup    = new CountryLookup         ( $this->m_dbPDO );
        
    }


    public function insertToDB ( $aData )
    {
        $record_store_name      = $aData['record_store_name'];
        $aData['country_id']    = $this->m_dbCountryLookup->lookupID($aData['country_name']);
        
        $record_store_id = $this->m_dbRecordStore->recordStoreNameToID( $record_store_name );

        if ( $record_store_id == 0 ) $record_store_id = $this->m_dbRecordStore->createNew ( $record_store_name );
        $aData['record_store_id']   = $record_store_id;
        $this->m_dbRecordStore->updateBaseData($aData);
        //var_dump( $aData );
    }

    // ----------------------------
    // --- PRIVATE: Member data --- 
    // ----------------------------
    private         $m_dbPDO            = null;
    private         $m_dbRecordStore;
    private         $m_dbCountryLookup;
}


?>







