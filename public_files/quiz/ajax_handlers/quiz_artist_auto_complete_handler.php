<?php
require_once ( __DIR__ . '/../../../aphp/aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('utils/general_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');
require_once ('quiz/quiz_utils.php');

dbgWritePostGetSession();


$auto_json = "";

$fac = new MusicDatabaseFactory();

$dbArtist = $fac->createDbInterface('ArtistData');

$sSearchFor 	= $_GET['term'];

if ( strlen($sSearchFor) >= 3 ) {
    $aRes = $dbArtist->autoCompleteNames( $sSearchFor );
    $auto_json = json_encode( $aRes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
}



//$auto_json = quizAutoCompleteSimpleTest(); // NOTE DEBUG ONLY:




function quizAutoCompleteSimpleTest()
{
    $aArtistNames = array(
          array('artist_name' => 'Hej Matematik' )
        , array('artist_name' => 'Michael Jackson' )
    );
    $aArtistNames = array(
          'Hej Matematik'
        , 'Michael Jackson' 
    );
    $auto_json = json_encode( $aArtistNames, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	return $auto_json;
}



echo $auto_json;

?>