<?php 
// $Id$



function ap_insertOrUpdate__album ( &$aTitles )
{
	$tblDefault 		= ap_get_tbl__album__default() ;	
 	$tblAlbumArtistName = ap_get_tbl__album__album_artist_name() ;	
	foreach ( $aTitles as &$aTitle ) {
		if ( $aTitle['album_artist_name'] != "" && $aTitle['data_record_type'] == 'song') {
			$tblAlbumArtistName->lookupTitleId( $aTitle );
			$tblAlbumArtistName->insertNewTitle( $aTitle );
			$tblAlbumArtistName->updateTitleByIndex( $aTitle );
		}
		else {
			$tblDefault->lookupTitleId( $aTitle );
			$tblDefault->insertNewTitle( $aTitle );
			$tblDefault->updateTitleByIndex( $aTitle );
		}
	}
	$tblDefault = null;
	$tblAlbumArtistName = null;
}


function ap_get_tbl__album__default ( )
{
	$tbl = new AirplayTable( 'album' );	
	$tbl->optionSet ( 'lookup_where', "WHERE album_name = '%s' AND artist_id = %d" );
	$tbl->optionSet ( 'lookup_fields', array( 'album_name', 'artist_id' ) );
	return $tbl;
}

function ap_get_tbl__album__album_artist_name ( )
{
	$tbl = new AirplayTable( 'album' );	
	$tbl->optionSet ( 'lookup_where', "WHERE album_name = '%s' AND artist_id = %d" );
	$tbl->optionSet ( 'lookup_fields', array( 'album_name', 'album_artist_id' ) );
	return $tbl;
}

// function ap_lookup_album_exact( $artist_id, $album_name ) 
// {
// 	$retVal = false;
// 	db_set_active('airplay_music');
// 	
// 	$query = "
// 		SELECT *  
// 		FROM album 
// 		LEFT JOIN genre ON album.genre_id = genre.genre_id 
// 		WHERE artist_id = %d AND album_name = '%s' 
// 	";
// // 	$query_result =  db_query($query, array(6, 'Hitovi') );
// 	$query_result =  db_query($query, array($artist_id, $album_name) );
// 	if ( $album = db_fetch_object($query_result)) {
// 		$retVal = $album;
// 	}
// // 	dfb ( "ap_lookup_album_exact: $artist_id, $album_name ");
// // 	dfb ($query);
// 	db_set_active('default');
// 	return $retVal;
// }


