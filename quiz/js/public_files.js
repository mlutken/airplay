// ---------------
// --- Globals ---
// ---------------
var g_socialMediaUser = { fb_id : null };
var g_apUser = { data: null, friends: null, music: null, user_data_updated: false };

// --------------------
// --- String utils ---
// --------------------

// RegExp.prototype.quote = function(sToQuote) {
//      return sToQuote.replace(/([.?*+^$[\]\\(){}-])/g, "\\$1");
// };

function regExQuote(sString) 
{
    var sRep = sString.replace(/([.?*+^$[\]\\(){}-])/g, "\\$1");
    return sRep;
}

String.prototype.fulltrim=function()
{
    return this.replace(/(?:(?:^|\n)\s+|\s+(?:$|\n))/g,'').replace(/\s+/g,' ');
};

String.prototype.replaceAll = function (replaceThis, withThis) {
   var re = new RegExp(regExQuote(replaceThis),"g"); 
   return this.replace(re, withThis);
};

// ------------------------
// --- Cookie functions ---
// ------------------------

function getCookie(cname)
{
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++)
    {
        var c = ca[i].trim();
        if (c.indexOf(name)==0) return c.substring(name.length,c.length);
    }
    return "";
} 

function clearCookie(cname)
{
} 

// ---------------------
// --- URL functions ---
// ---------------------

/** Get this frame's base path.
 Examples:
 http://quiz.airplaymusic.dk 
 https://quiz.airplaymusic.dk 
 
 NOT : https://quiz.airplaymusic.dk?partner_name=anr
 NOT : https://apps.facebook.com/airplaymusic/
 NOT : https://apps.facebook.com/airplaymusic/?partner_name=anr
 */
function urlFrameBasePath()
{
    var url = window.location.href;
    
    // Remove parameters and anchors
    var i = url.indexOf("#");
    if ( i >= 0 ) url = url.slice(0,i);
    i = url.indexOf("?");
    if ( i >= 0 ) url = url.slice(0,i);
    return url;
}


function urlRemoveAnchorPart( url )
{
	var i = url.indexOf("#");
	return url.slice(0,i);
}


function getUrlParameter( key )
{
    key = key.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");  
    var regexS = "[\\?&]"+key+"=([^&#]*)";  
    var regex = new RegExp( regexS );  
    var results = regex.exec( window.location.href ); 
    if( results == null )   return "";  
    else                    return results[1];
}

//TODO: Should we have more substitutions? ";", "/", "?", ":", "@", "=" and "&" 
var G_URL_TO_NORMAL_fromUrl    =  new Array("_"   , "-and-", "-slash-", "-qmark-", "-percent-", "-plus-"  );
var G_URL_TO_NORMAL_toNormal   =  new Array(" "   , "&"    , "/"      , "?"      , "%"        , "+"       );


/** Converts from url-name to real name. 
\note should stay in sync with PHP function in aphp/utils/string_utils.php */
function urlToName( sUrlName ) 
{
    var i;
    var sName = sUrlName;
    for(i = 0; i < G_URL_TO_NORMAL_fromUrl.length; i++){
        G_URL_TO_NORMAL_fromUrl[i];
        sName = sName.replaceAll(G_URL_TO_NORMAL_fromUrl[i] , G_URL_TO_NORMAL_toNormal[i] );
    }
    return sName;
}


/** Converts from real name to url-name. 
 \note should stay in sync with PHP function in aphp/utils/string_utils.php */
function nameToUrl( sName ) 
{
    var i;
    var sUrlName = sName;
    for(i = 0; i < G_URL_TO_NORMAL_fromUrl.length; i++){
        G_URL_TO_NORMAL_fromUrl[i];
        sUrlName = sUrlName.replaceAll( G_URL_TO_NORMAL_toNormal[i], G_URL_TO_NORMAL_fromUrl[i] );
    }
    return sUrlName;
}

function inIframe()
{
    return parent !== window;
}

/** Returns outermost frame's URL. If we are not in a iframe just return nomat window.location.href. */
function parentFrameUrl() 
{
    var isInIframe = (parent !== window);
    if (isInIframe) parentUrl = document.referrer;
    else            parentUrl = window.location.href;  
    return parentUrl;
}



