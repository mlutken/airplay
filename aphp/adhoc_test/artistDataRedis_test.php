<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('redis_api/RecordStoreDataRedis.php');
require_once ('redis_api/ArtistDataRedis.php');
require_once ('utils/string_utils.php');


// // $iMaxRecordsToRead = -1;
// // $g_argv = $GLOBALS['argv'];
// // 
// // $xmlFile            = $g_argv[1];
// // if ( count($g_argv) > 2 ) {
// //     $iMaxRecordsToRead  = $g_argv[2];
// // }

$ad = new ArtistDataRedis();

printf("Airplay Redis Artist test\n");
$record_store_id    = 1;

$artist_name = "Hej Matematik";
//$artist_id = $ad->createNew($artist_name);
$artist_id = $ad->nameToID($artist_name);
printf("artist_id: %d\n", $artist_id );

//var_dump($a);


$aData = array(   "artist_id" => $artist_id, "artist_url" => "http://www.hejmatematik.dk"
                , "country_id" => 45, "artist_genre_id" => 1
                , "artist_type" => "G", "year_start" => "2005" );

// $ad->updateBaseData($aData);
$a = $ad->getBaseData($artist_id);
pp_array($a);

printf( "IDToName(2): '%s'\n", $ad->IDToName(2) );

?>