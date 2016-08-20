
var g_timerInMilliSecondsHandle = null;
var g_timerInMilliSeconds = 0;
//var g_quizCurrentState = {};
var g_quiz = {};
var g_quizAutoLoadName = "";    // For use when auto loading a named quiz


var g_quizError = {
	  "quiz_name": "error"
	, "intro_text": "Simple default debug quiz."
	, "author_email": "ml@airplaymusic.dk"
	, "author_name": "Martin Lütken"
	, "type": "text" // 'text', 'audio', 'video'
	, "num_questions": 0
	, "num_choices": 0
	, "image_mode" : "none" // 'none', 'per_question', 'per_choice'
	, "questions" : [
		  { "question" : "What year was Kim Larsen born?", "c0" : "1944", "c1" : "1945", "c2" : "1946", "answer" : "c1" }
	]
};

// --------------------------
// --- Init/Run functions ---
// --------------------------

/** For resetting g_quiz.state when starting a new quiz.
Use like this:
g_quiz.state = quizGetStartStateObj();
.*/ 
function quizGetStartStateObj()
{
	return { 
	  "current_question" 	: -1
	, "subpage" 			: "question"
	, "num_correct" 		: 0
	, "pct_correct" 		: 0
	, "comment_mode" 		: "none"
	, "timerInMilliSeconds"	: 0
	, "user_answers" 		: [] 
	};
}

function quizDoRun(quizObj) 
{ 
	quizDoStop();
	quizObj.state = quizGetStartStateObj();
	g_quiz = quizObj;
    
	quizTimerStart();
	quizClearPage('quizAreaID');
	
    if ( -1 == quizObj.autogen_question_index ) {

		console.log("FIXME: AUTOGENERATE QUIZ!");
        jQuery('#runningQuizHeaderID').text('Creating...');
        jQuery('#creatingQuizImageID').show();
        quizObj.auto_gen_recour_counter = 0;
        quizObj.songsearch_recour_counter = 0;
        quizAutoGenerateQuiz(quizObj);
    }
    else {
         console.log("FIXME: RUN QUIZ!");
		// --- Ensure some default values ---
        youtubeCreatePlayer(300,200);
        quizObj.state.current_question -1;
        //quizObj.state = quizGetStartStateObj();
		quizObj.minPointsPerQuestion = quizObj.minPointsPerQuestion || 1;
		quizObj.maxPointsPerQuestion = quizObj.maxPointsPerQuestion || 10;
		quizObj.pointsDecreaseIntervalInSeconds = quizObj.pointsDecreaseIntervalInSeconds || 5; 
		quizObj.questionTimeOutInSeconds = quizObj.questionTimeOutInSeconds || 0; 
		
		quizObj.state.totalScore = 0; // Initialize points
        jQuery('#runningQuizHeaderID').text('Quiz ready');
        jQuery('#creatingQuizImageID').hide();
        quizRenderCurrentState(quizObj);
    }
}

function quizTimerStart()
{
    clearInterval(g_timerInMilliSecondsHandle);
    g_timerInMilliSeconds = 0;
    g_timerInMilliSecondsHandle = setInterval(function(){quiz100msTimerUpdate()}, 100);
	
}

function quizDoStop()
{
    clearInterval(g_timerInMilliSecondsHandle);
	g_quiz.state = quizGetStartStateObj();

}

