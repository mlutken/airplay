<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('utils/general_utils.php');
require_once ('utils/string_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');

echo "Settings test\n";

$fac = new MusicDatabaseFactory();
$set = $fac->createDbInterface('SettingsData');

$set->setValue('ArtistCron:limit', 2000 );

printf( "AS STRING => ArtistCron:limit: '%s'\n", $set->getValueStr('ArtistCron:limit') );
printf( "AS INT    => ArtistCron:limit:  %d\n", $set->getValueInt('ArtistCron:limit') );
printf( "AS FLOAT  => ArtistCron:limit:  %f\n", $set->getValueFloat('ArtistCron:limit') );

?>