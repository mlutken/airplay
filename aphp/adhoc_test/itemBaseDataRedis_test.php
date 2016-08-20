<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('redis_api/RecordStoreDataRedis.php');
require_once ('redis_api/ArtistDataRedis.php');
require_once ('redis_api/ItemBaseDataRedis.php');
require_once ('redis_api/ItemBaseCorrectionDataRedis.php');


// // $iMaxRecordsToRead = -1;
// // $g_argv = $GLOBALS['argv'];
// // 
// // $xmlFile            = $g_argv[1];
// // if ( count($g_argv) > 2 ) {
// //     $iMaxRecordsToRead  = $g_argv[2];
// // }

$ad = new ArtistDataRedis();
$id = new ItemBaseDataRedis();
$ibc = new ItemBaseCorrectionDataRedis();

printf("Airplay Redis ItemBase test\n");
$record_store_id    = 1;

$artist_name = "Hej Matematik";
$item_base_name = "Party i provinsen";
$item_type = 1;
$artist_id = 2;

//$item_base_id = $id->createNew($artist_id, $item_base_name, $item_type);
$item_base_id = $id->nameToID($artist_id, $item_base_name, $item_type);
printf("item_base_id: %d\n", $item_base_id );

//var_dump($a);


$aData = array(     "artist_id"         => $artist_id 
                  , "item_base_id"      => $item_base_id
                  , "item_type"         => $item_type
                  , "item_genre_id"     => 3
                  , "item_year"         => 2007
                  , "release_date"      => "2007-04-22"
                  , "parent_item"       => 120
                  , "item_time"         => 195
                  , "track_number"      => 8
                  );

$id->updateBaseData($aData);
$a = $id->getBaseData($item_base_id);
var_dump($a);

$ibc->setCorrectionName ( 2, "Walkman_", "Walkman" );
echo $ibc->correctionNameToBaseName(2, "Walkman_" ) . "\n";


?>