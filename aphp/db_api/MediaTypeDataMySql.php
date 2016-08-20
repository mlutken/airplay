<?php

require_once ("db_api/SimpleTableDataMySql.php");


class MediaTypeDataMySql extends SimpleTableDataMySql
{
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'media_type'
        , array( 'media_type_name' ) 
        , $dbPDO );
    }
    
}


?>