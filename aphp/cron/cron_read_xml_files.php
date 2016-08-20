<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('db_inserter/XmlDataReader.php');
require_once ('UploadReader.php');


global $argv, $argc, $g_sNormalUploadDir, $g_sPriorityUploadDir;

$sNormalUploadDir 	= $g_sNormalUploadDir;
$sPriorityUploadDir = $g_sPriorityUploadDir;

if ( count($argv) > 1 ) {
    $sNormalUploadDir = $argv[1];
}
if ( count($argv) > 2 ) {
    $sPriorityUploadDir = $argv[2];
}

$UploadReader = new UploadReader($sNormalUploadDir, $sPriorityUploadDir);
$UploadReader->cron();

/**
/ Function used to return name of the oldest file - to make sure we process in the correct order.
*/
function getOldestFile($a, $b) 
{
    return (filemtime($a) < filemtime($b)) ? -1 : 1; 
} 
?>