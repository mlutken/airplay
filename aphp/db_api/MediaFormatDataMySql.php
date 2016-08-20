<?php

require_once ("db_api/SimpleTableDataMySql.php");


class MediaFormatDataMySql extends SimpleTableDataMySql
{
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'media_format'
        , array( 'media_format_name' ) 
        , $dbPDO );
    }
    
}


?>