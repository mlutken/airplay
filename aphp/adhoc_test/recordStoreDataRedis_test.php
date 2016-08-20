<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('redis_api/RecordStoreDataRedis.php');


// // $iMaxRecordsToRead = -1;
// // $g_argv = $GLOBALS['argv'];
// // 
// // $xmlFile            = $g_argv[1];
// // if ( count($g_argv) > 2 ) {
// //     $iMaxRecordsToRead  = $g_argv[2];
// // }

$rd = new RecordStoreDataRedis();

printf("Airplay Redis test\n");
$record_store_name  = "Amazon UK";
$record_store_url   = "http://www.amazon.co.uk";
$country_id = 44;

//$id = $rd->createNewFull( $record_store_name, $record_store_url, $country_id );

$id = $rd->nameToID($record_store_name);
printf ("id: %d\n", $id );

$d = $rd->getBaseData($id);
var_dump($d);

$d['record_store_id'] = $id;
$d['record_store_url'] = 'http://www.amazon.co.uk';
$d['record_store_reliability'] = 25;

$rd->updateBaseDataCheckOld($d);
// 
// 
$d = $rd->getBaseData($id);
var_dump($d);

// // $mf = new MediaFormatLookup;
// // $mf->dbg();
// // 
// // $mf = new MediaTypeLookup;
// // $mf->dbg();

?>