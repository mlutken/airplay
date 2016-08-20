<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('utils/general_utils.php');
require_once ('utils/string_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');

echo "User test\n";

$fac = new MusicDatabaseFactory();
$dbUser = $fac->createDbInterface('UserData');

$aUserData = array ( 'fb_id' => 12, 'user_name' => 'Fætter Guf', "email" => 'fg@andeby.dk'); //  

$user_id = $dbUser->lookupAutoCreate($aUserData);

echo "user_id: $user_id\n";


?>