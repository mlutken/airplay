<?php 

/**
* Get Specific media_Format ids and names
*/
function airplay_price_agent_get_media_format_names()
{
	$aMediaFormats = array( array( "media_format_id" => 5, "media_format_name" => "CD"), array( "media_format_id" => 3, "media_format_name" => "MP3" ),
	array( "media_format_id" => 7, "media_format_name" => "Vinyl" ), array( "media_format_id" => 16, "media_format_name" => "FLAC"),
	array( "media_format_id" => 8, "media_format_name" => "DVD" ), array( "media_format_id" => 10, "media_format_name" => "Blu-ray" ) );
	return $aMediaFormats;
	/*$aAgents = array();
	$sQuery = "SELECT media_format_id, media_format_name FROM media_format WHERE media_format_id IN ( 3, 5, 7, 8, 10, 16 )";
	db_set_active('airplay_music_v1');
	$query_result = db_query($sQuery, array());
	while ( $a = $query_result->fetchAssoc() ) {
		$aAgents[] = $a;
	} 
	db_set_active('default');
	return $aAgents;*/
}

/**
* Test if an agent is ok and then it saves the media_formats
*/
function airplay_price_agent_save_media_format_for_agent($agent_id, $media_format_id)
{
	$aAgents = array();
	$sQuery = "SELECT agent_media_format_rel_id FROM agent_media_format_rel WHERE agent_id = :agent_id AND media_format_id = :media_format_id";
	$aArgs = array ( ':agent_id' => $agent_id, ":media_format_id" => $media_format_id);
	db_set_active('airplay_music_v1');
	$query_result = db_query($sQuery, $aArgs);
	while ( $a = $query_result->fetchAssoc() ) {
		$aAgents[] = $a;
	} 
	if (count($aAgents) == 0) {
		$id = db_insert('agent_media_format_rel')->fields(array( 'agent_id' => $agent_id, 'media_format_id' => $media_format_id))->execute();
	} else {
	}
	db_set_active('default');
}

/**
* Get Media_Formats for an agent
*/
function airplay_price_agent_get_media_format_for_agent($agent_id)
{
	$aMediaFormats = array();
	$sQuery = "SELECT agent_media_format_rel_id, media_format_id FROM agent_media_format_rel WHERE agent_id = :agent_id";
	$aArgs = array ( ':agent_id' => $agent_id);
	db_set_active('airplay_music_v1');
	$query_result = db_query($sQuery, $aArgs);
	while ( $a = $query_result->fetchAssoc() ) {
		$aMediaFormats[] = $a;
	} 
	db_set_active('default');
	return $aMediaFormats;
}

/**
* Update timestamp_last_sent form agent_id
*/
function airplay_price_agent_update_agent_last_sent($agent_id) {
	db_set_active('airplay_music_v1');
	$sQuery = "UPDATE agent SET timestamp_last_sent = CURRENT_TIMESTAMP WHERE agent_id = :agent_id";
	$aArgs = array ( ':agent_id' => $agent_id );
	$query_result = db_query($sQuery, $aArgs);
	db_set_active('default');
}

/**
* Test if an ALBUM agent is ok and then it saves the agent or update it
*/
function airplay_price_agent_save_album_agent_for_artist($user_id, $artist_id, $item_base_id, $price)
{
	$id = 0;
	$aAgents = array();
	$sQuery = "SELECT agent_id FROM agent WHERE user_id = :user_id AND artist_id = :artist_id AND item_type = :item_type AND item_base_id = :item_base_id";
	$aArgs = array ( ':user_id' => $user_id, ":artist_id" => $artist_id, ":item_type" => 1, ":item_base_id" => $item_base_id);
	db_set_active('airplay_music_v1');
	$query_result = db_query($sQuery, $aArgs);
	while ( $a = $query_result->fetchAssoc() ) {
		$aAgents[] = $a;
		$id = $a["agent_id"];
	} 
	if (count($aAgents) == 0) {
		$id = db_insert('agent')->fields(array( 'user_id' => $user_id, 'artist_id' => $artist_id, 'item_base_id' => $item_base_id, 'item_type' => 1, 'max_price' => $price, 'currency_id' => 2, 'timestamp_updated' => date('Y-m-d G:i:s')))->execute();
	} else {
		$sQuery = "UPDATE agent SET timestamp_updated = :timestamp_updated, max_price = :max_price, currency_id = 2 WHERE user_id = :user_id AND artist_id = :artist_id AND item_type = :item_type";
		$aArgs = array ( ':user_id' => $user_id, ":artist_id" => $artist_id, ":item_type" => 1, ":max_price" => $price, ':timestamp_updated' => date('Y-m-d G:i:s'));
		$query_result = db_query($sQuery, $aArgs);
	}
	db_set_active('default');
	return $id;	
}

