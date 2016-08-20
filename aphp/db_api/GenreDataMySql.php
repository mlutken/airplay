<?php

require_once ("db_api/SimpleTableDataMySql.php");


class GenreDataMySql extends SimpleTableDataMySql
{
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'genre'
        , array(  'genre_name' ) 
        , $dbPDO );
    }
    
}


?>