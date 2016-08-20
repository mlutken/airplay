<?php
require_once ( __DIR__ . '/../../aphp/aphp_fix_include_path.php' );
require_once ( __DIR__ . '/../public_files_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('quiztest/classes/PagesCommon.php');

$name = 'Auto generate song quiz test';
$pc = new PagesCommon();

echo $pc->pageStart("{$name}");

?>
<div class="ui-widget">
<label for="artistSearchID">Tags: </label>
<input id="artistSearchID">
</div>

<!--<div class="container">   
<div class="hero-unit">
<h1>jQuery Autocomplete - Modifying Source Data</h1>
<p>If the jQuery autocomplete plugin uses a remote datasource, the autocomplete expects it to return json data with a 'label' and/or a 'value' field. If the remote source returns neither a 'label' or 'value' and you cannot change the returned json of the remote source, you can modify the 'source' option of the autocomplete to assign one or both of the 'label' and 'value' fields values found in the json result.</p>
<form class="form-horizontal" id="autocompleteForm" name="autocompleteForm" action=""  method="post">
<fieldset>
<div class="control-group"><label for="state">State (abbreviation in separate field): </label>
    <div class="controls"> 
    <input type="text" id="state"  name="state" /> <input readonly="readonly" type="text" id="abbrev" name="abbrev" maxlength="2" size="2"/></p>
    <input type="hidden" id="state_id" name="state_id" />
    <input type="hidden" id="form_submitted" name="form_submitted" value="true" />
    </div>
</div>
<div class="form-actions">
<input class="btn btn-primary" type="submit" name="submit" value="Submit" />
</div>
</fieldset>
 </form> -->


<input type=button value='Play' onclick="apmPlay('youtube');" >
<span>Num choices/options: </span><input type=text size=30 id='numChoicesID' value='3' ><br>
<span>Num questions: </span><input type=text size=30 id='numQuestionsID' value='5' ><br>
<span>Artist names (semicolon separated): </span><input type=text size=30 id='artistNamesID' value='Hej matematik ;Johnny Deluxe' ><br>
<input type=button value='Run test quiz' onclick="quizRunTest();" >
<input type=button value='Save quiz' onclick="quizSaveTest();" >

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
<script>

// // $(function() {
// // 
// //     $('#abbrev').val("");
// //     
// //     $("#state").autocomplete({
// //         source: function( request, response ) {
// //         $.ajax({
// //             url: "/quiztest/ajax_handlers/quiz_artist_auto_complete_handler.php",
// //             dataType: "json",
// //             data: {term: request.term},
// //             success: function(data) {
// //                         console.log("FIXME: success");
// //                         response($.map(data, function(item) {
// //                         return {
// //                             label: item.state,
// //                             id: item.id,
// //                             abbrev: item.abbrev
// //                             };
// //                     }));
// //                 },
// //             error: function(data) {
// //                         console.log("FIXME: error");
// //                 }
// //             });
// //         },
// //         minLength: 2,
// //         select: function(event, ui) {
// //             $('#state_id').val(ui.item.id);
// //             $('#abbrev').val(ui.item.abbrev);
// //         }
// //     });
// // });
// // 

$( "#artistSearchID" ).autocomplete({
    source: "/quiztest/ajax_handlers/quiz_artist_auto_complete_handler.php"
});


function quizDoSave(quizObj, quizAreaID) 
{ 
	console.log("quizDoSave");
	///quizRenderCurrentState( g_quiz, g_quizCurrentState )
}

function quizSaveTest() 
{ 
	console.log("quizSaveTest");
//	quizDoSave(g_quiz, "quizAreaID" );
	quizSaveToServer(g_quiz);
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
    width: 300, // the width of the player
    height: 200, // the height of the player
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