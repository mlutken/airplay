<?php 
// $Id$



function ap_insertOrUpdate__song_simple ( &$aTitles )
{
	$tblDefault 		= ap_get_tbl__song_simple__default() ;	
	foreach ( $aTitles as &$aTitle ) {
		$aTitle['song_simple_name'] = ap_simplify_song_name( $aTitle['song_name'], $aTitle["artist_name"] );
		if ( $aTitle['song_simple_name'] == "" ) continue;
		if ( $aTitle['artist_id'] == 0 			) continue;
		
		$aTitle['do_insert'][] = 'song_simple';
		$aTitle['do_update']['song'][] = 'song_simple_id';
 		if ( count( $aTitle['do_update']['buy_song'] ) > 0 )	{
			$aTitle['do_update']['buy_song'][] = 'song_simple_id';
 		}
		
		$tblDefault->lookupTitleId( $aTitle );
		$tblDefault->insertNewTitle( $aTitle );
		$tblDefault->updateTitleByIndex( $aTitle );
	}
	$tblDefault = null;
	$tblAlbumArtistName = null;
}


function ap_get_tbl__song_simple__default ( )
{
	$tbl = new AirplayTable( 'song_simple' );	
	$tbl->optionSet ( 'lookup_where', "WHERE song_simple_name = '%s' AND artist_id = %d" );
	$tbl->optionSet ( 'lookup_fields', array( 'song_simple_name', 'artist_id' ) );
	return $tbl;
}


function ap_lookup_song_simple_exact( $song_simple_name, $artist_name ) 
{
    $retVal = false;
    db_set_active('airplay_music');
    
    $query = "
        SELECT *  
        FROM song_simple 
        LEFT JOIN song ON song.song_simple_id = song_simple.song_simple_id 
        LEFT JOIN artist ON artist.artist_id = song_simple.artist_id 
        LEFT JOIN genre ON song.genre_id = genre.genre_id 
        WHERE song_simple_name = '%s' AND artist_name = '%s' 
    ";
    $query_result =  db_query($query, array($song_simple_name, $artist_name) );
    db_set_active('default');
    if ( $artist_name != "" && $query_result ) {
        $retVal = db_fetch_object($query_result);
    }
    return $retVal;
}

