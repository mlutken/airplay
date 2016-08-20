<?php

require_once ("admin_site/classes/SimpleTableUI.php");
require_once ("db_manip/MusicDatabaseManip.php");

class ArtistMergeTblUI extends SimpleTableUI
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $mainName, $baseTableName )
    {
        parent::__construct( $mainName, $baseTableName ); 
        $this->fieldsToListSet( array(  'artist_id', 'artist_name', 'artist_real_name', 'country_id', 'url_artist_official' ) );
        $this->actionsAvailableSet( array('list') );
        $this->tableOptionsSet( array( 'paging' => 'true', 'selecting' => 'true', 'selectingCheckboxes' => 'true' ) );
    }
    
}


?>