function quiz100msTimerUpdate()
{
	// --- Update global running timer ---
	if ( !g_timerInMilliSeconds) g_timerInMilliSeconds = 0;
    g_timerInMilliSeconds += 100;

    if ( g_timerInMilliSeconds % 3000 == 0 ) {
        console.log("current question: " + g_quiz.state.current_question );
        console.log("apmCurrentPlayingTime: " + apmCurrentPlayingTime() );
//         console.log("fPointsMax: " + fPointsMax );
//         console.log("fCurQuestionSeconds: " + fCurQuestionSeconds );
//         console.log("pointsDecreaseIntervalInSeconds: " + g_quiz.pointsDecreaseIntervalInSeconds );
        console.log("timerInMilliSeconds: " + g_timerInMilliSeconds  + 
        " , g_quiz.state.currentQuestionPoints: " + g_quiz.state.currentQuestionPoints );
    }
    
	if (!g_quiz.state) return;
	// --- Debug only ---

    if ( g_quiz.state.current_question == -1 ) {
        if ( g_timerInMilliSeconds % 1000 == 0 ) {
//            console.log("apmCurrentPlayingTime: " + apmCurrentPlayingTime() +  "  ( "  + g_timerInMilliSeconds + " )");
            if ( apmCurrentPlayingTime() > 0.2 ) {
                console.log("FIXME: Go to first question quizClickNextState()");
                quizClickNextState();
            }
        }
    }
	
	if ( g_quiz.state.current_question < 0 ) return;
	if ( g_quiz.state.subpage != "question" ) return; // Answer page. We should not update points for current question 
	
	// --- Update points for current question ---
	var fPointsMax = g_quiz.maxPointsPerQuestion * g_quiz.pointsDecreaseIntervalInSeconds;
	var fCurQuestionSeconds = (g_timerInMilliSeconds - g_quiz.state.questionTimeStartInMilliSeconds)/1000;
	var fPoints = (fPointsMax - fCurQuestionSeconds+g_quiz.pointsDecreaseIntervalInSeconds+3) / g_quiz.pointsDecreaseIntervalInSeconds;
    var iPoints = Math.floor(fPoints);

	// Points boundary checks and ( timeout check if quiz is using that).
	if ( iPoints > g_quiz.maxPointsPerQuestion 	) iPoints = g_quiz.maxPointsPerQuestion;
	if ( iPoints < g_quiz.minPointsPerQuestion 	) iPoints = g_quiz.minPointsPerQuestion;
	if ( g_quiz.questionTimeOutInSeconds > 0 && (fCurQuestionSeconds > g_quiz.questionTimeOutInSeconds) ) {
		iPoints = 0;
	}
	g_quiz.state.currentQuestionPoints = iPoints;
	

	jQuery('#scoreCurQuestionTextID').text('Current: ' + iPoints );
	
	
}
// ---------------------------------------
// --- Save/load from server functions ---
// ---------------------------------------

/** Load quiz from server. 
\param postDataObj Has either a quiz_name or a quiz_id like this { quiz_name: "MyQuiz" }
\param quizName: Name of quiz to load. Set to empty string if \a quizID is specified.
\param quizID: Precise ID of quiz to load. Set to 0 if \a quizName is specified.
\param doRunFn: Function to call, when quiz is loaded. Typically the one to actually run the quiz, 
                like for example quizDoRun().
\return Quiz (javascipt/json) object. */
function quizLoadFromServer(postDataObj, doRunFn )
{
	var quiz = g_quizError;
	$.ajax({
	type: 'POST',
	async: true,
	dataType: "json",
	url: "/quiz/ajax_handlers/quiz_load_handler.php",
	data: postDataObj,
	success: function(data) {
		doRunFn(data);
    },
    error: function(data) {
        console.log("Error loading quiz: " + postDataObj.quiz_name );
        console.log( data );
    }
	});
	return quiz;
}

function quizSaveToServer(quiz)
{
	console.log('quizSaveToServer');
	console.log(quiz);

	// Clone quiz object an remove state part, before saving
	var quizSave = {};
	clone(quizSave, quiz);
	quizSave.state = {};
	quizSave.songs = {}; // NOTE: We could perhaps choose to save the songs also
	var sJsonQuiz = JSON.stringify(quizSave, null, '\t');

	console.log(quizSave);
	
	$.ajax({
	type: 'POST',
	async: true,
	dataType: "json",
	url: "/quiz/ajax_handlers/quiz_save_handler.php",
	data: quizSave,
	success: function(data) {
		console.log('Quiz saved: ' + data );
    },
    error: function(data) {
        console.log("Error saving quiz: " + quiz.quiz_name );
        console.log( data );
    }
	});
	return quiz;
}

