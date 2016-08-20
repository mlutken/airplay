<?php

require_once ("db_api/SimpleTableDataMySql.php");


class CurrencyToEuroDataMySql extends SimpleTableDataMySql
{
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'currency_to_euro'
        , array( 'currency_to_euro_name', 'currency_id', 'currency_name', 'to_euro' ) 
        , $dbPDO );
    }
    
}


?>