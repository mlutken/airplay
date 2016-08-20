<?php 
// $Id$



function ap_insertOrUpdate__album_song_rel ( &$aTitles )
{
	$tbl = ap_get_tbl__album_song_rel() ;	
// // 	$tbl->insertOrUpdateSimple( $aTitles );
	foreach ( $aTitles as &$aTitle ) 
	{
		if ( $aTitle['album_id'] == 0 || $aTitle['song_id'] == 0 )	continue;
		$tbl->lookupTitleId( $aTitle );
		$tbl->insertNewTitle( $aTitle );
		$tbl->updateTitleByIndex( $aTitle );
	}
	$tbl = null;
}


function ap_get_tbl__album_song_rel ( )
{
	$tbl = new AirplayTable( 'album_song_rel' );	
	$tbl->optionSet ( 'lookup_where', "WHERE album_id = %d AND song_id = %d" );
	$tbl->optionSet ( 'lookup_fields', array( 'album_id', 'song_id' ) );
	return $tbl;
}

