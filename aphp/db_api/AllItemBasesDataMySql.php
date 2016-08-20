<?php

require_once ("db_api/SimpleTableDataMySql.php");


class AllItemBasesDataMySql extends SimpleTableDataMySql
{
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'all_item_bases'
        , array(  'all_item_bases_name', 'item_type', 'artist_name' ) 
        , $dbPDO );
    }
	
	// TODO: Add functions to insert fast (one big INSERT MySQL command string for example) here
	
    
}


?>