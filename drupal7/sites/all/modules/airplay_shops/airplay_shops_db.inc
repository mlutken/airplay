<?php

// http://www.itsalif.info/content/mysql-query-mutivalued-bitwise-performance

function airplay_shop_get_data($record_store_name) {
	$aArgs = array(":record_store_name" => $record_store_name);
    $sQuery = "SELECT record_store.record_store_id, record_store_name, record_store_description, record_store_type_id, selling_type_id, is_in_ap_results, record_store_logo,
	GROUP_CONCAT( DISTINCT IF(record_store_media_format_rel.media_format_id < 64, media_format_name, 'Merchandise')) AS media_format_names
	FROM record_store
	INNER JOIN record_store_media_format_rel ON record_store_media_format_rel.record_store_id = record_store.record_store_id
	INNER JOIN media_format ON record_store_media_format_rel.media_format_id = media_format.media_format_id
	WHERE is_in_record_store_guide = 1 AND record_store_type_id > 0 AND record_store_name = :record_store_name";
	
    $saveDB = db_set_active('airplay_music_v1');
    $query_result = db_query($sQuery, $aArgs );
	
	$aAllRowsItems = array();
    while ( $o = $query_result->fetchAssoc() ) {
		$aAllRowsItems[] = $o;
    } 
    db_set_active($saveDB);
	
	return $aAllRowsItems;
}

function airplay_shops_get_data() {
	$aArgs = array();
	// SQL for media format selected
	if (isset($_REQUEST["smf"])) {
		$sQuery = "SELECT country_name, record_store_name, record_store_url, record_store_logo, is_in_ap_results, record_store_type_id, selling_type_id
 , 		GROUP_CONCAT( DISTINCT IF(record_store_media_format_rel.media_format_id < 64, media_format_name, 'Merchandise')) AS media_format_names
		FROM record_store
		INNER JOIN country ON country.country_id = record_store.country_id
		INNER JOIN record_store_media_format_rel ON record_store_media_format_rel.record_store_id = record_store.record_store_id
		INNER JOIN media_format ON record_store_media_format_rel.media_format_id = media_format.media_format_id, (
			SELECT media_format_id, Res.record_store_id FROM record_store_media_format_rel, 
				( SELECT record_store_id FROM record_store_media_format_rel ";
				if (isset($_REQUEST["smf"]) && $_REQUEST["smf"] == 64 && is_numeric($_REQUEST["smf"])) {
					$sQuery .= "	WHERE media_format_id BETWEEN 64 AND 128 ";
				} else if (isset($_REQUEST["smf"]) && $_REQUEST["smf"] < 64 && is_numeric($_REQUEST["smf"])) {
					$sQuery .= "	WHERE media_format_id = " . $_REQUEST["smf"];
				}
		$sQuery .= "	
				) AS Res
			WHERE record_store_media_format_rel.record_store_id = Res.record_store_id
			) AS Res2 
		WHERE is_in_record_store_guide = 1 AND record_store_type_id > 0 AND Res2.record_store_id = record_store.record_store_id
		GROUP BY record_store_name 
		ORDER BY record_store_name ";
	} else {
		$sQuery = "SELECT country_name, record_store_name, record_store_url, record_store_logo, is_in_ap_results, record_store_type_id, selling_type_id, 
		GROUP_CONCAT( DISTINCT IF(record_store_media_format_rel.media_format_id < 64, media_format_name, 'Merchandise')) AS media_format_names
		FROM record_store 
		INNER JOIN country ON country.country_id = record_store.country_id
		INNER JOIN record_store_media_format_rel ON record_store_media_format_rel.record_store_id = record_store.record_store_id
		INNER JOIN media_format ON record_store_media_format_rel.media_format_id = media_format.media_format_id
		WHERE is_in_record_store_guide = 1 AND record_store_type_id > 0";
		if (isset($_REQUEST["sn"]) && $_REQUEST["sn"] != "ALL" && $_REQUEST["sn"] != "NUM") { $sQuery .= " AND SUBSTRING(record_store_name, 1, 1) = '" . $_REQUEST["sn"] . "'"; } else if (isset($_REQUEST["sn"]) && $_REQUEST["sn"] == "NUM") { $sQuery .= " AND SUBSTRING(record_store_name, 1, 1) BETWEEN 1 AND 9 "; }
		if (isset($_REQUEST["sc"]) && is_numeric($_REQUEST["sc"])) { $sQuery .= " AND record_store.country_id = " . $_REQUEST["sc"]; }
		//if (isset($_REQUEST["smf"]) && $_REQUEST["smf"] < 64) { $sQuery .= " AND media_format.media_format_id = " . $_REQUEST["smf"]; } else if (isset($_REQUEST["smf"]) && $_REQUEST["smf"] == 64) {
		//$sQuery .= " AND media_format.media_format_id > 63 AND media_format.media_format_id < 128 "; }
		if (isset($_REQUEST["st"]) && $_REQUEST["st"] != 0 && is_numeric($_REQUEST["st"])) { $sQuery .= " AND record_store_type_id = " . $_REQUEST["st"]; } // Bitwise - not optimal for relation table with large number of items.
		$sQuery .= " GROUP BY record_store_name ";
		$sQuery .= " ORDER BY record_store_name ";
	}
	
    /*$sQuery = "SELECT country_name, record_store_name, record_store_url, record_store_logo, is_in_ap_results, record_store_type_id, selling_type_id, 
	GROUP_CONCAT( DISTINCT IF(record_store_media_format_rel.media_format_id < 64, media_format_name, 'Merchandise')) AS media_format_names
	FROM record_store 
	INNER JOIN country ON country.country_id = record_store.country_id
	INNER JOIN record_store_media_format_rel ON record_store_media_format_rel.record_store_id = record_store.record_store_id
	INNER JOIN media_format ON record_store_media_format_rel.media_format_id = media_format.media_format_id
	WHERE is_in_record_store_guide = 1 AND record_store_type_id > 0";
	if (isset($_REQUEST["sn"]) && $_REQUEST["sn"] != "ALL" && $_REQUEST["sn"] != "NUM") { $sQuery .= " AND SUBSTRING(record_store_name, 1, 1) = '" . $_REQUEST["sn"] . "'"; } else if (isset($_REQUEST["sn"]) && $_REQUEST["sn"] == "NUM") { $sQuery .= " AND SUBSTRING(record_store_name, 1, 1) BETWEEN 1 AND 9 "; }
	if (isset($_REQUEST["sc"])) { $sQuery .= " AND record_store.country_id = " . $_REQUEST["sc"]; }
	if (isset($_REQUEST["smf"]) && $_REQUEST["smf"] < 64) { $sQuery .= " AND media_format.media_format_id = " . $_REQUEST["smf"]; } else if (isset($_REQUEST["smf"]) && $_REQUEST["smf"] == 64) {
	$sQuery .= " AND media_format.media_format_id > 63 AND media_format.media_format_id < 128 "; }
	if (isset($_REQUEST["st"]) && $_REQUEST["st"] != 0) { $sQuery .= " AND record_store_type_id = " . $_REQUEST["st"]; } // Bitwise - not optimal for relation table with large number of items.
	//WHERE (UserInterests & 28) != 0  // 28 = (4 | 8 | 16)
	$sQuery .= " GROUP BY record_store_name ";
	$sQuery .= " ORDER BY record_store_name ";
	// selling_type_id = 1, 2, 3*/
	
    $saveDB = db_set_active('airplay_music_v1');
    $query_result = db_query($sQuery, $aArgs );
	
	$aAllRowsItems = array();
    while ( $o = $query_result->fetchAssoc() ) {
		$aAllRowsItems[] = $o;
    }
	
    db_set_active($saveDB);
	
	return $aAllRowsItems;
}

