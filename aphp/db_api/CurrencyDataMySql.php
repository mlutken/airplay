<?php

require_once ("db_api/SimpleTableDataMySql.php");


class CurrencyDataMySql extends SimpleTableDataMySql
{
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'currency'
        , array(  'currency_name', 'from_euro', 'to_euro' ) 
        , $dbPDO );
    }
    
}


?>