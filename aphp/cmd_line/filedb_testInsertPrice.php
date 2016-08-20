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
$item_price_name= $argv[3];
$item_type		= $argv[4];

printf ("baseDir			: '%s'\n"   , $baseDir );
printf ("artist_name	    : '$artist_name'\n" );
printf ("item_price_name   	: '$item_price_name'\n");
printf ("item_type   		: '$item_type'\n");


function testInserItemPrice_1( $ad, $aBaseData, $cc	 )
{
	$artist_name = $aBaseData['artist_name'];
	$artist_id = nameToID($artist_name);
	if ( '' $artist_id )
	$bExists = $ad->openForWriteFromID($artist_id, null);
	if ( !$bExists ) {
		printf("CREATE NEW ARTIST: '$artist_name'\n");
		$ad->createNew( $aBaseData, $artist_id, null );
	}
	
 	$ad->updateBaseDataCheckReliability($aBaseData);
	$item_base_id = itemBaseNameToID( $aBaseData['item_base_name'], $aBaseData['item_type'] );
	
	$ib;
	$bExists = $ad->openChildForWrite($item_base_id, $ib);
//	var_dump($ib->m_aAllData);
	if ( !$bExists ) {
// 	if ( true ) {
		printf("CREATE NEW ITEM BASE: '{$aBaseData['item_base_name']}'\n");
		$ad->createNewChild( $ib, $aBaseData, $item_base_id );
	}
 	$ib->updateBaseDataCheckReliability($aBaseData);
 	$ib->updatePrice( $aBaseData, $cc );
	$ad->writeCurrent();
	
	return 1;
}

$fac = new MusicDatabaseFactory($g_dbPDO, $g_redis);

// $cc = new CurrencyConvert( new CurrencyDataMySql() );
$cc = $fac->createDbInterface("CurrencyConvert");
////$cc->initialiseFromTableDb(  );

$ad = new ArtistDataFileDb($baseDir, $cc);


// Add/Update ItemBase
$aBaseData = array('artist_name' => $artist_name );

					
$aBaseData['item_type'] 			= $item_type;
$aBaseData['item_price_name'] 		= $item_price_name;
$aBaseData['item_base_name'] 		= cleanItemName( $aBaseData['item_price_name'] );
$aBaseData['item_genre_id'] 		= 7;
$aBaseData['price_local'] 			= 12;
$aBaseData['currency_id']			= 'NOK';	// DKK, USD, EUR, ...
$aBaseData['media_format_id']		= 3;
$aBaseData['media_type_id']			= 1;
$aBaseData['record_store_name'] 	= 'Musikonline.no (NO)' ;
$aBaseData['item_used'] 			= '';
$aBaseData['item_year'] 			= 2008;

$aBaseData['item_base_reliability'] = 13;
testInserItemPrice_1($ad, $aBaseData, $cc );


?>