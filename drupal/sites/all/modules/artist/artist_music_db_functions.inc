<?php 
// $Id$


function artist_lookup_artist( $sArtistName ) {
	db_set_active('airplay_music');
	
// 	db_query('SELECT * FROM table_in_anotherdb');
	$query = "SELECT artist_id,	artist_name, artist_url, genre_id FROM " .
				"{artist} WHERE artist_name = '%s'";
	$query_result =  db_query($query, $sArtistName );
	
	$aArtistInfo = array();
	$aArtistInfo['NAME'] = "";
	
	if ( $artist = db_fetch_object($query_result)) {
		$aArtistInfo['NAME'] = $artist->artist_name;
		$aArtistInfo['URL_HOME_PAGE'] = $artist->artist_url;
	}
	
	db_set_active('default');
	return $aArtistInfo;
}





