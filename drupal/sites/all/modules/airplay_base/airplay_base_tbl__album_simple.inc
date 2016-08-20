<?php 
// $Id$



function ap_insertOrUpdate__album_simple ( &$aTitles )
{
	$tblDefault 		= ap_get_tbl__album_simple__default() ;	
	foreach ( $aTitles as &$aTitle ) {
		$aTitle['album_simple_name'] = ap_simplify_album_name( $aTitle['album_name'], $aTitle["artist_name"] );
		if ( $aTitle['album_simple_name'] == "" ) continue;
		if ( $aTitle['artist_id'] == 0 			) continue;
		
		$aTitle['do_insert'][] = 'album_simple';
		$aTitle['do_update']['album'][] = 'album_simple_id';
 		if ( count( $aTitle['do_update']['buy_album'] ) > 0 )	{
			$aTitle['do_update']['buy_album'][] = 'album_simple_id';
 		}
		
		$tblDefault->lookupTitleId( $aTitle );
		$tblDefault->insertNewTitle( $aTitle );
		$tblDefault->updateTitleByIndex( $aTitle );
	}
	$tblDefault = null;
	$tblAlbumArtistName = null;
}


function ap_get_tbl__album_simple__default ( )
{
	$tbl = new AirplayTable( 'album_simple' );	
	$tbl->optionSet ( 'lookup_where', "WHERE album_simple_name = '%s' AND artist_id = %d" );
	$tbl->optionSet ( 'lookup_fields', array( 'album_simple_name', 'artist_id' ) );
	return $tbl;
}


function ap_lookup_album_simple_exact( $album_simple_name, $artist_name ) 
{
    $retVal = false;
    db_set_active('airplay_music');
    
    $query = "
        SELECT *  
        FROM album_simple 
        LEFT JOIN album ON album.album_simple_id = album_simple.album_simple_id 
        LEFT JOIN artist ON artist.artist_id = album_simple.artist_id 
        LEFT JOIN genre ON album.genre_id = genre.genre_id 
        WHERE album_simple_name = '%s' AND artist_name = '%s' 
    ";
    $query_result =  db_query($query, array($album_simple_name, $artist_name) );
    if ( $artist_name != "" && $query_result ) {
        $retVal = db_fetch_object($query_result);
    }
    db_set_active('default');
    return $retVal;
}

