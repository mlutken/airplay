<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" 
           xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Youtube songpage sandbox</title>
    <script src="../js/jquery-1.9.1.min.js"></script>
    <script type='text/javascript' src='../js/jQuery.tubeplayer.min.js'></script>
    <!--    <script src="http://www.google.com/jsapi"></script>-->

</head>

<body id="page">
<div id='youtube-player-container'> </div>

<a href="#" onClick='jQuery("#youtube-player-container").tubeplayer("play")'> 
    Play video in player
</a><br>
<a href="#" onClick='jQuery("#youtube-player-container").tubeplayer("pause")'> 
    Pause player 
</a><br>
<a href="#" onClick='jQuery("#youtube-player-container").tubeplayer("stop")'> 
    Stop player 
</a><br>
<a href="#" onClick='jQuery("#youtube-player-container").tubeplayer("play", "4V90AmXnguw" )'>   
    Play 4V90AmXnguw 
</a><br>
<a href="#" onClick='jQuery("#youtube-player-container").tubeplayer("seek", 30)'> 
    Seek position 30 s
</a><br>
</body>
        
    <script type="text/javascript">

 jQuery("#youtube-player-container").tubeplayer({
    width: 600, // the width of the player
    height: 450, // the height of the player
    allowFullScreen: "false", // true by default, allow user to go full screen
    initialVideo: "sOnqjkJTMaA", // the video that is loaded into the player
    preferredQuality: "default",// preferred quality: default, small, medium, large, hd720
    onPlay: function(id){console.log("OnPLay");}, // after the play method is called
    onPause: function(){console.log("Pause");}, // after the pause method is called
    onStop: function(){console.log("Stop");}, // after the player is stopped
    onSeek: function(time){console.log("OnSeek: " + time );}, // after the video has been seeked to a defined point
    onMute: function(){console.log("OnMute");}, // after the player is muted
    onUnMute: function(){console.log("OnUnMute");} // after the player is unmuted
});
   
    </script>
        
</html>


<?php

?>