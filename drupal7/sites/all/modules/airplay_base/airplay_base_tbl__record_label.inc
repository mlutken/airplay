<?php 
// $Id$


function ap_insertOrUpdate__record_label ( &$aTitles )
{
	$tbl = ap_get_tbl__record_label ();	
    foreach ( $aTitles as &$aTitle ) 
    {
        $drt = $aTitle['data_record_type'] ;
        if ( $drt == 'record_store' )   continue;
        if ( !array_key_exists( 'record_label_name', $aTitle ) ) continue;

        $tbl->lookupTitleId( $aTitle );
        $tbl->insertNewTitle( $aTitle );
        $tbl->updateTitleByIndex( $aTitle );
    }
}


function ap_get_tbl__record_label ( )
{
	$tbl = new AirplayTable( 'record_label' );	
	return $tbl;
}

