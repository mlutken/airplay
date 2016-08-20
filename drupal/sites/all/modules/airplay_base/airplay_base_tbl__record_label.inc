<?php 
// $Id$


function ap_insertOrUpdate__record_label ( &$aTitles )
{
	$tbl = ap_get_tbl__record_label ();	
	$tbl->insertOrUpdateSimple( $aTitles );
	$tbl = null;
}


function ap_get_tbl__record_label ( )
{
	$tbl = new AirplayTable( 'record_label' );	
	return $tbl;
}

/*
// Insert record_label_id's into an array of titles (songs/albums) 
function airplay_assign_record_label_ids( &$aTitles ) 
{
	airplay_insert_new_record_labels($aTitles);	// First create/insert new stores into DB.
	
	$sQuery = "
		SELECT record_label_name, record_label_id FROM {record_label} 
		WHERE record_label_name = '%s'";
	
	db_set_active('airplay_music');
	foreach ( $aTitles as &$aTitle ) {
		$queryResult =  db_query($sQuery, $aTitle['record_label_name'] );
		if ( $a = db_fetch_object($queryResult)) {
			$aTitle['record_label_id'] = $a->record_label_id;
		}
	}
	db_set_active('default');
}


//Insert new record stores from array of titles into DB. Existing records 
//are left untouched.
//\see http://www.xaprb.com/blog/2005/09/25/insert-if-not-exists-queries-in-mysql/ 
function airplay_insert_new_record_labels( $aTitles )
{
	db_set_active('airplay_music');
	
	$sQuery = "
		INSERT INTO record_label(record_label_name, record_label_url)
		SELECT '%s', '%s'
		FROM integers
			LEFT OUTER JOIN record_label
				ON record_label.record_label_name = '%s'
		WHERE integers.i = 1 and record_label.record_label_name is null;
	";
	
	foreach ( $aTitles as $aTitle ) {
		$record_label_name = $aTitle['record_label_name'];
		if ( $record_label_name == "" )	continue;
		$queryResult = db_query($sQuery, $record_label_name, $aTitle['record_label_url'], $record_label_name );
	}
	db_set_active('default');
}



//Get a php array that can translate a record_label_name to 
//a record_label_id. 
function airplay_record_label_name_to_id_array() 
{
	static $aNameToID = null;
	if ( $aNameToID == null ) {
		$aNameToID = array();
		db_set_active('airplay_music');
		$query = "SELECT record_label_name, record_label_id FROM {record_label}";
		$queryResult =  db_query($query);
		while ( $a = db_fetch_object($queryResult)) {
			$aNameToID[$a->record_label_name]= $a->record_label_id;
		}
		db_set_active('default');
	}
	return $aNameToID;
}

*/

