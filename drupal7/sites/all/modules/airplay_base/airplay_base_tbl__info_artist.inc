<?php 
// $Id$


function ap_insertOrUpdate__info_artist ( &$aTitles )
{
	$tbl = ap_get_tbl__info_artist ();	
    foreach ( $aTitles as &$aTitle ) 
    {
        $drt = $aTitle['data_record_type'] ;
        if ( $drt != 'info_artist' )   continue;

        $tbl->lookupTitleId( $aTitle );
        $tbl->insertNewTitle( $aTitle );
        $tbl->updateTitleByIndex( $aTitle );
    }
}


function ap_get_tbl__info_artist ( )
{
	$tbl = new AirplayTable( 'info_artist' );	
	$tbl->optionSet ( 'lookup_where', "WHERE artist_id = :artist_id" );
	$tbl->optionSet ( 'lookup_fields', array( 'artist_id' ) );
	return $tbl;
}

