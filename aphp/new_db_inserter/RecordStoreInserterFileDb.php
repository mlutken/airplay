<?php

require_once ("new_db_inserter/BaseInserterFileDb.php");



class RecordStoreInserterFileDb extends BaseInserterFileDb
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $fileDbBaseDir, $dbAll, $openParents )
    {
        parent::__construct( $fileDbBaseDir, $dbAll, $openParents );
    }

    
    public function insertToDB ( $aData )
    {
		printf("RecordStoreInserterFileDb::insertToDB() TODO: Implement me!\n");
 		return;
		
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
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    
}


?>