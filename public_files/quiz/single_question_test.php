<?php
require_once ( __DIR__ . '/../../aphp/aphp_fix_include_path.php' );
require_once ( __DIR__ . '/../public_files_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('quiztest/classes/PagesCommon.php');

$name = 'Single question test';
$pc = new PagesCommon();

echo $pc->pageStart("{$name}");

?>
<script>

var g_quizCurrentState = {};
var g_quiz = {};



var g_quizDefault = {
	  "quiz_name": "Default quiz"
	, "intro_text": "Simple default debug quiz."
	, "intro_image": "/images/k/i/m/kim_larsen_1.png"
	, "author_email": "ml@airplaymusic.dk"
	, "author_name": "Martin Lütken"
	, "type": "text" // 'text', 'audio', 'video'
	, "num_questions": 5
	, "num_choices": 3
	, "image_mode" : "none" // 'none', 'per_question', 'per_choice'
	, "questions" : [
		  { "question" : "What year was Kim Larsen born?", "c0" : "1944", "c1" : "1945", "c2" : "1946", "answer" : "c1" }
	]
};


var g_quizKimLarsen = {
	  "quiz_name": "Kim Larsen quiz 1"
	, "intro_text": "This is a simple text quiz featuring Kim Larsen questions"
	, "intro_image": "/images/k/i/m/kim_larsen_1.png"
	, "author_email": "ml@airplaymusic.dk"
	, "author_name": "Martin Lütken"
	, "type": "text" // 'text', 'audio', 'video'
	, "num_questions": 5
	, "num_choices": 3
	, "image_mode" : "none" // 'none', 'per_question', 'per_choice'
	, "questions" : [
		  { "question" : "What year was Kim Larsen born?", "c0" : "1944", "c1" : "1945", "c2" : "1946", "answer" : "c1" }
 		, { "question" : "Name of first album?", "c0" : "Sådan", "c1" : "Ja tak", "c2" : "Værsgo", "answer" : "c2" }
 		, { "question" : "Song about hospital ship in Korea?", "c0" : "Sealandia", "c1" : "Jutlandia", "c2" : "1949", "answer" : "c1" }
 		, { "question" : "Best selling album ever?", "c0" : "Midt om natten", "c1" : "Forklædt som voksen", "c2" : "Yummi Yummi", "answer" : "c0" }
 		, { "question" : "Kim Larsen's education?", "c0" : "School teacher", "c1" : "No education", "c2" : "Electrician", "answer" : "c0" }
	]
};





function objectSize(obj) 
{
     var size = 0, key;
     for (key in obj) {
         if (obj.hasOwnProperty(key)) size++;
     }
     return size;
}
 
 
 
/** Get the ID for the correct answer. I.e. 'c0', 'c1', 'c2', ... */
function quizGetCorrectAnswerID( quizObj, iCurQ )
{
////	var iCurQ = quizStateObj.current_question;
	return quizObj.questions[iCurQ].answer;
}


/** Check if the answer given is correct. */
function quizIsAnswerCorrect( quizObj, iCurQ, userChoiceID )
{
	var correctID = quizGetCorrectAnswerID( quizObj, iCurQ );
	return userChoiceID == correctID;
}

/** Get the text (original choice from question page) for the correct answer. */
function quizGetCorrectAnswerText( quizObj, iCurQ )
{
	var correctID = quizGetCorrectAnswerID( quizObj, iCurQ );
	return quizObj.questions[iCurQ][correctID];
}


/** Get comment for the answer page. If quizStateObj.comment_mode == 'none' we only return 
	'Correct' or 'Wrong' */
function quizGetAnswerComment( quizObj, quizStateObj, bIsAnswerCorrect )
{
	// TODO: Enhance this with comments/insults system.
	if ( bIsAnswerCorrect ) 	return "Correct!";
	else						return "Wrong!"
}

/** Load quiz from server. 
\param quizName: Name of quiz to load.
\return Quiz (javascipt/json) object. */
// function quizLoadFromServer( quizName )
// {
// 	// TODO: Implement this for real!
// 	return g_quizKimLarsen;
// }

// function quizLoadFromServer(quizName, quizID)
// {
// 	console.log('Do load quiz');
// 	var quiz = g_quizDefault;
//     $.getJSON("/quiztest/ajax_handlers/load_quiz_handler.php", { quiz_name: "Kim Larsen quiz 1", quiz_id: 0 } 
//     ).done ( function(data) {
// 		console.log('Quiz loaded' + data );
// 		quiz = data;
// 		
//     }
//     ).error( function(data) {
//         alert("Login: Error trying to access server");
//     }
//     ) 
//     ;    
// 	console.log('Return quiz');
// 	return quiz;
// }

function quizLoadFromServer(quizName, quizID, doRunFn, quizAreaID )
{
	console.log('Do load quiz');
	var quiz = g_quizDefault;
	$.ajax({
	type: 'POST',
	async: true,
	dataType: "json",
	url: "/quiztest/ajax_handlers/load_quiz_handler.php",
	data: { quiz_name: "Kim Larsen quiz 1", quiz_id: 0 },
	success: function(data) {
		console.log('Quiz loaded' + data );
		doRunFn(data, quizAreaID);
		//quiz = data;
		
    }
	});
//     $.getJSON("/quiztest/ajax_handlers/load_quiz_handler.php", { quiz_name: "Kim Larsen quiz 1", quiz_id: 0 } 
//     ).done ( function(data) {
// 		console.log('Quiz loaded' + data );
// 		quiz = data;
// 		
//     }
//     ).error( function(data) {
//         alert("Login: Error trying to access server");
//     }
//     ) 
//     ;    
	console.log('Return quiz');
	return quiz;
}

/** Render one text question page. 
\param domID: ID om dom element to insert the HTML in.
\param quizObj: The quiz object.
\param iQuestion: The question (i.e. number ) to render. */
function quizTextRenderQuestion(quizObj, quizStateObj )
{
//	jQuery('#'+domID).empty();
	var domID = quizStateObj.quizAreaID;
	var iQuestion = quizStateObj.current_question;
	var questionLine = jQuery("<span>" + quizObj.questions[iQuestion].question + "</span><br>");
	questionLine.fadeIn(500).appendTo('#' + domID);
	for ( var i=0; i < g_quiz.num_choices; i++)
	{
		var radioBtn = jQuery("<input type='radio' name='quiz_questions"+i + "' id=c"+i+" value=c" + i +" onclick='quizClickAnswerQuestion(this);' ><span>" + quizObj.questions[iQuestion]["c"+i] + "</span><br>");
		radioBtn.fadeIn(1000 + i*500).appendTo('#' + domID);
	}	
}


/** Render one text answer page. 
\param domID: ID om dom element to insert the HTML in.
\param quizObj: The quiz object.
\param iQuestion: The question (i.e. number ) to render answer for. 
\param userChoice: Choice id (cN) that the user answerered. 
*/
function quizTextRenderAnswer(quizObj, quizStateObj )
{
//	jQuery('#'+domID).empty();
	var iCurQ = quizStateObj.current_question;

	var userChoiceID = quizStateObj.user_answers[quizStateObj.current_question];
	var domID = quizStateObj.quizAreaID;
	var bIsAnswerCorrect = quizIsAnswerCorrect( quizObj, iCurQ, userChoiceID );
	var sComment = quizGetAnswerComment(quizObj, quizStateObj, bIsAnswerCorrect);
	var sCorrectAnswer = quizGetCorrectAnswerText(quizObj, iCurQ);
	var commentLine = jQuery("<span>" + sComment + "</span><br>");
	var correctAnswer = jQuery("<span>" + sCorrectAnswer + "</span><br>");
	var continueBtn = jQuery("<input type=button value='Continue' onclick='quizClickNextState(this);' ><br>");
	commentLine.fadeIn(500).appendTo('#' + domID);
	correctAnswer.fadeIn(500).appendTo('#' + domID);
	continueBtn.fadeIn(500).appendTo('#' + domID);	
}


function quizTextRenderResults( quizObj, quizStateObj ) 
{ 
	var domID = quizStateObj.quizAreaID;

	var l1 = jQuery("<span>Quiz results:</span><br>");
	l1.fadeIn(500).appendTo('#' + domID);

	var iNumCorrect = 0;
	var iNumQuestions = objectSize(quizObj.questions);
	for ( var i=0; i < iNumQuestions; i++)
	{
		var userChoiceID = quizStateObj.user_answers[i];
		if ( quizIsAnswerCorrect( quizObj, i, userChoiceID ) ) iNumCorrect++;
	}	
	var l2= jQuery("<span>You got</span><br>");
	l2.fadeIn(1000).appendTo('#' + domID);
	var l3= jQuery("<span>" + iNumCorrect + " correct</span><br>");
	l3.fadeIn(1000).appendTo('#' + domID);
	var l4= jQuery("<span>of " + iNumQuestions + " questions.</span><br>");
	l4.fadeIn(1000).appendTo('#' + domID);
}


/** Render quiz start page. 
\param domID: ID om dom element to insert the HTML in.
\param quizObj: The quiz object. */
function quizRenderStartPage( quizObj, quizStateObj )
{
//	jQuery('#'+domID).empty();
	var domID = quizStateObj.quizAreaID;
	var headLine = jQuery("<span>" + quizObj.quiz_name + "</span><br>");
	headLine.fadeIn(500).appendTo('#' + domID);
	var introText = jQuery("<span>" + quizObj.intro_text + "</span><br>");
	introText.fadeIn(1000).appendTo('#' + domID);
	var continueBtn = jQuery("<input type=button value='Continue' onclick='quizClickNextState(this);' ><br>");
	continueBtn.fadeIn(1500).appendTo('#' + domID);	
}


function quizRenderSelectCommentModePage( quizObj, quizStateObj  ) 
{ 
	var domID = quizStateObj.quizAreaID;
	var headLine = jQuery("<span>Funny comments on my progress?</span><br>");
	headLine.fadeIn(500).appendTo('#' + domID);
	var radioBtnYes = jQuery("<input type='radio' name='sel_commment_mode' value='funny' onclick='quizClickSelectCommentMode(this);' ><span>Yes! hit me. I\'m game.</span><br>");
	radioBtnYes.fadeIn(1000).appendTo('#' + domID);
	var radioBtnNo = jQuery("<input type='radio' name='sel_commment_mode' value='none' onclick='quizClickSelectCommentMode(this);' ><span>No thanks</span><br>");
	radioBtnNo.fadeIn(1000).appendTo('#' + domID);
}



function quizClickSelectCommentMode(answerElem) 
{ 
	console.log("You selected: Val: '" + answerElem.value + "' Text: '" + jQuery(answerElem).next('span').text() + "'");
	g_quizCurrentState.comment_mode = answerElem.value;
	quizClickNextState(answerElem);
}


function quizClickAnswerQuestion(answerElem) 
{ 
	console.log("You answerered: " + answerElem.value + " '" + jQuery(answerElem).next('span').text() + "'");
	g_quizCurrentState.user_answers[g_quizCurrentState.current_question] = answerElem.value;
	quizAdvanceAndRenderNextState( g_quiz, g_quizCurrentState );
}



function quizClickNextState(clickElem) 
{ 
//	console.log("quizClickNextState: " + clickElem.value );
	quizAdvanceAndRenderNextState( g_quiz, g_quizCurrentState );
}


function quizClearPage(domID) 
{ 
	jQuery('#'+domID).empty();
}

/** Advance current state to next and render the next page. */
function quizAdvanceAndRenderNextState( quizObj, quizStateObj )
{
	quizNextState( quizObj, quizStateObj );
	quizClearPage(quizStateObj.quizAreaID);
	quizRenderCurrentState( quizObj, quizStateObj );
}

/** Advance current state to next and render the next page. */
function quizRenderCurrentState( quizObj, quizStateObj )
{
	var domID = quizStateObj.quizAreaID;
	var iCurQ = quizStateObj.current_question;
	var iNumQuestions = objectSize(quizObj.questions);
	if ( iCurQ < 0 ) {
		if 	( -2 == iCurQ ) quizRenderStartPage( quizObj, quizStateObj );
		else 				quizRenderSelectCommentModePage( quizObj, quizStateObj );
	}
	else if ( 0 <= iCurQ && iCurQ < iNumQuestions ) {
		if ( "question" == quizStateObj.subpage )	quizTextRenderQuestion(quizObj, quizStateObj );
		else 										quizTextRenderAnswer(quizObj, quizStateObj );
	}
	else if ( iCurQ == iNumQuestions ) {
		quizTextRenderResults(quizObj, quizStateObj );
	}
	
}

/////////////


/** Are we at the last answer page in the quiz ?*/
function quizIsStateLastAnswer( quizObj, quizStateObj )
{
	var iCurQ = quizStateObj.current_question;
	if ( iCurQ == objectSize(quizObj.questions) -1 ) {
		return "answer" == quizStateObj.subpage;
	}
	return false;
}


/** Advance current state to next. */
function quizNextState( quizObj, quizStateObj )
{
	var iCurQ = quizStateObj.current_question;
	var iNumQuestions = objectSize(quizObj.questions);
	
	if ( iCurQ < 0 ) quizStateObj.current_question = iCurQ +1;
	else if ( 0 <= iCurQ && iCurQ < iNumQuestions) {
		// --- iCurQ >= 0 --- 
		if ( "question" == quizStateObj.subpage ) quizStateObj.subpage = "answer";
		else {
			quizStateObj.current_question = iCurQ +1;
			quizStateObj.subpage = "question";
		}
	}
	else {
		quizStateObj.subpage = "";
		quizStateObj.current_question = iCurQ +1;
	}
	console.log("quizNextState New current state is: question=" + quizStateObj.current_question + " subpage=" + quizStateObj.subpage + "Num questions: " + objectSize(quizObj.questions) );
}

function quizDoRun(quizObj, quizAreaID) 
{ 
	console.log("quizDoRun");
	g_quiz = quizObj;
	g_quizCurrentState = { "current_question" : -2, "subpage" : "question", "num_correct" : 0, "pct_correct" : 0, "comment_mode" : "none", "quizAreaID" : quizAreaID, "user_answers" : [] };
	quizRenderCurrentState( g_quiz, g_quizCurrentState )
}


function quizRun() 
{ 
	console.log("Load quiz ");
	g_quiz = quizLoadFromServer("Kim Larsen quiz 1", "0", quizDoRun, "quizAreaID" );
}

/////////////
//////////////

function test() 
{ 
// 	console.log("Test");
// 	for(var i=0; i<3;i++)
// 	{
// 		//var radioBtn = jQuery('<input type="radio" name="rbtnCount" /><br>');
// 		var radioBtn = jQuery("<input type='radio' name='q1' id='myRadio"+i+"' value=v" + i + "><span>" + "text" + i + "</span><br>");
// 		radioBtn.fadeIn(500 + i*500).appendTo('#quizAreaID');
// 		console.log("json: " + g_myJSONObject.bindings[i].method );
// 	}	
// 	console.log("Quiz name : " + g_quiz.quiz_name );
// 	console.log("Num questions : " + g_quiz.num_questions );

// 	for ( var i=0; i < g_quiz.num_questions; i++)
// 	{
// 		g_quizCurrentState.current_question = i;
// 		quizTextRenderQuestion('quizAreaID', g_quiz, g_quizCurrentState );
// 	}	
	
// 	var elemObj = document.getElementById(elemID);
// 	elemObj.parentNode.removeChild(elemObj);
	quizNextState(g_quiz, g_quizCurrentState );
}

</script>

<form action="">
	<input type="radio" name="q1" onclick="quizClickAnswerQuestion(this);" value="thriller" ><span>Thriller</span><br>
	<input type="radio" name="q1" onclick="quizClickAnswerQuestion(this);" value="bad"><span>Bad</span><br>
	<input type="radio" name="q1" onclick="quizClickAnswerQuestion(this);" value="billy_jean"><span>Billy Jean</span>
</form>

<input type=button value='Test' onclick="test();" ></input><br>
<input type=button value='Run quiz' onclick="quizRun();" ></input><br>
<input type=button value='Qlear quiz page' onclick="quizClearPage('quizAreaID');" ></input><br>

<form id='quizAreaID' action="">
</form>


<?php
echo $pc->pageEnd();

?>