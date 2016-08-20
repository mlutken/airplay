<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" 
           xml:lang="en" lang="en">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Search Snippet</title>
    <script src="http://www.google.com/jsapi"></script>

    <script type="text/javascript">

        var g_allVideoIDs       = [];
        var g_currentVideoID    = -1;
        
    // YouTube API v2 dev key: AI39si5YQsWjHNbEFLjUEavBB4f5mH_IXFLuruhYsNkwzVnZDNCx7lQp3ebWO8p1KxIdv2feE1Myf5lF33WYRfvSj2EGVLFukQ
    
        function getYoutubeVideoId( videoEntry )
        {
            var href = videoEntry.link[0].href;
            //http://www.youtube.com/watch?v=muJ6sRI29qQ&feature=youtube_gdata
            var iStart  = href.search("\\?v=") + 3;
            var iEnd    = href.search("&feature");
            var videoID = href.slice(iStart, iEnd);
            return videoID;
        }

        
        function getEmbedYoutubeVideo( videoID )
        {
            var sPlayer = "<param name='movie' value='https://www.youtube.com/v/" + videoID + "?version=3'></param>\
                <param name='allowFullScreen' value='true'></param>\
                <param name='allowScriptAccess' value='always'></param>\
                <embed src='https://www.youtube.com/v/" + videoID +"?version=3' type='application/x-shockwave-flash' allowfullscreen='true' allowScriptAccess='always' width='640' height='360'></embed>\
                ";
            return sPlayer;
        }


        function asyncYoutubeSearch(sSearchFor, iMaxResults, callBack )
        {
            document.getElementById("videoResultsDiv").innerHTML =  'Loading YouTube videos ...';

            //create a JavaScript element that returns our JSON data.
            var script = document.createElement('script');
            script.setAttribute('id', 'jsonScript');
            script.setAttribute('type', 'text/javascript');
            script.setAttribute('src', 'http://gdata.youtube.com/feeds/' + 
                   'videos?vq=' + sSearchFor + '&max-results=' + iMaxResults + '&' + 
                   'alt=json-in-script&callback=' + callBack +'&' + 
                   'orderby=relevance&sortorder=descending&format=5');

            //attach script to current page -  this will submit asynchronous
            //search request, and when the results come back callback 
            //function showMyVideos(data) is called and the results passed to it
            document.documentElement.firstChild.appendChild(script);
        }
        
        
        function searchClicked(sSearchFor)
        {
            document.getElementById("videoResultsDiv").innerHTML =  'Loading YouTube videos ...';

            //create a JavaScript element that returns our JSON data.
            var script = document.createElement('script');
            script.setAttribute('id', 'jsonScript');
            script.setAttribute('type', 'text/javascript');
            script.setAttribute('src', 'http://gdata.youtube.com/feeds/' + 
                   'videos?vq=' + sSearchFor + '&max-results=8&' + 
                   'alt=json-in-script&callback=showMyVideos&' + 
                   'orderby=relevance&sortorder=descending&format=5');

            //attach script to current page -  this will submit asynchronous
            //search request, and when the results come back callback 
            //function showMyVideos(data) is called and the results passed to it
            document.documentElement.firstChild.appendChild(script);
        }

// //         function showMyVideos(data)
// //         {
// //             var feed = data.feed;
// //             var entries = feed.entry || [];
// //             var html = ['<ul>'];
// //             var sPlayer = "";
// //             for (var i = 0; i < entries.length; i++)
// //             {
// //                 var entry = entries[i];
// //                 var playCount = entry.yt$statistics.viewCount.valueOf() + ' views';
// //                 var title = entry.title.$t;
// //                 var lnk = '<a href = \"' + entry.link[0].href + '\">link</a>';
// //                 var href = entry.link[0].href;
// //                 var videoID = getYoutubeVideoId(entry);
// //                 html.push('<li>', title, ', ', playCount, ', ', lnk, '</li>');
// //             }
// //             if ( entries.length > 0 ) {
// //                 var videoID = getYoutubeVideoId(entries[0]);
// //                 sPlayer = getEmbedYoutubeVideo( videoID );
// // //                player.loadVideoById("bHQqvYy5KYo", 5, "large");
// //                 player.loadVideoById( videoID );
// //             }
// //             
// //             html.push('</ul>');
// //             document.getElementById('videoResultsDiv').innerHTML = html.join('');
// //         }

        function parseVideoDataAndLoadFirst(data)
        {
            var feed = data.feed;
            var entries = feed.entry || [];
            for (var i = 0; i < entries.length; i++)
            {
                var entry = entries[i];
                g_allVideoIDs[i] = getYoutubeVideoId(entry);
            }
            if ( entries.length > 0 ) {
                g_currentVideoID = 0;
                var videoID = getYoutubeVideoId(entries[0]);
//                player.loadVideoById("bHQqvYy5KYo", 5, "large");
                player.loadVideoById( videoID );
            }
            
        }
    </script>

    </head>

    <body id="page">
        <div>
            <p>
                <input name="searchButton" type="submit" 
                  value="Search" onclick="asyncYoutubeSearch('Michael Jackson, Thriller', 5, 'parseVideoDataAndLoadFirst' )" />
            </p>

            <div id="player"></div>
<!--            <iframe id="player" type="text/html" width="640" height="390"
                src="http://www.youtube.com/embed/u1zgFlCw8Aw?enablejsapi=1&origin=http://example.com"
                frameborder="0">
            </iframe>-->
            
            
            <object id="youtubePlayer" width="300" height="200">
            </object>
 
  
            Results:<br/>
            <div id="videoResultsDiv"></div> 
            
            <!-- ShowMyVideos() will populate this div with results-->
        </div>
        
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

        // 3. This function creates an <iframe> (and YouTube player)
        //    after the API code downloads.
        var player;
        function onYouTubeIframeAPIReady() {
            player = new YT.Player('player', {
            height: '390',
            width: '640',
            videoId: '',
            events: {
                'onReady': onPlayerReady,
                'onStateChange': onPlayerStateChange
            }
            });
        }

        // 4. The API will call this function when the video player is ready.
        function onPlayerReady(event) {
            event.target.playVideo();
        }

        // 5. The API calls this function when the player's state changes.
        //    The function indicates that when playing a video (state=1),
        //    the player should play for six seconds and then stop.
        var done = false;
        function onPlayerStateChange(event) {
            if (event.data == YT.PlayerState.PLAYING && !done) {
            setTimeout(stopVideo, 0);
            done = true;
            }
        }
        function stopVideo() {
            player.stopVideo();
        }
        </script>
  </body>
        
        
    </body>
</html>

<?php

?>