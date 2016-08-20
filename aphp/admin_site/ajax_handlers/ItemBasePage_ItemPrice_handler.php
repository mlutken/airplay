<?php
require_once ( __DIR__ . '/../../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('admin_site/classes/session_start.php');
require_once ('utils/general_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');
require_once ('db_api/GenreLookup.php');
require_once ('db_api/CountryLookup.php');
require_once ("admin_site/classes/SimpleTableUI.php");


class ItemBasePageItemPriceHandler extends SimpleTableUI
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( )
    {
        parent::__construct( 'ItemBasePage_ItemPrice', 'item_price' ); 
        $fac = new MusicDatabaseFactory();
        $dbItemPrice = $fac->createDbInterface("ItemPriceData");
        $this->dbInterfaceSet($dbItemPrice);
    }

    
    // ------------------------------------------------
    // --- PROTECTED: Ajax handler action functions --- 
    // ------------------------------------------------
    protected function actionList( &$jTableResult, $aData )
    {
        $item_base_id = (int)$_GET['item_base_id'];
        $jTableResult['Result'] = "OK";
        $rows = $this->m_dbInterface->getItemPrices( $item_base_id );
        $jTableResult['TotalRecordCount'] = count($rows);
        $jTableResult['Records'] = $rows;
    }
    
}


$ui = new ItemBasePageItemPriceHandler();


print $ui->ajaxHandler();

?>