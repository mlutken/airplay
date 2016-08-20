<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

$g_useRedis = true;
$g_redis = new Redis();
$g_redis->connect("127.0.0.1");

require_once ('db_inserter/XmlDataReader.php');
require_once ('db_api/MediaFormatLookup.php');
require_once ('db_api/MediaTypeLookup.php');

$iMaxRecordsToRead = -1;
$g_argv = $GLOBALS['argv'];

$xmlFile            = $g_argv[1];
if ( count($g_argv) > 2 ) {
    $iMaxRecordsToRead  = $g_argv[2];
}

printf ("read xml file: $xmlFile\n" );
$r = new XmlDataReader(null, null);
$r->maxRecordsToRead($iMaxRecordsToRead);
$r->showProgress( true );
$r->readXMLData($xmlFile);

//var_dump($v);

// // $mf = new MediaFormatLookup;
// // $mf->dbg();
// // 
// // $mf = new MediaTypeLookup;
// // $mf->dbg();

?>