/** Tries to see if we have a request to auto load a specific quiz from the server. 
 The functions looks for these two possibilities - prioritized as shown:
 1) The global variable g_quizAutoLoadName has a value different from the empty string. 
 2) We look for URL parameters 'quiz_name_url' and 'quiz_cmd'. If 'quiz_cmd' == 'load' we atttemt to load
    the quiz named 'quiz_name'. Again we only try this secondary option if the global var in 1) is not set.
    Note that we decode the name if it comes from the URL using urlToName() function.
 */
function quizGetAutoLoadName ()
{
    if ( g_quizAutoLoadName != "" ) return g_quizAutoLoadName;
    if ( getUrlParameter("quiz_cmd") == "load" ) {
        return urlToName(getUrlParameter("quiz_name_url"));
    }
    return "";
}

function quizGetAutoLoadUrl (sBaseUrl)
{
    return sBaseUrl + "?quiz_cmd=load&quiz_name_url=" + nameToUrl(g_quiz.quiz_name) + "#createAndRunQuizPageID";
}

// -----------------------------
// --- Quiz player functions ---
// -----------------------------

/** Show player by resizing it. 
 */
function quizShowPlayer()
{
    apmSetSize(g_sCurrentProvider, 300, 200);
}


/** Show player by resizing it. 
 */
function quizHidePlayer()
{
    apmSetSize(g_sCurrentProvider, 1, 1);
}

// ------------------------
// --- Answer functions ---
// ------------------------

 
/** Get the ID for the correct answer. I.e. 'c0', 'c1', 'c2', ... */
function quizGetCorrectAnswerID( quizObj, iQuestionIndex )
{
////	var iCurQ = quizObj.state.current_question;
	return quizObj.questions[iQuestionIndex].answer;
}


/** Check if the answer given is correct. */
function quizIsAnswerCorrect( quizObj, iCurQ, userChoiceID )
{
	var correctID = quizGetCorrectAnswerID( quizObj, iCurQ );
	return userChoiceID == correctID;
}

function quizIsCurrentAnswerCorrect()
{
	var iCurQ = g_quiz.state.current_question;
	var userChoiceID = g_quiz.state.user_answers[g_quiz.state.current_question];
	var bIsAnswerCorrect = quizIsAnswerCorrect( g_quiz, iCurQ, userChoiceID );
	return bIsAnswerCorrect;
}

/** Get the text (original choice from question page) for the correct answer. */
function quizGetCorrectAnswerText( quizObj, iCurQ )
{
	var correctID = quizGetCorrectAnswerID( quizObj, iCurQ );
	var sSongName = quizObj.questions[iCurQ][correctID];
    var sArtistName = quizObj.questions[iCurQ].artist_name;
    return sSongName + " (" + sArtistName + ")";
}


/** Get comment for the answer page. If quizObj.state.comment_mode == 'none' we only return 
	'Correct' or 'Wrong' */
function quizGetAnswerComment( quizObj, bIsAnswerCorrect )
{
	// TODO: Enhance this with comments/insults system.
	if ( bIsAnswerCorrect ) 	return "Correct!";
	else						return "Wrong!"
}

// ----------------------------------------------------------------
// --- Media: Create questions and assign playObjects functions ---
// ----------------------------------------------------------------

/** Assign playObject to current question.
 We need an apmPlayObject assigned to the 'do_play_obj' field 
 in the current question. 
 This can be done in two ways:
  - Simple: In case there is a hard_coded (fixed_play_obj) associated with the question
    we simply assign that and we are done. 
  - Lookup: In case no such object exists we need to lookup the apmPlayObject 
    using the apmAsyncSearch() function and then find the best match. */
function assignPlayObject(quizObj)
{
    //var iQuestion = quizObj.state.current_question;
    var iQuestion = quizObj.state.current_question;
    
	if ( !quizObj.questions[iQuestion].do_play_obj ) {
		quizObj.questions[iQuestion].do_play_obj = quizObj.questions[iQuestion].fixed_play_obj;
	}
	
}
// --------------------------------
// --- Render functions: Common ---
// --------------------------------
function quizClearPage(domID) 
{ 
	jQuery('#'+domID).empty();
}

