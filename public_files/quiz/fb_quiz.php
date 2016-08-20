<?php
require_once ( __DIR__ . '/../../aphp/aphp_fix_include_path.php' );
require_once ( __DIR__ . '/../public_files_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('quiz/classes/PagesCommon.php');

$name = 'Mobile generate song quiz test';
$pc = new PagesCommon();

echo $pc->htmlHeaderStart("{$name}");
echo "<script src='/js/jquery.mobile-1.3.1.min.js'></script>";
echo $pc->htmlHeaderEnd();
// echo $pc->pageTopContents($name);

?>
<!-- 
************************
***  quizWelcomePage ***
************************ -->
<div data-role="page" id="quizWelcomePageID" class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <h5>Airplay Music</h5>
    </div>
    <div data-role="content">
        <h3>Challenge yourself and friends on music</h3>
        <a data-role="button" href="#autoCreateQuizPageID" data-transition="" data-theme="a" >Create from artist</a>
        <a data-role="button" href="#selectThemeMainPageID" data-transition="" data-theme="a" >Create from theme</a>
        <a data-role="button" href="#searchQuizPageID" data-theme="a" >Search quiz</a>
        <a data-role="button" href="#shareMainQuizPageID" data-theme="a" >Share this</a>
		<div data-role="collapsible" data-theme="a" data-content-theme="a" >
            <h3>Help</h3>
            <p>Easily generate song quizzes from your favorite artists in a few clicks. 
                <ol>
                    <li>Hit the 'Create from artist' button.</li>
                    <li>Type in your favorite artist(s).</li>
                    <li>Go!</li>
                </ol>
            </p>
            <p>Or: easily generate song quizzes from themes like 80s, charts, christmas, dance, ... in a few clicks. 
                <ol>
                    <li>Hit the 'Create from theme' button.</li>
                    <li>Select your theme.</li>
                    <li>Go!</li>
                </ol>
            </p>
        </div>
    </div>
</div>
<!-- 
***************************
***  autoCreateQuizPage ***
*************************** -->
<div data-role="page" id="autoCreateQuizPageID" data-add-back-btn="false" class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Auto create quiz</h5>
        <a href="/quiz/mobile_quiz_test.php" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content">
		<legend>Artist name:</legend>
		<table style='width:100%'>
			<tbody>
			<tr>
				<td>
					<input id="searchArtist1ID" class="searchArtist" type="text" />
				</td>
				<td style='width:3em; padding-left:1em'>
					<a id="clearArtistNamesBtnID" data-role="button" data-icon="delete" data-mini="true" >names</a>
				</td>
			</tr>
			</tbody>    
		</table>
    
        <div data-role="collapsible" data-theme="c" data-content-theme="c" data-mini="true">
            <h5>Add artists</h5>
			<input id="searchArtist2ID" class="searchArtist" type="text" >
			<input id="searchArtist3ID" class="searchArtist" type="text" >
			<input id="searchArtist4ID" class="searchArtist" type="text" >
        </div>
        <fieldset data-role="controlgroup" data-type="horizontal" data-theme="a" data-mini="false" >
            <legend>Number of questions:</legend>
            <input type="radio" name="numQuestionsRadio" id="numQuestionsRadioID_0" value=1 data-theme="a" />
            <label for="numQuestionsRadioID_0">1</label>

            <input type="radio" name="numQuestionsRadio" id="numQuestionsRadioID_1" value=5 data-theme="a" />
            <label for="numQuestionsRadioID_1">5</label>

            <input type="radio" name="numQuestionsRadio" id="numQuestionsRadioID_2" value=10 checked="checked" data-theme="a" />
            <label for="numQuestionsRadioID_2">10</label>

            <input type="radio" name="numQuestionsRadio" id="numQuestionsRadioID_3" value=20 data-theme="a" />
            <label for="numQuestionsRadioID_3">20</label>
        </fieldset>
        <fieldset data-role="controlgroup" data-type="horizontal" data-theme="a" >
            <legend>Choices per question:</legend>
            <input type="radio" name="numChoicesRadio" id="numChoicesRadioID_1" value=3 checked="checked" data-theme="a" />
            <label for="numChoicesRadioID_1">3</label>

            <input type="radio" name="numChoicesRadio" id="numChoicesRadioID_2" value=4 data-theme="a" />
            <label for="numChoicesRadioID_2">4</label>

            <input type="radio" name="numChoicesRadio" id="numChoicesRadioID_3" value=5 data-theme="a" />
            <label for="numChoicesRadioID_3">5</label>
        </fieldset>
        <a data-role="button" id="createAndRunQuizID" href="#createAndRunQuizPageID" data-theme="a" >Run my quiz</a>
    </div>
</div>
<!-- 
*****************************
***  createAndRunQuizPage ***
***************************** -->
<div data-role="page" id="createAndRunQuizPageID" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5 id=runningQuizHeaderID >Quiz ready</h5>
        <a href="/quiz/mobile_quiz_test.php" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content">
        <img id=creatingQuizImageID src="/css/img/generating_quiz.gif" class="center" style="display:none" />
        <div id='AirplayMusicPlayerContainerID'>
            <div id='apmYoutubePlayerContainerID'> </div>
        </div>
        <div id='quizAreaID' action=""></div>
    </div>
    <div data-role="footer" class="ui-bar" data-theme="a" data-position="fixed" >
		<h3>
		<div class="ui-grid-a">
			<div class="ui-block-a" style="text-align: left;" data-theme="b" >
				<span id=scoreTotalTextID data-theme="b" >Total Score: </span>
			</div>
			<div class="ui-block-b" style="text-align: right; padding-right:1em">
				<h3 id=scoreCurQuestionTextID  >Current: 10</h3>
			</div>
		</div>
		</h3>
    </div>    
</div>
<!-- 
***************************
*** selectThemeMainPage ***
*************************** -->
<div data-role="page" id="selectThemeMainPageID" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Top themes</h5>
        <a href="/quiz/mobile_quiz_test.php" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content">
    <ul data-role='listview' >
		<li><a href='#popRockByDecadePageID' >
			<img src='/css/img/themes_80x80/popRockByDecade.png' />
			<h3>Pop/Rock by decade</h3><p>60s, 70s, 80s, 90s, etc.</p></a>
		</li>
		<li><a href='#seasonAndEventsPageID' >
			<img src='/css/img/themes_80x80/seasonAndEvents.png' />
			<h3>Season and events</h3><p>Christmas, summer, Eurovision, ...</p></a>
		</li>
	</ul>
    </div>
</div>
<!-- 
***************************
*** popRockByDecadePage ***
*************************** -->
<div data-role="page" id="popRockByDecadePageID" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>By decade</h5>
        <a href="/quiz/mobile_quiz_test.php" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content">
    <ul data-role='listview' >
		<li><a href='#60sMusicPageID' >
			<img src='/css/img/themes_80x80/60sMusic.png' />
			<h3>60s music</h3><p></p></a>
		</li>
		<li><a href='#70sMusicPageID' >
			<img src='/css/img/themes_80x80/70sMusic.png' />
			<h3>70s music</h3><p></p></a>
		</li>
		<li><a href='#80sMusicPageID' >
			<img src='/css/img/themes_80x80/80sMusic.png' />
			<h3>80s music</h3><p></p></a>
		</li>
		<li><a href='#90sMusicPageID' >
			<img src='/css/img/themes_80x80/90sMusic.png' />
			<h3>90s music</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>

<!-- 
***************************
*** seasonAndEventsPage ***
*************************** -->
<div data-role="page" id="seasonAndEventsPageID" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Season/events</h5>
        <a href="/quiz/mobile_quiz_test.php" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content">
    <ul data-role='listview' >
		<li><a href='#christmasMusicPageID' >
			<img src='/css/img/themes_80x80/christmasMusic.png' />
			<h3>Christmas music</h3><p></p></a>
		</li>
		<li><a href='#eurovisionMusicPageID' >
			<img src='/css/img/themes_80x80/eurovisionMusic.png' />
			<h3>Eurovision music</h3><p></p></a>
		</li>
		<li><a href='#summerMusicPageID' >
			<img src='/css/img/themes_80x80/summerMusic.png' />
			<h3>Summer music</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>

<!-- 
***************************
*** 60sMusicPagePage ***
*************************** -->
<div data-role="page" id="60sMusicPageID" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Select local</h5>
        <a href="/quiz/mobile_quiz_test.php" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content" id="content_60sMusicPageID">
    <ul data-role='listview' >
		<li><a href='#' >
			<img src='/css/img/flags_256x256/int.png' />
			<h3>International 60s music</h3><p></p></a>
		</li>
		<li><a href='#' >
			<img src='/css/img/flags_256x256/dk.png' />
			<h3>Danish 60s music</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>

<!-- 
***************************
*** 70sMusicPagePage ***
*************************** -->
<div data-role="page" id="70sMusicPageID" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Select local</h5>
        <a href="/quiz/mobile_quiz_test.php" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content" id="content_70sMusicPageID">
    <ul data-role='listview' >
		<li><a href='#' >
			<img src='/css/img/flags_256x256/int.png' />
			<h3>International 70s music</h3><p></p></a>
		</li>
		<li><a href='#' >
			<img src='/css/img/flags_256x256/dk.png' />
			<h3>Danish 70s music</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>

<!-- 
***************************
*** 80sMusicPagePage ***
*************************** -->
<div data-role="page" id="80sMusicPageID" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Select local</h5>
        <a href="/quiz/mobile_quiz_test.php" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content" id="content_80sMusicPageID">
    <ul data-role='listview' >
		<li><a href='#' >
			<img src='/css/img/flags_256x256/int.png' />
			<h3>International 80s music</h3><p></p></a>
		</li>
		<li><a href='#' >
			<img src='/css/img/flags_256x256/dk.png' />
			<h3>Danish 80s music</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>

<!-- 
***************************
*** 90sMusicPagePage ***
*************************** -->
<div data-role="page" id="90sMusicPageID" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Select local</h5>
        <a href="/quiz/mobile_quiz_test.php" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content" id="content_90sMusicPageID">
    <ul data-role='listview' >
		<li><a href='#' >
			<img src='/css/img/flags_256x256/int.png' />
			<h3>International 90s music</h3><p></p></a>
		</li>
		<li><a href='#' >
			<img src='/css/img/flags_256x256/dk.png' />
			<h3>Danish 90s music</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>



<!-- 
***************************
*** christmasMusicPagePage ***
*************************** -->
<div data-role="page" id="christmasMusicPageID" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Select local</h5>
        <a href="/quiz/mobile_quiz_test.php" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content" id="content_christmassMusicPageID">
    <ul data-role='listview' >
		<li><a href='#' >
			<img src='/css/img/flags_256x256/int.png' />
			<h3>International christmas music</h3><p></p></a>
		</li>
		<li><a href='#' >
			<img src='/css/img/flags_256x256/dk.png' />
			<h3>Danish christmassmusic</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>


<!-- 
***************************
*** eurovisionMusicPagePage ***
*************************** -->
<div data-role="page" id="eurovisionMusicPageID" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Select local</h5>
        <a href="/quiz/mobile_quiz_test.php" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content" id="content_eurovisionsMusicPageID">
    <ul data-role='listview' >
		<li><a href='#' >
			<img src='/css/img/flags_256x256/int.png' />
			<h3>International eurovision music</h3><p></p></a>
		</li>
		<li><a href='#' >
			<img src='/css/img/flags_256x256/dk.png' />
			<h3>Danish eurovision music</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>

<!-- 
***************************
*** summerMusicPagePage ***
*************************** -->
<div data-role="page" id="summerMusicPageID" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Select local</h5>
        <a href="/quiz/mobile_quiz_test.php" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content" id="content_summersMusicPageID">
    <ul data-role='listview' >
		<li><a href='#' >
			<img src='/css/img/flags_256x256/int.png' />
			<h3>International summer music</h3><p></p></a>
		</li>
		<li><a href='#' >
			<img src='/css/img/flags_256x256/dk.png' />
			<h3>Danish summer music</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>


<!-- 
**********************
***  shareQuizPage ***
********************** -->
<div data-role="page" id="shareQuizPageID" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5 id=shareQuizHeaderID >Share quiz</h5>
        <a href="/quiz/mobile_quiz_test.php" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content">
        <h3><a id=quizPageSocialShareID-url href="http://www.dr.dk" ></a></h3>
        <p></p>
        <label>Share message (optional):</label>
        <input id='quizPageSocialShareID-summaryInput' class="clearable" type='text' onkeyup="addSocialShareQuiz();" />
        <p></p>
        <div id="quizPageSocialShareID" action="" data-inline="true" ></div>    
    </div>
    <div data-role="footer" class="ui-bar" data-theme="a" data-position="fixed" >
		<h3>
		</h3>
    </div>    
</div>
<!-- 
**************************
***  shareMainQuizPage ***
************************** -->
<div data-role="page" id="shareMainQuizPageID" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Share quiz</h5>
        <a href="/quiz/mobile_quiz_test.php" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content">
        <p></p>
        <label>Share message (optional):</label>
        <input id='welcomePageSocialShareID-summaryInput' class="clearable" type='text' onkeyup="addSocialShareQuizMain();" />
        <p></p>
        <div id="welcomePageSocialShareID" action="" data-inline="true" ></div>    
    </div>
</div>

<!--<div id='AirplayMusicPlayerContainerID'>
	<div id='apmYoutubePlayerContainerID'> </div>
</div>-->
<script>

function addSocialShareQuiz()
{
    var sShareUrl = quizGetAutoLoadUrl ("http://public.airplaymusic.dk/quiz/mobile_quiz_test.php");
    var sTitle = "Airplay Music Quiz: " + g_quiz.quiz_name;
    var sSummary = $('#quizPageSocialShareID-summaryInput')[0].value;

    $('#quizPageSocialShareID-url').attr("href", sShareUrl );
    $('#quizPageSocialShareID-url').text( g_quiz.quiz_name );
    addSocialShare('quizPageSocialShareID', sShareUrl, sTitle, sSummary );
}


function addSocialShareQuizMain()
{
//    var sShareUrl = "http://www.airplaymusic.dk";
    var sShareUrl = "http://public.airplaymusic.dk/quiz/mobile_quiz_test.php";
    var sTitle = "Airplay Music Quiz";
    var sSummary = $('#welcomePageSocialShareID-summaryInput')[0].value;
    addSocialShare('welcomePageSocialShareID', sShareUrl, sTitle, sSummary );
}



$( ".searchArtist" ).autocomplete({
    source: "/quiz/ajax_handlers/quiz_artist_auto_complete_handler.php"
});

$("#clearArtistNamesBtnID").click(function (e) {
    e.stopImmediatePropagation();
    e.preventDefault();
    $(".searchArtist").val("");
});

$('#createAndRunQuizID').click(function() {
    var iNumQuestions = $('input[name=numQuestionsRadio]:checked').val()
    var iNumChoices = $('input[name=numChoicesRadio]:checked').val()

    // Get all artist names
    var aArtistNames = $(".searchArtist");
    var sArtistNames = "";
    var iActualAdded = 0;
    for ( var i = 0; i < aArtistNames.length; i++ ) {
        var sArtistName = aArtistNames[i].value.trim();
        if (sArtistName != "" ) {
			if ( iActualAdded > 0 ) sArtistNames += ";";
			sArtistNames += sArtistName;
			iActualAdded++;
        }
    }
     
    postDataObj = { 
          auto_gen_quiz_type: "GUESS_SONG_PLAYING"
        , artist_names : sArtistNames 
        , difficulty: 3
        , num_questions: iNumQuestions
        , num_choices: iNumChoices
        , quiz_name: ""
        , quiz_id: 0
    };
    g_quiz = quizLoadFromServer(postDataObj, myQuizDoRun);
   
});


// -----------------------
// --- quizWelcomePage ---
// -----------------------

$( "#quizWelcomePageID" ).on( "pageshow", function( event, ui ) {
	if ( quizGetAutoLoadName () != "" ) {
		window.location.href = "/quiz/mobile_quiz_test.php#quizWelcomePageID"; // Remove autoload params from URL
		return;
	}
});


// --------------------------
// --- autoCreateQuizPage ---
// --------------------------

$( "#autoCreateQuizPageID" ).on( "pageshow", function( event, ui ) {
	if ( quizGetAutoLoadName () != "" ) {
		window.location.href = "/quiz/mobile_quiz_test.php#autoCreateQuizPageID"; // Remove autoload params from URL
		return;
	}
});


// ----------------------------
// --- createAndRunQuizPage ---
// ----------------------------

/** Stop player on page leave*/
$( "#createAndRunQuizPageID" ).on( "pagehide", function( event, ui ) {
    apmStop();
});

$( "#createAndRunQuizPageID" ).on( "pageshow", function( event, ui ) {
	if ( quizGetAutoLoadName () != "" ) {
		console.log( "FIXME: createAndRunQuizPage Quiz do autoload: '" + quizGetAutoLoadName() + "'" );
		g_quiz = quizLoadFromServer({ quiz_name: quizGetAutoLoadName(), quiz_id: 0 }, myQuizDoRun );
	}
});


/** Mute/un-mute function */
$( "#muteSwitchID" ).bind( "change", function(event, ui) {
    if ( $('#muteSwitchID').val() == 'muted' ) {
        apmMute();
    }
    else {
        apmUnMute();
    }
});


// -------------------------
// --- shareQuizPage ---
// -------------------------

$( "#shareQuizPageID" ).on( "pageshow", function( event, ui ) {
    addSocialShareQuiz();
});


// -------------------------
// --- shareMainQuizPage ---
// -------------------------

$( "#shareMainQuizPageID" ).on( "pageshow", function( event, ui ) {
    if ( quizGetAutoLoadName () != "" ) {
        window.location.href = "/quiz/mobile_quiz_test.php#shareMainQuizPageID"; // Remove autoload params from URL
        return;
    }
    
    addSocialShareQuizMain();
});




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


function myQuizDoRun(quizObj) 
{ 
    quizDoRun(quizObj);
}


</script>



<?php
echo $pc->pageEnd();

?>