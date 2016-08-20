// --------------------
// --- Social media ---
// --------------------

function addSocialShare(domID, sUrl, sTitle, sSummary )
{
    jQuery('#'+domID).empty();
    
    var logo80Path = g_apPartnerSettings.img_path + "/logos_80x80/";
    var sUrlPartnerLogo = urlFrameBasePath() + logo80Path + "partner_logo_80x80.png";
// 
    var sUrlWithHttpCoded   = encodeURIComponent(sUrl);

    var sTitleCoded     = encodeURIComponent(sTitle);
    var sSummaryCoded   = encodeURIComponent(sSummary);
    var sUrlImageCoded  = encodeURIComponent(sUrlPartnerLogo);
    
    
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
    sHtml += "<img src='" + logo80Path + "facebook_logo_80x80.png' />";
    sHtml += "<h3>Facebook</h3>";
    sHtml += "<p>" + sUrl + "</p>";
    sHtml += "</a></li>";

    // --- Twitter ---
    var sTwitterUrl = "https://twitter.com/intent/tweet?text=TARGET_SUMMARY&url=TARGET_URL";
    sTwitterUrl = sTwitterUrl.replace('TARGET_URL', sUrlWithHttpCoded );
    sTwitterUrl = sTwitterUrl.replace('TARGET_SUMMARY', sSummaryCoded );
    sHtml += "<li><a href='" + sTwitterUrl + "' target='_blank' >";
    sHtml += "<img src='" + logo80Path + "twitter_logo_80x80.png' />";
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
    sHtml += "<img src='" + logo80Path + "linkedin_logo_80x80.png' />";
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
        sHtml += "<img src='" + logo80Path + "sms_logo_80x80.png' />";
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
    sHtml += "<img src='" + logo80Path + "mail_logo_80x80.png' />";
    sHtml += "<h3>Mail</h3>";
    sHtml += "<p>" + sUrl + "</p>";
    sHtml += "</a></li>";

    // --- Finalize ---
    sHtml += "</ul>";
    $('#' + domID).append(sHtml);
    $('#' + domID).trigger("create");
}

/** Load friends from current social media. */
function smLoadFriends(postActionsObj)
{
    if ( FB != null && FB != undefined ) {
    
        FB.api('/me/friends', {fields: 'name,id,username,gender,location,bio,age_range'}, function (response) {
            console.log(response);
            g_apUser.friends = fbUserArrayToAirplayUserArray(response.data);
            postActionsObj.on_friends_loaded();
        } );
        
    }
//    g_socialMediaUser
}


// --------------------------
// --- Facebook functions ---
// --------------------------
function fbTestAPI() {
    console.log('Welcome!  Fetching your information.... ');
    FB.api('/me', function(response) {
        console.log('Good to see you, ' + response.name + '.');
    });
    FB.api('/me/permissions', function (response) {
        console.log(response);
    } );
    FB.api('/me/friends', {fields: 'name,id,username,gender,location,bio,age_range'}, function (response) {
        console.log(response);
        //fbUserArrayToAirplayUserArray(response.data);
    } );

    FB.api('/me/music', function(response) { 
        console.log('Music:');
        console.log(response);

    });
}

function fbInFaceBookIFrame()
{
    if(window.name.indexOf('iframe_canvas_fb') != -1){
        return true;
    }
    return false;
}

function fbProfileImageFromUserName(userName)
{
    return "http://graph.facebook.com/" + userName + "/picture"
}


function fbUserArrayToAirplayUserArray(aFacebookUsers)
{
    var aAirplayUsers = [];
    for (i = 0; i < aFacebookUsers.length; i++) {
        var apUserObj = new Object();
        if ( aFacebookUsers[i].username != undefined ) {
            apUserObj.user_name = aFacebookUsers[i].name;
            apUserObj.fb_id = aFacebookUsers[i].id;
            apUserObj.fb_user_name = aFacebookUsers[i].username;
            apUserObj.profile_image_url = fbProfileImageFromUserName(aFacebookUsers[i].username);
            aAirplayUsers.push(apUserObj);
        }
    }
    return aAirplayUsers;
}


