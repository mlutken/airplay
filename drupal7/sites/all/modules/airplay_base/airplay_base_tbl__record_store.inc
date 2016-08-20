<?php 
// $Id$


function ap_insertOrUpdate__record_store ( &$aTitles )
{
	$tbl = ap_get_tbl__record_store ();	
    
    $i = 0;
  	
    foreach ( $aTitles as &$aTitle ) 
    {
// //         $i++;
// //         printf("ap_insertOrUpdate__record_store $i\n");
        if ( !array_key_exists( 'record_store_name', $aTitle ) ) continue;

        $tbl->lookupTitleId( $aTitle );
        $tbl->insertNewTitle( $aTitle );
        $tbl->updateTitleByIndex( $aTitle );
    }
}


function ap_get_tbl__record_store ( )
{
	$tbl = new AirplayTable( 'record_store' );	
	return $tbl;
}