/** Render quiz start page. 
\param domID: ID om dom element to insert the HTML in.
\param quizObj: The quiz object. */
function quizRenderStartPage( quizObj )
{
//	jQuery('#'+domID).empty();
    // Header
    console.log("FIXME: quizRenderStartPage, quizShowPlayer() ");
    quizShowPlayer();
	var domID = 'quizAreaID';
	var headLine = "<div class=\"ui-bar ui-bar-b\"><h1>" + quizObj.quiz_name + "</h1></div>";
    $('#' + domID).append(headLine);

	var sIntro = quizObj.intro_text.replace('{num_questions}', quizObj.num_questions );
	var introText = jQuery("<span>" + sIntro + "</span><br>");
	introText.fadeIn(1000).appendTo('#' + domID);

	$('#' + domID).append("<h3>Pres 'Play' in player window to continue.</h3>");

// //     $('#' + domID).append("<a id=\"continueBtnID\" data-role=\"button\" href=\"#\" data-theme=\"a\" >Continue</a>");
// //     $('#' + domID).trigger("create");
// //     $('#continueBtnID').click(function() {
// //         quizClickNextState();
// //     });
}


function quizRenderSelectCommentModePage( quizObj  ) 
{ 
	var domID = 'quizAreaID';
	var headLine = jQuery("<span>Funny comments on my progress?</span><br>");
	headLine.fadeIn(500).appendTo('#' + domID);
	var radioBtnYes = jQuery
	(
		  "<input class=\"quizTextOptionInput\" type='radio' name='sel_commment_mode' value='funny' id='commentFunnyID' onclick='quizClickSelectCommentMode(this);' >"
		+ "<span class=\"quizTextOptionLine\" onclick=\"quizClickSelectCommentMode(document.getElementById('commentFunnyID'));\" >Yes! hit me. I\'m game.</span><br>"
		 
	);
	radioBtnYes.fadeIn(1000).appendTo('#' + domID);
	var radioBtnNo = jQuery
	(
		  "<input class=\"quizTextOptionInput\" type='radio' name='sel_commment_mode' value='none' id='commentNoneID' onclick='quizClickSelectCommentMode(this);' >"
		+ "<span class=\"quizTextOptionLine\" onclick=\"quizClickSelectCommentMode(document.getElementById('commentNoneID'));\" >No thanks</span><br>"
	);
	radioBtnNo.fadeIn(1000).appendTo('#' + domID);
}

/** Render curret page/state of quiz. */
function quizRenderCurrentState( quizObj )
{
	var domID = 'quizAreaID';
	var iCurQ = quizObj.state.current_question;
	var iNumQuestions = objectSize(quizObj.questions);
	if ( iCurQ < 0 ) {
		if 	( -1 == iCurQ ) quizRenderStartPage( quizObj );
// 		if 	( -2 == iCurQ ) quizRenderStartPage( quizObj );
// 		else 				quizRenderSelectCommentModePage( quizObj );
	}
	else if ( 0 <= iCurQ && iCurQ < iNumQuestions ) {
		if ( "question" == quizObj.state.subpage )	quizRenderQuestion(quizObj );
		else 										quizRenderAnswer(quizObj );
	}
	else if ( iCurQ == iNumQuestions ) {
		quizRenderResults(quizObj );
	}
	
}


/** Render one question page. 
\param domID: ID om dom element to insert the HTML in.
\param quizObj: The quiz object.
\param iQuestion: The question (i.e. number ) to render. */
function quizRenderQuestion(quizObj )
{
	if (quizObj.type == "text" ) {
		quizTextRenderQuestion(quizObj );
	}
	else if (quizObj.type == "media"){
		quizMediaRenderQuestion(quizObj );
	}
	else {
		console.log("Error: quizRenderQuestion: Unknown quiz type: " + quizObj.type );
	}
	g_quiz.state.questionTimeStartInMilliSeconds = g_timerInMilliSeconds;
	g_quiz.state.currentQuestionPoints = 10;
}


