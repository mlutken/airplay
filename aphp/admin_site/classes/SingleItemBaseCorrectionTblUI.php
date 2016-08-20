<?php

require_once ("admin_site/classes/SimpleTableUI.php");

class SingleItemBaseCorrectionTblUI extends SimpleTableUI
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $mainName, $baseTableName )
    {
        parent::__construct( $mainName, $baseTableName ); 
        $this->fieldsToListSet  ( array( 'item_base_correction_name'  ) );
        $this->fieldsToEditSet  ( array( 'item_base_correction_name'  ) );
        $this->fieldsToCreateSet( array( 'item_base_correction_name'  ) );
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
        $item_base_name = $_GET['item_base_name'];
        return $this->m_dbInterface->getCorrectionDataForBaseName($artist_id, $item_base_name);
    }
    protected function dbCreate($aData)
    {
        $artist_id                  = (int)$_GET['artist_id'];
        $item_base_name             = $_GET['item_base_name'];
        $item_base_correction_name  = $aData['item_base_correction_name'];
        return $this->m_dbInterface->createNew ( $artist_id, $item_base_correction_name, $item_base_name );
    }
    protected function dbUpdateBaseData($aData)
    {
        $artist_id = (int)$_GET['artist_id'];
        $item_base_name = $_GET['item_base_name'];
        $aData['artist_id'] = $artist_id;
        return $this->m_dbInterface->updateBaseData($aData);
    }
    
}


?>