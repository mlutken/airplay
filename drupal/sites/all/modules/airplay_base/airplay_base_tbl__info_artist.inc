<?php 
// $Id$


function ap_insertOrUpdate__info_artist ( &$aTitles )
{
	$tbl = ap_get_tbl__info_artist ();	
	$tbl->insertOrUpdateSimple( $aTitles );
	$tbl = null;
}


function ap_get_tbl__info_artist ( )
{
	$tbl = new AirplayTable( 'info_artist' );	
	$tbl->optionSet ( 'lookup_where', "WHERE artist_id = %d" );
	$tbl->optionSet ( 'lookup_fields', array( 'artist_id' ) );
	return $tbl;
}

