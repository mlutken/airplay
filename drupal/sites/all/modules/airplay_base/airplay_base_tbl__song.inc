<?php 
// $Id$


function ap_insertOrUpdate__song ( &$aTitles )
{
	$tbl = ap_get_tbl__song ();	
	$tbl->insertOrUpdateSimple( $aTitles );
	$tbl = null;
}


function ap_get_tbl__song ( )
{
	$tbl = new AirplayTable( 'song' );	
	$tbl->optionSet ( 'lookup_where', "WHERE song_name = '%s' AND artist_id = %d" );
	$tbl->optionSet ( 'lookup_fields', array( 'song_name', 'artist_id' ) );
	return $tbl;
}



