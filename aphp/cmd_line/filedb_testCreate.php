<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('utils/general_utils.php');
require_once ('utils/string_utils.php');
require_once ('db_api/db_string_utils.php');
require_once ('db_manip/AllDbTables.php');
require_once ('filedb_api/ArtistDataFileDb.php');
require_once ('filedb_api/ItemBaseDataFileDb.php');
require_once ('db_api/CurrencyDataMySql.php');
require_once ('db_api/CurrencyConvert.php');


//  time php cmd_line/filedb_test.php /home/airplay/filedb 1 40000 0
//  time php cmd_line/filedb_test.php /home/airplay/filedb 50000 40000 0

global $argv;

// // $artist_name = $argv[1];
$baseDir	= $argv[1];
$start 		= $argv[2];
$count 		= $argv[3];
// printf ("Artist name: '%s'\n"   , $artist_name );
printf ("baseDir: '%s'\n"   , $baseDir );
printf ("Start ID: '$start'\n" );
printf ("Count   : '$count'\n");

if ($argv[4] != '' ) {
	$g_fileDbPrettyJson    	= (bool)$argv[4];
}


function getArtistNamesFromMySql( $start, $count )
{
	global $g_MySqlPDO;
	$q = "SELECT artist_name FROM artist ORDER BY artist_id ASC LIMIT $start, $count";
	$aArtistNames = pdoQueryAllRowsFirstElem($g_MySqlPDO, $q, array() );
	return $aArtistNames;
}


function testCreateArtist_1($ad, $artist_name, $cc)
{
	$artist_id = nameToID($artist_name);
	$bExists = $ad->openForWriteFromID($artist_id, null);
	if ( !$bExists ) {
		printf("CREATE NEW: $artist_name\n");
		$aBaseData = array('artist_name' => $artist_name, 'artist_real_name' => 'Anders And' );
		$ad->createNew( $aBaseData, $artist_id, null );
	}
	
	$aBaseData = array('artist_name' => 'sddfdsf', 'artist_real_name' => 'Fætter Gufs', 'artist_subgenres' => 'dansk jul, funk', 'artist_reliability' => 9 );
// // 	$aBaseData = array('artist_name' => 'sddfdsf' );
	$ad->updateBaseDataCheckReliability($aBaseData);
 	//var_dump($ad->m_aAllData['base_data'] );
	$ad->m_aAllData['text']['da'] = array( 'artist_article' => 'Hej hej', 'artist_text_reliability' => 10 ); 
	$ad->writeCurrent();
	
	return 1;
}

function testCreateArtist_2($ad, $artist_name, $cc)
{
	$artist_id = nameToID($artist_name);
	$bExists = $ad->openForWriteFromID($artist_id, null);
	if ( !$bExists ) {
		printf("CREATE NEW ARTIST: '$artist_name'\n");
		$aBaseData = array('artist_name' => $artist_name, 'artist_real_name' => 'Anders And' );
		$ad->createNew( $aBaseData, $artist_id, null );
	}
	
// 	$aBaseData = array('artist_name' => $artist_name, 'artist_real_name' => 'Fætter Gufs', 'artist_subgenres' => 'dansk jul, funk', 'artist_reliability' => 9 );
// 	$ad->updateBaseDataCheckReliability($aBaseData);
// 	$ad->updateText ( 'da', "Hej med dig, jeg hedder Kaj. Kaj Kaj Kaj", 12 );
	
	// Add/Update ItemBase
	$aBaseData = array('artist_name' => $artist_name, 'item_base_name' => 'The long road', 'item_type' => 1
						, 'item_base_reliability' => 9, 'item_year' => 2006 );
	$item_base_id = itemBaseNameToID( $aBaseData['item_base_name'], $aBaseData['item_type'] );
	
	$ib;
	$bExists = $ad->openChildForWrite($item_base_id, $ib);
//	var_dump($ib->m_aAllData);
	if ( !$bExists ) {
		printf("CREATE NEW ITEM BASE: '{$aBaseData['item_base_name']}'\n");
		$ad->createNewChild( $ib, $aBaseData, $item_base_id );
	}
	$ib->updateBaseData($aBaseData);
// 	$ib->updateBaseDataCheckReliability($aBaseData);
	$ib->updateText ( 'da', "Hej med dig, jeg hedder Kaj. Kaj Kaj Kaj. Du bi du bi du bi dej.", 13 );
	
	
	$ad->writeCurrent();
	
	return 1;
}


$cc = new CurrencyConvert();
$cc->initialiseFromTableDb( new CurrencyDataMySql() );
$ad = new ArtistDataFileDb($baseDir, $cc);



printf("getArtistNamesFromMySql( $start, $count )\n");
$aArtistNames = getArtistNamesFromMySql( $start, $count );
$countArtists = count($aArtistNames);
printf("getArtistNamesFromMySql retreived %d number of names\n", $countArtists );
printf("Create/update artists\n");

$totalFiles = 0;
for ( $i = 0; $i < $countArtists; ++$i) {
	$artist_name = $aArtistNames[$i];
	$totalFiles += testCreateArtist_2($ad, $artist_name, $cc );
	if ($i % 100 == 0) printf("Artist[%d] (%d): '%s'\n", $i+1, $totalFiles, $artist_name);
}
// var_dump($aArtistNames);

?>