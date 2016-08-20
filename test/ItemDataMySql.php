<?php

require_once ("db_helpers.php");


class ItemDataMySql
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

    
    /** Lookup item ID from item "base/real/official" name.
    \return one item ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function baseNameToID ($artist_id, $item_base_name, $item_type)
    {
        $q = "SELECT item_base_id FROM item_base WHERE artist_id = ? AND item_type = ? AND item_base_name = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($artist_id, $item_type, $item_base_name) );
    }
    

    // --------------------------
    // --- Get data functions --- 
    // --------------------------
    /** Get item base data,. Info like name, genre, wiki info etc. */
    public function getBaseData ($item_base_id)
    {
        $q = "SELECT * FROM item_base WHERE item_base_id = $item_base_id";
        return pdoQueryAssocFirstRow($this->m_dbPDO, $q); 
    }
    

    /** Get child song base_item IDs. The \a $item_base_id should be an album, otherwise an emty list is returned. */
    public function getChildSongIDs ($item_base_id)
    {
        $q = "SELECT child_items FROM item_base WHERE item_base_id = $item_base_id";
        $sList = pdoLookupSingleStringQuery($this->m_dbPDO, $q, array() ); 
        if ( $sList != "" ) return explode( ',', $sList );
        return array();
    }
    
     
    /** Get all data needed to display an item page */
    public function getPageData ($item_base_id)
    {
        // TODO: Implement this!
//         $q = "SELECT * from item, info_item WHERE item.item_base_id = $item_base_id AND info_item.item_base_id = $item_base_id";
//         
//         return $a;
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
    public function createNew ($artist_id, $item_base_name, $item_type)
    {
        $stmt = $this->m_dbPDO->prepare("INSERT INTO item_base (artist_id, item_base_name, item_type) VALUES (?, ?, ?)" );
        $stmt->execute( array($artist_id, $item_base_name, $item_type) );
        return (int)$this->m_dbPDO->lastInsertId();
    }
    
    
    
    /**  Update base data of existing item. */
    public function updateBaseData ($aData)
    {
        static $aTblFields = array( 'item_type', 'artist_id', 'item_base_name', 'record_label_id', 'item_genre_id', 'item_subgenre_ids', 'item_year', 'release_date', 'parent_item', 'item_time', 'child_items' );

        $result = 0;
        $item_base_id = $aData['item_base_id'];

        $aUpd = pdoGetUpdate ($aData, $aTblFields );
        if ($aUpd[0] != "" ) {
            $q = 'UPDATE item_base SET ' . $aUpd[0] . ' WHERE item_base_id = ?';
            $aUpd[1][] = $item_base_id;
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