// --------------------
// --- Object utils ---
// --------------------
function clone(destination, source) 
{
	for (var property in source) {
		if (typeof source[property] === "object" && source[property] !== null && destination[property]) { 
			clone(destination[property], source[property]);
		} else {
			destination[property] = source[property];
		}
	}
};


// --------------------
// -- General utils ---
// --------------------

function checkBoxToggle(domID)
{
    if (document.getElementById(domID).checked == false) {
        document.getElementById(domID).checked = true;
    }
    else {
        document.getElementById(domID).checked = false;
    }
    
}

function selectGotoPage(select) 
{ 
    var index=select.selectedIndex
    if (select.options[index].value != "0") {
        location=select.options[index].value;
    }
}

function removeElementById(elemID) 
{ 
	var elemObj = document.getElementById(elemID);
	elemObj.parentNode.removeChild(elemObj);
}

/** Get number of element in an object. */
function objectSize(obj) 
{
     var size = 0, key;
     for (key in obj) {
         if (obj.hasOwnProperty(key)) size++;
     }
     return size;
}

/** Standard levenshtein distance. 
 \return Number of chars to change to get from one to another. */
function levenshteinDistance (a, b) {
	if(a.length == 0) return b.length; 
	if(b.length == 0) return a.length; 

	var matrix = [];

	// increment along the first column of each row
	var i;
	for(i = 0; i <= b.length; i++){
		matrix[i] = [i];
	}

	// increment each column in the first row
	var j;
	for(j = 0; j <= a.length; j++){
		matrix[0][j] = j;
	}

	// Fill in the rest of the matrix
	for(i = 1; i <= b.length; i++){
		for(j = 1; j <= a.length; j++){
			if(b.charAt(i-1) == a.charAt(j-1)){
				matrix[i][j] = matrix[i-1][j-1];
			} 
			else {
				matrix[i][j] = Math.min(matrix[i-1][j-1] + 1,      // substitution
									Math.min(matrix[i][j-1] + 1,   // insertion
											 matrix[i-1][j] + 1)); // deletion
			}
		}
	}

	return matrix[b.length][a.length];
}


/**
 * Returns a random floating point number between min and max
 */
function randomFloat (min, max) 
{
    return Math.random() * (max - min) + min;
}

/**
 * Returns a random integer between min and max
 * Note: Using Math.round() will give you a non-uniform distribution! So we don't do that here!
 */
