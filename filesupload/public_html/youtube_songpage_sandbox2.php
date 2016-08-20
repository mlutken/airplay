<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" 
           xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Youtube songpage sandbox 2</title>
    <script src="youtube_utils.js"></script>

</head>

<body id="page">
    <div>
        <div id="playerYoutube"></div>
        <br />
        <input name="nextVideo" type="submit" 
            value="Next video" onclick="playNextYoutubeVideo()" />
    </div>
    <?php
    
    echo addYoutubePlayer( 'playerYoutube', 'Michael Jackson, Thriller', 8, 300, 200 );
    ?>
    
    
</body>
        
</html>

    <script type="text/javascript">
    // YouTube API v2 dev key: AI39si5YQsWjHNbEFLjUEavBB4f5mH_IXFLuruhYsNkwzVnZDNCx7lQp3ebWO8p1KxIdv2feE1Myf5lF33WYRfvSj2EGVLFukQ
    </script>

<?php

function addYoutubePlayer($playerYoutube, $sInitialYoutubeSearchString, $iMaxResults, $iWidth, $iHeight )
{
$s=
<<<SCRIPT
    <script>
    // 2. This code loads the IFrame Player API code asynchronously.
    var tag = document.createElement('script');

    // This is a protocol-relative URL as described here:
    //     http://paulirish.com/2010/the-protocol-relative-url/
    // If you're testing a local page accessed via a file:/// URL, please set tag.src to
    //     "https://www.youtube.com/iframe_api" instead.
    tag.src = "//www.youtube.com/iframe_api";
    var firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

    // 3. This function creates an <iframe> (and YouTube $playerYoutube)
    //    after the API code downloads.
    var $playerYoutube;
    function onYouTubeIframeAPIReady() {
        $playerYoutube = new YT.Player('$playerYoutube', {
        height: '$iHeight',
        width: '$iWidth',
        videoId: '',
        events: {
            'onReady': onPlayerReady,
            'onStateChange': onPlayerStateChange
        }
        });
    }

    // 4. The API will call this function when the video $playerYoutube is ready.
    function onPlayerReady(event) {
        //event.target.playVideo();
        asyncYoutubeSearch( '{$sInitialYoutubeSearchString}', $iMaxResults, 'parseYoutubeVideoDataAndLoadFirst' );
    }

    // 5. The API calls this function when the $playerYoutube's state changes.
    //    The function indicates that when playing a video (state=1),
    //    the $playerYoutube should play for six seconds and then stop.
    var done = false;
    function onPlayerStateChange(event) {
        if (event.data == YT.PlayerState.PLAYING && !done) {
        setTimeout(stopVideo, 0);
        done = true;
        }
    }
    function stopVideo() {
        $playerYoutube.stopVideo();
    }
</script>
SCRIPT;
    return $s;
}


?>