function fbLogin(postActionsObj)
{
    FB.login(function(response) {
        if (response.authResponse) {
            console.log('Welcome!  Fetching your information.... ');
            FB.api('/me', function(response) {
                var profile_image_url_s = fbProfileImageFromUserName(response.username);
                console.log('XX Good to see you, ' + response.name + '.');
                console.log('XX email: ' + response.email );
                console.log('XX FB ID: ' + response.id );
                console.log('XX FB profile image: ' + profile_image_url_s );
                console.log(response);
                apLogin(    { 
                                  fb_id : response.id
                                , email : response.email
                                , user_name : response.name
                                , profile_image_url: profile_image_url_s 
                            }
                            , postActionsObj 
                       );
            });
        } 
        else {
            console.log('User cancelled login or did not fully authorize.');
            userLoginButtonsUpdate();
        }
    }, {scope: 'email,user_likes,friends_likes'});
}

/** Render Facebook friends invite list. */
function fbRenderFriendsInvitePage( domID, listDataObj ) 
{ 
    var domIdHash = '#' + domID;
    jQuery(domIdHash).empty();

    var sHtml = "";
    sHtml += "<ul data-role='listview' >";
    for ( var i=0; i < listDataObj.length; i++)
    {
        var fbIdHash = '#' + listDataObj[i].fb_id;
        var image_s = listDataObj[i].profile_image_url;
        var userName_s = listDataObj[i].user_name;
        var s = "<li" + " onclick=\"checkBoxToggle('" + listDataObj[i].fb_id + "');\" >";
        s += "<img src='" + image_s + "' />";
        s += "<h3>" + userName_s + "</h3>";
        s += "<input type='checkbox' name='" + listDataObj[i].user_name + "' id='" + listDataObj[i].fb_id + "' >";
        s += "</li>";
        sHtml += s;
    }
    sHtml += "</ul>";

    $(domIdHash).append(sHtml);
    $(domIdHash).trigger("create");
    
    // Prevent click event from propergating when clicking on actual checkbox.
    // This prevent the checkbox from being oggles twice!
    $( domIdHash + " input[type=checkbox]").click(function(event) {
        event.stopPropagation();
    });
}


/** Render highscore login page. */
function fbRenderFriendsInviteLogin( domID ) 
{ 
    quizClearPage(domID);
    var sHtml = "<h1>Load friends</h1>";
    sHtml += "<p>" 
    sHtml += "Please press button to login/load your friends list."; 
    sHtml += "<br>";
    sHtml += "Thank you!" 
    sHtml += "</p>";
    sHtml += "<button id='fbInviteMainQuizPageLogInOutBtnID' class='loginOnly' href='' data-theme='a' >Login/load friends</button>";
    $('#' + domID).append(sHtml);
    $('#' + domID).trigger("create");
    $("#fbInviteMainQuizPageLogInOutBtnID").click(function (e) {
        var postActionsObj = userGetDefaultLoginPostActionsObj();
        postActionsObj.on_friends_loaded = function () { jqmChangePageSame('fbInviteMainQuizPageID'); }
        userLogin(postActionsObj);
    });
}

/** Send invitation to main quiz.
\note: Not used currently. We might need it later */
function fbSendInviteQuizMainMobile(aUserIDs) 
{
    var sUserIDs = aUserIDs.join(",");
    FB.ui({   
            method: 'apprequests',
            to: sUserIDs,
            title: 'Airplay Music Quiz',
            message: 'Hej! Er lige ved at teste FB invitationer. Håber du siger ja og lige giver vores nye quiz en lille test. Beklager hvis du får flere invitationer - jeg tester som sagt og er lidt grøn i FB App programmering.',
        }, 
        function(response) {
            console.log(response);
        }
    );
}

function fbSendInviteQuizMain()
{
    FB.ui({
            method: 'apprequests',
            title: 'Airplay Music Quiz',
            message: "Hi.\nI really think you should give this new quiz a try. It is for music what 'Wordfeud' is for words. Just select your favorite artist(s), hit 'run', and try guessing the titles of the songs played. Regards",
    });
}

function fbSendInviteQuizMainMessage(sMessage)
{
    FB.ui({
            method: 'apprequests',
            title: 'Airplay Music Quiz',
            message: sMessage,
    });
}


