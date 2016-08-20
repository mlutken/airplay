<?php
require_once ( __DIR__ . '/../../aphp/aphp_fix_include_path.php' );
require_once ( __DIR__ . '/../public_files_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('quiz/classes/PagesCommon.php');

$name = 'Media quiz test';
$pc = new PagesCommon();

echo $pc->pageStart("{$name}");

?>
<input id=createYoutubePlayerID  type=button value='Create Youtube player' onclick="testCreateYouTubePlayer();" ></input><br>
<div id='apmYoutubePlayerShowInQuizPosID' >
    <div id='apmYoutubePlayerContainerID'> </div>
</div>

<div id=youtubeSearchID > 
<input id=youtubeSearchIDInputID type=text size=40  ></input>
&nbsp;<input id=incrementalSearchSearchID  type=button value='Search Youtube' onclick="apmAsyncSearch(jQuery('#youtubeSearchIDInputID')[0].value, 30, onSearchDone, 'youtube' );" ></input>
</div>
<br />

<a href="#" onClick="apmPlay('youtube')"> 
    Play video in player
</a><br>
<a href="#" onClick="apmPause('youtube')"> 
    Pause player 
</a><br>
<a href="#" onClick="apmStop('youtube')"> 
    Stop player 
</a><br>
<a href="#" onClick="apmSeek('youtube', 30)"> 
    Seek position 30 s
</a><br>
<a href="#" onClick="apmSetSize('youtube', 200, 100)"> 
    Set size
</a><br>
<a href="#" onClick="apmMovePlayerToQuizArea('youtube')"> 
    apmMovePlayerToQuizArea
</a><br>
<a href="#" onClick="apmMovePlayerToHideArea('youtube')"> 
    apmMovePlayerToHideArea
</a><br>


<div id=apmDbgShowSearchResultsID ></div>

<div id='apmYoutubePlayerHidePosID' >
</div>

</body>
    <script type="text/javascript">
    // Thriller: sOnqjkJTMaA
    // pres play in player window 5s: W94PRyofCS8

    function testCreateYouTubePlayer()
    {
        jQuery("#apmYoutubePlayerContainerID").tubeplayer('destroy');
        jQuery("#apmYoutubePlayerContainerID").empty();
        jQuery("#apmYoutubePlayerContainerID").tubeplayer({
            width: 400, // the width of the player
            height: 300, // the height of the player
            protocol: 'https',
            allowFullScreen: "false", // true by default, allow user to go full screen
            initialVideo: "W94PRyofCS8", // the video that is loaded into the player
            preferredQuality: "default",// preferred quality: default, small, medium, large, hd720
            onPlay: function(id){console.log("TEST onPlay");}, // after the play method is called
            onPause: function(){console.log("TEST onPause");}, // after the pause method is called
            onStop: function(){console.log("TEST onStop");}, // after the player is stopped
            onSeek: function(time){console.log("TEST onSeek: " + time );}, // after the video has been seeked to a defined point
            onMute: function(){console.log("TEST onMute");}, // after the player is muted
            onUnMute: function(){console.log("TEST onUnMute");} // after the player is unmuted
        });
    
    }
    
 jQuery("#apmYoutubePlayerContainerID").tubeplayer({
    width: 400, // the width of the player
    height: 300, // the height of the player
	protocol: 'https',
    allowFullScreen: "false", // true by default, allow user to go full screen
    initialVideo: "W94PRyofCS8", // the video that is loaded into the player
    preferredQuality: "default",// preferred quality: default, small, medium, large, hd720
    onPlay: function(id){}, // after the play method is called
    onPause: function(){console.log("onPause");}, // after the pause method is called
    onStop: function(){console.log("onStop");}, // after the player is stopped
    onSeek: function(time){console.log("onSeek: " + time );}, // after the video has been seeked to a defined point
    onMute: function(){console.log("onMute");}, // after the player is muted
    onUnMute: function(){console.log("onUnMute");} // after the player is unmuted
});

    function onSearchDone()
    {
        console.log("onSearchDone");
        for (var i = 0; i < g_apmPlaylist.length; i++)
        {
            var sProvider = 'youtube';
            //console.log("youtube_id: " + g_apmPlaylist[i].youtube_id + " title: " + g_apmPlaylist[i].title );
        }
        
        apmAudioVideoLoadFromPlayList( 0, null, sProvider );
        apmDbgRenderPlaylistSimple( 'apmDbgShowSearchResultsID', g_apmPlaylist);
    
    }

    </script>
        
</html>


<?php

?>