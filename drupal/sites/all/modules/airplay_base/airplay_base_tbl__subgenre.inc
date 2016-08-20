<?php 
// $Id$


function ap_insertOrUpdate__subgenre ( &$aTitles )
{
	$tbl = ap_get_tbl__subgenre ();	
	$tbl->insertOrUpdateSimple( $aTitles );
	$tbl = null;
}


function ap_get_tbl__subgenre ( )
{
	$tbl = new AirplayTable( 'subgenre' );	
	return $tbl;
}