/** Render one answer page. 
\param domID: ID om dom element to insert the HTML in.
\param quizObj: The quiz object.
\param iQuestion: The question (i.e. number ) to render answer for. 
\param userChoice: Choice id (cN) that the user answerered. 
*/
function quizRenderAnswer(quizObj )
{
	if (quizObj.type == "text" ) {
		quizTextRenderAnswer(quizObj );
	}
	else if (quizObj.type == "media"){
		quizMediaRenderAnswer(quizObj );
	}
	else {
		console.log("Error: quizRenderAnswer: Unknown quiz type: " + quizObj.type );
	}
	g_quiz.state.questionTimeInMilliSeconds = g_timerInMilliSeconds - g_quiz.state.questionTimeStartInMilliSeconds;
	

	if ( quizIsCurrentAnswerCorrect() ) {
		g_quiz.state.totalScore += g_quiz.state.currentQuestionPoints;
	}
	jQuery('#scoreTotalTextID').text('Total Score: ' + g_quiz.state.totalScore );
	
}


/** Render results of quiz. */
function quizRenderResults( quizObj ) 
{ 
	if (quizObj.type == "text" ) {
		quizTextRenderResults(quizObj );
	}
	else if (quizObj.type == "media"){
		quizMediaRenderResults(quizObj );
	}
	else {
		console.log("Error: quizRenderResults: Unknown quiz type: " + quizObj.type );
	}
}

/** Advance current state to next and render the next page. */
function quizAdvanceAndRenderNextState( quizObj )
{
	quizNextState( quizObj );
	quizClearPage('quizAreaID');
	quizRenderCurrentState( quizObj );
}

// -----------------------------------
// --- Render functions: Text quiz ---
// ------------------------------------
/** Render one text question page. 
\param domID: ID om dom element to insert the HTML in.
\param quizObj: The quiz object.
\param iQuestion: The question (i.e. number ) to render. */
function quizTextRenderQuestion(quizObj )
{
    var domID = 'quizAreaID';
    var iQuestion = quizObj.state.current_question;
    
    // Header
    jQuery('#runningQuizHeaderID').text('Question ' + (iQuestion +1)+ ' / ' + quizObj.num_questions );
    
	// Render question
	var sQuestionHtml = "<div class=\"ui-bar ui-bar-b\" ><span>";
	sQuestionHtml += quizObj.questions[iQuestion].question;
	sQuestionHtml += "</span></div>";
    $('#' + domID).append(sQuestionHtml);
    $('#' + domID).trigger("create");
    
	// Render choices
    var ulStart = jQuery("<ul class=\"bullet  ap-corner-all\" id='optionsID' >");
    jQuery("<ul class='bullet  ap-corner-all' id='optionsID' >").fadeIn(500).appendTo('#' + domID);
    for ( var i=0; i < g_quiz.num_choices; i++)
    {
        var liElementID = "option" + i + "ID";
        var listItem = jQuery
        (
            "<li id='" + liElementID + "' class=\"ap-corner-all\" onclick=\"quizClickAnswerQuestionValue('c" + i + "');\" ></li>"
        );
        listItem.fadeIn(500).appendTo('#optionsID');

        var textDivID = "textDiv" + i + "ID";
        var textDiv = jQuery
        (
            "<div id='" + textDivID + "' class=\"ap-bullet-text\" >" 
            + quizObj.questions[iQuestion]["c"+i] + "</div>"
        );
        textDiv.fadeIn(500).appendTo('#' + liElementID );
        
    }
    jQuery("</ul>").fadeIn(600).appendTo('#' + domID);
}