function randomInt (min, max) 
{
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

/**
 * Simplifies and artist_name or an item_base_name.
 * All chars are converted to lowercase and we remove special chars like apostrophe etc.
 * TODO: We should also replace 'and' with '&' etc.
 */
function apNormalizeName (sName, aExtraRemove) 
{
	aExtraRemove = aExtraRemove || [];
    sName = sName.toLowerCase();
	for ( i = 0; i < aExtraRemove.length; i++ ) {
		sName = sName.replace(aExtraRemove[i], '').fulltrim();
	}
    sName = sName.replace('\'', '').fulltrim();
	return sName;
}


// -------------------------------
// --- Youtube: Authentication ---
// -------------------------------

// The client id is obtained from the Google APIs Console at https://code.google.com/apis/console
// If you run access this code from a server other than http://localhost, you need to register
// your own client id.
// AI39si5YQsWjHNbEFLjUEavBB4f5mH_IXFLuruhYsNkwzVnZDNCx7lQp3ebWO8p1KxIdv2feE1Myf5lF33WYRfvSj2EGVLFukQ
var OAUTH2_CLIENT_ID = '752279067568';
var OAUTH2_SCOPES = [
  'https://www.googleapis.com/auth/youtube'
];

// This callback is invoked by the Google APIs JS client automatically when it is loaded.
googleApiClientReady = function() {
  gapi.auth.init(function() {
    window.setTimeout(checkAuth, 1);
  });
}

// Attempt the immediate OAuth 2 client flow as soon as the page is loaded.
// If the currently logged in Google Account has previously authorized OAUTH2_CLIENT_ID, then
// it will succeed with no user intervention. Otherwise, it will fail and the user interface
// to prompt for authorization needs to be displayed.
function checkAuth() {
  gapi.auth.authorize({
    client_id: OAUTH2_CLIENT_ID,
    scope: OAUTH2_SCOPES,
    immediate: true
  }, handleAuthResult);
}

// Handles the result of a gapi.auth.authorize() call.
function handleAuthResult(authResult) {
  if (authResult) {
    // Auth was successful; hide the things related to prompting for auth and show the things
    // that should be visible after auth succeeds.
    $('.pre-auth').hide();
    loadAPIClientInterfaces();
  } else {
    // Make the #login-link clickable, and attempt a non-immediate OAuth 2 client flow.
    // The current function will be called when that flow is complete.
    $('#login-link').click(function() {
      gapi.auth.authorize({
        client_id: OAUTH2_CLIENT_ID,
        scope: OAUTH2_SCOPES,
        immediate: false
        }, handleAuthResult);
    });
  }
}

// Loads the client interface for the YouTube Analytics and Data APIs.
// This is required before using the Google APIs JS client; more info is available at
// http://code.google.com/p/google-api-javascript-client/wiki/GettingStarted#Loading_the_Client
function loadAPIClientInterfaces() {
  gapi.client.load('youtube', 'v3', function() {
    handleAPILoaded();
  });
}



// --------------------------------
// --- Mobile/handheld specific ---
// --------------------------------

window.mobilecheck = function() {
    var check = false;
    ( function(a) { if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
    return check; 
}



// ---------------------
// --- jQuery Mobile ---
// ---------------------

/** Change page and allow transition to same page. */
function jqmChangePage(domID, options)
{
    options = options || {};
    $.mobile.pageContainer.pagecontainer('change', '#' + domID, options );    
}

/** Change page and allow transition to same page. */
function jqmChangePageSame(domID)
{
    $.mobile.pageContainer.pagecontainer('change', '#' + domID, {changeHash: false, allowSamePageTransition: true} );   
}

/** jQuery mobile, render list. 
\example 
    var dbgList =   [ 
                      { link: "", user_name: 'Donald Duck', score : 111, profile_image_url : 'https://fbcdn-profile-a.akamaihd.net/hprofile-ak-prn1/41496_766189393_7109_q.jpg' } 
                    , { link: "", user_name: 'Mickey mouse', score : 114, profile_image_url : 'https://fbcdn-profile-a.akamaihd.net/hprofile-ak-prn1/41496_766189393_7109_q.jpg' } 
                    ];
    
    var paramsObj = { 
          dom_id: 'debugAreaWelcomePageID'
        , list_type: 'ol'
        , image_key: 'profile_image_url'
        , headline_key: 'user_name'
        , text_key: 'score'
        ,  
        };
    jqmRenderList(dbgList, paramsObj );
 
\endexample
 */
function jqmRenderList( listDataObj, paramsObj ) 
{ 
    quizClearPage(domID);
    var domID = paramsObj.dom_id ;

    var listType_s      = ( paramsObj.list_type && paramsObj.list_type == "ol"      ) ? "ol" : "ul";
    var linkKey_s       = ( paramsObj.link_key && paramsObj.link_key != ""          ) ? paramsObj.link_key : "link";
    var imageKey_s      = ( paramsObj.image_key && paramsObj.image_key != ""        ) ? paramsObj.image_key : "image";
    var headlineKey_s   = ( paramsObj.headline_key && paramsObj.headline_key != ""  ) ? paramsObj.headline_key : "headline";
    var textKey_s       = ( paramsObj.text_key && paramsObj.text_key != ""          ) ? paramsObj.text_key : "text";

    var sHtml = "";
  
    sHtml += "<" + listType_s + " data-role='listview' >";
    for ( var i=0; i < listDataObj.length; i++)
    {
        var link_s = listDataObj[i][linkKey_s];
        var image_s = listDataObj[i][imageKey_s];
        var headline_s = listDataObj[i][headlineKey_s];
        var text_s = listDataObj[i][textKey_s];
        var s = "<li><a href='" + link_s + "' >";
        s += "<img src='" + image_s + "' />";
        s += "<h3>" + headline_s + "</h3>";
        s += "<p>" + text_s + "</p>";
        s += "</a></li>";
        sHtml += s;
    }
    sHtml += "</" + listType_s + ">";

    $('#' + domID).append(sHtml);
    $('#' + domID).trigger("create");
}

function refreshPage(pageID){
////    $('#' + pageID ).trigger('pagecreate');
////    $('#' + pageID ).listview('refresh');
}


