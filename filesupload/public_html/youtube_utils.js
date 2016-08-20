
var g_allYoutubeVideoIDs       = [];
var g_currentYoutubeVideoID    = -1;

function playNextYoutubeVideo()
{
    if ( g_currentYoutubeVideoID == -1) return;
    var iNumVideos = g_allYoutubeVideoIDs.length;
    g_currentYoutubeVideoID = (g_currentYoutubeVideoID + 1) % iNumVideos
////    console.log("playNextYoutubeVideo: " + g_currentYoutubeVideoID);
    playerYoutube.loadVideoById( g_allYoutubeVideoIDs[g_currentYoutubeVideoID] );
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


function parseYoutubeVideoDataAndLoadFirst(data)
{
    var feed = data.feed;
    var entries = feed.entry || [];
    for (var i = 0; i < entries.length; i++)
    {
        var entry = entries[i];
        g_allYoutubeVideoIDs[i] = getYoutubeVideoId(entry);
    }
    if ( entries.length > 0 ) {
        g_currentYoutubeVideoID = 0;
        var videoID = getYoutubeVideoId(entries[0]);
        playerYoutube.loadVideoById( videoID );
    }    
}
