<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('utils/general_utils.php');
require_once ('utils/string_utils.php');
require_once ('db_api/ArtistDataMySql.php');
require_once ('db_manip/MusicDatabaseManip.php');
require_once ('db_manip/MusicDatabaseFactory.php');

global $argv;



// // $iMaxRecordsToRead = -1;
// // $g_argv = $GLOBALS['argv'];
// // 
// // $xmlFile            = $g_argv[1];
// // if ( count($g_argv) > 2 ) {
// //     $iMaxRecordsToRead  = $g_argv[2];
// // }
// $fac = new MusicDatabaseFactory();

$mdm = new MusicDatabaseManip();
// $dbArtist = $fac->createDbInterface("ArtistData");
$dbArtist = new ArtistDataMySql();
printf("Airplay MySql Artist test: {$argv[1]}\n");

//$aRes = $dbArtist->lookupSimilarNames( $argv[1] );
$aRes = $dbArtist->autoCompleteNames( $argv[1] );

//var_dump( $aRes );

// print json_encode($aRes, JSON_PRETTY_PRINT );

echo pretty_json(json_encode($aRes));

?>