<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" 
           xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Google image search artist sandbox</title>
    <script src="https://www.google.com/jsapi"></script>
<!--     <script src="youtube_utils.js"></script> -->
    <script type="text/javascript">

        var g_allArtistImages           = [];
        var g_currentArtistImageIndex   = -1;
        
    
        function showNextImage()
        {
            if ( g_currentArtistImageIndex == -1) return;
            var iNumVideos = g_allArtistImages.length;
            g_currentArtistImageIndex = (g_currentArtistImageIndex + 1) % iNumVideos
            console.log("showNextImage: " + g_currentArtistImageIndex);
 //           player.loadVideoById( g_allArtistImages[g_currentArtistImageIndex] );
            
            
        }
    
        function getYoutubeVideoId( videoEntry )
        {
            var href = videoEntry.link[0].href;
            //http://www.youtube.com/watch?v=muJ6sRI29qQ&feature=youtube_gdata
            var iStart  = href.search("\\?v=") + 3;
            var iEnd    = href.search("&feature");
            var videoID = href.slice(iStart, iEnd);
            return videoID;
        }



        function asyncYoutubeSearch(sSearchFor, iMaxResults, callBack )
        {
            //create a JavaScript element that returns our JSON data.
            var script = document.createElement('script');
            script.setAttribute('id', 'asyncYoutubeSearchScriptID');
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
        

    function searchComplete(searcher) {
        // Check that we got results
        if (searcher.results && searcher.results.length > 0) {
            // Grab our artistImage div, clear it.
            var contentDiv = document.getElementById('images');
            contentDiv.innerHTML = '';

            // Loop through our results, printing them to the page.
            var results = searcher.results;
            for (var i = 0; i < results.length; i++) {
                // For each result write it's title and image to the screen
                var result = results[i];
                console.log("img: " + result.tbUrl);
                var imgContainer = document.createElement('div');

                var title = document.createElement('h2');
                // We use titleNoFormatting so that no HTML tags are left in the title
                title.innerHTML = result.titleNoFormatting;

                var newImg = document.createElement('img');
                // There is also a result.url property which has the escaped version
                newImg.src = result.tbUrl;

                imgContainer.appendChild(title);
                imgContainer.appendChild(newImg);

                // Put our title + image in the content
                contentDiv.appendChild(imgContainer);
            }
        }
    }
        
        
        function parseGoogleImageDataAndLoadFirst(data)
        {
        // Check that we got results
        if (data.results && data.results.length > 0) {
            // Grab our artistImage div, clear it.
            var contentDiv = document.getElementById('images');
            contentDiv.innerHTML = '';

            // Loop through our results, printing them to the page.
            var results = data.results;
            for (var i = 0; i < results.length; i++) {
                // For each result write it's title and image to the screen
                var result = results[i];
                console.log("img: " + result.tbUrl);
                var imgContainer = document.createElement('div');

                var title = document.createElement('h2');
                // We use titleNoFormatting so that no HTML tags are left in the title
                title.innerHTML = result.titleNoFormatting;

                var newImg = document.createElement('img');
                // There is also a result.url property which has the escaped version
                newImg.src = result.tbUrl;

                imgContainer.appendChild(title);
                imgContainer.appendChild(newImg);

                // Put our title + image in the content
                contentDiv.appendChild(imgContainer);
            }
        }
        
        
        
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
                player.loadVideoById( videoID );
            }
            
        }
        
    google.load('search', '1');
        
    </script>

</head>

<body id="page">
    <div>
        <div id="artistImage"></div>
        <br />
        <input name="test" type="submit" 
            value="Test" onclick="console.log('hey')" />
    </div>
    <br />
    <div id="images"></div>
    <?php
    
//    echo addYoutubePlayer( 'playerYoutube', 'Michael Jackson, Thriller', 8, 300, 200 );
    ?>
    
    
</body>
        
</html>

    <script type="text/javascript">


    function OnLoad() {
        // Our ImageSearch instance.
        var imageSearch = new google.search.ImageSearch();

        // Restrict to extra large images only
        imageSearch.setRestriction(google.search.ImageSearch.RESTRICT_IMAGESIZE,
                                    google.search.ImageSearch.IMAGESIZE_LARGE);

        // Here we set a callback so that anytime a search is executed, it will call
        // the searchComplete function and pass it our ImageSearch searcher.
        // When a search completes, our ImageSearch object is automatically
        // populated with the results.
        imageSearch.setSearchCompleteCallback(this, searchComplete, [imageSearch]);

        imageSearch.execute("Michael Jackson -cover");
    }
    google.setOnLoadCallback(OnLoad);    
    </script>

<?php

function addArtistImage($playerYoutube, $sInitialYoutubeSearchString, $iMaxResults, $iWidth, $iHeight )
{
$s=
<<<SCRIPT
<script>
</script>
SCRIPT;
    return $s;
}


?>