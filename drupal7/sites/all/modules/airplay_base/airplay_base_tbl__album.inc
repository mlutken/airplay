<?php 
// $Id$



function ap_insertOrUpdate__album ( &$aTitles )
{
	$tblDefault 		= ap_get_tbl__album__default() ;	
 	$tblAlbumArtistName = ap_get_tbl__album__album_artist_name() ;	
    $i = 0;
 	foreach ( $aTitles as &$aTitle ) {
// //          printf ( "aTitle['album_simple_id']: {$aTitle['album_simple_id']}\n"  );
         $drt = $aTitle['data_record_type'] ;
         if ( $drt == 'record_store' )   continue;

// //          $i++;
// //          printf("ap_insertOrUpdate__album: $i, drt: $drt, song: {$aTitle['song_name']}\n");

         if ( $drt == 'song' && $aTitle['album_artist_name'] != "") {
			$tblAlbumArtistName->lookupTitleId( $aTitle );
			$tblAlbumArtistName->insertNewTitle( $aTitle );
			$tblAlbumArtistName->updateTitleByIndex( $aTitle );
		}
		else {
//            var_dump($aTitle);
            if ( !array_key_exists( 'album_simple_id', $aTitle) )  continue;
			$tblDefault->lookupTitleId( $aTitle );
			$tblDefault->insertNewTitle( $aTitle );
			$tblDefault->updateTitleByIndex( $aTitle );
		}
	}
}


function ap_get_tbl__album__default ( )
{
	$tbl = new AirplayTable( 'album' );	
	$tbl->optionSet ( 'lookup_where', "WHERE album_name = :album_name AND artist_id = :artist_id" );
	$tbl->optionSet ( 'lookup_fields', array( 'album_name', 'artist_id' ) );
	return $tbl;
}

function ap_get_tbl__album__album_artist_name ( )
{
	$tbl = new AirplayTable( 'album' );	
	$tbl->optionSet ( 'lookup_where', "WHERE album_name = :album_name AND artist_id = :artist_id" );
	$tbl->optionSet ( 'lookup_fields', array( 'album_name', 'album_artist_id' ) );
	return $tbl;
}

