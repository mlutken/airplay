// $Id: 
// See: drupal/misc/autocomplete.js
// Look for offset_top, AP_HACK
// See also: http://stackoverflow.com/questions/1018981/how-to-dynamically-reconfigure-drupals-jquery-based-autocomplete-at-runtime   (we don't use this method anymore)


// --------------------------------------------------
// --- Pure Cookie Functions (currently not used) ---
// --------------------------------------------------

function getCookie(name)
{
    var cookies = document.cookie;
    var start = cookies.indexOf(name + '=');
    if (start == -1) return null;
    var len = start + name.length + 1;
    var end = cookies.indexOf(';',len);
    if (end == -1) end = cookies.length;
    return unescape(cookies.substring(len,end));
}

function setCookie(name, value, expires, path, domain, secure)
{
    if(path == null) {path = "/"};
    value = escape(value);
    expires = (expires) ? ';expires=' + expires.toGMTString() :'';
    path    = (path)    ? ';path='    + path                  :'';
    domain  = (domain)  ? ';domain='  + domain                :'';
    secure  = (secure)  ? ';secure'                           :'';

    document.cookie =   name + '=' + value + expires + path + domain + secure;
}

function deleteCookie(name, path, domain)
{
    var expires = ';expires=Thu, 01-Jan-70 00:00:01 GMT';
    (path)    ? ';path='    + path                  : '';
    (domain)  ? ';domain='  + domain                : '';

    if (getCookie(name))
        document.cookie = name + '=' + expires + path + domain;
}

// ---------------------------------
// --- XX Functions
// ---------------------------------


function hideElement( elem ){
	if ( elem == undefined ) return;
	elem.style.visibility = "hidden";
	elem.style.display = "none";
}	

function showElement( elem ){
	if ( elem == undefined ) return;
	elem.style.visibility = "visible";
	elem.style.display = "";
}	


function getActiveSearchInputName()
{
    var activeName = "";
    var albumObj    = jQuery('#edit-apms-lookup-large-album').get(0);
    var songObj     = jQuery('#edit-apms-lookup-large-song').get(0);

    if      ( songObj.style.visibility     == "visible" )   activeName = "song";
    else if ( albumObj.style.visibility    == "visible" )   activeName = "album";
    else                                                    activeName = "artist";
    
    return activeName;
}

function onload() {
    var search_for = "";
    var artist = jQuery('#edit-apms-search-for-large-artist').get(0);
    if ( artist == undefined )  return; // If page does not contain the search field

    var album = jQuery('#edit-apms-search-for-large-album').get(0);
    var song = jQuery('#edit-apms-search-for-large-song').get(0);
    
    if      ( song.checked    == true )   search_for = "song";
    else if ( album.checked   == true )   search_for = "album";
    else                                  search_for = "artist";

    //console.log("JS:onload search_for: " + search_for );
	var inputField = jQuery('#edit-apms-lookup-large-' + search_for).get(0);
	hideElement( jQuery('#edit-apms-lookup-large-artist').get(0));
	hideElement( jQuery('#edit-apms-lookup-large-album').get(0));
	hideElement( jQuery('#edit-apms-lookup-large-song').get(0));
	showElement( inputField );
    inputField.focus();

}

window.onload = onload; 


function ap_music_search_autocomplete_set_search_for( obj )
{
    var search_for_oldName = getActiveSearchInputName();
    ////console.log("ap_music_search_autocomplete_set_search_for : " + obj.value + "  search_for_oldName: "  + search_for_oldName  );
	var inputField = jQuery('#edit-apms-lookup-large-' + obj.value).get(0);
    var inputFieldOld = jQuery('#edit-apms-lookup-large-' + getActiveSearchInputName()).get(0);
    
    if ( inputField != inputFieldOld ) {
        inputField.value = inputFieldOld.value;
        hideElement(inputFieldOld);
    }
    
	showElement( inputField );
	inputField.focus();
    
}


// // svn_load_dirs.pl -t 6.18 https://angel1.projectlocker.com/nitram/airplay/svn/vendor/drupal current  /home/ml/code/airplay/drupal
