<?php

require_once ("admin_site/classes/SimpleTableUI.php");
require_once ("db_manip/MusicDatabaseManip.php");

class ItemPriceTblUI extends SimpleTableUI
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $mainName, $baseTableName )
    {
        parent::__construct( $mainName, $baseTableName ); 
    }
    
    
}


?>