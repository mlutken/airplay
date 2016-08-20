<?php
require_once ( __DIR__ . '/../../aphp/aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('quiz/quiz_utils.php');


echo quizSaveCurrentHandler($_POST);

?>