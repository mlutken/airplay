<?php

require_once ( 'DbInserter_record_store.php');
require_once ( 'DbInserter_artist_data.php');
require_once ( 'DbInserter_item_price.php');

class DefaultRecordSplitter
{
    public  function    __construct( )
    {
    }

    public function splitRecord ( $aData )
    {
        return array( $aData );
    }
}


class DatabaseInserterFactory 
{

    public  function    __construct( $dbPDO = null )
    {
        global $g_MySqlPDO;
        $this->m_dbPDO = $dbPDO;
        if ( $dbPDO == null ) $this->m_dbPDO = $g_MySqlPDO;      
    }

    public function createRecordSplitter ( $data_record_type )
    {
        switch ( $data_record_type ) 
        {
            case 'album'        :   return new RecordSplitter_item_price;
            case 'song'         :   return new RecordSplitter_item_price;
            case 'item_price'   :   return new RecordSplitter_item_price;
            default             :   return new DefaultRecordSplitter;
        }
        
    }

    public function createInserter ( $data_record_type )
    {
        switch ( $data_record_type ) 
        {
            case 'artist_data'  :   return new DbInserter_artist_data   ( $this->m_dbPDO );
            case 'item_price'   :   return new DbInserter_item_price    ( $this->m_dbPDO );
            case 'record_store' :   return new DbInserter_record_store  ( $this->m_dbPDO );
            default             :   return null;
        }
        
    }

    // ----------------------------
    // --- PRIVATE: Member data --- 
    // ----------------------------
    private         $m_dbPDO = null;
    private         $m_iTitlesCounter;
    
}


?>







