<?php 

function ap_user_settings_get_user_settings($user_id,  $email, $gender, $birthday, $firstname, $lastname) 
{
	$user_settings = array();
	if ($user_id != 0) {
		$sQuery = "SELECT gender, birthday, recieve_newsletter, mail_approved, user_country, mail, firstname, lastname FROM user_settings WHERE user_id = :user_id";
		$aArgs = array ( ':user_id' => $user_id);
		db_set_active('airplay_music_v1');
		$query_result = db_query($sQuery, $aArgs);
		while ( $a = $query_result->fetchAssoc() ) {
			$user_settings[] = $a;
		}
		// If user has no user_settings - then create one.
		if (count($user_settings) == 0) {
			$hash = md5(date("Y-m-d H:i:s"));
			//db_insert('user_settings')->fields(array( 'user_id' => $user_id ,  'gender' => $gender ,  'birthday' => $birthday, 'firstname' => $firstname, 'lastname' => $lastname, 'mail' => $email, 'recieve_newsletter' => 0, 'mail_approved' => 0, 'user_country' => 0, 'hash_timestamp_created' => md5($hash) ))->execute();
			db_insert('user_settings')->fields(array( 'user_id' => $user_id ,  'gender' => $gender,  'firstname' => $firstname, 'lastname' => $lastname, 'mail' => $email, 'hash_timestamp_created' => md5($hash) ))->execute();
			$sQuery = "SELECT gender, birthday, recieve_newsletter, mail_approved, user_country, mail, firstname, lastname FROM user_settings WHERE user_id = :user_id";
			$aArgs = array ( ':user_id' => $user_id);
			$query_result = db_query($sQuery, $aArgs);
			while ( $a = $query_result->fetchAssoc() ) {
				$user_settings[] = $a;
			}
		}
		db_set_active('default');
	}
	return $user_settings;
}

function ap_user_settings_get_user_agents($user_id) 
{
	$aAgents = array();
	if ($user_id != 0) {
	//FROM agent INNER JOIN artist ON agent.artist_id = artist.artist_id INNER JOIN item_base ON agent.item_base_id = item_base.item_base_id 
		$sQuery = "SELECT agent.agent_id, agent.artist_id, artist_name, item_base_name, agent.item_type, image_url, item_base.item_base_id, agent_id
		FROM agent INNER JOIN artist ON agent.artist_id = artist.artist_id LEFT JOIN item_base ON agent.item_base_id = item_base.item_base_id 
		WHERE user_id = :user_id ORDER BY item_base.item_type ASC, artist_name ASC, item_base_name ASC";
		$aArgs = array ( ':user_id' => $user_id);
		db_set_active('airplay_music_v1');
		$query_result = db_query($sQuery, $aArgs);
		while ( $a = $query_result->fetchAssoc() ) {
			$aAgents[] = $a;
		} 
		db_set_active('default');
	}
	return $aAgents;
}

function ap_user_settings_update_user_agents_mail($user_id, $mail) 
{
	if ($user_id != 0) {
		$sQuery = "UPDATE user_settings SET mail = :mail, mail_approved = 0 WHERE user_id = :user_id";
		$aArgs = array ( ':mail' => $mail, ':user_id' => $user_id);
		db_set_active('airplay_music_v1');
		$query_result = db_query($sQuery, $aArgs);
		db_set_active('default');
	}
}


function ap_user_settings_update_user_agents_has_news_letter($user_id, $newsletter) 
{
	if ($user_id != 0) {
		$sQuery = "UPDATE user_settings SET recieve_newsletter = :newsletter WHERE user_id = :user_id";
		$aArgs = array ( ':newsletter' => $newsletter, ':user_id' => $user_id);
		db_set_active('airplay_music_v1');
		$query_result = db_query($sQuery, $aArgs);
		db_set_active('default');
	}
}

function ap_user_settings_update_user_agents_gender($user_id, $gender) 
{
	if ($user_id != 0) {
		$sQuery = "UPDATE user_settings SET gender = :gender WHERE user_id = :user_id";
		$aArgs = array ( ':gender' => $gender, ':user_id' => $user_id);
		db_set_active('airplay_music_v1');
		$query_result = db_query($sQuery, $aArgs);
		db_set_active('default');
	}
}

function ap_user_settings_update_user_agents_firstname($user_id, $firstname) 
{
	if ($user_id != 0) {
		$sQuery = "UPDATE user_settings SET firstname = :firstname WHERE user_id = :user_id";
		$aArgs = array ( ':firstname' => $firstname, ':user_id' => $user_id);
		db_set_active('airplay_music_v1');
		$query_result = db_query($sQuery, $aArgs);
		db_set_active('default');
	}
}

