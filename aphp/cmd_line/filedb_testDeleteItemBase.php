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
$baseDir		= $argv[1];
$artist_name 	= $argv[2];
$item_base_name	= $argv[3];
$item_type		= $argv[4];

printf ("baseDir			: '%s'\n"   , $baseDir );
printf ("artist_name		: '$artist_name'\n" );
printf ("item_base_name   	: '$item_base_name'\n");
printf ("item_type   		: '$item_type'\n");


function testDeleteItemBase_1( $ad, $artist_name, $item_base_name, $item_type )
{
	$artist_id 		= nameToID($artist_name);
	$item_base_id 	= nameToID($item_base_name);
	$bExists = $ad->openForWriteFromID($artist_id, null);
	if ( !$bExists ) {
		printf("Unknown artist: '$artist_name'\n");
		return;
	}
	
	$item_base_id = itemBaseNameToID( $item_base_name, $item_type );
	
	$ib;
	$bExists = $ad->openChildForWrite($item_base_id, $ib);
	if ( !$bExists ) {
		printf("Unknown item_base: '$item_base_name', '$item_base_id' \n");
		return;
	}
	$ad->eraseChild($ib);
	$ad->writeCurrent();
}


$ad = new ArtistDataFileDb($baseDir);

testDeleteItemBase_1($ad, $artist_name, $item_base_name, $item_type );


?>