/**
*	Get agent data from an user id and artist_id and item_type.
*/
function airplay_price_agent_get_agent_from_user_and_artist($user_id, $artist_id, $item_type)
{
	$id = 0;
	$aAgents = array();
	$sQuery = "SELECT agent_id FROM agent WHERE user_id = :user_id AND artist_id = :artist_id AND item_type = :item_type";
	$aArgs = array ( ':user_id' => $user_id, ":artist_id" => $artist_id, ":item_type" => $item_type);
	db_set_active('airplay_music_v1');
	$query_result = db_query($sQuery, $aArgs);
	while ( $a = $query_result->fetchAssoc() ) {
		$aAgents[] = $a;
	} 
	return $aAgents;	
}


/**
* Test if an CONCERT agent is ok and then it saves the agent or update it
*/
function airplay_price_agent_save_concert_agent_for_artist($user_id, $artist_id)
{
	$id = 0;
	$aAgents = airplay_price_agent_get_agent_from_user_and_artist($user_id, $artist_id, 4);
	
	if (count($aAgents) == 0) {
		$id = db_insert('agent')->fields(array( 'user_id' => $user_id, 'artist_id' => $artist_id, 'item_base_id' => 0, 'currency_id' => 2, 'item_type' => 4, 'timestamp_updated' => date('Y-m-d G:i:s')))->execute();
	} else {
		$sQuery = "UPDATE agent SET timestamp_updated = :timestamp_updated, currency_id = 2 WHERE user_id = :user_id AND artist_id = :artist_id AND item_type = :item_type";
		$aArgs = array ( ':user_id' => $user_id, ":artist_id" => $artist_id, ":item_type" => 4, ':timestamp_updated' => date('Y-m-d G:i:s'));
		$query_result = db_query($sQuery, $aArgs);
	}
	db_set_active('default');
	return $id;	
}

/**
* Get user_settings data for a specific user - that has validated mail....
*/
function airplay_price_agent_get_user_settings_data($user_id) {
	$aUserSettings = array();
	$sQuery = "SELECT firstname, lastname, mail, mail_approved FROM user_settings WHERE user_id = :user_id AND mail_approved = 1";
	$aArgs = array ( ":user_id" => $user_id);
	db_set_active('airplay_music_v1');
	$query_result = db_query($sQuery, $aArgs);
	while ( $a = $query_result->fetchAssoc() ) {
		$aUserSettings[] = $a;
	} 
	db_set_active('default');
	return $aUserSettings;
}


/**
* Get item_price_data for an agent
*/
function airplay_price_agent_get_data_for_init_mail_for_concerts($artist_id, $media_format_ids) {

	$aConcerts = array();
	if (stristr($media_format_ids, ",")) {
		$sQuery = "SELECT artist_name, item_price_name, item_event_time, CEIL(price_local * currency_to_euro.to_euro * currency.from_euro) as price_local, 
		IF (item_event_date = '0000-00-00', IF (record_store_event_date_text = '', '0000-00-00', SUBSTR(record_store_event_date_text, 1, 10)), item_event_date) AS item_event_date
		FROM item_price
		INNER JOIN artist ON artist.artist_id = item_price.artist_id
		INNER JOIN record_store ON record_store.record_store_id = item_price.record_store_id
		INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id
		INNER JOIN currency ON currency.currency_id = 2
		WHERE artist.artist_id = :artist_id AND item_price.item_type = 4 AND
		(item_event_date >= CURDATE() OR (item_event_date = '0000-00-00' AND item_price.timestamp_updated >= DATE_ADD(now(), INTERVAL -10 DAY))) 
		ORDER BY item_event_date ASC, item_event_time ASC";
		$aArgs = array ( ":artist_id" => $artist_id);
	} else {
		$sQuery = "SELECT artist_name, item_price_name, item_event_time, CEIL(price_local * currency_to_euro.to_euro * currency.from_euro) as price_local, item_event_date
		FROM item_price
		INNER JOIN artist ON artist.artist_id = item_price.artist_id
		INNER JOIN record_store ON record_store.record_store_id = item_price.record_store_id
		INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id
		INNER JOIN currency ON currency.currency_id = 2
		WHERE artist.artist_id = :artist_id AND item_price.item_type = 4 AND media_format_id IN ( :media_format_ids ) AND
		(item_event_date >= CURDATE() OR (item_event_date = '0000-00-00' AND item_price.timestamp_updated >= DATE_ADD(now(), INTERVAL -10 DAY))) 
		ORDER BY item_event_date ASC, item_event_time ASC";
		$aArgs = array ( ":artist_id" => $artist_id, ":media_format_ids" => $media_format_ids);
	}
	db_set_active('airplay_music_v1');
	$query_result = db_query($sQuery, $aArgs);
	while ( $a = $query_result->fetchAssoc() ) {
		$aConcerts[] = $a;
	}
	db_set_active('default');
	return $aConcerts;	
};