function ap_user_settings_update_user_agents_lastname($user_id, $lastname) 
{
	if ($user_id != 0) {
		$sQuery = "UPDATE user_settings SET lastname = :lastname WHERE user_id = :user_id";
		$aArgs = array ( ':lastname' => $lastname, ':user_id' => $user_id);
		db_set_active('airplay_music_v1');
		$query_result = db_query($sQuery, $aArgs);
		db_set_active('default');
	}
}


function ap_user_settings_update_user_agents_birthday($user_id, $birthday) 
{
	//$query_result = 0;
	if ($user_id != 0) {
		$sQuery = "UPDATE user_settings SET birthday = :birthday WHERE user_id = :user_id";
		$aArgs = array ( ':birthday' => $birthday, ':user_id' => $user_id);
		db_set_active('airplay_music_v1');
		$query_result = db_query($sQuery, $aArgs);
		db_set_active('default');
	}
	//return $query_result;
}


function ap_user_settings_update_user_agents_country($user_id, $user_country) 
{
	if ($user_id != 0) {
		$sQuery = "UPDATE user_settings SET user_country = :user_country WHERE user_id = :user_id";
		$aArgs = array ( ':user_country' => $user_country, ':user_id' => $user_id);
		db_set_active('airplay_music_v1');
		$query_result = db_query($sQuery, $aArgs);
		db_set_active('default');
	}
}

function ap_user_settings_update_user_agents_fan($user_id, $artist_id, $fan_type_id) {
	$fans = array();
	if ($user_id != 0) {
		$sQuery = "SELECT user_fan_id FROM user_fan WHERE user_id = :user_id AND artist_id = :artist_id";
		$aArgs = array ( ':user_id' => $user_id, ":artist_id" => $artist_id);
		db_set_active('airplay_music_v1');
		$query_result = db_query($sQuery, $aArgs);
		while ( $a = $query_result->fetchAssoc() ) {
			$fans[] = $a;
		}
		// If user has no user_settings - then create one.
		if (count($fans) == 0) {
			$id = db_insert('user_fan')->fields(array( 'user_id' => $user_id ,  'artist_id' => $artist_id ,  'fan_type_id' => $fan_type_id ))->execute();
		} else {
			$id = db_update('user_fan')->where( 'user_id = :user_id AND artist_id = :artist_id',     array( ':user_id' => $user_id, ':artist_id' => $artist_id)   )->fields(array( 'fan_type_id' => $fan_type_id ))->execute();
		}
		db_set_active('default');
	}
	//return $user_settings;
}

function ap_user_settings_delete_user_agents_fan($user_id, $artist_id) {
	if ($user_id != 0) {
		$sQuery = "DELETE FROM user_fan WHERE user_id = :user_id AND artist_id = :artist_id";
		$aArgs = array ( ':user_id' => $user_id, ":artist_id" => $artist_id);
		db_set_active('airplay_music_v1');
		$query_result = db_query($sQuery, $aArgs);
		db_set_active('default');
	}
}


function airplay_user_settings_validate_mail($user_id, $email, $hash) {
	if ($user_id != 0) {
		$sQuery = "UPDATE user_settings SET mail_approved = 1 WHERE user_id = :user_id AND mail = :mail AND hash_timestamp_created = :hash";
		$aArgs = array ( ':user_id' => $user_id, ':mail' => $email, 'hash' => $hash);
		db_set_active('airplay_music_v1');
		$query_result = db_query($sQuery, $aArgs);
		db_set_active('default');
	}
}

function ap_user_settings_get_user_agents_fan($user_id) {
	$fans = array();
	if ($user_id != 0) {
		$sQuery = "SELECT user_fan.artist_id, artist.artist_name, fan_type_id FROM user_fan INNER JOIN artist ON user_fan.artist_id = artist.artist_id WHERE user_id = :user_id";
		$aArgs = array ( ':user_id' => $user_id );
		db_set_active('airplay_music_v1');
		$query_result = db_query($sQuery, $aArgs);
		while ( $a = $query_result->fetchAssoc() ) {
			$fans[] = $a;
		}
		
		db_set_active('default');
	}
	return $fans;
}

function ap_user_settings_get_country_data() {
	$countries = array();
	$sQuery = "SELECT country_id, country_name FROM country WHERE country_id <> 0 ORDER BY country_name ASC";
	db_set_active('airplay_music_v1');
	$query_result = db_query($sQuery, array());
	while ( $a = $query_result->fetchAssoc() ) {
		$countries[] = $a;
	}
	db_set_active('default');
	return $countries;
}

