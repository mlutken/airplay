<?php

require_once ("db_api/SimpleTableDataMySql.php");


class RecordStoreDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'record_store'
        , array(  'record_store_name', 'record_store_url', 'country_id'
                , 'use_affiliate', 'affiliate_link', 'affiliate_encode_times'
                , 'record_store_reliability', 'record_store_event_date_text', 'record_store_logo', 'freight_price_da', 'freight_price_en', 'is_in_record_store_guide', 'record_store_description',
				'selling_type_id', 'is_in_ap_results', 'record_store_type_id') 
        , $dbPDO );
    }
    
    
    
    /**  Create new item with more fields
    \return ID of new item. */
    public function createNewFull ( $record_store_name, $record_store_url, $country_id )
    {
        if ( skipDbWrite() ) return 0;
        $stmt = $this->m_dbPDO->prepare("INSERT INTO record_store (record_store_name, record_store_url, country_id) VALUES (?, ?, ?)" );
        $stmt->execute( array($record_store_name, $record_store_url, $country_id) );
        $id = (int)$this->m_dbPDO->lastInsertId();
        return $id;
    }
}


?>