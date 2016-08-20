<?php 
// $Id$


function ap_insertOrUpdate__song ( &$aTitles )
{
	$tbl = ap_get_tbl__song ();	
    $i = 0;
	foreach ( $aTitles as &$aTitle ) 
    {
        $drt = $aTitle['data_record_type'] ;
        if ( $drt != 'song' )   continue;
//        if ( $drt == 'record_store' )   continue;
//          $i++;
//          printf("ap_insertOrUpdate__song: $i, drt: $drt, song: {$aTitle['song_name']}\n");
//        var_dump($aTitle);
        $tbl->lookupTitleId( $aTitle );
        $tbl->insertNewTitle( $aTitle );
        $tbl->updateTitleByIndex( $aTitle );
    }
}


function ap_get_tbl__song ( )
{
	$tbl = new AirplayTable( 'song' );	
	$tbl->optionSet ( 'lookup_where', "WHERE song_name = :song_name AND artist_id = :artist_id" );
	$tbl->optionSet ( 'lookup_fields', array( 'song_name', 'artist_id' ) );
	return $tbl;
}



