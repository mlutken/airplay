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

//printf ("filedb_test\n");

function getArtistNamesFromMySql( $start, $count )
{
	global $g_MySqlPDO;
	$q = "SELECT artist_name FROM artist ORDER BY artist_id ASC LIMIT $start, $count";
	$aArtistNames = pdoQueryAllRowsFirstElem($g_MySqlPDO, $q, array() );
	return $aArtistNames;
}

function testUpdateArtist_1($ad, $artist_name, $cc )
{
	$artist_id = nameToID($artist_name);
	$aAllData = $ad->getAllData($artist_id);

	$count = (int)$aAllData['data']['spd_test_count'];
	$aAllData['data']['spd_test_count'] = $count +1;;
	// var_dump($aAllData);
// // 	printf("count: $count\n");


	$ad->writeAllData($artist_id, $aAllData);
}


function testUpdateArtist_2($ad, $artist_name, $cc )
{
	$artist_id = nameToID($artist_name);
	$ad->openForWriteFromID($artist_id, null);

	$count = (int)$ad->m_aAllData['base_data']['spd_test_count'];
	$ad->m_aAllData['base_data']['spd_test_count'] = $count +1;;
	if ( "Shaun Bartlett" == $artist_name) {
		$item_base_id = itemBaseNameToID('Dragon On A Plane', 2 );
// // 	 	printf("item_base_id: $item_base_id\n");
	 	$ib = $ad->openChildForWrite( $item_base_id );
	 	
		$count = (int)$ib->m_aAllData['base_data']['spd_test_count'];
		$ib->m_aAllData['base_data']['spd_test_count'] = $count +1;
		
	}
	
	
	// var_dump($aAllData);
// // 	printf("count: $count\n");

	$ad->writeCurrent();
}



function testUpdateArtist_3($ad, $artist_name, $cc )
{
	$artist_id = nameToID($artist_name);
	$ad->openForWriteFromID($artist_id, null);

	$count = (int)$ad->m_aAllData['base_data']['spd_test_count'];
	$ad->m_aAllData['base_data']['spd_test_count'] = $count +1;
	
	
	if ( "Shaun Bartlett" == $artist_name) {
		$item_base_id = itemBaseNameToID('Dragon On A Plane', 2 );
// // 	 	printf("item_base_id: $item_base_id\n");
// 	 	$ib = $ad->openChildForWrite( $item_base_id );
	 	

		$aAllItemBaseIds = $ad->openAllChildrenForWrite();
// 		var_dump($aAllItemBaseIds);
		var_dump(count($ad->m_openChilds));
		foreach ( $ad->m_openChilds as $ib ) {
			printf("ib: %d,  %s\n", $ib->m_aAllData['base_data']['item_time'], $ib->m_aAllData['base_data']['item_base_name'] );
			$count = (int)$ib->m_aAllData['base_data']['spd_test_count'];
			$ib->m_aAllData['base_data']['spd_test_count'] = $count +1;
		}
	}
	
	
	// var_dump($aAllData);
// // 	printf("count: $count\n");

	$ad->writeCurrent();
}



function testUpdateArtist_4($ad, $artist_name, $cc )
{
	$artist_id = nameToID($artist_name);
	$ad->openForWriteFromID($artist_id, null);

	$count = (int)$ad->m_aAllData['base_data']['spd_test_count'];
	$ad->m_aAllData['base_data']['spd_test_count'] = $count +1;
	
	$aAllItemBaseIds = $ad->openAllChildrenForWrite();
	foreach ( $ad->m_openChilds as $ib ) {
		$count = (int)$ib->m_aAllData['base_data']['spd_test_count'];
		$ib->m_aAllData['base_data']['spd_test_count'] = $count +1;
	}
	
	$ad->writeCurrent();
	return count($aAllItemBaseIds) +1;
}

function testUpdateArtist_5($ad, $artist_name, $cc)
{
	$artist_id = nameToID($artist_name);
	$ad->openForWriteFromID($artist_id, null);

	$count = (int)$ad->m_aAllData['base_data']['spd_test_count'];
	$ad->m_aAllData['base_data']['spd_test_count'] = $count +1;
	
	$aAllItemBaseIds = $ad->openAllChildrenForRead();
//	$aAllItemBaseIds = $ad->openAllChildrenForWrite();
	$ad->recalculatePricesCache();
	
	$ad->writeCurrent();
	return count($aAllItemBaseIds) +1;
}

function testUpdateArtist_6($ad, $artist_name, $cc)
{
	$artist_id = nameToID($artist_name);
	$ad->openForWriteFromID($artist_id, null);

	$count = (int)$ad->m_aAllData['base_data']['spd_test_count'];
	$ad->m_aAllData['base_data']['spd_test_count'] = $count +1;
	
	$aAllItemBaseIds = $ad->openAllChildrenForRead();
	$ad->recalculatePricesCache();
	
	$ad->writeCurrentToBaseDir('/home/ml/_filedb');
	$ad->erase();
	$ad->writeCurrent();
	return count($aAllItemBaseIds) +1;
}


$cc = new CurrencyConvert();
$cc->initialiseFromTableDb( new CurrencyDataMySql() );
$ad = new ArtistDataFileDb($baseDir, $cc);



printf("getArtistNamesFromMySql( $start, $count )\n");
$aArtistNames = getArtistNamesFromMySql( $start, $count );
$countArtists = count($aArtistNames);
printf("getArtistNamesFromMySql retreived %d number of names\n", $countArtists );
printf("Update all artists\n");

$totalFiles = 0;
for ( $i = 0; $i < $countArtists; $i++) {
	$artist_name = $aArtistNames[$i];
	$totalFiles += testUpdateArtist_5($ad, $artist_name, $cc );
	if ($i % 100 == 0) printf("Artist[%d] (%d): '%s'\n", $i, $totalFiles, $artist_name);
}
// var_dump($aArtistNames);

?>