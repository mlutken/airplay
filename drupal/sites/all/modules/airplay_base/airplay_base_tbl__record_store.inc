<?php 
// $Id$


function ap_insertOrUpdate__record_store ( &$aTitles )
{
	$tbl = ap_get_tbl__record_store ();	
	$tbl->insertOrUpdateSimple( $aTitles );
	$tbl = null;
}


function ap_get_tbl__record_store ( )
{
	$tbl = new AirplayTable( 'record_store' );	
	return $tbl;
}

