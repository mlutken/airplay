<?php 
// $Id$

function addYoutubeLink( $search_term ) {
$search_term = str_replace("'", "\'", $search_term);
$sHtml = "<div id='youtube_link_container'><a href='' id='youtube_link' title='Se på YouTube' target='_blank'><div class='icon'></div></a></div>";
return $sHtml . "<script type='text/javascript'>
    var search_term = escape('$search_term');
    jQuery(document).ready(function() {
        jQuery.ajax({
        type: 'POST',
        url: '/proxy.php',
        data: { s: 'youtube' , q: search_term }, 
        dataType: 'xml',
        success: parseYoutubeXml
        });

    function parseYoutubeXml(xml) {
        jQuery(xml).find('feed').find('entry:eq(0)').each(function() {
            var youtube_link = jQuery(this).find('id').text().replace('http://gdata.youtube.com/feeds/api/videos/', 'http://www.youtube.com/watch?v=');
            jQuery('a#youtube_link').attr('href', youtube_link);
            jQuery('div#youtube_link_container').show();
        });
    }
    });
</script>";
}




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

