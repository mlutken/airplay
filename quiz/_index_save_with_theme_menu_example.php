<?php
require_once ( __DIR__ . '/../aphp/aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('quiz/quiz_partner_utils.php');
require_once ('classes/PagesCommon.php');



$pageTitle = 'Airplay Music Quiz';
$cssPath = 'airplaymusic';
$aPartnerSettings = getPartnerSettingsFrom_GET();

$pc = new PagesCommon();

echo $pc->htmlHeaderStart($aPartnerSettings);
echo $pc->htmlHeaderEnd();
// echo $pc->pageTopContents($name);



?>
<!-- 
**************************
***  FB initialization ***
************************** -->
<div id="fb-root"></div>
<script>

// // $("#fbInviteMainQuizPageSelectAllID").click(function (e) {
// //     $("#fbInviteMainQuizPageContentID input[type=checkbox]").prop('checked',true);
// // });
// // 
// // $("#fbInviteMainQuizPageDeselectAllID").click(function (e) {
// //     $("#fbInviteMainQuizPageContentID input[type=checkbox]").prop('checked',false);
// // });

// // $("#fbInviteMainQuizPageInviteAllID").click(function (e) {
// //     var aUserIDs = new Array();
// //     $('#fbInviteMainQuizPageContentID input[type=checkbox]').each(function() {
// //         aUserIDs.push($(this).attr('id'));
// //     });
// //     fbSendInviteQuizMain(aUserIDs);
// // });


  window.fbAsyncInit = function() {
    FB.init({
        appId      : '177367848975485',
        status     : true, // check login status
        cookie     : true, // enable cookies to allow the server to access the session
        xfbml      : true  // parse XFBML
    });

  // Here we subscribe to the auth.authResponseChange JavaScript event. This event is fired
  // for any authentication related change, such as login, logout or session refresh. This means that
  // whenever someone who was previously logged out tries to log in again, the correct case below 
  // will be handled. 
  FB.Event.subscribe('auth.authResponseChange', function(response) {
    // Here we specify what we do with the response anytime this event occurs. 
    if (response.status === 'connected') {
      // The response object is returned with a status field that lets the app know the current
      // login status of the person. In this case, we're handling the situation where they 
      // have logged in to the app.
      //testAPI();
    } else if (response.status === 'not_authorized') {
      // In this case, the person is logged into Facebook, but not into the app, so we call
      // FB.login() to prompt them to do so. 
      // In real-life usage, you wouldn't want to immediately prompt someone to login 
      // like this, for two reasons:
      // (1) JavaScript created popup windows are blocked by most browsers unless they 
      // result from direct interaction from people using the app (such as a mouse click)
      // (2) it is a bad experience to be continually prompted to login upon page load.
      //FB.login();
      //console.lo
      //jsGlobalsSetLoggedOut();
    } else {
      // In this case, the person is not logged into Facebook, so we call the login() 
      // function to prompt them to do so. Note that at this stage there is no indication
      // of whether they are logged into the app. If they aren't then they'll see the Login
      // dialog right after they log in to Facebook. 
      // The same caveats as above apply to the FB.login() call here.
      //FB.login();
      //jsGlobalsSetLoggedOut();
    }
  });
  };

  
// basic_info
// installed
// public_profile
// user_friends

  // Load the SDK asynchronously
  (function(d){
   var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
   if (d.getElementById(id)) {return;}
   js = d.createElement('script'); js.id = id; js.async = true;
   js.src = "//connect.facebook.net/en_US/all.js";
   ref.parentNode.insertBefore(js, ref);
  }(document));

  // Here we run a very simple test of the Graph API after login is successful. 
  // This testAPI() function is only called in those cases. 
  function testAPI() {
    console.log('Welcome!  Fetching your information.... ');
    FB.api('/me', function(response) {
      console.log('Good to see you, ' + response.name + '.');
    });
    FB.api('/me/permissions', function (response) {
        console.log(response);
    } );
    FB.api('/me/friends', function (response) {
        console.log(response);
    } );

    FB.api('/me/music', function(response) { 
        console.log('Music:');
        console.log(response);
    
    });

  }

</script>

<!-- 
************************
***  quizWelcomePage ***
************************ -->
<div data-role="page" id="quizWelcomePageID" data-theme="c" class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="" data-theme="a" class="logInOut" data-role="button" >Login</a>  
        <h5>Airplay Music Quiz (beta)</h5>
    </div>
    <div data-role="content">
<!-- 		<fb:login-button show-faces="true" perms="user_likes, friends_likes"  width="200" max-rows="1"></fb:login-button> -->
        <h3>Challenge yourself and friends on music</h3>
        <a data-role="button" href="#autoCreateQuizPageID" data-transition="" data-theme="a" >Run from artist</a>
<!--         <a data-role="button" href="#selectThemeMainPageID" data-transition="" data-theme="a" >Create from theme</a> -->
<!--         <a data-role="button" href="#searchQuizPageID" data-theme="a" >Search quiz</a> -->
<!--         <a data-role="button" href="#showHighScorePageID" data-theme="a" >Show highscore</a>  -->
         <a data-role="button" href="#fbInviteMainQuizPageID" data-theme="a" >Invite friends (coming soon)</a> 
        <a data-role="button" href="#shareMainQuizPageID" data-theme="a" >Share this</a>
<!--        <a data-role="button" id=debugBtnWelcomePageID href="" data-theme="a" >Debug test 1</a>
        <a data-role="button" id=debugBtn2WelcomePageID href="" data-theme="a" >Debug test 2</a>-->
<!--        <a data-role="button" id=debugBtn3WelcomePageID href="" data-theme="a" >Debug test 3</a>
        <a data-role="button" id=debugBtn4WelcomePageID href="" data-theme="a" >Debug test 4</a>-->
		<div data-role="collapsible" data-theme="a" data-content-theme="a" >
            <h3>Help</h3>
            <p>Easily generate song quizzes from your favorite artists in a few clicks. 
                <ol>
                    <li>Hit the 'Run from artist' button.</li>
                    <li>Type in your favorite artist(s).</li>
                    <li>Go!</li>
                </ol>
            </p>
            <p>Or: easily generate song quizzes from themes like 80s, charts, christmas, dance, ... in a few clicks.<br>
            (Coming soon)
                <ol>
                    <li>Hit the 'Create from theme' button.</li>
                    <li>Select your theme.</li>
                    <li>Go!</li>
                </ol>
            </p>
        </div>
<!--         <div id=creatingQuizImageID_XXXX class="apGeneratingQuizImage"  >DDDDDDDDDD</div> -->
<!--        <input type='checkbox' >Debug checkbox<br> 
        <div id=debugAreaWelcomePageID></div>-->

    </div>
    <div data-role="footer" data-theme="a" data-position="fixed" >
        <a href="" data-theme="a" class="showLatestResults" data-role="button" >Results</a>  
        <a href="" data-theme="a" class="showLatestHighscore" data-role="button" >Highscore</a>  
    </div>    
</div>

<!-- 
***************************
***  autoCreateQuizPage ***
*************************** -->
<div data-role="page" id="autoCreateQuizPageID" data-theme="c" data-add-back-btn="false" class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Auto create quiz</h5>
        <a href="/" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
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
<div data-role="page" id="createAndRunQuizPageID" data-theme="c" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5 id=runningQuizHeaderID >Quiz ready</h5>
        <a href="/" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content">
<!--         <div id=creatingQuizImageID class="apGeneratingQuizImage" style="display:none" ></div> -->
        <img id=creatingQuizImageID src="/css/airplaymusic/img/generating_quiz.gif" class="center" style="display:none" />
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
				<h3 id=scoreCurQuestionTextID  >Current: </h3>
			</div>
		</div>
		</h3>
    </div>    
</div>

<!-- 
*******************
*** resultsPage ***
******************* -->
<div data-role="page" id="resultsPageID" data-theme="c" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="" data-theme="a" class="logInOut" data-role="button" >Login</a>  
        <h5 id=resultsPageHeaderID >Results</h5>
        <a href="/" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content">
        <a data-role='button' href='#showHighScorePageID' data-theme='a' >Show highscore</a>
        <p></p>
        <div id=resultsPageContentID ></div>
    </div>
</div>

<!-- 
*************************
*** showHighScorePage ***
************************* -->
<div data-role="page" id="showHighScorePageID" data-theme="c" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5 id=showHighScorePageHeaderID >Highscore</h5>
        <a href="/" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content" id=showHighScorePageContentID >
    </div>
</div>


<!-- 
***************************
*** selectThemeMainPage ***
*************************** -->
<div data-role="page" id="selectThemeMainPageID" data-theme="c" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Top themes</h5>
        <a href="/" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content" >
    <ul data-role='listview' >
		<li><a href='#popRockByDecadePageID' >
			<!-- <img src='/img/themes_80x80/popRockByDecade.png' /> -->
			<h3>Pop/Rock by decade</h3><p>60s, 70s, 80s, 90s, etc.</p></a>
		</li>
		<li><a href='#seasonAndEventsPageID' >
			<!-- <img src='/img/themes_80x80/seasonAndEvents.png' /> -->
			<h3>Season and events</h3><p>Christmas, summer, Eurovision, ...</p></a>
		</li>
	</ul>
    </div>
</div>
<!-- 
***************************
*** popRockByDecadePage ***
*************************** -->
<div data-role="page" id="popRockByDecadePageID" data-theme="c" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>By decade</h5>
        <a href="/" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content">
    <ul data-role='listview' >
		<li><a href='#60sMusicPageID' >
			<!-- <img src='/img/themes_80x80/60sMusic.png' />  -->
			<h3>60s music</h3><p></p></a>
		</li>
		<li><a href='#70sMusicPageID' >
			<!-- <img src='/img/themes_80x80/70sMusic.png' />  -->
			<h3>70s music</h3><p></p></a>
		</li>
		<li><a href='#80sMusicPageID' >
			<!-- <img src='/img/themes_80x80/80sMusic.png' />  -->
			<h3>80s music</h3><p></p></a>
		</li>
		<li><a href='#90sMusicPageID' >
			<!-- <img src='/img/themes_80x80/90sMusic.png' />  -->
			<h3>90s music</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>

<!-- 
***************************
*** seasonAndEventsPage ***
*************************** -->
<div data-role="page" id="seasonAndEventsPageID" data-theme="c" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Season/events</h5>
        <a href="/" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content">
    <ul data-role='listview' >
		<li><a href='#christmasMusicPageID' >
			<!-- <img src='/img/themes_80x80/christmasMusic.png' /> -->
			<h3>Christmas music</h3><p></p></a>
		</li>
		<li><a href='#eurovisionMusicPageID' >
			<!-- <img src='/img/themes_80x80/eurovisionMusic.png' /> -->
			<h3>Eurovision music</h3><p></p></a>
		</li>
		<li><a href='#summerMusicPageID' >
			<!-- <img src='/img/themes_80x80/summerMusic.png' /> -->
			<h3>Summer music</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>

<!-- 
***************************
*** 60sMusicPagePage ***
*************************** -->
<div data-role="page" id="60sMusicPageID" data-theme="c" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Select local</h5>
        <a href="/" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content" id="content_60sMusicPageID">
    <ul data-role='listview' >
		<li><a href='#' >
			<!-- <img src='/img/flags_256x256/int.png' /> -->
			<h3>International 60s music</h3><p></p></a>
		</li>
		<li><a href='#' >
				
			<h3>Danish 60s music</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>

<!-- 
***************************
*** 70sMusicPagePage ***
*************************** -->
<div data-role="page" id="70sMusicPageID" data-theme="c" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Select local</h5>
        <a href="/" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content" id="content_70sMusicPageID">
    <ul data-role='listview' >
		<li><a href='#' >
			<!-- <img src='/img/flags_256x256/int.png' /> -->
			<h3>International 70s music</h3><p></p></a>
		</li>
		<li><a href='#' >
			<!-- <img src='/img/flags_256x256/dk.png' /> -->
			<h3>Danish 70s music</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>

<!-- 
***************************
*** 80sMusicPagePage ***
*************************** -->
<div data-role="page" id="80sMusicPageID" data-theme="c" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Select local</h5>
        <a href="/" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content" id="content_80sMusicPageID">
    <ul data-role='listview' >
		<li><a href='#' >
			<!-- <img src='/img/flags_256x256/int.png' /> -->
			<h3>International 80s music</h3><p></p></a>
		</li>
		<li><a href='#' >
			<!-- <img src='/img/flags_256x256/dk.png' /> -->
			<h3>Danish 80s music</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>

<!-- 
***************************
*** 90sMusicPagePage ***
*************************** -->
<div data-role="page" id="90sMusicPageID" data-theme="c" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Select local</h5>
        <a href="/" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content" id="content_90sMusicPageID">
    <ul data-role='listview' >
		<li><a href='#' >
			<!-- <img src='/img/flags_256x256/int.png' /> -->
			<h3>International 90s music</h3><p></p></a>
		</li>
		<li><a href='#' >
			<!-- <img src='/img/flags_256x256/dk.png' /> -->
			<h3>Danish 90s music</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>

<!-- 
***************************
*** christmasMusicPagePage ***
*************************** -->
<div data-role="page" id="christmasMusicPageID" data-theme="c" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Select local</h5>
        <a href="/" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content" id="content_christmassMusicPageID">
    <ul data-role='listview' >
		<li><a href='#' >
			<!-- <img src='/img/flags_256x256/int.png' /> -->
			<h3>International christmas music</h3><p></p></a>
		</li>
		<li><a href='#' >
			<!-- <img src='/img/flags_256x256/dk.png' /> -->
			<h3>Danish christmas music</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>

<!-- 
***************************
*** eurovisionMusicPagePage ***
*************************** -->
<div data-role="page" id="eurovisionMusicPageID" data-theme="c" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Select local</h5>
        <a href="/" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content" id="content_eurovisionsMusicPageID">
    <ul data-role='listview' >
		<li><a href='#' >
			<!-- <img src='/img/flags_256x256/int.png' /> -->
			<h3>International eurovision music</h3><p></p></a>
		</li>
		<li><a href='#' >
			<!-- <img src='/img/flags_256x256/dk.png' /> -->
			<h3>Danish eurovision music</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>

<!-- 
***************************
*** summerMusicPagePage ***
*************************** -->
<div data-role="page" id="summerMusicPageID" data-theme="c" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Select local</h5>
        <a href="/" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content" id="content_summersMusicPageID">
    <ul data-role='listview' >
		<li><a href='#' >
			<!-- <img src='/img/flags_256x256/int.png' /> -->
			<h3>International summer music</h3><p></p></a>
		</li>
		<li><a href='#' >
			<!-- <img src='/img/flags_256x256/dk.png' /> -->
			<h3>Danish summer music</h3><p></p></a>
		</li>
	</ul>
    </div>
</div>

<!-- 
**********************
***  shareQuizPage ***
********************** -->
<div data-role="page" id="shareQuizPageID" data-theme="c" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5 id=shareQuizHeaderID >Share quiz</h5>
        <a href="/" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
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
<div data-role="page" id="shareMainQuizPageID" data-theme="c" data-add-back-btn="true"  class="defaultBackground" >
    <div data-theme="a" data-role="header" data-position="fixed" >
        <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a>
        <h5>Share quiz</h5>
        <a href="/" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content">
        <p></p>
        <label>Share message (optional):</label>
        <input id='welcomePageSocialShareID-summaryInput' class="clearable" type='text' onkeyup="addSocialShareQuizMain();" />
        <p></p>
        <div id="welcomePageSocialShareID" action="" data-inline="true" ></div>    
    </div>
</div>

<!-- 
****************************
*** fbInviteMainQuizPage ***
**************************** -->
<div data-role="page" id="fbInviteMainQuizPageID" data-theme="c" data-add-back-btn="true"  class="defaultBackground" >
    <div data-role="header" data-theme="a" data-position="fixed" >
        <a id=fbInviteMainQuizPageInviteAllID href="" data-icon="user" data-theme="a" data-iconpos="right" >Invite All</a>
<!--         <a href="" data-theme="a" class="logInOut" data-role="button" >Login</a>   -->
<!--         <a href="#" data-rel="back" data-icon="arrow-l" data-theme="a" data-role="button">Back</a> -->
        <h5>Invite friends (coming soon)</h5>
        <a href="/" data-icon="home" data-theme="a" data-iconpos="right" >Home</a>
    </div>
    <div data-role="content">
        <div id="fbInviteMainQuizPageContentID"></div>    
    </div>
    <div data-role="footer" data-theme="a" data-position="fixed" >
        <a id=fbInviteMainQuizPageSelectAllID href="" data-icon="check" data-theme="a" data-iconpos="right" >Select All</a>
        <a id=fbInviteMainQuizPageInviteID href="" data-icon="navigation" data-theme="a" data-iconpos="right" >Invite</a>
        <a id=fbInviteMainQuizPageDeselectAllID href="" data-icon="delete" data-theme="a" data-iconpos="right" >Deselect All</a>
    </div>    
</div>

<!--<div id='AirplayMusicPlayerContainerID'>
	<div id='apmYoutubePlayerContainerID'> </div>
</div>-->
<script>

apCheckUserLoggedIn(); // Update our login state after a page (re)load
quizLoadCurrentFromServer(); // Make sure we get latest completed quiz restored in case of browser reload

function addSocialShareQuiz()
{
    var sShareUrl = quizGetAutoLoadUrl (quizGetBasePageUrl());
    var sTitle = "Airplay Music Quiz: " + g_quiz.quiz_name;
    var sSummary = $('#quizPageSocialShareID-summaryInput')[0].value;

    $('#quizPageSocialShareID-url').attr("href", sShareUrl );
    $('#quizPageSocialShareID-url').text( g_quiz.quiz_name );
    addSocialShare('quizPageSocialShareID', sShareUrl, sTitle, sSummary );
}


function addSocialShareQuizMain()
{
    var sShareUrl = quizGetBasePageUrl();
    var sTitle = "Airplay Music Quiz";
    var sSummary = $('#welcomePageSocialShareID-summaryInput')[0].value;
    addSocialShare('welcomePageSocialShareID', sShareUrl, sTitle, sSummary );
}


$(".logInOut").click(function (e) {
    userLogInOut();
});

$(".showLatestResults").click(function (e) {
    $.mobile.pageContainer.pagecontainer('change', '#resultsPageID', {} );
});

$(".showLatestHighscore").click(function (e) {
    $.mobile.pageContainer.pagecontainer('change', '#showHighScorePageID', {} );
});


// -----------------------
// --- quizWelcomePage ---
// -----------------------


// ?quiz_cmd=load&quiz_name_url=Kim_larsen--67
// ?quiz_cmd=load&quiz_name_url=Hej_matematik--1
// ?quiz_cmd=show_highscore&quiz_name_url=Kim_larsen--32
// http://quiz.airplaymusic.dk/?quiz_cmd=show_highscore&quiz_name_url=Kim_larsen--67
// https://quiz.airplaymusic.dk/?quiz_cmd=show_highscore&quiz_name_url=Kim_larsen--67
// https://apps.facebook.com/airplaymusic/?quiz_cmd=show_highscore&quiz_name_url=Kim_larsen--67

$( "#quizWelcomePageID" ).on( "pageshow", function( event, ui ) {
	userLoginButtonsUpdate();
	if ( quizSaveUrlCommandsToCookie() ) {
        window.top.location = quizGetBasePageUrl();  // OBSERVE: This seems to do what we want, but we have many scenarios so, keep an eye ...
        //// OLD implementation: window.location.href = "/"; // Remove autoload params from URL, TODO: Use g_base_path (JS var) URL set from server. 
        return;
	}
	
	// --- Look in cookies for 'quiz_cmd' and navigate to corect sub page
    var quiz_cmd = getCookie("quiz_cmd");
    if ( quiz_cmd == "load" ) {
        console.log("DBG: Auto: load quiz: '" + getCookie("quiz_name_url") + "'" );
        $.mobile.pageContainer.pagecontainer('change', '#createAndRunQuizPageID', {} );
    }
    else if ( quiz_cmd == "show_highscore" ) {
        console.log("DBG: Auto: show_highscore: " + getCookie("quiz_name_url") );
        $.mobile.pageContainer.pagecontainer('change', '#showHighScorePageID', {} );
    }
});



$("#debugBtnWelcomePageID").click(function (e) {
    console.log("debugBtnWelcomePageID.... list render test");

    var dbgList =   [ 
                      { fb_id: 12, user_name: 'Donald Duck', score : 111, profile_image_url : 'https://fbcdn-profile-a.akamaihd.net/hprofile-ak-prn1/41496_766189393_7109_q.jpg' } 
                    , { fb_id: 3425, user_name: 'Mickey mouse', score : 114, profile_image_url : 'https://fbcdn-profile-a.akamaihd.net/hprofile-ak-prn1/41496_766189393_7109_q.jpg' } 
                    ];
    
    fbRenderFriendsInvitePage('debugAreaWelcomePageID', dbgList );
    // debugAreaWelcomePageID    
    
});

$("#debugBtn2WelcomePageID").click(function (e) {
    console.log("debugBtn2WelcomePageID.... ");

    // Get all selected
//     var selected = new Array();
//     $('#debugAreaWelcomePageID input:checked').each(function() {
//         console.log("Selected id: " + $(this).attr('id') + ", name: " +  $(this).attr('name')  );
//         selected.push($(this).attr('id'));
//     });
    
//    $('#debugAreaWelcomePageID :checkbox').prop('checked',true);


    var domIdHash = '#debugAreaWelcomePageID';
    // Select all
//     $(domIdHash + " input[type=checkbox]").prop('checked',true);

    // de-select all
    $(domIdHash + " input[type=checkbox]").prop('checked',false);
    
//     $cb = $(':checkbox:checked');
    
});


$("#debugBtn3WelcomePageID").click(function (e) {
    console.log("debugBtn3WelcomePageID.... save curent quiz...");
    quizSaveCurrentToServer();
});

$("#debugBtn4WelcomePageID").click(function (e) {
    console.log("debugBtn4WelcomePageID.... load curent quiz...");
    quizLoadCurrentFromServer();
});

// --------------------------
// --- autoCreateQuizPage ---
// --------------------------

$( "#autoCreateQuizPageID" ).on( "pageshow", function( event, ui ) {
});


$( ".searchArtist" ).autocomplete({
    source: "/ajax_handlers/quiz_artist_auto_complete_handler.php"
});

//userLoginButtonsUpdate();

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


// ----------------------------
// --- createAndRunQuizPage ---
// ----------------------------

/** Stop player on page leave*/
$( "#createAndRunQuizPageID" ).on( "pagehide", function( event, ui ) {
    apmStop();
});

$( "#createAndRunQuizPageID" ).on( "pageshow", function( event, ui ) {
    quizClearPage();
    var quizAutoLoadName = quizGetAutoLoadName ();
    quizClearUrlCommandsInCookie();
	if ( quizAutoLoadName != "" ) {
		g_quiz = quizLoadFromServer({ quiz_name: quizAutoLoadName, quiz_id: 0 }, myQuizDoRun );
	}
});

// -------------------
// --- resultsPage ---
// -------------------
$( "#resultsPageID" ).on( "pageshow", function( event, ui ) {
    quizRenderResults();
});

// -------------------------
// --- showHighScorePage ---
// -------------------------


$( "#showHighScorePageID" ).on( "pageshow", function( event, ui ) {
    
    if ( userIsLoggedIn() ) {
        console.log(g_quiz);
        var postDataObj = quizGetHighScorePostData ();
// // //         quizClearUrlCommandsInCookie(); // Moved to quizGetHighScorePostData
        quizLoadHighScoreList(postDataObj, 'showHighScorePageContentID' );
    }
    else {
        quizHighScoreRenderLogin( 'showHighScorePageContentID' );
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
    addSocialShareQuizMain();
});


// ----------------------------
// --- fbInviteMainQuizPage ---
// ----------------------------

$( "#fbInviteMainQuizPageID" ).on( "pageshow", function( event, ui ) {
    console.log("fbInviteMainQuizPage...");
    FB.api('/me/friends', {fields: 'name,id,username,gender,location,bio,age_range'}, function (response) {
        console.log(response);
        var aAirplayUsers = fbUserArrayToAirplayUserArray(response.data);
        fbRenderFriendsInvitePage('fbInviteMainQuizPageContentID', aAirplayUsers );
        
    } );
    
});

$("#fbInviteMainQuizPageSelectAllID").click(function (e) {
    $("#fbInviteMainQuizPageContentID input[type=checkbox]").prop('checked',true);
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

///////////////


</script>



<?php
echo $pc->pageEnd();

?>