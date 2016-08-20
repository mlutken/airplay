<?php

require_once ("db_helpers.php");


class RecordStoreDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        global $g_MySqlPDO;
        $this->m_dbPDO = $dbPDO;
        if ( $dbPDO == null ) $this->m_dbPDO = $g_MySqlPDO;      
    }

    // ----------------------------------
    // --- Item ID lookup functions --- 
    // ----------------------------------

    
    /** Lookup record store ID from its name.
    \return One record_store ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function recordStoreNameToID ($record_store_name)
    {
        $q = "SELECT record_store_id FROM record_store WHERE record_store_name = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($record_store_name) );
    }
    

    // --------------------------
    // --- Get data functions --- 
    // --------------------------
    /** Get record_store base data. Eg. XX ... etc. */
    public function getBaseData ($record_store_id)
    {
        $q = "SELECT * FROM record_store WHERE record_store_id = $record_store_id";
        return pdoQueryAssocFirstRow($this->m_dbPDO, $q); 
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
        $stmt = $this->m_dbPDO->prepare("
        INSERT INTO record_store (record_store_name) 
        VALUES (?)" );
        $stmt->execute( array($record_store_name) );
        return (int)$this->m_dbPDO->lastInsertId();
    }
    
    
    
    /**  Update base data of existing item. */
    public function updateBaseData ($aData)
    {
        static $aTblFields = array( 'record_store_name', 'record_store_url', 'country_id', 'use_affiliate', 'affiliate_link', 'affiliate_encode_times' );

        $result = 0;
        $record_store_id = $aData['record_store_id'];

        $aUpd = pdoGetUpdate ($aData, $aTblFields );
        if ($aUpd[0] != "" ) {
            $q = 'UPDATE record_store SET ' . $aUpd[0] . ' WHERE record_store_id = ?';
            $aUpd[1][] = $record_store_id;
            $stmt = $this->m_dbPDO->prepare($q);
            $stmt->execute($aUpd[1]);
            $result += $stmt->rowCount();
        }
        return $result;
     }
    
    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private         $m_dbPDO = null;
    
}


// ########################################
// ########################################
////        printf("query: %s\n", $q);

?>
