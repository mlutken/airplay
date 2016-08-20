<?php
require_once ( __DIR__ . '/../../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('admin_site/classes/session_start.php');
require_once ('utils/general_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');
require_once ('quiz/quiz_utils.php');



$sSearchFor 	= $_GET['query'];

$ok = strlen($sSearchFor) >= 2;

if ( $ok ) {
    $fac = new MusicDatabaseFactory();
    $dbQuizTheme = $fac->createDbInterface('QuizThemeData');
    $aThemes = $dbQuizTheme->autoCompleteNamesAndIDs( $sSearchFor );

//    dbgWritePostGetSessionData( $aThemes, "/tmp/dbgWritePost_QuizThemeEditAutoComplete_handler.txt");  // For debugging help look here!
   
    $aSuggestions = array();
    $i = 0;
    foreach( $aThemes as $aTheme ) {
        $i++;
        $aSuggestions[] = array( 'value' => $aTheme['quiz_theme_name'], 'data' => array($aTheme['quiz_theme_id']) );
        if ( $i > 12 ) break;
    }

    $a = array( 'query' => $query, 'suggestions' => $aSuggestions );
    print json_encode($a);
}
else {
    print json_encode( array( 'query' => $query, 'suggestions' =>array() ) );
}


?>