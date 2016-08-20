<?php

require_once ("db_api/db_helpers.php");


class SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $baseTableName, $aBaseDataFields, $dbPDO = null  )
    {
        global $g_MySqlPDO;
        $this->m_baseTableName = $baseTableName;
        $this->m_aBaseDataFields = $aBaseDataFields;
        $this->m_aAllDataFields = array("{$this->m_baseTableName}_id" );
        foreach( $this->m_aBaseDataFields as $f ) $this->m_aAllDataFields[] = $f;
         
        $this->m_dbPDO = $dbPDO;
        if ( $dbPDO == null ) $this->m_dbPDO = $g_MySqlPDO;      
    }

    // -------------------------------
    // --- Erase/delete functions ----
    // -------------------------------
    /** Completely erase an entry. */
    public function erase ($id)
    {
        $aArgs = array($id);
        $stmt = $this->m_dbPDO->prepare( "DELETE FROM {$this->m_baseTableName} WHERE {$this->m_baseTableName}_id = ?" );
        $stmt->execute( $aArgs );
    }
    
    // ----------------------------------
    // --- Item ID lookup functions --- 
    // ----------------------------------
    /** Lookup ID from its name.
    \return One ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function nameToIDSimple ($name)
    {
        $q = "SELECT {$this->m_baseTableName}_id FROM {$this->m_baseTableName} WHERE {$this->m_baseTableName}_name = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($name) );
    }

    /** Check if a given name exists.
    \return true if found.  */
    public function nameSimpleExists ($name)
    {
        $id = $this->nameToIDSimple($name);
        return 0 != $id;
    }
    
    /** Lookup ID from $aData.
    \return One ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function toID ($aData)
    {
        $name = $aData["{$this->m_baseTableName}_name"];
        $q = "SELECT {$this->m_baseTableName}_id FROM {$this->m_baseTableName} WHERE {$this->m_baseTableName}_name = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($name) );
    }

    // ------------------------------
    // --- Names lookup functions --- 
    // ------------------------------
    public function lookupSimilarBaseData ( $name )
    {
        $q = "SELECT * FROM {$this->m_baseTableName} WHERE {$this->m_baseTableName}_name LIKE ?";
        return pdoQueryAssocRows( $this->m_dbPDO, $q, array("{$name}%") );
    }
   
    // --------------------------
    // --- Get data functions --- 
    // --------------------------
    /** Get base data for one item. */
    public function getBaseData ($id)
    {
        $q = "SELECT * FROM {$this->m_baseTableName} WHERE {$this->m_baseTableName}_id = ?";
        return pdoQueryAssocFirstRow($this->m_dbPDO, $q, array($id) ); 
    }
    
    /** Get all base data rows from table, obeying the limits given. */
    public function getBaseDataRows ( $start, $count )
    {
        $limit = "LIMIT $start, $count";
        if ( $count == 0 ) $limit = '';
        $q = "SELECT * FROM {$this->m_baseTableName} ${limit}";
        return pdoQueryAssocRows($this->m_dbPDO, $q); 
    }
    
    public function getSize() 
    {
        $q = "SELECT COUNT(*) FROM {$this->m_baseTableName}";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array() );
    }

    public function getFirstID() 
    {
        $q = "SELECT MIN({$this->m_baseTableName}_id) FROM {$this->m_baseTableName}";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array() );
    }
    
    public function getLastID() 
    {
        $q = "SELECT MAX({$this->m_baseTableName}_id) FROM {$this->m_baseTableName}";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array() );
    }
    
    /** Get official name from ID. 
    \return Official AP name. */
    public function IDToName ($id)
    {
        $q = "SELECT {$this->m_baseTableName}_name FROM {$this->m_baseTableName} WHERE {$this->m_baseTableName}_id = ?";
        $s = pdoLookupSingleStringQuery($this->m_dbPDO, $q, array($id) );
        return $s;
    }


    /** Get aray of auto completed artists names given the search string */
    function autoCompleteNames($sSearchFor)
    {
        $q =
<<<TEXT
SELECT {$this->m_baseTableName}_name
FROM {$this->m_baseTableName}
WHERE {$this->m_baseTableName}_name LIKE ?
ORDER BY {$this->m_baseTableName}_name ASC
LIMIT 0 , 10
TEXT;
        return pdoQueryAllRowsFirstElem( $this->m_dbPDO, $q, array("{$sSearchFor}%") );
    }

    /** Get aray of auto completed artists names and IDs given the search string */
    function autoCompleteNamesAndIDs($sSearchFor)
    {
        $q =
<<<TEXT
SELECT {$this->m_baseTableName}_id, {$this->m_baseTableName}_name
FROM {$this->m_baseTableName}
WHERE {$this->m_baseTableName}_name LIKE ?
ORDER BY {$this->m_baseTableName}_name ASC
LIMIT 0 , 10
TEXT;
        return pdoQueryAssocRows( $this->m_dbPDO, $q, array("{$sSearchFor}%") );
    }
    
    
    
    
    // --------------------------
    // --- Set data functions --- 
    // --------------------------
    /**  Set base data of item. Creates new item if name not found. */
    public function setBaseData ($aData)
    {

    }
    
    
    /**  Create new item from name. 
    \return ID of new item. */
    public function newItemFromName ( $name )
    {
        $stmt = $this->m_dbPDO->prepare("INSERT INTO {$this->m_baseTableName} ({$this->m_baseTableName}_name) VALUES (?)" );
        $stmt->execute( array($name) );
        $id = (int)$this->m_dbPDO->lastInsertId();
        return $id;
    }
    
    /**  Create new item. 
    \return ID of new item. */
    public function newItem ( $aData )
    {
        $name = $aData["{$this->m_baseTableName}_name"];
        $stmt = $this->m_dbPDO->prepare("INSERT INTO {$this->m_baseTableName} ({$this->m_baseTableName}_name) VALUES (?)" );
        $stmt->execute( array($name) );
        $id = (int)$this->m_dbPDO->lastInsertId();
        return $id;
    }
    
    
    /**  Update base data of existing item. 
    \return Number of row affected if succesfull or zero if nothing was updated. */
    public function updateBaseData ($aData)
    {
        $result = 0;
        $id = $aData["{$this->m_baseTableName}_id"];
        
        $aUpd = pdoGetUpdate ($aData, $this->getBaseDataFields() );
        if ($aUpd[0] != "" ) {
            $q = "UPDATE {$this->m_baseTableName} SET " . $aUpd[0] . " WHERE {$this->m_baseTableName}_id = ?";
            $aUpd[1][] = $id;
            $stmt = $this->m_dbPDO->prepare($q);
            $stmt->execute($aUpd[1]);
            $result += $stmt->rowCount();
        }
        return $result;
     }
    
    /**  Update base data of existing item, but checking against the data already in DB and 
        only overwites non-empty values if new data has higher reliability (record_store_reliability). */
    public function updateBaseDataCheckOld ($aData)
    {
        $result = 0;
        $id = $aData["{$this->m_baseTableName}_id"];
        
        $aDataOld = $this->getBaseData($id);

        $aUpd = pdoGetUpdate ($aData, $this->getBaseDataFields(), $aDataOld, "{$this->m_baseTableName}_reliability" );
        if ($aUpd[0] != "" ) {
            $q = "UPDATE {$this->m_baseTableName} SET " . $aUpd[0] . " WHERE {$this->m_baseTableName}_id = ?";
            $aUpd[1][] = $id;
            $stmt = $this->m_dbPDO->prepare($q);
            $stmt->execute($aUpd[1]);
            $result += $stmt->rowCount();
        }
        return $result;
     }
    // ------------------------------
    // --- PUBLIC: Info Functions --- 
    // ------------------------------
    /** Get array with all table fields (including the primarry xx_id name ) */
    public function getBaseDataFields() 
    {
        return $this->m_aBaseDataFields;
    }

    
    /** Get array with all table fields (including the primarry xx_id name ) */
    public function getAllDataFields() 
    {
        return $this->m_aAllDataFields;
    }

    public function hasInCache ($k)
    {
        return array_key_exists( $k, $this->m_aCache );
    }
    
    // ----------------------------
    // --- PROTECTED: Functions --- 
    // ----------------------------
    protected function cachePut ($k,$v)
    {
        if ( count($this->m_aCache) > $this->m_iCacheMaxSize ) $this->m_aCache = array();
        $this->m_aCache[$k] = $v;
    }

    protected function cacheGet ($k)
    {
        return $this->m_aCache[$k];
    }
    
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    protected       $m_dbPDO = null;
    protected       $m_baseTableName;
    protected       $m_aBaseDataFields  = array();
    protected       $m_aAllDataFields   = array();
    protected       $m_iCacheMaxSize    = 100;
    protected       $m_aCache           = array();
    
}


?>