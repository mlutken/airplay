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


// ---------------------
// --- URL functions ---
// ---------------------

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

// --------------------
// --- Social media ---
// --------------------

// function shareFacebookLike(url)
// {
//     window.location="http://www.facebook.com/sharer/sharer.php?u=" + encodeURIComponent(url);
// }
// 
// function shareTwitter(url, text)
// {
//     window.location = "https://twitter.com/intent/tweet?text=" + encodeURIComponent(text) + "&url=" + encodeURIComponent(url);
// }
// 
// function shareEmail(subject, body)
// {
//     window.location = "mailto:&subject=" + subject + "&body=" + body;
// }

function addSocialShare(domID, sUrl, sTitle, sSummary )
{
    jQuery('#'+domID).empty();
    var sUrlNoHttp      = sUrl.replace("http://", "" );
    var sUrlWithHttp    = "http://" + sUrlNoHttp ;
    var sUrlImage       = "http://public.airplaymusic.dk/css/img/airplaymusic_logo_80x80.png";
// 
    var sUrlNoHttpCoded     = encodeURIComponent(sUrlNoHttp);
    var sUrlWithHttpCoded   = encodeURIComponent(sUrlWithHttp);

    var sTitleCoded     = encodeURIComponent(sTitle);
    var sSummaryCoded   = encodeURIComponent(sSummary);
    var sUrlImageCoded  = encodeURIComponent(sUrlImage);
    
    
    var sListViewDomID = domID + "-listview";
    var sSummaryDomID = domID + "-summaryInput";
    var sHtml = "";
    sHtml += "<ul data-role='listview' id='" + sListViewDomID + "' >";
    
    var bOnMobile = window.mobilecheck();
    
    // --- Facebook ---
    var sFbUrl = "http://www.facebook.com/sharer.php?s=100&p[title]=TARGET_TITLE&p[summary]=TARGET_SUMMARY&p[url]=TARGET_URL&p[images][0]=TARGET_IMAGE_TO_SHARE_OBJECT";
    if ( bOnMobile ) {
        sFbUrl = "http://m.facebook.com/sharer.php?u=TARGET_URL&t=TARGET_SUMMARY";
    }
    sFbUrl = sFbUrl.replace('TARGET_URL', sUrlWithHttpCoded );
    sFbUrl = sFbUrl.replace('TARGET_TITLE', sTitleCoded );
    sFbUrl = sFbUrl.replace('TARGET_SUMMARY', sSummaryCoded );
    sFbUrl = sFbUrl.replace('TARGET_IMAGE_TO_SHARE_OBJECT', sUrlImageCoded );
    sHtml += "<li><a href='" + sFbUrl + "' target='_blank' >";
    sHtml += "<img src='/css/img/facebook_logo_80x80.png' />";
    sHtml += "<h3>Facebook</h3>";
    sHtml += "<p>" + sUrl + "</p>";
    sHtml += "</a></li>";

    // --- Twitter ---
    var sTwitterUrl = "https://twitter.com/intent/tweet?text=TARGET_SUMMARY&url=TARGET_URL";
    sTwitterUrl = sTwitterUrl.replace('TARGET_URL', sUrlWithHttpCoded );
    sTwitterUrl = sTwitterUrl.replace('TARGET_SUMMARY', sSummaryCoded );
    sHtml += "<li><a href='" + sTwitterUrl + "' target='_blank' >";
    sHtml += "<img src='/css/img/twitter_logo_80x80.png' />";
    sHtml += "<h3>Twitter</h3>";
    sHtml += "<p>" + sUrl + "</p>";
    sHtml += "</a></li>";

    // --- LinkedIn ---
    var sLinkedInUrl = "http://www.linkedin.com/shareArticle?mini=true&url=TARGET_URL&title=TARGET_TITLE&source=TARGET_URL";
    sLinkedInUrl = sLinkedInUrl.replaceAll('TARGET_URL', sUrlWithHttpCoded );
    sLinkedInUrl = sLinkedInUrl.replace('TARGET_TITLE', sTitleCoded );
    sLinkedInUrl = sLinkedInUrl.replace('TARGET_SUMMARY', sSummaryCoded );
    sLinkedInUrl = sLinkedInUrl.replace('TARGET_IMAGE_TO_SHARE_OBJECT', sUrlImageCoded );
    sHtml += "<li><a href='" + sLinkedInUrl + "' target='_blank' >";
    sHtml += "<img src='/css/img/linkedin_logo_80x80.png' />";
    sHtml += "<h3>LinkedIn</h3>";
    sHtml += "<p>" + sUrl + "</p>";
    sHtml += "</a></li>";
    //http://www.linkedin.com/shareArticle | mini=true  url=CONTENT-URL  title=CONTENT-TITLE summary=DEATILS-OPTIONAL source=YOURWEBSITE-NAME

    // --- Sms ---
    if ( bOnMobile ) {
        var sSmsUrl = "sms:?subject=TARGET_TITLE&body=MAIL_BODY";
        var sSmsBody = sTitle + " \n " + sSummary + " \n " + sUrl;
        sSmsUrl = sSmsUrl.replace('TARGET_TITLE', sTitleCoded );
        sSmsUrl = sSmsUrl.replace('MAIL_BODY', encodeURIComponent(sSmsBody) );
        sHtml += "<li><a href='" + sSmsUrl + "' target='_blank' >";
        sHtml += "<img src='/css/img/sms_logo_80x80.png' />";
        sHtml += "<h3>Sms</h3>";
        sHtml += "<p>" + sUrl + "</p>";
        sHtml += "</a></li>";
    }
    
    // --- Mail ---
    var sMailUrl = "mailto:?subject=TARGET_TITLE&body=MAIL_BODY";
    var sMailBody = sTitle + " \n\n " + sSummary + " \n\n " + sUrl;
    sMailUrl = sMailUrl.replace('TARGET_TITLE', sTitleCoded );
    sMailUrl = sMailUrl.replace('MAIL_BODY', encodeURIComponent(sMailBody) );
    sHtml += "<li><a href='" + sMailUrl + "' target='_blank' >";
    sHtml += "<img src='/css/img/mail_logo_80x80.png' />";
    sHtml += "<h3>Mail</h3>";
    sHtml += "<p>" + sUrl + "</p>";
    sHtml += "</a></li>";

    // --- Finalize ---
    sHtml += "</ul>";
    $('#' + domID).append(sHtml);
    $('#' + domID).trigger("create");
}


// --------------------------------
// --- Mobile/handheld specific ---
// --------------------------------

window.mobilecheck = function() {
    var check = false;
    ( function(a) { if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);
    return check; 
}
