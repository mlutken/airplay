<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('utils/general_utils.php');
require_once ('utils/string_utils.php');
require_once ('quiz/quiz_utils.php');
require_once ('db_manip/QuizManip.php');

echo "Score test\n";

// $fac = new MusicDatabaseFactory();
// $aListScoreData = array ( "user_id" => 3, "quiz_name" => "Hej Matematik--1", "score" => 76 );
// $quizManip = new QuizManip();
// $quizManip->quizScoreSave($aListScoreData);
// 
// $dbScore = $fac->createDbInterface('QuizScoreData');
// 
// $aHighScoreList = $dbScore->highScoreListFull(216);
// 
// $aHighScore =array (
//       'quiz_name'   => 'My Quiz'
//     , 'user_id'     => 3
//     , 'user_score'  => 654
//     , 'list'        => $aHighScoreList
// );
// 
// var_dump($aHighScore);
// 
// $highscoreJson = json_encode( $aHighScore, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

$aLookupScoreData = array ( "user_id" => 3, "quiz_name" => "Hej Matematik--1" );

$highscoreJson = quizHighScoreListHandler ($aLookupScoreData);
echo $highscoreJson;

?>