
var g_apmPlaylist = [];
var g_youtubeParseSearchResultsCallBack = null;

var g_sLatestSearchForString = "";
var g_sCurrentProvider = '';
var g_sDefaultProvider = 'youtube';


function youtubeCreatePlayer(iWidth, iHeight)
{
    jQuery("#apmYoutubePlayerContainerID").tubeplayer('destroy');
    jQuery("#apmYoutubePlayerContainerID").empty();
    jQuery("#apmYoutubePlayerContainerID").tubeplayer({
        width: iWidth, // the width of the player
        height: iHeight, // the height of the player
        autoPlay: false,
		protocol: 'https',
        allowFullScreen: "false", // true by default, allow user to go full screen
        initialVideo: "W94PRyofCS8", // the video that is loaded into the player
        preferredQuality: "default",// preferred quality: default, small, medium, large, hd720
        onPlay: function(id){}, // after the play method is called
        onPause: function(){}, // after the pause method is called
        onStop: function(){}, // after the player is stopped
        onSeek: function(time){}, // after the video has been seeked to a defined point
        onMute: function(){}, // after the player is muted
        onUnMute: function(){} // after the player is unmuted
    });

}


function youtubeApmObjectGet( videoEntry )
{
    var href = videoEntry.link[0].href;
    //http://www.youtube.com/watch?v=muJ6sRI29qQ&feature=youtube_gdata
    var iStart  = href.search("\\?v=") + 3;
    var iEnd    = href.search("&feature");
    var videoID = href.slice(iStart, iEnd);
    var apmObj = { youtube_id : videoID, title: videoEntry.title.$t };
    return apmObj;
}


function youtubeAsyncSearch(sSearchFor, iMaxResults, callBack )
{
    // Delete old element first
    var element = document.getElementById('youtubeAsyncSearchScriptID');
    if (element) element.parentNode.removeChild(element);    
    
    //create a JavaScript element that returns our JSON data.
    var script = document.createElement('script');
    script.setAttribute('id', 'youtubeAsyncSearchScriptID');
    script.setAttribute('type', 'text/javascript');
    script.setAttribute('src', 'https://gdata.youtube.com/feeds/' + 
            'videos?vq=' + sSearchFor + '&max-results=' + iMaxResults + '&' + 
            'alt=json-in-script&callback=' + callBack +'&' + 
            'orderby=relevance&sortorder=descending&format=5');

    //attach script to current page -  this will submit asynchronous
    //search request, and when the results come back callback 
    //function showMyVideos(data) is called and the results passed to it
    document.documentElement.firstChild.appendChild(script);
}


function youtubeParseSearchResults(data, callBack)
{
    var feed = data.feed;
    var entries = feed.entry || [];
    g_apmPlaylist = [];
    for (var i = 0; i < entries.length; i++)
    {
        var entry = entries[i];
        g_apmPlaylist[i] = youtubeApmObjectGet(entry);
        //console.log("entry: " + entry );
    }
    g_youtubeParseSearchResultsCallBack();
}

// ----------------------------------------------------
// --- Airplay Music Media player general functions ---
// ----------------------------------------------------
// NOTE: Currently the only provider we support is 'youtube'.

/** Helper function to get provider name from playObject. */
function apmProviderFromPlayObj(apmPlayObj)
{
	if ( apmPlayObj.youtube_id != '' ) return 'youtube';
	else {
		console.log("ERROR: apmProviderFromPlayObj(), unkknown provider.");
		return "ErrorUnknownProvider";
	}
}

/** Do an async search using the "provider" given. 
 * Providers: 'youtube'.
 */
function apmAsyncSearch(searchFor, iMaxResults, callBack, sProvider )
{
    sProvider = sProvider || g_sDefaultProvider;
	var sSearchFor = "";
	if ( typeof searchFor === 'string' ) {
		sSearchFor = searchFor
	}
	else {
		sSearchFor = searchFor.artist_name + " - " +  searchFor.item_base_name;
	}
	g_sLatestSearchForString = sSearchFor;
    //console.log("FIXME apmAsyncSearch: " + sSearchFor );
    if ( sProvider == 'youtube' ) {
        g_youtubeParseSearchResultsCallBack = callBack;
        youtubeAsyncSearch(sSearchFor, iMaxResults, 'youtubeParseSearchResults' );
    }
}



/** Load an audio/video. 
 * Providers: 'youtube'.
 */
function apmAudioVideoLoad(apmPlayObj)
{
    var sProvider = apmProviderFromPlayObj(apmPlayObj);
    if ( sProvider == 'youtube' ) {
		g_sCurrentProvider = 'youtube';
        jQuery("#apmYoutubePlayerContainerID").tubeplayer("play", apmPlayObj.youtube_id );
        jQuery("#apmYoutubePlayerContainerID").tubeplayer("stop");
    }
}