/** Render one text answer page. 
\param domID: ID om dom element to insert the HTML in.
\param quizObj: The quiz object.
\param iQuestion: The question (i.e. number ) to render answer for. 
\param userChoice: Choice id (cN) that the user answerered. 
*/
function quizTextRenderAnswer(quizObj )
{
	var iCurQ = quizObj.state.current_question;
	var userChoiceID = quizObj.state.user_answers[quizObj.state.current_question];
	var bIsAnswerCorrect = quizIsAnswerCorrect( quizObj, iCurQ, userChoiceID );
	
	var sComment = quizGetAnswerComment(quizObj, bIsAnswerCorrect);
	var sCorrectAnswer = quizGetCorrectAnswerText(quizObj, iCurQ);
	
	var domID = 'quizAreaID';
	
	// Comment and ansver
	var sCommentHtml = "<div class=\"ui-bar ui-bar-b\"><h1>" + sComment + "</h1></div>";
//	var sAnswerHtml = "<div class=\"ui-bar ui-bar-b\" data-mini=\"true\" ><span>Answer is: <i>" + sCorrectAnswer + "</i></span></div>";
	var sAnswerHtml = "<span><b>Answer is: </b><i>" + sCorrectAnswer + "</i></span>";
    $('#' + domID).append(sCommentHtml);
    $('#' + domID).append(sAnswerHtml);

	
	// Render continue button
    $('#' + domID).append("<br><br><a id=\"continueBtnID\" data-role=\"button\" href=\"#\" data-theme=\"a\" >Continue</a>");
    
	$('#' + domID).trigger("create");

	$('#continueBtnID').click(function() {
		quizClickNextState();
	});
	
}

/** Render results of text quiz. */
function quizTextRenderResults( quizObj ) 
{ 
    // Header
    jQuery('#runningQuizHeaderID').text('Results' );
    
    var domID = 'quizAreaID';
    
// // 	var l1 = jQuery("<span>Quiz results:</span><br>");
// // 	l1.fadeIn(500).appendTo('#' + domID);

    // Get correct number of answers
	var iNumCorrect = 0;
	var iNumQuestions = objectSize(quizObj.questions);
	for ( var i=0; i < iNumQuestions; i++)
	{
		var userChoiceID = quizObj.state.user_answers[i];
		if ( quizIsAnswerCorrect( quizObj, i, userChoiceID ) ) iNumCorrect++;
	}

	//  Score and share link
    var sHtml = "<h1><b>Score: </b>" + quizObj.state.totalScore + "</h1>";
    sHtml += "<h5>You got <b>" + iNumCorrect + "</b> correct of <b>" + iNumQuestions + "</b> questions.</h5>";
    sHtml += "<p></p>";
    sHtml += "<a data-role='button' href='#shareQuizPageID' data-theme='a' >Share this</a>";
    sHtml += "<p>&nbsp;</p>";
    $('#' + domID).append(sHtml);
	
  
    var sSongList = "<ol data-role='listview' >";
    for ( var i=0; i < iNumQuestions; i++)
    {
        var sUrl = quizObj.questions[i].answer_url;
        var sUrlImage = quizObj.questions[i].answer_url_image;
        var sArtistName = quizObj.questions[i].artist_name;
        var sSongName = quizObj.questions[i].item_base_name;
        var sHtml = "<li><a href='" + sUrl + "' >";
        sHtml += "<img src='" + sUrlImage + "' />";
        sHtml += "<h3>" + sSongName + "</h3>";
        sHtml += "<p>" + sArtistName + "</p>";
        sHtml += "</a></li>";
        sSongList += sHtml;
    }
    
    sSongList += "</ol>";

    $('#' + domID).append(sSongList);
    $('#' + domID).trigger("create");
}

// ------------------------------------------
// --- Render functions: Audio/video quiz ---
// ------------------------------------------

/** Render one text question page. 
\param domID: ID om dom element to insert the HTML in.
\param quizObj: The quiz object.
\param iQuestion: The question (i.e. number ) to render. */
function quizMediaRenderQuestion(quizObj )
{
    var iQuestion = quizObj.state.current_question;
    if ( iQuestion == 0 ) {
        console.log("FIXME: Hiding player");
        quizHidePlayer();
    }
    quizTextRenderQuestion(quizObj );
	assignPlayObject(quizObj);
	apmAudioVideoLoadAndplay(quizObj.questions[iQuestion].do_play_obj);
    apmPlay();
}


/** Render one text answer page. 
\param domID: ID om dom element to insert the HTML in.
\param quizObj: The quiz object.
\param iQuestion: The question (i.e. number ) to render answer for. 
\param userChoice: Choice id (cN) that the user answerered. 
*/
function quizMediaRenderAnswer(quizObj )
{
    quizTextRenderAnswer(quizObj);
}


function quizMediaRenderResults( quizObj ) 
{ 
	apmStop();
    quizTextRenderResults( quizObj );
}


