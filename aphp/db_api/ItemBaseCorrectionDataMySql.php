<?php

require_once ("db_api/SimpleTableDataMySql.php");


class ItemBaseCorrectionDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'item_base_correction'
        , array(  'artist_id', 'item_base_correction_name', 'item_base_name' ) 
        , $dbPDO );
    }
    
    // -------------------------------------
    // --- Erase/delete/merge functions ----
    // -------------------------------------
    public function mergeArtist ($into_artist_id, $from_artist_id)
    {
        $stmt = $this->m_dbPDO->prepare( 'UPDATE item_base_correction SET artist_id=? WHERE artist_id = ?' );
        $stmt->execute( array($into_artist_id, $from_artist_id ) );
    }
    
    // ----------------------------------
    // --- Item ID lookup functions --- 
    // ----------------------------------

    
    /** XX.
    \return One  item_base_correction ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function correctionNameToID ($artist_id, $item_base_correction_name)
    {
        if ( $artist_id == 0 ) {
            $q = "SELECT item_base_correction_id FROM item_base_correction WHERE item_base_correction_name = ?";
            return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($item_base_correction_name) );
        }
        else {
            $q = "SELECT item_base_correction_id FROM item_base_correction WHERE artist_id = ? AND item_base_correction_name = ?";
            return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($artist_id, $item_base_correction_name) );
        }
    }
    
    public function correctionNameToBaseName ($artist_id, $item_base_correction_name)
    {
        $item_base_name = '';
        if ($artist_id != 0 ) {
            $q = "SELECT item_base_name FROM item_base_correction WHERE artist_id = ? AND item_base_correction_name = ?";
            $item_base_name = pdoLookupSingleStringQuery($this->m_dbPDO, $q, array($artist_id, $item_base_correction_name) );
        }
        return $item_base_name != '' ? $item_base_name : $item_base_correction_name;
    }


     /**  Check if a given alias exists for artist. 
    \return ID of alias or zero if not found. */
    public function correctionExists ($artist_id, $item_base_correction_name, $item_base_name)
    {
        $q = "SELECT item_base_correction_id FROM item_base_correction WHERE artist_id = ? AND item_base_correction_name = ? AND item_base_name = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($artist_id, $item_base_correction_name, $item_base_name) );
    }
    
    // -----------------------------------------------
    // --- Get data functions needing an artist_id ---
    // -----------------------------------------------
    
    /** Get artist base item IDs. */
    public function getCorrectionIDsForArtist ($artist_id)
    {
        $q = "SELECT item_base_correction_id FROM item_base_correction WHERE artist_id = ?";
        return pdoQueryAllRowsFirstElem($this->m_dbPDO, $q, array($artist_id)); 
    }


    /** Get artist base item IDs. */
    public function getCorrectionDataForBaseName ($artist_id, $item_base_name)
    {
        $q = "SELECT * FROM item_base_correction WHERE artist_id = ? AND item_base_name = ?";
        return pdoQueryAssocRows($this->m_dbPDO, $q, array($artist_id, $item_base_name)); 
    }
    
    /**  Create new item. 
    \return ID of new item. */
    public function createNew ( $artist_id, $item_base_correction_name, $item_base_name )
    {
        $stmt = $this->m_dbPDO->prepare("
        INSERT INTO item_base_correction (artist_id, item_base_correction_name, item_base_name) 
        VALUES (?,?,?)" );
        $res = $stmt->execute( array($artist_id, $item_base_correction_name, $item_base_name) );
        return (int)$this->m_dbPDO->lastInsertId();
    }
    
}
?>