/**
* Get agent data form an agent_id and user_id (to make sure user can not hijack other users agents.
*/

function airplay_price_agent_get_agent_data($user_id, $agent_id) {
	$aUserAgent = array();
	$sQuery = "SELECT agent_id, item_type, artist_id, item_base_id, max_price, currency_id FROM agent WHERE user_id = :user_id AND agent_id = :agent_id";
	$aArgs = array ( ":user_id" => $user_id, ":agent_id" => $agent_id );
	db_set_active('airplay_music_v1');
	$query_result = db_query($sQuery, $aArgs);
	while ( $a = $query_result->fetchAssoc() ) {
		$aUserAgent[] = $a;
	} 
	db_set_active('default');
	return $aUserAgent;
}

/**
* Test if an user has validated the mail address
*/
function airplay_price_agent_has_validated_mail($user_id) {
	$validated_mail = 0;
	$aAgents = array();
	$sQuery = "SELECT mail_approved FROM user_settings WHERE user_id = :user_id";
	$aArgs = array ( ':user_id' => $user_id );
	db_set_active('airplay_music_v1');
	$query_result = db_query($sQuery, $aArgs);
	while ( $a = $query_result->fetchAssoc() ) {
		if ($a["mail_approved"] == 1) {
			$validated_mail = 1;
		}
	} 
	db_set_active('default');
	return $validated_mail;
}

/**
* Delete all items in agent for a specific agent_id
*/
function airplay_price_agent_delete_agent($agent_id) {
	$sQuery = "DELETE FROM agent WHERE agent_id = :agent_id";
	$aArgs = array ( ":agent_id" => $agent_id );
	db_set_active('airplay_music_v1');
	$query_result = db_query($sQuery, $aArgs);
	db_set_active('default');
}

/**
* Delete all items in agent_sent_log for a specific agent_id
*/
function airplay_price_agent_delete_agent_log($agent_id) {
	$sQuery = "DELETE FROM agent_sent_log WHERE agent_id = :agent_id";
	$aArgs = array ( ":agent_id" => $agent_id );
	db_set_active('airplay_music_v1');
	$query_result = db_query($sQuery, $aArgs);
	db_set_active('default');
}

/**
* Delete all items in agent_media_format_rel for a specific agent_id
*/
function airplay_price_agent_delete_agent_media_format_rel($agent_id) {
	$sQuery = "DELETE FROM agent_media_format_rel WHERE agent_id = :agent_id";
	$aArgs = array ( ":agent_id" => $agent_id );
	db_set_active('airplay_music_v1');
	$query_result = db_query($sQuery, $aArgs);
	db_set_active('default');
}

/**
* Delete all items in agent_queue for a specific agent_id
*/
function airplay_price_agent_delete_agent_queue($agent_id) {
	$sQuery = "DELETE FROM agent_queue WHERE agent_id = :agent_id";
	$aArgs = array ( ":agent_id" => $agent_id );
	db_set_active('airplay_music_v1');
	$query_result = db_query($sQuery, $aArgs);
	db_set_active('default');
}
