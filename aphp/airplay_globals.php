<?php
//
// IMPORTANT: You MUST have a file called local_settings.php in this directory. Copy from local_settings.php.in and adjust.
//

error_reporting(1);
ini_set('error_reporting', E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);

define('PHP_INT_MIN', ~PHP_INT_MAX);	// See buttom of page: http://php.net/manual/en/reserved.constants.php

// Insert at offending line to get a simple stacktrace
// debug_print_backtrace();

$g_useRedis     		= false;
$g_useFileDb    		= false;
$g_fileDbPrettyJson    	= true;

// --- MySQL Parameters ---
$g_mySqlHost     =    "localhost";
$g_mySqlDbNname  =    "airplay_music_v1";
$g_mySqlUserName =    "airplay_user";
$g_mySqlPassword =    "Deeyl1819";

$g_redis    = null;
$g_MySqlPDO = null;

if ( $g_useRedis ) {
    $g_redis = new Redis();
    $g_redis->connect("127.0.0.1");
}

$g_MySqlPDO = new PDO( "mysql:host=$g_mySqlHost;dbname=$g_mySqlDbNname;charset=utf8", $g_mySqlUserName, $g_mySqlPassword );


//require_once ('local_settings.php');


$g_sNormalUploadDir 	= '/home/sleipner/airplay/filesupload/upload/files';
$g_sPriorityUploadDir	= '/home/sleipner/airplay/filesupload/priority_upload/files';
// // $g_sUploadDir = str_replace("/cron", "", __DIR__) . "/jactest";
// // // $g_sUploadDir = str_replace("/cron", "", __DIR__) . "/test_data";

function skipDbWrite()
{
    return false;
}


// ---------------
// --- Logging ---
// ---------------

function logWarning( $msg )
{
    $s = "Warning: ${msg}\n";
    echo $s;
}

function logError( $msg )
{
    $s = "Error: ${msg}\n";
    echo $s;
}


function logDbInsertWarning( $msg )
{
//     $s = "DB Insert Warning: ${msg}\n";
//     echo $s;
}

function logDbInsertError( $msg )
{
    $s = "DB Insert Error: ${msg}\n";
    echo $s;
}


// --- Debugging stacktrace ---

?>