function fbSendChallengeInviteQuiz()
{
    var postDataObj = {
        app_id: '177367848975485',
        method: 'apprequests',
        title: 'Airplay Music Quiz',
//        data: g_quiz.quiz_name
        data: "Kim_Larsen--67",
        redirect_uri: "https://apps.facebook.com/airplaymusic/?quiz_cmd=load&quiz_name_url=Hej_matematik--1",
        message: "Hi.\nI challenge you to get a better score than my in this music quiz :-).",
    };
    
    console.log(postDataObj);
    
    FB.ui(postDataObj,
        function(response) {
            console.log("fbSendChallengeInviteQuiz, response:");
            console.log(response);
        }
    );
}

// ------------------------------
// --- Login/logout functions ---
// ------------------------------

function jsGlobalsSetLoggedOut()
{
    g_socialMediaUser = { fb_id : null };
    g_apUser = { data: null, friends: null, music: null, user_data_updated: false };
}

function userGetDefaultLoginPostActionsObj()
{
    return { 
          on_user_logged_in: function() { console.log ("DBG: on_user_logged_in"); }
        , on_user_logged_out: function() {console.log ("DBG: on_user_logged_out");}
        , on_friends_loaded : function() {console.log ("DBG: on_friends_loaded");}  
        , on_music_loaded : function() {console.log ("DBG: on_music_loaded");}  
        , error_function : function() {console.log ("DBG: error_function");}  
    };   
}

function userIsLoggedIn()
{
    return !( (g_apUser.data == null) || jQuery.isEmptyObject (g_apUser.data));
}

function userFriendsLoaded()
{
    if ( userIsLoggedIn() ) return g_apUser.friends != null;
    return false;
}

function userMusicLoaded()
{
    if ( userIsLoggedIn() ) return g_apUser.music != null;
    return false;
}

function userLoginButtonsUpdate()
{
    if ( userIsLoggedIn() ) $(".logInOut").text("Logout");
    else                    $(".logInOut").text("Login");
}


function userLogInOut(postActionsObj)
{
    postActionsObj = postActionsObj || userGetDefaultLoginPostActionsObj();
    if ( userIsLoggedIn() ) {
        userLogout(postActionsObj);
    }
    else {
        userLogin(postActionsObj);
    }
}


function userLogin(postActionsObj)
{
    postActionsObj = postActionsObj || userGetDefaultLoginPostActionsObj();
    fbLogin(postActionsObj);
}

function userLogout(postActionsObj)
{
    postActionsObj = postActionsObj || userGetDefaultLoginPostActionsObj();
    apLogout(postActionsObj);
}

// http://graph.facebook.com/sarfraz.anees/picture



function apLogin( loginDataObj, postActionsObj )
{
    $.ajax({
        type: 'POST',
        async: true,
        dataType: "json",
        url: "/ajax_handlers/quiz_login_handler.php",
        data: loginDataObj,
        success: function(data) {
            g_apUser.data = data;
            g_apUser.user_data_updated = false;
            userLoginButtonsUpdate();
            smLoadFriends(postActionsObj);
            postActionsObj.on_user_logged_in();
        },
        error: function(data) {
            g_apUser.data = null;
            userLoginButtonsUpdate();
            postActionsObj.error_function();
        }
    });
}

function apLogout(postActionsObj)
{
    $.ajax({
        type: 'POST',
        async: true,
        dataType: "json",
        url: "/ajax_handlers/quiz_logout_handler.php",
        data: {},
        success: function(data) {
            g_apUser.data = null;
            userLoginButtonsUpdate();
            postActionsObj.on_user_logged_out();
        },
        error: function(data) {
            userLoginButtonsUpdate();
            postActionsObj.error_function();
        }
    });
}



/** Check if we currently have a user logged in*/
function apCheckUserLoggedIn()
{
    $.ajax({
        type: 'POST',
        async: true,
        dataType: "json",
        url: "/ajax_handlers/quiz_check_logged_in_handler.php",
        data: {},
        success: function(data) {
            g_apUser.data = data;
            userLoginButtonsUpdate();
        },
        error: function(data) {
            g_apUser.data = null;
            userLoginButtonsUpdate();
        }
    });
}

