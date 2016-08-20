
var g_timerInMilliSecondsHandle = null;
var g_timerInMilliSeconds = 0;
//var g_quizCurrentState = {};
var g_quiz = { quiz_name : "", state: null };
var g_quizAutoLoadName = "";        // For use when auto loading a named quiz
var g_quizAutoLoadArtistName = "";  // For use when autoloading an artist name
var g_quizAutoLoadThemeName = "";   // For use when autoloading a theme

////        var postDataObj = { user_id: g_apUser.data.user_id, quiz_name: g_quiz.quiz_name, score: g_quiz.state.totalScore };


var g_quizError = {
	  quiz_name: ""
	, intro_text: "Simple default debug quiz."
	, author_email: "ml@airplaymusic.dk"
	, author_name: ""
	, type: "text" // 'text', 'audio', 'video'
	, num_questions: 0
	, num_choices: 0
	, image_mode : "none" // 'none', 'per_question', 'per_choice'
	, questions : null
};

// --------------------------
// --- Init/Run functions ---
// --------------------------

/** Sets (global) quiz object to initial value. */
function resetQuiz()
{
    g_quiz = { quiz_name : "", state: null };
}

/** Sets (global) quiz object to quiz object given. */
function setQuiz(quizObj)
{
    g_quiz = quizObj;
//    if ( !g_quiz.name || g_quiz.name == "" || !g_quiz.state || g_quiz.state == "" ) {
    if ( g_quiz.name == "" || !g_quiz.state || g_quiz.state == "" ) {
        console.log("DBG: resettting ...");
        resetQuiz();
    }
}

/** For resetting g_quiz.state when starting a new quiz.
Use like this:
g_quiz.state = quizGetStartStateObj();
.*/ 
function quizGetStartStateObj()
{
	return { 
	  current_question 	        : -1
	, subpage 			        : "question"
	, num_correct 		        : 0
	, pct_correct 		        : 0
	, comment_mode 		        : "none"
	, timerInMilliSeconds	    : 0
	, do_handle_on_song_started : false
	, user_answers 		        : [] 
	};
}

function quizValid()
{ 
    if (!g_quiz.state) return false;
    if (!g_quiz.quiz_name || g_quiz.quiz_name == "") return false;
    return true;
}

/** Have we got a valid (completed) quiz ?*/
function quizCompleted() 
{ 
    if ( !quizValid() ) return false;
    
    var iCurQ = g_quiz.state.current_question;
    var iNumQuestions = objectSize(g_quiz.questions);
    return iCurQ >= iNumQuestions;
}

/** Have we got an active quiz running ? */
function quizRunning() 
{ 
    if (!quizValid()) return false;
    var iCurQ = g_quiz.state.current_question;
    var iNumQuestions = objectSize(g_quiz.questions) || 0;
    return 0 <= iCurQ && iCurQ < iNumQuestions
}


