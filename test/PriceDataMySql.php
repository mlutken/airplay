<?php

require_once ("db_helpers.php");


class PriceDataMySql
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

    
    /** Lookup item_price ID from item_base_id and "full" item_price_name.
    \return one item ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function priceNameToID ($item_base_id, $item_price_name)
    {
        $q = "SELECT item_price_id FROM item_price WHERE item_base_id = ? AND item_price_name = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($item_base_id, $item_price_name) );
    }
    

    // --------------------------
    // --- Get data functions --- 
    // --------------------------
    /** Get price base data. Info item_price_name, price, buy_at_url, etc. */
    public function getBaseData ($item_price_id)
    {
        $q = "SELECT * FROM item_price WHERE item_price_id = $item_price_id";
        return pdoQueryAssocFirstRow($this->m_dbPDO, $q); 
    }
    

    /** Get a list of all item_price_id's that belongs to the given \a $item_base_id. */
    public function getPriceIDs ($item_base_id)
    {
        $q = "SELECT item_price_id FROM item_price WHERE item_base_id = $item_base_id";
        return pdoQueryAllRowsFirstElem($this->m_dbPDO, $q);
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
    public function createNew ($item_base_id, $item_price_name, $record_store_id, $media_format_id, $price_local, $currency_id, $buy_at_url )
    {
        $stmt = $this->m_dbPDO->prepare("
        INSERT INTO item_base (item_base_id, item_price_name, record_store_id, media_format_id, price_local, currency_id, buy_at_url) 
        VALUES (?, ?, ?, ?, ?, ?, ?)" );
        $stmt->execute( array($item_base_id, $item_price_name, $record_store_id, $media_format_id, $price_local, $currency_id, $buy_at_url) );
        return (int)$this->m_dbPDO->lastInsertId();
    }
    
    
    
    /**  Update base data of existing item. */
    public function updateBaseData ($aData)
    {
        static $aTblFields = array( 'item_base_id', 'item_price_name', 'record_store_id', 'media_format_id', 'price_local', 'currency_id', 'buy_at_url', 'release_date' );

        $result = 0;
        $item_price_id = $aData['item_price_id'];

        $aUpd = pdoGetUpdate ($aData, $aTblFields );
        if ($aUpd[0] != "" ) {
            $q = 'UPDATE item_price SET ' . $aUpd[0] . ' WHERE item_price_id = ?';
            $aUpd[1][] = $item_price_id;
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
