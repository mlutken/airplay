<?php 
// $Id$



function ap_read_settings ( )
{
	$prev_active = db_set_active( 'airplay_music' );
	$aSettings = array();
	$sQuery = "SELECT * FROM {settings} WHERE 1 = 1";
	$queryResult =  db_query($sQuery, array() );
	while ( $a = $queryResult->fetchAssoc()) {
		$aSettings[$a['settings_name']] = $a['settings_value'];
	}
	db_set_active($prev_active);
	return $aSettings;
}

function ap_write_settings ( $aSettings )
{
	$prev_active = db_set_active( 'airplay_music' );
	foreach ( $aSettings as $sKey => $sValue ) {
        $sQuery = "
        INSERT INTO {settings} (settings_name, settings_value  )
        VALUES ( '$sKey', '$sValue' )
        ON DUPLICATE KEY UPDATE 
        settings_value = '$sValue'  
        ";
		db_query($sQuery, array() );
	}
	db_set_active($prev_active);
}

// //  $sQuery = "UPDATE {settings} 
// //  SET settings_value = '$sValue'  
// //  WHERE settings_name='$sKey' 
// //  ";

function ap_read_setting ( $sKey )
{
	$prev_active = db_set_active( 'airplay_music' );
	$aSettings = array();
	$sValue = false;
	$sQuery = "SELECT * FROM {settings} WHERE settings_name = :settings_name";
	$queryResult =  db_query( $sQuery, array( ':settings_name' => $sKey) );
	if ( $a = $queryResult->fetchAssoc()) {
		$sValue = $a['settings_value'];
	}
	db_set_active($prev_active);
	return $sValue;
}

function ap_write_setting ( $sKey, $sValue )
{
    $prev_active = db_set_active( 'airplay_music' );
    $sQuery = "
    INSERT INTO {settings} (settings_name, settings_value  )
    VALUES ( '$sKey', '$sValue' )
    ON DUPLICATE KEY UPDATE 
    settings_value = '$sValue'  
    ";
    db_query($sQuery, array() );
    
    db_set_active($prev_active);
}