function quizDoRun(quizObj) 
{ 
	quizDoStop();
	quizObj.state = quizGetStartStateObj();
	g_quiz = quizObj;
    
	quizTimerStart();
	quizClearPage('quizAreaID');
	
    if ( -1 == quizObj.autogen_question_index ) {
        jQuery('#runningQuizHeaderID').text('Creating...');
        jQuery('#creatingQuizImageID').show();
        quizObj.auto_gen_recour_counter = 0;
        quizObj.songsearch_recour_counter = 0;
        quizAutoGenerateQuiz(quizObj);
    }
    else {
		// --- Ensure some default values ---
        youtubeCreatePlayer(300,200);
        quizObj.state.current_question -1;
        //quizObj.state = quizGetStartStateObj();
		quizObj.minPointsPerQuestion = quizObj.minPointsPerQuestion || 1;
		quizObj.maxPointsPerQuestion = quizObj.maxPointsPerQuestion || 50;
		quizObj.pointsDecreaseIntervalInSeconds = quizObj.pointsDecreaseIntervalInSeconds || 1; 
		quizObj.questionTimeOutInSeconds = quizObj.questionTimeOutInSeconds || 0; 
        if (quizObj.start_pos_in_seconds == -1) quizObj.start_pos_in_seconds = 30;
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
    
	if (!quizValid()) return;
    var iCurQ = g_quiz.state.current_question;

    // Handle state == -1 (quiz_ready). We check to see if user has activated (Youtube) player
    // and if so advances to first question in quiz.
    if ( iCurQ == -1 ) {
        if ( g_timerInMilliSeconds % 1000 == 0 ) {
            if ( apmCurrentPlayingTime() > 0.2 ) {
                quizClickNextState();
            }
        }
    }
	
	if ( !quizRunning() ) return;
	if ( g_quiz.state.subpage != "question" ) return; // Answer page. We should not update points for current question 

	// --- See if song is started and we should do the 'handler' ---
    if ( apmCurrentPlayingTime() > 0.01 && g_quiz.state.do_handle_on_song_started ) {
        g_quiz.state.do_handle_on_song_started = false;
        var iQuestion = g_quiz.state.current_question;
        var iStartPos = quizGetSongStartPos(iQuestion);
        if ( iStartPos > 0 ) {
            apmSeek(null, iStartPos);
        }
        // Um mute and set start time
        apmUnMute();
        g_quiz.state.questionTimeStartInMilliSeconds = g_timerInMilliSeconds;
    }

	
	// ------------------------------------------
	// --- Update points for current question ---
    // ------------------------------------------
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
	
    // --- Debug only ---
//    if ( g_timerInMilliSeconds % 1000 == 0 ) {
//        console.log("DBG_ML: current question: " + g_quiz.state.current_question + " quizRunning(): " + quizRunning() );
//        console.log("apmCurrentPlayingTime: " + apmCurrentPlayingTime() );
//         console.log("fPointsMax: " + fPointsMax );
//         console.log("fCurQuestionSeconds: " + fCurQuestionSeconds );
//         console.log("pointsDecreaseIntervalInSeconds: " + g_quiz.pointsDecreaseIntervalInSeconds );
//        console.log("timerInMilliSeconds: " + g_timerInMilliSeconds  + " , g_quiz.state.currentQuestionPoints: " + g_quiz.state.currentQuestionPoints );
//    }

	jQuery('#scoreCurQuestionTextID').text('Current: ' + iPoints );
	
	
}
// ---------------------------------------
// --- Save/load from server functions ---
// ---------------------------------------

/** Load quiz from server. 
\param postDataObj Has either a quiz_name or a quiz_id like this { quiz_name: "MyQuiz" }
\param doRunFn: Function to call, when quiz is loaded. Typically the one to actually run the quiz, 
                like for example quizDoRun().
*/
function quizLoadFromServer(postDataObj, doRunFn )
{
	var quiz = g_quizError;
	$.ajax({
        type: 'POST',
        async: true,
        dataType: "json",
        url: "/ajax_handlers/quiz_load_handler.php",
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
	// Clone quiz object an remove state part, before saving
	var quizSave = {};
	clone(quizSave, quiz);
	quizSave.state = {};
	quizSave.songs = {}; // NOTE: We could perhaps choose to save the songs also

	$.ajax({
        type: 'POST',
        async: true,
        dataType: "json",
        url: "/ajax_handlers/quiz_save_handler.php",
        data: quizSave,
        success: function(data) {
        },
        error: function(data) {
            console.log("Error saving quiz: " + quiz.quiz_name );
            console.log( data );
        }
	});
	return quiz;
}


function quizSaveCurrentToServer()
{
    var quizSave = {};
    clone(quizSave, g_quiz);
    quizSave.songs = {}; // NOTE: Seems to exceed POST size ,if we include songs!

    $.ajax({
        type: 'POST',
        async: true,
        dataType: "json",
        url: "/ajax_handlers/quiz_save_current_handler.php",
        data: quizSave,
        success: function(data) {
        },
        error: function(data) {
            console.log("Error saving current quiz in _SESSION: " + quiz.quiz_name );
            console.log( data );
        }
    });
}

/** Load current quiz from server _SESSION into global g_quiz object. 
 */
function quizLoadCurrentFromServer()
{
    $.ajax({
        type: 'POST',
        async: true,
        dataType: "json",
        url: "/ajax_handlers/quiz_load_current_handler.php",
        data: {},
        success: function(data) {
            setQuiz(data);
        },
        error: function(data) {
            console.log("Error loading current quiz from _SESSION");
            console.log( data );
            resetQuiz();
        }
    });
}


/** Load quiz highscore list from server. 
\param postDataObj An object similar to this { quiz_name: "MyQuiz" } or null to use default
\param sRenderDomID: Where to render the highscore list when loaded.
\return Quiz (javascipt/json) object. */
function quizLoadHighScoreList(postDataObj, sRenderDomID )
{
    postDataObj = postDataObj || { user_id: g_apUser.data.user_id, quiz_name: g_quiz.quiz_name, score: g_quiz.state.totalScore };
    sRenderDomID = sRenderDomID || 'showHighScorePageContentID';
    $.ajax({
        type: 'POST',
        async: true,
        dataType: "json",
        url: "/ajax_handlers/quiz_highscore_list_handler.php",
        data: postDataObj,
        success: function(data) {
            quizHighScoreRenderResults(data, sRenderDomID);
        },
        error: function(data) {
            console.log("Error loading highscore list for quiz: " + postDataObj.quiz_name );
            console.log( data );
        }
    });
}


/** Quiz save score function. 
 * IMPLEMENT: Not implemented yet. Perhaps we don't need this since the highscore list 
 * handler also saves the score of the current quiz!
 \todo Implement me! */
function quizSaveScore(quiz)
{
    console.log('quizSaveScore');
    console.log(quiz);
    
    var postDataObj = {};
    
    $.ajax({
        type: 'POST',
        async: true,
        dataType: "json",
        url: "/ajax_handlers/quiz_highscore_save_handler.php",
        data: postDataObj,
        success: function(data) {
            console.log('Score saved saved: ' + data );
        },
        error: function(data) {
            console.log("Error saving score: " + quiz.quiz_name );
            console.log( data );
        }
    });
    return quiz;
}


// ------------------------
// --- Cookie functions ---
// ------------------------

/** Save URL commands and parameters related to quiz_cmd paramters. 
The commands are saved in a cookie. */
function quizSaveUrlCommandsToCookie()
{
    if ( window.location.href.indexOf("quiz_cmd") != -1 ) {
        var quiz_cmd = getUrlParameter("quiz_cmd");
        var quiz_name_url = getUrlParameter("quiz_name_url");
        var artist_names_url = getUrlParameter("artist_names_url");
        var quiz_theme_names_url = getUrlParameter("quiz_theme_names_url");
        if ( quiz_cmd != "" ) { 
            document.cookie="quiz_cmd=" + quiz_cmd + ";path=/";
            document.cookie="quiz_name_url=" + quiz_name_url + ";path=/";
            document.cookie="artist_names_url=" + artist_names_url + ";path=/";
            document.cookie="quiz_theme_names_url=" + quiz_theme_names_url + ";path=/";
            console.log("..." );
            
            return true;
        }
        return false
    }
    return false;
}

/** Clear URL commands and parameters related to quiz_cmd paramters from the cookie. */
function quizClearUrlCommandsInCookie()
{
    document.cookie="quiz_cmd= ;path=/";
    document.cookie="quiz_name_url= ;path=/";
    document.cookie="artist_names_url= ;path=/";
    document.cookie="quiz_theme_names_url= ;path=/";
}



/** Tries to see if we have a request to auto load a specific quiz from the server. 
 The function looks for these two possibilities - prioritized as shown:
 1) The global variable g_quizAutoLoadName has a value different from the empty string. 
 2) We look for URL parameters saved in our cookie 'quiz_name_url' and 'quiz_cmd', if 'quiz_cmd' == 'load'.
 \return The quiz name to autoload if any or an empty string. */
function quizGetAutoLoadName ()
{
    if ( g_quizAutoLoadName != "" ) return g_quizAutoLoadName;
    if ( getCookie("quiz_cmd") == "load" ) {
        return urlToName(getCookie("quiz_name_url"));
    }
    return "";
}

/** Tries to see if we have a request to auto load a specific artist name from the server. 
 1) The global variable g_quizAutoLoadArtistName has a value different from the empty string. 
 2) We look for URL parameters saved in our cookie 'artist_names_url' and 'quiz_cmd', if 'quiz_cmd' == 'load_artist'. 
 \return The quiz_theme_name(s) to autoload if any or an empty string. */
function quizGetAutoLoadArtistName ()
{
    if ( g_quizAutoLoadArtistName != "" ) return g_quizAutoLoadArtistName;
    if ( getCookie("quiz_cmd") == "load_artist" ) {
        return urlToName(getCookie("artist_names_url"));
    }
    return "";
}


/** Tries to see if we have a request to auto load a specific theme from the server. 
 1) The global variable g_quizAutoLoadThemeName has a value different from the empty string. 
 2) We look for URL parameters saved in our cookie 'quiz_theme_names_url' and 'quiz_cmd', if 'quiz_cmd' == 'load_theme'. 
 \return The quiz_theme_name(s) to autoload if any or an empty string. */
function quizGetAutoLoadThemeName ()
{
    if ( g_quizAutoLoadThemeName != "" ) return g_quizAutoLoadThemeName;
    if ( getCookie("quiz_cmd") == "load_theme" ) {
        return urlToName(getCookie("quiz_theme_names_url"));
    }
    return "";
}


// ---------------------
// --- URL functions ---
// ---------------------

/** Get basic page URL of this quiz. Used for generating for example autoload URL links.
 Examples:
 https://quiz.airplaymusic.dk 
 https://quiz.airplaymusic.dk?partner_name=anr
 https://apps.facebook.com/airplaymusic/
 https://apps.facebook.com/airplaymusic/?partner_name=anr
 */
function quizGetBasePageUrl()
{
    var url = parentFrameUrl();
    var paramsSave = "";
    if ( url.indexOf("partner_name") != -1) {
        paramsSave = "?partner_name=" + getUrlParameter("partner_name");
    }
    
    // Remove parameters and anchors
    var i = url.indexOf("#");
    if ( i >= 0 ) url = url.slice(0,i);
    i = url.indexOf("?");
    if ( i >= 0 ) url = url.slice(0,i);
    url = url + paramsSave;
    return url;
}


function quizGetAutoLoadUrl (sBaseUrl)
{
    sBaseUrl = sBaseUrl || quizGetBasePageUrl();
    var glue = "?";
    if ( sBaseUrl.indexOf("?") != -1 ) glue = "&";
    return sBaseUrl + glue + "quiz_cmd=load&quiz_name_url=" + nameToUrl(g_quiz.quiz_name);
}

/** Get highscore: {user_id quiz_name, score} from either cookie (set from URL params and then reload) 
 * OR global g_quiz object (in case we have just completed a quiz).*/
function quizGetHighScorePostData ()
{
    if ( getCookie("quiz_cmd") == "show_highscore" ) {
        var sQuizName = urlToName(getCookie("quiz_name_url"));
        quizClearUrlCommandsInCookie();
        return { user_id: g_apUser.data.user_id, quiz_name: sQuizName, score: -1 };
    }
    else if ( quizCompleted() ) {
        return { user_id: g_apUser.data.user_id, quiz_name: g_quiz.quiz_name, score: g_quiz.state.totalScore };
    }
    
    return {};
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
    domID = domID || 'quizAreaID';
	jQuery('#'+domID).empty();
}


/** Render quiz start page. 
\param domID: ID om dom element to insert the HTML in.
\param quizObj: The quiz object. */
function quizRenderStartPage( quizObj )
{
    // Header
    var domID = '#quizAreaID';
    jQuery(domID).empty();
    quizShowPlayer();
	var headLine = "<div class=\"ui-bar ui-bar-b\"><h1>" + quizObj.quiz_name + "</h1></div>";
    $(domID).append(headLine);

	var sIntro = quizObj.intro_text.replace('{num_questions}', quizObj.num_questions );
	var introText = jQuery("<span>" + sIntro + "</span><br>");
	introText.fadeIn(1000).appendTo(domID);

	$(domID).append("<h3>Pres 'Play' in player window to continue.</h3>");

    // --- Reset display of points ---
    jQuery('#scoreCurQuestionTextID').text('Current: ');
    jQuery('#scoreTotalTextID').text('Total Score: ');
    
// //     $(domID).append("<a id=\"continueBtnID\" data-role=\"button\" href=\"#\" data-theme=\"a\" >Continue</a>");
// //     $(domID).trigger("create");
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
        $.mobile.pageContainer.pagecontainer('change', '#resultsPageID', {} )
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
////	g_quiz.state.questionTimeStartInMilliSeconds = g_timerInMilliSeconds;
	/////g_quiz.state.currentQuestionPoints = 10;
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
    quizObj = quizObj || g_quiz;
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
    quizSaveCurrentToServer();
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
        var iOptionNumber = i + 1;
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
            + iOptionNumber + ". " + quizObj.questions[iQuestion]["c"+i] + "</div>"
        );
        textDiv.fadeIn(500).appendTo('#' + liElementID );
        
    }
    jQuery("</ul>").fadeIn(600).appendTo('#' + domID);
}


/** Render one text answer page. 
\param quizObj: The quiz object.
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
    
    ////var domID = 'quizAreaID';
    var domID = '#resultsPageContentID';
    jQuery(domID).empty();

    // Get correct number of answers
	var iNumCorrect = 0;
	var iNumQuestions = objectSize(quizObj.questions);
	for ( var i=0; i < iNumQuestions; i++)
	{
		var userChoiceID = quizObj.state.user_answers[i];
		if ( quizIsAnswerCorrect( quizObj, i, userChoiceID ) ) iNumCorrect++;
	}

	//  Score and share link
	var sHtml = "";
    sHtml += "<h3>" + quizObj.quiz_name + "</h3>";
    sHtml += "<h1><b>Score: </b>" + quizObj.state.totalScore + "</h1>";
    sHtml += "<h5>You got <b>" + iNumCorrect + "</b> correct of <b>" + iNumQuestions + "</b> questions.</h5>";
    sHtml += "<p></p>";
    sHtml += "<a data-role='button' href='#shareQuizPageID' data-theme='a' >Share this</a>";
    sHtml += "<p>&nbsp;</p>";
	
  
    sHtml += "<ol data-role='listview' >";
    for ( var i=0; i < iNumQuestions; i++)
    {
        var sUrl = quizObj.questions[i].answer_url;
        var sUrlImage = quizObj.questions[i].answer_url_image;
        var sArtistName = quizObj.questions[i].artist_name;
        var sSongName = quizObj.questions[i].item_base_name;
        var s = "<li><a target='_blank' href='" + sUrl + "' >";
        s += "<img src='" + sUrlImage + "' />";
        s += "<h3>" + sSongName + "</h3>";
        s += "<p>" + sArtistName + "</p>";
        s += "</a></li>";
        sHtml += s;
    }
    
    sHtml += "</ol>";

    $(domID).append(sHtml);
    $(domID).trigger("create");
}

// ------------------------------------------
// --- Render functions: Audio/video quiz ---
// ------------------------------------------

/** Render one text question page. 
\param domID: ID om dom element to insert the HTML in.
\param quizObj: The quiz object.
\param iQuestion: The question (i.e. number ) to render. */
function quizMediaRenderQuestion(quizObj)
{
    var iQuestion = quizObj.state.current_question;
    if ( iQuestion == 0 ) {
        quizHidePlayer();
    }
    quizObj.state.do_handle_on_song_started = true;
    apmMute();
    quizTextRenderQuestion(quizObj );
	assignPlayObject(quizObj);
	apmAudioVideoLoadAndplay(quizObj.questions[iQuestion].do_play_obj);
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


// -----------------------------------
// --- Render functions: HighScore ---
// -----------------------------------
/** Render highscore list. */
function quizHighScoreRenderResults( highScoreObj, domID ) 
{ 
    domID = domID || 'showHighScorePageContentID';
    quizClearPage(domID);
    
    //  Score and share link
    var sHtml = "<h1>" + highScoreObj.quiz_name + "</h1>";
    
    if ( highScoreObj.user_id == g_apUser.data.user_id ) {
        sHtml += "<p></p>";
        sHtml += "<h3>Your score is: " + highScoreObj.score + "</h3>";
    }
    
    sHtml += "<p></p>";
    sHtml += "<a data-role='button' href='#shareQuizPageID' data-theme='a' >Share this</a>";
    sHtml += "<p>&nbsp;</p>";
    $('#' + domID).append(sHtml);
    
  
    var sList = "<ol data-role='listview' >";
    for ( var i=0; i < highScoreObj.list.length; i++)
    {
        var sUrl = "";
        var sUserImageUrl = highScoreObj.list[i].profile_image_url;
        var sScore = highScoreObj.list[i].score;
        var sUserName = highScoreObj.list[i].user_name;
        var sHtml = "<li>";
        sHtml += "<img src='" + sUserImageUrl + "' />";
        sHtml += "<h3>" + sUserName + "</h3>";
        sHtml += "<p>" + sScore + "</p>";
        sHtml += "</li>";
        sList += sHtml;
    }
    
    sList += "</ol>";

    $('#' + domID).append(sList);
    $('#' + domID).trigger("create");
}

/** Render highscore login page. */
function quizHighScoreRenderLogin( domID ) 
{ 
    domID = domID || 'showHighScorePageContentID';
    quizClearPage(domID);
    
    //  Long message and button
    var sHtml = "<h1>Please login</h1>";
    sHtml += "<p>" 
    sHtml += "In order for us to save your score and to show you the list of the others users, who has taken this quiz, we need you to please login."; 
    sHtml += "<br>";
    sHtml += "Thank you!" 
    sHtml += "</p>";
    sHtml += "<button id='highScorePagePageLogInOutBtnID' class='logInOut' href='' data-theme='a' >Login</button>";
    $('#' + domID).append(sHtml);
    $('#' + domID).trigger("create");
    $("#highScorePagePageLogInOutBtnID").click(function (e) {
        var postActionsObj = userGetDefaultLoginPostActionsObj();
        postActionsObj.on_user_logged_in = function () { $.mobile.pageContainer.pagecontainer('change', '#showHighScorePageID', {changeHash: false, allowSamePageTransition: true} ) };
        userLogInOut(postActionsObj);
    });
    userLoginButtonsUpdate(); // Mostly needed for translation, since here it should always be "Login" if this is shown.
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
        //console.log("quizNextState DONE page" );
        quizObj.state.current_question = iCurQ +1;
        quizObj.state.subpage = "question";
    }
	else {
        //console.log("quizNextState ELSE" );
		quizObj.state.subpage = "";
		quizObj.state.current_question = iCurQ +1;
	}
	//console.log("quizNextState New current state is: question=" + quizObj.state.current_question + " subpage='" + quizObj.state.subpage + "' Num questions: " + objectSize(quizObj.questions) );
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
//    var searchFor = { artist_name : 'Hej Matematik', item_base_name: 'Walkman' }; // DEBUG ONLY!
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
    ////if ( bestMatchObj.recur_counter < 5 ) return false; 
    return true;
}

function getEmptyBestMatchObject()
{
    return { recur_counter : -1 };
}


function quizGetSongStartPos( iQuestion )
{
    iQuestion = iQuestion || g_quiz.state.current_question;
    var playObj = g_quiz.questions[iQuestion].do_play_obj;
    var iStartPos = playObj.start_pos_in_seconds || g_quiz.start_pos_in_seconds || 0;
//     console.log("playObj.start_pos_in_seconds: " + playObj.start_pos_in_seconds);
//     console.log(playObj.start_pos_in_seconds);
//     console.log("g_quiz.start_pos_in_seconds: " + g_quiz.start_pos_in_seconds);
//     console.log(g_quiz.start_pos_in_seconds);
    return Number(iStartPos);
}

