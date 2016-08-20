<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('quiz/quiz_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');


printf("json test\n");

$theme_json = quizGetTheme_TEST();

//echo $theme_json;

$aJson = json_decode($theme_json);
var_dump( $aJson) ;


?>