/** Load and play an audio/video. 
 * Providers: 'youtube'.
 */
function apmAudioVideoLoadAndplay(apmPlayObj)
{
    var sProvider = apmProviderFromPlayObj(apmPlayObj);
    if ( sProvider == 'youtube' ) {
		g_sCurrentProvider = 'youtube';
        jQuery("#apmYoutubePlayerContainerID").tubeplayer("play", apmPlayObj.youtube_id );
    }
    else {
        alert("Error: apmAudioVideoLoadAndplay");
    }
}

/** Load an audio/video from playlist using the "provider" given. 
 * Providers: 'youtube'.
 */
function apmAudioVideoLoadFromPlayList(iPlayObjIndex, playListArray)
{
    var playList = playListArray;
    if ( !playList ) playList = g_apmPlaylist;
    var apmPlayObj = playList[iPlayObjIndex];
    apmAudioVideoLoad(apmPlayObj); 
}


/** Load next audio/video from playlist using the "provider" given. 
 * Providers: 'youtube'.
 */
// function apmAudioVideoPlayListNext(playListArray, iCurrentIndex, sProvider )
// {
//     sProvider = sProvider || g_sDefaultProvider;
//     var playList = playListArray;
//     if ( !playList ) playList = g_apmPlaylist;
//     var apmPlayObj = playList[iPlayObjIndex];
//     apmAudioVideoLoad(apmPlayObj,sProvider); 
// }


/** Play the current audio/video using the "provider" given. 
 * Providers: 'youtube'.
 */
function apmPlay(sProvider)
{
    sProvider = sProvider || g_sCurrentProvider || g_sDefaultProvider;
//     console.log("apmPlay: " + sProvider );
    if ( sProvider == 'youtube' ) {
        jQuery("#apmYoutubePlayerContainerID").tubeplayer("play");
    }
    else {
        alert("Error: apmPlay");
    }
}


/** Stop the current audio/video using the "provider" given. 
 * Providers: 'youtube'.
 */
function apmStop(sProvider)
{
    sProvider = sProvider || g_sCurrentProvider || g_sDefaultProvider;
	console.log("apmStop: " + sProvider );
    if ( sProvider == 'youtube' ) {
        jQuery("#apmYoutubePlayerContainerID").tubeplayer("stop");
    }
    else {
        alert("Error: apmStop");
    }
}


/** Pause the current audio/video using the "provider" given. 
 * Providers: 'youtube'.
 */
function apmPause(sProvider)
{
    sProvider = sProvider || g_sCurrentProvider || g_sDefaultProvider;
//     console.log("apmPause: " + sProvider );
    if ( sProvider == 'youtube' ) {
        jQuery("#apmYoutubePlayerContainerID").tubeplayer("pause");
    }
}


/** Seek a position in seconds in the current audio/video using the "provider" given. 
 * Providers: 'youtube'.
 */
function apmSeek(sProvider, iPosInSeconds)
{
    sProvider = sProvider || g_sCurrentProvider || g_sDefaultProvider;
    if ( sProvider == 'youtube' ) {
        jQuery("#apmYoutubePlayerContainerID").tubeplayer("seek", iPosInSeconds);
    }
}


/** Mute player using the "provider" given. 
 * Providers: 'youtube'.
 */
function apmMute(sProvider)
{
    sProvider = sProvider || g_sCurrentProvider || g_sDefaultProvider;
    if ( sProvider == 'youtube' ) {
        jQuery("#apmYoutubePlayerContainerID").tubeplayer("mute");
    }
}

/** Un-mute player using the "provider" given. 
 * Providers: 'youtube'.
 */
function apmUnMute(sProvider)
{
    sProvider = sProvider || g_sCurrentProvider || g_sDefaultProvider;
    if ( sProvider == 'youtube' ) {
        jQuery("#apmYoutubePlayerContainerID").tubeplayer("unmute");
    }
}

/** Set player size using the "provider" given. 
 * Providers: 'youtube'.
 */
function apmSetSize(sProvider, iWidth, iHeight )
{
    sProvider = sProvider || g_sCurrentProvider || g_sDefaultProvider;
    if ( sProvider == 'youtube' ) {
        jQuery("#apmYoutubePlayerContainerID").tubeplayer("size", { width : iWidth, height: iHeight } );
    }
}

/** Get current playing time. 
 * Providers: 'youtube'.
 */
