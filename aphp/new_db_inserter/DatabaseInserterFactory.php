<?php

require_once ( 'new_db_inserter/RecordStoreInserterFileDb.php');
require_once ( 'new_db_inserter/ArtistInserterFileDb.php');
require_once ( 'new_db_inserter/ItemBaseInserterFileDb.php');
require_once ( 'new_db_inserter/ItemPriceInserterFileDb.php');


/** Factory for creating record splitters and db inserters. */
class DatabaseInserterFactory 
{

    public  function    __construct( $fileDbBaseDir, $dbAll, $openParents )
    {
        $this->m_fileDbBaseDir  = $fileDbBaseDir;
        $this->m_dbAll          = $dbAll;
        $this->m_openParents	= $openParents;
    }


    public function createInserter ( $data_record_type )
    {
        switch ( $data_record_type ) 
        {
            case 'artist_data'      :   return new ArtistInserterFileDb     ( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );
            case 'artist_info'      :   return new ArtistInserterFileDb     ( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );
            case 'item_base'        :   return new ItemBaseInserterFileDb   ( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );
            case 'album_base'       :   return new ItemBaseInserterFileDb   ( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );
            case 'song_base'        :   return new ItemBaseInserterFileDb   ( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );
            case 'merchandise_base' :   return new ItemBaseInserterFileDb   ( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );
            case 'concert_base'     :   return new ItemBaseInserterFileDb   ( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );
            case 'item_price'       :   return new ItemPriceInserterFileDb  ( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );
            case 'album_price'      :   return new ItemPriceInserterFileDb  ( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );
            case 'song_price'       :   return new ItemPriceInserterFileDb  ( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );
            case 'merchandise_price':   return new ItemPriceInserterFileDb  ( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );
            case 'concert_price'    :   return new ItemPriceInserterFileDb  ( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );
            case 'album'            :   return new ItemPriceInserterFileDb  ( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );
            case 'song'             :   return new ItemPriceInserterFileDb  ( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );
            case 'merchandise'      :   return new ItemPriceInserterFileDb  ( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );
            case 'concert'          :   return new ItemPriceInserterFileDb  ( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );
            case 'record_store'     :   return new RecordStoreInserterFileDb( $this->m_fileDbBaseDir, $this->m_dbAll, $this->m_openParents );
            default                 :   return null;
        }
        
    }

    // ----------------------------
    // --- PRIVATE: Member data --- 
    // ----------------------------
    private         $m_fileDbBaseDir;
    private         $m_dbAll;
    private			$m_openParents;
    
}


?>