<?php 
// $Id$


function ap_insertOrUpdate__artist ( &$aTitles )
{
	$tbl 		= ap_get_tbl__artist ();
	$synonym	= ap_get_tbl__artist_synonym ();
	foreach ( $aTitles as &$aTitle ) 
	{
		if ( $aTitle['album_artist_name'] != "" && $aTitle['data_record_type'] == 'song') {
			$artist_name = $aTitle['artist_name'];	// Save artist_name
			$aTitle['artist_name'] = $aTitle['album_artist_name'];
			ap_insertOrUpdate__one_artist( $aTitle, $tbl, $synonym );
			$aTitle['album_artist_id'] = $aTitle['artist_id'];
			$aTitle['artist_name'] = $artist_name;	// Restore artist_name
		}
		// Normal lookup
		ap_insertOrUpdate__one_artist( $aTitle, $tbl, $synonym );
	}
	$tbl = null;
	$synonym = null;
}


function ap_insertOrUpdate__one_artist( &$aTitle, $tbl, $synonym)
{
	ap_cleanUpArtistName( $aTitle );
	$tbl->lookupTitleId( $aTitle );
	$bFoundArtist = false;
	$bInsertSynonym = false;
	if ( $aTitle['artist_id'] != 0 ) $bFoundArtist = true;
	else  {
		//drush_print  ( "lookup synonym for: {$aTitle['artist_name']}" );
		$aTitle['artist_synonym_name'] = $aTitle['artist_name'];
		$synonym->lookupTitleId( $aTitle );
		if ( $aTitle['artist_id'] != 0 ) {
			//drush_print  ( "Found synonym" );
			////dfb ("Found synonym");
			$bFoundArtist = true;
		}
		else {
			//drush_print  ( "Auto synonym" );
			// --- Auto-synonym: --- 
			// If artist name contains one ',' (eg. "Turner, Tina") we reverse the name 
			// and try to look up the reversed ( eg. "Tina Turner").
			// If name found we have or artist_id and does not need to insert a new record 
			// In the artist table. If not found we keep $bFoundArtist at false, so a new 
			// record is inserted below .
			// In both cases we add the original name with ',' in the synonym table, so 
			// next time it is found there instead.
			$sArtistNameReversed = ap_tryReverseName( $aTitle['artist_name'] );
			////dfb ("Try reversing name: $sArtistNameReversed");
			if ( $aTitle['artist_name'] != $sArtistNameReversed ) {
				//$aTitle['artist_synonym_name'] = $aTitle['artist_name']; // Already done!
				$bInsertSynonym = true;
				
				// --- Now we try looking up the reversed artist name in the artist table ---
				$aTitle['artist_name'] = $sArtistNameReversed;
				$tbl->lookupTitleId( $aTitle );
				if ( $aTitle['artist_id'] != 0 ) {
					////dfb ("Found artist reversed");
					$bFoundArtist = true;
				}
			}
		}
	}
	
	$sDbgArtistName = $aTitle['artist_name'];
	////dfb ( $aTitle, "Artist '$sDbgArtistName' found: '$bFoundArtist'" );
	if ( !$bFoundArtist ) {
		////dfb ("Insert new artist: {$aTitle['artist_name']}");
		$tbl->insertNewTitle( $aTitle );
	}
	if ( $bInsertSynonym ) {
		// Insert 'original' (non reversed ) artist name in the synonym table
		////dfb ("Insert synonym: {$aTitle['artist_synonym_name']}");
		$synonym->insertNewTitle( $aTitle );
	}
	$tbl->updateTitleByIndex( $aTitle );

}

function ap_get_tbl__artist ( )
{
	$tbl = new AirplayTable( 'artist' );	
	return $tbl;
}

function ap_get_tbl__artist_synonym ( )
{
	$synonym = new AirplayTable( 'artist_synonym' );	
	$synonym->optionSet ( 'assign_to_names', array( 'artist_synonym_id', 'artist_id' ) );
	return $synonym;
}


function ap_cleanUpArtistName( &$aTitle )
{
	$artist_name = trim( $aTitle['artist_name'] );
	$a = explode( '/', $artist_name );
	if ( count($a) > 1 ) {
		$artist_name = trim( $a[0] );
		for ( $i = 1 ; $i < count($a); $i++ ) {
			$artist_name .= " / " . trim( $a[$i] );
		}
	}
	$aTitle['artist_name'] = $artist_name;
}


/// If the artist name is written 'surname,first name'. Like eg. (Turner, Tina),  
/// We try reversing the 
function ap_tryReverseName( $sArtistNameOrig )
{
	$sArtistNameReversed = $sArtistNameOrig;
	$a = explode( ',', $sArtistNameOrig );
	if ( count($a) == 2 ) {
		$first 	= trim ( $a[1] );
		$last 	= trim ( $a[0] );
		$a = explode( '/', $first );
		if ( count($a) == 1 ) {
			$sArtistNameReversed = $first . " " . $last;	
		}
		else {
			$sArtistNameReversed = trim( $a[0] ) . " " . $last;
			for ( $i = 1 ; $i < count($a); $i++ ) {
				$sArtistNameReversed .= " / " . trim( $a[$i] );
			}
		}
	}
	return $sArtistNameReversed; 
}

function airplay_base_lookup_artist_exact( $sArtistName ) 
{
	$retVal = false;
	db_set_active('airplay_music');
	
	$query = "
		SELECT artist_id, artist_name, artist_url, genre_name FROM 
		{artist} 
		LEFT JOIN {genre} ON artist.genre_id = genre.genre_id 
		WHERE artist_name = '%s' 
		";
	$query_result =  db_query($query, $sArtistName );
	if ( $artist = db_fetch_object($query_result)) {
		$retVal = $artist;
	}
	db_set_active('default');
	return $retVal;
}


function airplay_base_lookup_artists( $aArtistNames ) 
{
	db_set_active('airplay_music');
	$aArtistIDs = array();
	$query = "SELECT artist_id, artist_name FROM {artist} WHERE artist_name = '%s'";
	foreach ( $aArtistNames as $sArtistName ) {
		$query_result = db_query($query, $sArtistName );
		
		if ( $a = db_fetch_object($query_result)) {
			$aArtistIDs[] = array( $a->artist_id, $a->artist_name, $sArtistName );
		}
	}
	db_set_active('default');
	return $aArtistIDs;
}




