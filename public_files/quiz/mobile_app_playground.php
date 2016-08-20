<?php
require_once ( __DIR__ . '/../../aphp/aphp_fix_include_path.php' );
require_once ( __DIR__ . '/../public_files_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('quiz/classes/PagesCommon.php');

$name = 'Auto generate song quiz test';
$pc = new PagesCommon();

echo $pc->pageStart("{$name}");

?>
<span>Num choices/options: </span><input type=text size=30 id='numChoicesID' value='3' ><br>
<span>Num questions: </span><input type=text size=30 id='numQuestionsID' value='5' ><br>
<span>Artist names (semicolon separated): </span><input type=text size=30 id='artistNamesID' value='Hej matematik ;Johnny Deluxe' ><br>
<input type=button value='Run test quiz' onclick="quizRunTest();" >
<div id='AirplayMusicPlayerContainerID'>
	<div id='AirplayMusicYoutubePlayerContainerID'> </div>
</div>
<form id='quizAreaID' action="">
</form>
<a href=# type=button value='Toggle show quiz json' onclick="toggleShowQuizJson();" >Vis/skjul quiz json. (Virker først efter du trykker 'Run test quiz').</a><br> 
<div id='ShowQuizJsonID' style="display:none" >
<textarea id='TextAreaQuizJsonID' style="width:800px;" rows="100" cols="50" >
</textarea> 
</div>
<!--<h3>Unordered list bullet using CSS background image</h3>
<ul class="bullet" title="unordered list-background image">
  <li>List item #1</li>
  <li>List item #2</li>
  <li>List item #3</li>
  <li>List item #4</li>
  <li>List item #5</li>
  <li>List item #6</li>
</ul> -->
<script>



function quizDoSave(quizObj, quizAreaID) 
{ 
	console.log("quizDoSave");
	///quizRenderCurrentState( g_quiz, g_quizCurrentState )
}

function quizSaveTest() 
{ 
	console.log("quizSaveTest");
	quizDoSave(g_quiz, "quizAreaID" );
}

function toggleShowQuizJson()
{
    var sJsonQuiz = JSON.stringify(g_quiz, null, '\t');
    document.getElementById('TextAreaQuizJsonID').value = sJsonQuiz;
    jQuery('#ShowQuizJsonID').toggle();
}

function myQuizDoRun(quizObj, quizAreaID) 
{ 
    var sJsonQuiz = JSON.stringify(quizObj, null, '\t');
    document.getElementById('TextAreaQuizJsonID').value = sJsonQuiz;
    quizDoRun(quizObj, quizAreaID);
}


function quizRunTest() 
{ 
	console.log("quizRunTest: " + document.getElementById('artistNamesID').value );
	postDataObj = { 
          auto_gen_quiz_type: "GUESS_SONG_PLAYING"
        , artist_names : document.getElementById('artistNamesID').value 
        , difficulty: 3
        , num_questions: document.getElementById('numQuestionsID').value
        , num_choices: document.getElementById('numChoicesID').value
        , quiz_name: ""
        , quiz_id: 0
    };
	g_quiz = quizLoadFromServer(postDataObj, myQuizDoRun, "quizAreaID" );
}

 jQuery("#AirplayMusicYoutubePlayerContainerID").tubeplayer({
    width: 1, // the width of the player
    height: 1, // the height of the player
    allowFullScreen: "false", // true by default, allow user to go full screen
    initialVideo: "", // the video that is loaded into the player
    preferredQuality: "default",// preferred quality: default, small, medium, large, hd720
    onPlay: function(id){}, // after the play method is called
    onPause: function(){console.log("onPause");}, // after the pause method is called
    onStop: function(){console.log("onStop");}, // after the player is stopped
    onSeek: function(time){console.log("onSeek: " + time );}, // after the video has been seeked to a defined point
    onMute: function(){console.log("onMute");}, // after the player is muted
    onUnMute: function(){console.log("onUnMute");} // after the player is unmuted
});

</script>



<?php
echo $pc->pageEnd();

?>