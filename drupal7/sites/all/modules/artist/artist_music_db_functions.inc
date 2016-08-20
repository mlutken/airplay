<?php 
// $Id$


function artist_lookup_artist( $sArtistName ) {
	db_set_active('airplay_music');
	
	$query = "SELECT artist_id,	artist_name, artist_url, genre_id FROM " .
				"{artist} WHERE artist_name = :artist_name";
	$query_result =  db_query($query, array( ':artist_name' => $sArtistName) );
	
	$aArtistInfo = array();
	$aArtistInfo['NAME'] = "";
	
	if ( $artist = $query_result->fetchObject()) {
		$aArtistInfo['NAME'] = $artist->artist_name;
		$aArtistInfo['URL_HOME_PAGE'] = $artist->artist_url;
	}
	
	db_set_active('default');
	return $aArtistInfo;
}





