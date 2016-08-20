<?php

require_once ("admin_site/classes/SimpleTableUI.php");
require_once ("db_manip/MusicDatabaseManip.php");

class ItemBaseMergeTblUI extends SimpleTableUI
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $mainName, $baseTableName )
    {
        parent::__construct( $mainName, $baseTableName ); 
        $this->fieldsToListSet( array(  'item_base_id', 'item_type', 'artist_id', 'item_base_name' ) );
        $this->actionsAvailableSet( array('list') );
        $this->tableOptionsSet( array( 'paging' => 'true', 'selecting' => 'true', 'selectingCheckboxes' => 'true' ) );
    }
    
}


?>