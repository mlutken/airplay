<?php

$g_mySqlHost     =    "localhost";
$g_mySqlDbNname  =    "airplay_music";
$g_mySqlUserName =    "airplay_user";
$g_mySqlPassword =    "Deeyl1819";

$g_MySqlPDO = new PDO( "mysql:host=$g_mySqlHost;dbname=$g_mySqlDbNname;charset=utf8", $g_mySqlUserName, $g_mySqlPassword );



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
    $s = "DB Warning: ${msg}\n";
    echo $s;
}

function logDbInsertError( $msg )
{
    $s = "DB Error: ${msg}\n";
    echo $s;
}


?>



