<?php

require_once ("db_api/SimpleTableDataMySql.php");


class ItemBaseReviewDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'item_base_review'
        , array(  'record_store_id', 'item_base_id', 'artist_id', 'review_rating'
                , 'review_url', 'review_text', 'language_code', 'timestamp_updated'
                )
        , $dbPDO );
    }
    
    /** Lookup ID from $aData.
    \return One ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function toID ($aData)
    {
        $record_store_id = $aData["record_store_id"];
        $item_base_id = $aData["item_base_id"];
        $q = "SELECT {$this->m_baseTableName}_id FROM {$this->m_baseTableName} WHERE record_store_id = ? AND item_base_id = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($record_store_id,$item_base_id) );
    }

    /**  Create new item. 
    \return ID of new item. */
    public function newItem ( $aData )
    {
        $record_store_id = $aData["record_store_id"];
        $item_base_id = $aData["item_base_id"];
        $stmt = $this->m_dbPDO->prepare("INSERT INTO {$this->m_baseTableName} (record_store_id,item_base_id) VALUES (?,?)" );
        $stmt->execute( array($record_store_id,$item_base_id) );
        $id = (int)$this->m_dbPDO->lastInsertId();
        return $id;
    }
    
    
}

?>