function airplay_shops_get_record_store_names_data() {
	$aArgs = array();
    $sQuery = "SELECT record_store_name
	FROM record_store 
	INNER JOIN record_store_media_format_rel ON record_store_media_format_rel.record_store_id = record_store.record_store_id
	WHERE is_in_record_store_guide = 1 AND record_store_type_id > 0";
	$sQuery .= " ORDER BY record_store_name ";
    $saveDB = db_set_active('airplay_music_v1');
    $query_result = db_query($sQuery, $aArgs );
	
	$aAllRowsItems = array();
    while ( $o = $query_result->fetchAssoc() ) {
		$aAllRowsItems[] = $o;
    }
	
    db_set_active($saveDB);
	
	return $aAllRowsItems;
}


function airplay_shops_get_country_data() {
	$aArgs = array();
    $sQuery = "SELECT country_name, country.country_id
	FROM country
	LEFT JOIN record_store ON record_store.country_id = country.country_id
	WHERE country.country_id > 0 AND is_in_record_store_guide = 1 AND record_store_type_id > 0
	GROUP BY country.country_id
	ORDER BY country_name ASC ";
	
    $saveDB = db_set_active('airplay_music_v1');
    $query_result = db_query($sQuery, $aArgs );
	
	$aAllRowsItems = array();
    while ( $o = $query_result->fetchAssoc() ) {
		$aAllRowsItems[] = $o;
    } 
    db_set_active($saveDB);
	
	return $aAllRowsItems;
}

function airplay_shops_get_media_format_data() {
	$aArgs = array();
    $sQuery = "SELECT media_format_name, media_format_id,
	IF (media_format_id < 63, 1, 2) AS type
	FROM media_format
	WHERE media_format_id NOT IN ( 0, 2, 6, 9, 11, 13 ) AND media_format_id < 64
	ORDER BY type, media_format_name ASC ";
	
    $saveDB = db_set_active('airplay_music_v1');
    $query_result = db_query($sQuery, $aArgs );
	
	$aAllRowsItems = array();
    while ( $o = $query_result->fetchAssoc() ) {
		$aAllRowsItems[] = $o;
    } 
    db_set_active($saveDB);
	
	return $aAllRowsItems;
}


function airplay_shops_get_record_store_type_data() {
	$aAllRowsItems = array();
	$aAllRowsItems[] = array( "record_store_type_id" => 2,  "type_name" => "Webshop");
	$aAllRowsItems[] = array( "record_store_type_id" => 4,  "type_name" => "Fysisk butik");
	$aAllRowsItems[] = array( "record_store_type_id" => 8,  "type_name" => "Fysisk butik + Webshop");
	$aAllRowsItems[] = array( "record_store_type_id" => 0,  "type_name" => "Alle typer");
	return $aAllRowsItems;
}