<?php
date_default_timezone_set('Europe/Copenhagen');
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

$g_useRedis = false;

require_once ('db_inserter/XmlDataReader.php');
require_once ('db_api/MediaFormatLookup.php');
require_once ('db_api/MediaTypeLookup.php');

$iMaxRecordsToRead = -1;
// $g_argv = $GLOBALS['argv'];
// 
// $xmlFile            = $g_argv[1];
// if ( count($g_argv) > 2 ) {
//     $iMaxRecordsToRead  = $g_argv[2];
// }

global $argv;

//printf ("read xml file: $xmlFile\n" );
printf ("read xml file\n" );
$r = new XmlDataReader(null, null);
//$r->maxRecordsToRead($iMaxRecordsToRead);
$r->showProgress( true );

$N = count($argv);

for ( $i = 1; $i < $N; $i++) {
    $xmlFile = $argv[$i];
    $r->readXMLData($xmlFile);
}


/*
---------------------------------------
--- Empty key tables for easy test ----
---------------------------------------
TRUNCATE `artist`;
TRUNCATE `artist_synonym`;
TRUNCATE `info_artist`;
TRUNCATE `item_base`;
TRUNCATE `item_price`;
TRUNCATE `record_store`;

*/

//var_dump($v);

// // $mf = new MediaFormatLookup;
// // $mf->dbg();
// // 
// // $mf = new MediaTypeLookup;
// // $mf->dbg();

?>