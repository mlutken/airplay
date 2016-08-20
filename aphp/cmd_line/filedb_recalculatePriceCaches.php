<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('utils/general_utils.php');
require_once ('utils/string_utils.php');
require_once ('db_api/db_string_utils.php');
require_once ('db_manip/AllDbTables.php');
require_once ('db_manip/MusicDatabaseFactory.php');
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


function testUpdateArtist_5($ad, $artist_name, $cc)
{
	$artist_id = nameToID($artist_name);
	$bExists = $ad->openForWriteFromID($artist_id, null);
	if (!$bExists ) return;
	
	$aAllItemBaseIds = $ad->openAllChildrenForRead();
	$ad->recalculatePricesCache();
	
	$ad->writeCurrent();
	return count($aAllItemBaseIds) +1;
}


$fac = new MusicDatabaseFactory();

$cc = $fac->createDbInterface( 'CurrencyConvert' );
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