<?php
require_once ( __DIR__ . '/../../aphp/aphp_fix_include_path.php' );
require_once ( __DIR__ . '/../public_files_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('quiztest/classes/PagesCommon.php');

$name = 'Media quiz test';
$pc = new PagesCommon();

echo $pc->pageStart("{$name}");

?>
<input type=button value='Qlear quiz page' onclick="quizClearPage('quizAreaID');" >
<p>
Vores første simple musik quiz tester. Quizzerne her er ikke autogenererede (det kommer i næste runde), 
men de hentes fra vores quiz tabel her:<br>
<a href='http://adminairplay.airplaymusic.dk/QuizTbl.php' target="_blank" >Quiz table</a><br>
Lige nu findes der disse simple quizzer, som du kan skrive ind i feltet og trykke 'Run test quiz':<br>
(<i>Tip: Du kan indsætte quiz navnet i tekstfeltet ved at klikke på det i nedenstående liste</i>)
<ul>
<li class=quizTextOptionLine onclick="document.getElementById('testNameID').value = 'TEST: Michael Jackson quiz 1';" >'TEST: Michael Jackson quiz 1'</li><br>
<li class=quizTextOptionLine onclick="document.getElementById('testNameID').value = 'TEST: TV-2 quiz 1';" >'TEST: TV-2 quiz 1'</li><br>
</ul>
<!-- <input type=button value='Save quiz test' onclick="quizSaveTest();" ><br> -->
<input type=text size=30 id='testNameID' value='TEST: Michael Jackson quiz 1' >
<input type=button value='Run test quiz' onclick="quizRunTest();" >
<!-- <input type=button value='Toggle video' onclick="jQuery('#AirplayMusicPlayerContainerID').toggle();" ></input><br> -->
<!--<a href="#" onClick="apmStop('youtube')"> 
    Stop player 
</a><br>-->
<div id='AirplayMusicPlayerContainerID'>
	<div id='AirplayMusicYoutubePlayerContainerID'> </div>
</div>
<form id='quizAreaID' action="">
</form>
<a href=# type=button value='Toggle show quiz json' onclick="jQuery('#ShowQuizJsonID').toggle();" >Vis/skjul quiz json. (Virker først efter du trykker 'Run test quiz').</a><br> 
<div id='ShowQuizJsonID' style="display:none" >
<textarea id='TextAreaQuizJsonID' style="width:800px;" rows="100" cols="50" >
</textarea> 
</div>
<script>


if ( quizGetAutoLoadName () != "" ) {
    console.log( "Quiz do autoload: '" + quizGetAutoLoadName() + "'" );
    g_quiz = quizLoadFromServer({ quiz_name: quizGetAutoLoadName(), quiz_id: 0 }, myQuizDoRun, "quizAreaID" );
}

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

function myQuizDoRun(quizObj, quizAreaID) 
{ 
    var sJsonQuiz = JSON.stringify(quizObj, null, '\t');
    document.getElementById('TextAreaQuizJsonID').value = sJsonQuiz;
    quizDoRun(quizObj, quizAreaID);
}


function quizRunTest() 
{ 
	console.log("quizRunTest: " + document.getElementById('testNameID').value );
    postDataObj = { quiz_name: document.getElementById('testNameID').value, quiz_id: 0 }
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