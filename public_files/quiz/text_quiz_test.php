<?php
require_once ( __DIR__ . '/../../aphp/aphp_fix_include_path.php' );
require_once ( __DIR__ . '/../public_files_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('quiztest/classes/PagesCommon.php');

$name = 'Text quiz test XX';
$pc = new PagesCommon();

echo $pc->htmlHeaderStart("{$name}");
echo "<script src='/js/jquery.mobile-1.3.1.min.js'></script>";
echo $pc->htmlHeaderEnd();
echo $pc->pageTopContents($name);

?>
<script>


// var g_quizKimLarsen = {
// 	  "quiz_name": "Kim Larsen quiz 1"
// 	, "intro_text": "This is a simple text quiz featuring Kim Larsen questions"
// 	, "intro_image": "/images/k/i/m/kim_larsen_1.png"
// 	, "author_email": "ml@airplaymusic.dk"
// 	, "author_name": "Martin Lütken"
// 	, "type": "text" // 'text', 'audio', 'video'
// 	, "num_questions": 5
// 	, "num_choices": 3
// 	, "image_mode" : "none" // 'none', 'per_question', 'per_choice'
// 	, "questions" : [
// 		  { "question" : "What year was Kim Larsen born?", "c0" : "1944", "c1" : "1945", "c2" : "1946", "answer" : "c1" }
//  		, { "question" : "Name of first album?", "c0" : "Sådan", "c1" : "Ja tak", "c2" : "Værsgo", "answer" : "c2" }
//  		, { "question" : "Song about hospital ship in Korea?", "c0" : "Sealandia", "c1" : "Jutlandia", "c2" : "1949", "answer" : "c1" }
//  		, { "question" : "Best selling album ever?", "c0" : "Midt om natten", "c1" : "Forklædt som voksen", "c2" : "Yummi Yummi", "answer" : "c0" }
//  		, { "question" : "Kim Larsen's education?", "c0" : "School teacher", "c1" : "No education", "c2" : "Electrician", "answer" : "c0" }
// 	]
// };


// // function test() 
// // { 
// // 	console.log("Test");
// // 	for(var i=0; i<3;i++)
// // 	{
// // 		//var radioBtn = jQuery('<input type="radio" name="rbtnCount" /><br>');
// // 		var radioBtn = jQuery("<input type='radio' name='q1' id='myRadio"+i+"' value=v" + i + "><span>" + "text" + i + "</span><br>");
// // 		radioBtn.fadeIn(500 + i*500).appendTo('#quizAreaID');
// // 		console.log("json: " + g_myJSONObject.bindings[i].method );
// // 	}	
// // 	console.log("Quiz name : " + g_quiz.quiz_name );
// // 	console.log("Num questions : " + g_quiz.num_questions );
// // 
// // 	for ( var i=0; i < g_quiz.num_questions; i++)
// // 	{
// // 		g_quizCurrentState.current_question = i;
// // 		quizTextRenderQuestion('quizAreaID', g_quiz, g_quizCurrentState );
// // 	}	
// // 	
// // 	var elemObj = document.getElementById(elemID);
// // 	elemObj.parentNode.removeChild(elemObj);
// // 	quizNextState(g_quiz, g_quizCurrentState );
// // }


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


function quizRunTest() 
{ 
	console.log("quizRunTest");
    postDataObj = { quiz_name: "Test 1", quiz_id: 0 }
	g_quiz = quizLoadFromServer(postDataObj, quizDoRun, "quizAreaID" );
}


</script>

<input type=button value='Qlear quiz page' onclick="quizClearPage('quizAreaID');" ></input>
<input type=button value='Save quiz test' onclick="quizSaveTest();" ></input><br>
<input type=button value='Run test quiz' onclick="quizRunTest();" ></input><br>

<form id='quizAreaID' action="">
</form>


<?php
echo $pc->pageEnd();

?>