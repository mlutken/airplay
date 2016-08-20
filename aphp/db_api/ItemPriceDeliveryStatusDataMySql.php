<?php

require_once ("db_api/SimpleTableDataMySql.php");


class ItemPriceDeliveryStatusDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        ' item_price_delivery_status'
        , array(  'delivery_status' )
        , $dbPDO );
    }
}

?>