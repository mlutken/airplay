<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

$g_useRedis = false;

require_once ('new_db_inserter/XmlDataReader.php');
require_once ('db_api/MediaFormatLookup.php');
require_once ('db_api/MediaTypeLookup.php');

$iMaxRecordsToRead 	= -1;
$iModuloBase		= 1;
$iModuloMatch		= 0;

global $argv, $g;

$baseDir        = $argv[1];
$xmlFile        = $argv[2];
if ( count($argv) > 3 ) {
    $iMaxRecordsToRead  = $argv[3];
}
if ( count($argv) > 4 ) {
    $iModuloBase  = $argv[4];
}
if ( count($argv) > 5 ) {
    $iModuloMatch  = $argv[5];
}


printf ("baseDir            : '%s'\n"   , $baseDir );
printf ("xmlFile            : '$xmlFile'\n" );
printf ("iMaxRecordsToRead  : '$iMaxRecordsToRead'\n");
printf ("iModuloBase        : '$iModuloBase'\n");
printf ("iModuloMatch       : '$iModuloMatch'\n");


printf ("read xml file: $xmlFile\n" );
$r = new XmlDataReader($baseDir, $g_MySqlPDO, $g_redis);
$r->maxRecordsToRead($iMaxRecordsToRead);
$r->moduloBaseSet	( $iModuloBase );
$r->moduloMatchSet	( $iModuloMatch );
$r->showProgress( true );
$r->init();
$r->readXMLData($xmlFile);

?>