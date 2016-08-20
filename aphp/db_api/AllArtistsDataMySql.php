<?php

require_once ("db_api/SimpleTableDataMySql.php");


class AllArtistsDataMySql extends SimpleTableDataMySql
{
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'all_artists'
        , array(  'all_artists_name' ) 
        , $dbPDO );
    }
	
	// TODO: Add functions to insert fast (one big INSERT MySQL command string for example) here
	
    
}


?>