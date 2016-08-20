<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('quiz/quiz_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');


printf("Quiz creator test\n");

$fac = new MusicDatabaseFactory();
$dbQuizData = $fac->createDbInterface('QuizData');

// // $artist_name = 'Christian';
// // $artist_id = $dbArtist->lookupID($artist_name);
// // printf("ID: %d, Name: %s\n", $artist_id, $artist_name );

// // $aSongs = $dbArtist->getAllBaseItemsWithNumPrices( $artist_id, 2 );

//var_dump($aSongs);

$aCreatOptions = array 
(
      'artist_names'    => "Mazzy Star"
    , 'difficulty'      => 2
    , 'num_questions'   => 5
    , 'num_choices'     => 3
);


$aQuiz = quizAutoGenerate_GUESS_SONG_PLAYING($aCreatOptions);

// foreach ( $aQuiz['songs'] as $aSong ) {
//     printf("Song[%d]: %s - %s\n", $aSong['prices_count'], $aSong['artist_name'], $aSong['item_base_name'] );
// }

$sJson = json_encode( $aQuiz, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

echo $sJson;

$quiz_name = $aQuiz['quiz_name'];

printf("\nautoNameFirstFreeNumber['$quiz_name']: %d\n", $dbQuizData->autoNameFirstFreeNumber($quiz_name) );


?>