// ----------------------------------------
// --- Mouse click handlers (functions) ---
// ----------------------------------------

function quizClickSelectCommentMode(answerElem) 
{ 
	g_quiz.state.comment_mode = answerElem.value;
	quizClickNextState();
}


function quizClickAnswerQuestion(answerElem) 
{ 
	g_quiz.state.user_answers[g_quiz.state.current_question] = answerElem.value;
	quizAdvanceAndRenderNextState( g_quiz );
}

function quizClickAnswerQuestionValue(answerValue) 
{ 
	g_quiz.state.user_answers[g_quiz.state.current_question] = answerValue;
	quizAdvanceAndRenderNextState( g_quiz );
}



function quizClickNextState() 
{ 
	quizAdvanceAndRenderNextState( g_quiz );
}

// -----------------------
// --- State functions ---
// -----------------------
/** Are we at the last answer page in the quiz ?*/
function quizIsStateLastAnswer( quizObj )
{
	var iCurQ = quizObj.state.current_question;
	if ( iCurQ == objectSize(quizObj.questions) -1 ) {
		return "answer" == quizObj.state.subpage;
	}
	return false;
}


/** Advance current state to next. */
function quizNextState( quizObj )
{
	var iCurQ = quizObj.state.current_question;
	var iNumQuestions = objectSize(quizObj.questions);
	
	if ( iCurQ < 0 ) quizObj.state.current_question = iCurQ +1;
	else if ( 0 <= iCurQ && iCurQ <= (iNumQuestions -1) ) {
		// --- iCurQ >= 0 --- 
		if ( "question" == quizObj.state.subpage ) quizObj.state.subpage = "answer";
		else {
			quizObj.state.current_question = iCurQ +1;
			quizObj.state.subpage = "question";
		}
	}
	else if ( iCurQ == iNumQuestions  ) {
        console.log("quizNextState DONE page" );
        quizObj.state.current_question = iCurQ +1;
        quizObj.state.subpage = "question";
    }
	else {
        console.log("quizNextState ELSE" );
		quizObj.state.subpage = "";
		quizObj.state.current_question = iCurQ +1;
	}
	console.log("quizNextState New current state is: question=" + quizObj.state.current_question + " subpage='" + quizObj.state.subpage + "' Num questions: " + objectSize(quizObj.questions) );
}

// -------------------------------
// --- Auto generate functions ---
// -------------------------------

function quizAutoGenerateQuiz( quizObj )
{
    quizObj.autogen_question_index++;
    ////quizObj.auto_gen_recour_counter++;
    ////console.log( "FIXME quizAutoGenerateQuiz, songsearch_recour_counter: " + quizObj.songsearch_recour_counter );
    if ( (quizObj.autogen_question_index < quizObj.num_questions) && 
		 (quizObj.songs.length >= quizObj.num_choices) &&
		 (quizObj.songsearch_recour_counter < (quizObj.num_questions+100)) // fail safe 
	  ) 
	{
        var bestMatchObj = getEmptyBestMatchObject();
        quizAutoPickAnswer(quizObj, bestMatchObj, null ) ;
    }
    else {
		quizObj.num_questions = quizObj.questions.length;
		quizSaveToServer(quizObj);
        quizDoRun( quizObj );
    }
}

function onAnswerPickedDefaultHandler(quizObj, bestMatchObj) 
{ 
    quizInsertQuestion(quizObj, bestMatchObj);
    quizAutoGenerateQuiz(quizObj );
}

function quizAutoPickAnswer(quizObj, bestMatchObj, onAnswerPickedFun ) 
{ 
    if ( !quizAutoCanWeUseMatch(bestMatchObj) ) {
        quizAutoPickAnswerFromSongsArray(quizObj, quizObj.songs, bestMatchObj, onAnswerPickedFun);
    }
    else {
        bestMatchObj.answer_url = quizObj.songs[bestMatchObj.song_index_selected].url;
        bestMatchObj.answer_url_image = quizObj.songs[bestMatchObj.song_index_selected].url_image;
        quizObj.songs.splice(bestMatchObj.song_index_selected, 1); // Remove selected song from list
        if (onAnswerPickedFun) {
            onAnswerPickedFun(quizObj, bestMatchObj);
        }
        else {
            onAnswerPickedDefaultHandler(quizObj, bestMatchObj);
        }
    }
}


