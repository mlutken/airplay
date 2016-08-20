<?php
require_once ( __DIR__ . '/../../aphp/aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('utils/general_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');
require_once ('quiz/quiz_utils.php');

$auto_json = "";

$fac = new MusicDatabaseFactory();
$dbQuizTheme = $fac->createDbInterface('QuizThemeData');
$sSearchFor 	= $_GET['term'];

if ( strlen($sSearchFor) >= 3 ) {
    $aRes = $dbQuizTheme->autoCompleteNames( $sSearchFor );
    $auto_json = json_encode( $aRes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
}

echo $auto_json;

?>