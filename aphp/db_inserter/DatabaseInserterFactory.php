<?php

require_once ( 'db_inserter/DbInserter_record_store.php');
require_once ( 'db_inserter/DbInserter_artist_data.php');
require_once ( 'db_inserter/DbInserter_item_base.php');
require_once ( 'db_inserter/DbInserter_item_price.php');

/** Default record splitter. Is used if the record type does not need/have 
a splitter defined. The defalt splitter simply returns the an array with 
the incoming record as its only element. */
class DefaultRecordSplitter
{
    public  function    __construct()
    {
    }

    public function splitRecord ( $aData )
    {
        return array( $aData );
    }
}

/** Factory for creating record splitters and db inserters. */
class DatabaseInserterFactory 
{

    public  function    __construct( $dbAll )
    {
        $this->m_dbAll = $dbAll;
    }

    public function createRecordSplitter ( $data_record_type )
    {
        switch ( $data_record_type ) 
        {
            case 'album'              :   return new RecordSplitter_item_price( $this->m_dbAll );
            case 'song'               :   return new RecordSplitter_item_price( $this->m_dbAll );
            case 'album_price'        :   return new RecordSplitter_item_price( $this->m_dbAll );
            case 'song_price'         :   return new RecordSplitter_item_price( $this->m_dbAll );
            case 'merchandise'        :   return new RecordSplitter_item_price( $this->m_dbAll );
            case 'merchandise_price'  :   return new RecordSplitter_item_price( $this->m_dbAll );
            case 'concert'            :   return new RecordSplitter_item_price( $this->m_dbAll );
            case 'concert_price'      :   return new RecordSplitter_item_price( $this->m_dbAll );
            default                   :   return new DefaultRecordSplitter;
        }
        
    }

    public function createInserter ( $data_record_type )
    {
        switch ( $data_record_type ) 
        {
            case 'artist_data'       :   return new DbInserter_artist_data   ( $this->m_dbAll );
            case 'album_base'        :   return new DbInserter_item_base     ( $this->m_dbAll );
            case 'song_base'         :   return new DbInserter_item_base     ( $this->m_dbAll );
            case 'album_price'       :   return new DbInserter_item_price    ( $this->m_dbAll );
            case 'song_price'        :   return new DbInserter_item_price    ( $this->m_dbAll );
            case 'record_store'      :   return new DbInserter_record_store  ( $this->m_dbAll );
            case 'album'             :   return new DbInserter_item_price    ( $this->m_dbAll );
            case 'song'              :   return new DbInserter_item_price    ( $this->m_dbAll );
            case 'artist_info'       :   return new DbInserter_artist_data   ( $this->m_dbAll );
            case 'merchandise'       :   return new DbInserter_item_price    ( $this->m_dbAll );
            case 'merchandise_base'  :   return new DbInserter_item_base     ( $this->m_dbAll );
            case 'merchandise_price' :   return new DbInserter_item_price    ( $this->m_dbAll );
            case 'concert'           :   return new DbInserter_item_price    ( $this->m_dbAll );
            case 'concert_base'      :   return new DbInserter_item_base     ( $this->m_dbAll );
            case 'concert_price'     :   return new DbInserter_item_price    ( $this->m_dbAll );
            default                  :   return null;
        }
        
    }

    // ----------------------------
    // --- PRIVATE: Member data --- 
    // ----------------------------
    private         $m_dbAll = null;
    
}


?>