function apmCurrentPlayingTime(sProvider)
{
    sProvider = sProvider || g_sCurrentProvider || g_sDefaultProvider;
    if ( sProvider == 'youtube' ) {
        return jQuery("#apmYoutubePlayerContainerID").tubeplayer("data").currentTime;
    }
}



/** Move player to running quiz area (DIV apmYoutubePlayerShowInQuizPosID ). 
 * Providers: 'youtube'.
 */
function apmMovePlayerToQuizArea(sProvider)
{
    sProvider = sProvider || g_sCurrentProvider || g_sDefaultProvider;
    if ( sProvider == 'youtube' ) {
        jQuery("#apmYoutubePlayerContainerID").detach().appendTo('#apmYoutubePlayerShowInQuizPosID');  
    }
}

/** Move player hiding area (DIV apmYoutubePlayerHidePosID ). 
 * Providers: 'youtube'.
 */
function apmMovePlayerToHideArea(sProvider)
{
    sProvider = sProvider || g_sCurrentProvider || g_sDefaultProvider;
    if ( sProvider == 'youtube' ) {
        jQuery("#apmYoutubePlayerContainerID").detach().appendTo('#apmYoutubePlayerHidePosID');  
    }
}

// ----------------------
// --- Util functions ---
// ----------------------
function apmCandidateDistance(sSearchForInLowerCase, sCandidateInLowercase)
{
	var iLevDist = levenshteinDistance(sSearchForInLowerCase, sCandidateInLowercase);
	var dist = iLevDist / sSearchForInLowerCase.length;
// // 	console.log("Dist(" + sSearchForInLowerCase + ", " + sCandidateInLowercase + "):" + dist);
	return dist;
	
}

/** Finds best match from a playlist/search-results-list (like from apmAsyncSearch) and returns the 
an object like this:
returnObj = {
      play_object : standard apmPlayObject
    , lev_dist : The levenshtein distance for the one selected
    , playlist_index_selected: Index selected from search/playlist 
    , search_for : The searchFor object that we called this function with
}; 
 */
function apmBestMatchFromList(apmPlaylist, searchFor, bestMatchObj)
{
    var artist_name_NOR 	= apNormalizeName(searchFor.artist_name);
    var item_base_name_NOR 	= apNormalizeName(searchFor.item_base_name);
	
    var sSearchFor = artist_name_NOR + " - " +  item_base_name_NOR;
	
    var iBestCandidate                  = -1;
    var iShortestCandidateDist          = 1000;
    
// //     // Normalize playlist names
// //     for (var i = 0; i < apmPlaylist.length; i++)
// //     {
// //         apmPlaylist[i].title_NOR = apNormalizeName(apmPlaylist[i].title); 
// //     }

    var candidateList = [];
    var toSortList1 = [];

    // --- Check with full search-for name --
    for (var i = 0; i < apmPlaylist.length; i++)
    {
//        var sCandidate = apmPlaylist[i].title;
        var sCandidate = apNormalizeName(apmPlaylist[i].title);
		var iCandidateDistDelta = i/100;
        var iCandidateDist = apmCandidateDistance(sSearchFor, sCandidate)+iCandidateDistDelta; // We add something small increasing down the provider list. This is to also account for the order that the provider suggests
        apmPlaylist[i].candidate_dist = iCandidateDist;
        apmPlaylist[i].provider_index = i;
//        console.log(" Candidate[" + i + "]: " + sCandidate );
       
        // Special treatment of first provider result
        if ( i == 0 ) {
			if (    
				   (iCandidateDist < 0.2) ||
			       ((sCandidate.indexOf(artist_name_NOR) != -1) && (sCandidate.indexOf(item_base_name_NOR) != -1))
			   )
			{
				apmPlaylist[i].candidate_dist = 0; // Force candidate_dist to zero!!
				candidateList.push(apmPlaylist[i]);
			}
		}
        else {
            if ( iCandidateDist < 0.00001 ) { // NB: If zero, just using very low 'epsilon' value to avoid rounding errors
                candidateList.push(apmPlaylist[i]);
            }
            else if ( iCandidateDist < (0.10 + iCandidateDistDelta )) {
                toSortList1.push(apmPlaylist[i]);
            }
            else if ( 	(artist_name_NOR.length > 10					) && 
						(item_base_name_NOR.length > 10					) && 
						(sCandidate.indexOf(artist_name_NOR) != -1		) && 
						(sCandidate.indexOf(item_base_name_NOR) != -1	)
					)
			{
                toSortList1.push(apmPlaylist[i]);
			}
        }
        if (candidateList.length >= 3 ) break;
    }
    
    toSortList1.sort( function(lhs,rhs) { return lhs.candidate_dist > rhs.candidate_dist; } );
    
    var n = 0;
    while (candidateList.length < 3 && (n < toSortList1.length) ) {
        candidateList.push(toSortList1[n]);
        n++;
    }
    
    if ( candidateList.length > 0 ) {
        iShortestCandidateDist = candidateList[0].candidate_dist;
    }
    else {
        candidateList[0] = null;
    }
    
    var returnObj = {
          recur_counter : bestMatchObj.recur_counter
        , play_object : candidateList[0]
        , search_for : searchFor
        , cadidate_list : candidateList
    };
    
    return returnObj;
}

