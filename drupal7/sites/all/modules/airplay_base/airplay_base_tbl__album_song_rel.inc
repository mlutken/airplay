<?php 
// $Id$



function ap_insertOrUpdate__album_song_rel ( &$aTitles )
{
	$tbl = ap_get_tbl__album_song_rel() ;	
	foreach ( $aTitles as &$aTitle ) 
	{
        $drt = $aTitle['data_record_type'] ;
        if ( $drt == 'record_store' )   continue;

// //         printf("album_id: {$aTitle['album_id']}, song_id: {$aTitle['song_id']}\n");
// //         var_dump($aTitle);
        if ( $aTitle['album_id'] == 0 || $aTitle['song_id'] == 0 )	continue;
		$tbl->lookupTitleId( $aTitle );
		$tbl->insertNewTitle( $aTitle );
		$tbl->updateTitleByIndex( $aTitle );
	}
}


function ap_get_tbl__album_song_rel ( )
{
	$tbl = new AirplayTable( 'album_song_rel' );	
	$tbl->optionSet ( 'lookup_where', "WHERE album_id = :album_id AND song_id = :song_id" );
	$tbl->optionSet ( 'lookup_fields', array( 'album_id', 'song_id' ) );
	return $tbl;
}

