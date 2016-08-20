<?php 
// $Id$


function ap_insertOrUpdate__subgenre ( &$aTitles )
{
	$tbl = ap_get_tbl__subgenre ();	
    foreach ( $aTitles as &$aTitle ) 
    {
        $drt = $aTitle['data_record_type'] ;
        if ( $drt == 'record_store' )   continue;
        if ( !array_key_exists( 'subgenre_name', $aTitle ) ) continue;
        
        $tbl->lookupTitleId( $aTitle );
        $tbl->insertNewTitle( $aTitle );
        $tbl->updateTitleByIndex( $aTitle );
    }
}


function ap_get_tbl__subgenre ( )
{
	$tbl = new AirplayTable( 'subgenre' );	
	return $tbl;
}