/*
*/

// -----------------------
// --- Debug functions ---
// -----------------------
/** Seek a position in seconds in the current audio/video using the "provider" given. 
 * Providers: 'youtube'.
 */
function apmDbgRenderPlaylistSimple(domID, playlist, sComputeLevDistTo )
{
	sComputeLevDistTo = sComputeLevDistTo || g_sLatestSearchForString;
    playlist = playlist || g_apmPlaylist;
    
    jQuery('#'+domID).empty();
    for ( var i=0; i < playlist.length; i++)
    {
        var youtube_id = playlist[i].youtube_id;
        var title = playlist[i].title;
        title = title.replace('\'', '');
		var levDist = apmCandidateDistance(title, sComputeLevDistTo);
        
        var radioBtn = jQuery
        (
			  "<input type=radio name=playlist onclick=\"apmAudioVideoLoadAndplay( {youtube_id: '" + youtube_id 
			+ "', title: '" + title + "' } );\" ><span><b>" + title 
			+ '</b>&nbsp;&nbsp;&nbsp;'+ youtube_id 
			+ '&nbsp;&nbsp;(</i>Levenshtein&nbsp;'+ levDist 
			+ "</i>)</span><br>"
		);
        radioBtn.fadeIn(500).appendTo('#' + domID);
    }   
    
}




function __OLD_apmBestMatchFromList(apmPlaylist, searchFor, bestMatchObj)
{
    var artist_name_NOR     = apNormalizeName(searchFor.artist_name);
    var item_base_name_NOR  = apNormalizeName(searchFor.item_base_name);
    
    var sSearchFor = artist_name_NOR + " - " +  item_base_name_NOR;
    
    var iBestCandidate                  = -1;
    var iBestCandidateFullSearchName    = -1; 
    var iBestCandidateItemBaseName      = -1; 
    var iShortestCandidateDist                = 1000;
    var iShortestCandidateDistFullSearchName  = 10000; // Just pick a big number :-)
    var iShortestCandidateDistItemBaseName    = 10000; // 
    
    
    
    for (var i = 0; i < apmPlaylist.length; i++)
    {
        var iLevDist = 0;
        var sCandidate = apNormalizeName(apmPlaylist[i].title); 
        var sCandidate_item_base_name = sCandidate.replace(artist_name_NOR, ''); // Remove artist_name before we match them up
        sCandidate_item_base_name = sCandidate_item_base_name.replace('-', '').fulltrim();
        
        // --- Check with full search-for name --
        var iLevDistFullSearchName = apmCandidateDistance(sSearchFor, sCandidate );
        if ( iLevDistFullSearchName < iShortestCandidateDistFullSearchName ) {
            iShortestCandidateDistFullSearchName = iLevDistFullSearchName;
            iBestCandidateFullSearchName = i;
        }
        
        // --- Check with artist_name removed --
        var iLevDistItemBaseName = apmCandidateDistance(item_base_name_NOR, sCandidate_item_base_name );
        if ( iLevDistItemBaseName < iShortestCandidateDistItemBaseName ) {
            iShortestCandidateDistItemBaseName = iLevDistItemBaseName;
            iBestCandidateItemBaseName = i;
        }

        if ( iShortestCandidateDistFullSearchName == 0 ) break;
    }
    
    if ( iShortestCandidateDistItemBaseName < iShortestCandidateDistFullSearchName ) {
        iBestCandidate      = iBestCandidateItemBaseName;
        iShortestCandidateDist    = iShortestCandidateDistItemBaseName;
    }
    var returnObj = {
          recur_counter : bestMatchObj.recur_counter
        , play_object : apmPlaylist[iBestCandidate]
        , lev_dist : iShortestCandidateDist
        , best_candidate: iBestCandidate
        , search_for : searchFor
        , lev_dist_full_search_name : iShortestCandidateDistFullSearchName
        , lev_dist_item_base_name : iBestCandidateItemBaseName
//        , playlist_index_selected: -1
        , best_candidate_full_search_name: iBestCandidateFullSearchName
        , best_candidate_item_base_name: iBestCandidateItemBaseName
    };
    
    return returnObj;
}