/** Picks an answer from a songs array. */
function quizAutoPickAnswerFromSongsArray(quizObj, aSongs, bestMatchObj, onAnswerPickedFun) 
{ 
    var iIndex  = randomInt(0, aSongs.length -1);
    var songObj = aSongs[iIndex]; 
    
    var searchFor = { artist_name : songObj.artist_name, item_base_name: songObj.item_base_name };
//    var searchFor = { artist_name : 'Hej Matematik', item_base_name: 'Walkman' }; // FIXME: DEBUG ONLY!
    quizObj.songsearch_recour_counter++;    
    apmAsyncSearch(
          searchFor
        , 30
        , function()
        {
            var newBestMatchObj = apmBestMatchFromList(g_apmPlaylist, searchFor, bestMatchObj);
            newBestMatchObj.song_index_selected = iIndex;
            quizAutoPickAnswer(quizObj, newBestMatchObj, onAnswerPickedFun);
        }
        , null );
}

/** Picks a wrong answer option from a songs array. */
function quizAutoPickWrongAnswerOptionFromSongsArray(aSongs) 
{ 
    var iIndex  = randomInt(0, aSongs.length -1);
    var songObj = aSongs[iIndex]; 
    aSongs.splice(iIndex, 1); // Remove selected song from list
    return songObj;
}


// -----------------------------
// --- Quiz helper functions ---
// -----------------------------

function quizInsertQuestion(quizObj, bestMatchObj) 
{ 
    var questionObj = quizGetWrongAnswersQuestionObject(quizObj);
    var sCorrectAnswerKey = questionObj.answer;
    questionObj[sCorrectAnswerKey] = bestMatchObj.search_for.item_base_name;
    questionObj.answer_url = bestMatchObj.answer_url;
    questionObj.answer_url_image = bestMatchObj.answer_url_image;
    
////    questionObj.search_for = bestMatchObj.search_for;
    questionObj.artist_name = bestMatchObj.search_for.artist_name;
    questionObj.item_base_name = bestMatchObj.search_for.item_base_name;
    questionObj.fixed_play_obj = bestMatchObj.play_object;
    questionObj.cadidate_list = bestMatchObj.cadidate_list;
 //   questionObj.best_match_obj = bestMatchObj;
    quizObj.questions[quizObj.autogen_question_index] = questionObj;
}


function quizGetWrongAnswersQuestionObject(quizObj) 
{ 
    //Get 'c0', 'c1', 'c2', ... randomly depending on number of options per question
    var iCorrectIndex  = randomInt(0, quizObj.num_choices -1);
    var sCorrectAnswerKey = "c" + iCorrectIndex;
    
    var questionObj = { 
        question : "Which song is playing?", 
        answer : sCorrectAnswerKey
    };
    for ( i = 0; i < quizObj.num_choices; i++ ) {
        if ( i == iCorrectIndex ) continue;
        var wrongAnswerObj = quizAutoPickWrongAnswerOptionFromSongsArray(quizObj.songs);
        var sWrongAnswerKey = "c" + i;
        questionObj[sWrongAnswerKey] = wrongAnswerObj.item_base_name;
    }
    return questionObj;
}



function quizAutoCanWeUseMatch(bestMatchObj)
{
//    console.log("FIXME quizAutoCanWeUseMatch");
//    console.log(bestMatchObj);
    if ( -1 == bestMatchObj.recur_counter ) {
        bestMatchObj.recur_counter = 0;
        return false;
    }
    bestMatchObj.recur_counter = bestMatchObj.recur_counter +1;
    if (bestMatchObj.play_object == null ) return false;
    //var 
    if ( bestMatchObj.candidate_dist > 1 ) return false;
    ////if ( bestMatchObj.recur_counter < 5 ) return false; // FIXME: Debug only !!!
    return true;
}

function getEmptyBestMatchObject()
{
    return { recur_counter : -1 };
}

