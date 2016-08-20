<?php

require_once ("admin_site/classes/SimpleTableUI.php");

class SingleArtistAliasTblUI extends SimpleTableUI
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $mainName, $baseTableName )
    {
        parent::__construct( $mainName, $baseTableName ); 
        $this->fieldsToListSet  ( array( 'artist_synonym_name'  ) );
        $this->fieldsToEditSet  ( array( 'artist_synonym_name'  ) );
        $this->fieldsToCreateSet( array( 'artist_synonym_name'  ) );
        $this->tableOptionsSet  ( array( 'paging' => ''    ) ); // Disable paging
        
    }
    
    // ------------------------------------------------
    // --- PROTECTED: Ajax handler action functions --- 
    // ------------------------------------------------
    protected function actionList( &$jTableResult, $aData )
    {
        $jTableResult['Result'] = "OK";
        $rows = array();
        if ( 'listAll' == $this->m_viewMode ) {
                $rows   = $this->dbListAll($this->m_listAllStartIndex, $this->m_listAllCount);
                $jTableResult['TotalRecordCount'] = count($rows);
        }
        $jTableResult['Records'] = $rows;
    }

    protected function dbListAll($startIndex, $count)
    {
        $artist_id = (int)$_GET['artist_id'];
        return $this->m_dbInterface->getAliasesDataForArtist($artist_id);
    }
    protected function dbCreate($aData)
    {
        $artist_id = (int)$_GET['artist_id'];
        $aData['artist_id'] = $artist_id;
        return $this->m_dbInterface->newItem($aData);
    }
    protected function dbUpdateBaseData($aData)
    {
        $artist_id = (int)$_GET['artist_id'];
        $aData['artist_id'] = $artist_id;
        return $this->m_dbInterface->updateBaseData($aData);
    }
    
}


?>