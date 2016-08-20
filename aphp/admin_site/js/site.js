/** Get number of element in an object. */
function objectSize(obj) 
{
     var size = 0, key;
     for (key in obj) {
         if (obj.hasOwnProperty(key)) size++;
     }
     return size;
}


function login(userName, pass)
{
    $.post("/ajax_handlers/login_handler.php", { action: "login", user: userName, password: pass } 
    ).done ( function(data) {
        location.reload(); 
    }
    ).error( function(data) {
        alert("Login: Error trying to access server");
    }
    ) 
    ;    
}

function onKeyUpLogin(e, userName, pass) 
{
    if(e.which == 13 || e.keyCode == 13){
		login(userName, pass);
    }        
}

function logout()
{
    $.post("/ajax_handlers/login_handler.php", { action: "logout" } 
    ).done ( function(data) {
        location.reload(); 
    }
    ).error( function(data) {
        alert("Logout: Error trying to access server");
    }
    ) 
    ;    
}



function selectGotoPage(select) 
{ 
    var index=select.selectedIndex
    if (select.options[index].value != "0") {
        location=select.options[index].value;
    }
}

/** Incremental search in 'raw' tables. Searchbox is provided from 
 *  PagesCommon::pageIncrementalSearchBox() */
function incrementalSearch(inputObj, tblMainName, bDoClearSearch )
{
    try { window.clearTimeout(incrementalSearchInput); }
    catch (err) { }
    incrementalSearchInput = setTimeout(function() { incrementalSearchDelayed(inputObj, tblMainName, bDoClearSearch) }, 500);
}

/** Called from incrementalSearch delayed */
function incrementalSearchDelayed(inputObj, tblMainName, bDoClearSearch )
{
    if ( inputObj == 0 ) inputObj = document.getElementById('incrementalSearchInputID');
    if ( bDoClearSearch ) inputObj.value = '';
    var searchStr = inputObj.value;
// //     var sHandlerPath = "/ajax_handlers/" + tblMainName + "_handler.php";
    var tableObj = document.getElementById( tblMainName + "TableContainer" );
    var sTableContainer = '#' + tblMainName + 'TableContainer';
    var mode = 'listAll';
    if ( searchStr != "" ) {
        mode = "incrSearch";
    }
    $(sTableContainer).jtable('load', { viewMode: mode, searchString: searchStr });
}

/** Incremental search in music DB. Searchbox is provided from 
 *  PagesCommon::pageIncrementalSearchBoxMusicDB() */
function incrementalSearchMusicRedirect( res )
{
    var artist_id       = res.data[0];
    var item_base_id    = res.data[1];
    var sPageUrl = "/ArtistPage.php?artist_id=" + artist_id;
    if ( item_base_id != 0 ) sPageUrl = "/ItemBasePage.php?item_base_id=" + item_base_id;
    document.location.href=sPageUrl;
}

function incrementalSearchBoxMusicDb_AutoCompleteCreate( autoCompleteRedirectFun )
{
    if (autoCompleteRedirectFun === undefined) {
        autoCompleteRedirectFun = function( res ) { return incrementalSearchMusicRedirect( res );};
    }
    
    
    var a = $('#incrementalSearchBoxMusicDbInputID').autocomplete({
        serviceUrl:'ajax_handlers/MusicDatabaseAutoComplete_handler.php',
        minChars:2,
        delimiter: /(,|;)\s*/, // regex or character
        maxHeight:400,
        width:700,
        zIndex: 9999,
        deferRequestBy: 0, //miliseconds
        params: { country:'Yes' }, //aditional parameters
        noCache: true, //default is false, set to true to disable caching
        // callback function:
        onSelect: autoCompleteRedirectFun
    });    
}


// ----------------------------------------
// --- Artist text javascript functions ---
// ----------------------------------------

function ckArtistTextSave( sArtistID )
{
    var sLanguageCode   = $('#artist_text_languageID')[0].value;
    var ckEditor        = CKEDITOR.instances['artist_text'];
    var sHtml           = ckEditor.getData();
    var iReliability    = $('#artist_text_reliability')[0].value;
    
    $.post('ajax_handlers/ArtistPage_handler.php', { action: "artistTextSave", artist_id: sArtistID, language_code: sLanguageCode, value: sHtml, artist_text_reliability: iReliability } 
    ).done ( function(saveStatus) {
        ckEditor.resetDirty();
        ckArtistTextDirtyChanged();
        if ( saveStatus != 'OK' ) alert("Error: Save artist article text failed. Reason unknown :-(.");
// //         console.log('Save status: ' + saveStatus );
    }
    ).error( function(data) {
        alert("ArtistTextSave: Error trying to access server");
    }
    ) 
    ;    
    
}


function ckArtistTextReload( sArtistID )
{
    var sLanguageCode = $('#artist_text_languageID')[0].value;
    var ckEditor = CKEDITOR.instances['artist_text'];
    
    $.post('ajax_handlers/ArtistPage_handler.php', { action: "artistTextReload", artist_id: sArtistID, language_code: sLanguageCode } 
    ).done ( function(sData) {
        var n           = sData.indexOf("\n"); 
        var sHtml       = sData.substr(n+1);
        var iReliability= sData.substring(0, n);
        $('#artist_text_reliability')[0].value = iReliability;
        ckEditor.setData(sHtml);
        ckEditor.resetDirty();
        ckArtistTextDirtyChanged();
    }
    ).error( function(data) {
        alert("ArtistTextReload: Error trying to access server");
    }
    ) 
    ;    
    
}

function ckArtistTextDirtyChanged()
{
    if ( CKEDITOR.instances['artist_text'].checkDirty() ) {
        $('#artist_text_languageID').attr('disabled', 'disabled');
    }
    else {
        $('#artist_text_languageID').removeAttr('disabled');
    }
}

function artistTextLanguageChanged( sArtistID )
{
    ckArtistTextReload(sArtistID);
}




// ------------------------------------------
// --- ItemBase text javascript functions ---
// ------------------------------------------

function ckItemBaseTextSave( sItemBaseID )
{
    var sLanguageCode   = $('#item_base_text_languageID')[0].value;
    var ckEditor        = CKEDITOR.instances['item_base_text'];
    var sHtml           = ckEditor.getData();
    var iReliability    = $('#item_base_text_reliability')[0].value;
    
    $.post('ajax_handlers/ItemBasePage_handler.php', { action: "itemBaseTextSave", item_base_id: sItemBaseID, language_code: sLanguageCode, value: sHtml, item_base_text_reliability: iReliability } 
    ).done ( function(saveStatus) {
        ckEditor.resetDirty();
        ckItemBaseTextDirtyChanged();
        if ( saveStatus != 'OK' ) alert("Error: Save itemBase article text failed. Reason unknown :-(.");
// //         console.log('Save status: ' + saveStatus );
    }
    ).error( function(data) {
        alert("ItemBaseTextSave: Error trying to access server");
    }
    ) 
    ;    
    
}


function ckItemBaseTextReload( sItemBaseID )
{
    var sLanguageCode = $('#item_base_text_languageID')[0].value;
    var ckEditor = CKEDITOR.instances['item_base_text'];
    
    $.post('ajax_handlers/ItemBasePage_handler.php', { action: "itemBaseTextReload", item_base_id: sItemBaseID, language_code: sLanguageCode } 
    ).done ( function(sData) {
        var n           = sData.indexOf("\n"); 
        var sHtml       = sData.substr(n+1);
        var iReliability= sData.substring(0, n);
        $('#item_base_text_reliability')[0].value = iReliability;
        ckEditor.setData(sHtml);
        ckEditor.resetDirty();
        ckItemBaseTextDirtyChanged();
    }
    ).error( function(data) {
        alert("ItemBaseTextReload: Error trying to access server");
    }
    ) 
    ;    
    
}

function ckItemBaseTextDirtyChanged()
{
    if ( CKEDITOR.instances['item_base_text'].checkDirty() ) {
        $('#item_base_text_languageID').attr('disabled', 'disabled');
    }
    else {
        $('#item_base_text_languageID').removeAttr('disabled');
    }
}

function itemBaseTextLanguageChanged( sItemBaseID )
{
    ckItemBaseTextReload(sItemBaseID);
}



function getTableSelectedValues( tblName, coloumName )
{
    var selectedRows = $('#' + tblName).jtable('selectedRows');
    var aValues      = new Array();
    var i = 0;
    selectedRows.each(function () {
        var record = $(this).data('record');
        aValues[i] = record[coloumName];
        i++;
    });
    return aValues;
}


function artistMergeFromTable1( intoArtistId, artistMergeFromTblName, bDoConfirm )
{
    var artistMergeFromIDs      = getTableSelectedValues( artistMergeFromTblName, 'artist_id' );
    var artistMergeFromNames    = getTableSelectedValues( artistMergeFromTblName, 'artist_name' );
    
    if ( artistMergeFromIDs.length == 0 ) return;
    
    artistMerge( intoArtistId, artistMergeFromIDs[0], '', artistMergeFromNames[0], bDoConfirm );
}


function artistMerge( intoArtistId, fromArtistId, intoArtistName, fromArtistName, bDoConfirm )
{
    if ( bDoConfirm ) {
        var r = confirm("Confirm merging!\nYou are about to merge the selected artist '" + fromArtistName + "' into this artist " + intoArtistName + ".\nOperation can not be undone!\nDo you wish to continue?");
        if ( r == false )   return;
    }    
    
        
    $.post("/ajax_handlers/MusicDbOperations_handler.php", { action: "artistMerge", into_artist_id: intoArtistId, from_artist_id: fromArtistId } 
    ).done ( function(retVal) {
        var sMergeStatusMsg = "Merge was succesful. You might want to reload the current page (press 'F5') to see the results.\nThe merge from artist name has been created as an alias to the current.\nIn the rare cases where you don't want thi, please delete it again from the 'Aliases' tab.";
        if ( retVal != "OK" ) sMergeStatusMsg = retVal;
        r = confirm(sMergeStatusMsg);
        if (r == true) location.reload();
    }
    ).error( function(data) {
        alert("Artist merge: Error trying to access server");
    }
    ) 
    ;    
    
}


function itemBaseMergeFromTable1( intoItemBaseId, itemBaseMergeFromTblName, bDoConfirm )
{
    var itemBaseMergeFromIDs      = getTableSelectedValues( itemBaseMergeFromTblName, 'item_base_id' );
    var itemBaseMergeFromNames    = getTableSelectedValues( itemBaseMergeFromTblName, 'item_base_name' );
    
    if ( itemBaseMergeFromIDs.length == 0 ) return;
    
    itemBaseMerge( intoItemBaseId, itemBaseMergeFromIDs[0], '', itemBaseMergeFromNames[0], bDoConfirm );
}

function itemBaseMergeFromTable2( itemBaseMergeTblName, bDoConfirm )
{
    var itemBaseMergeIDs      = getTableSelectedValues( itemBaseMergeTblName, 'item_base_id' );
    var itemBaseMergeNames    = getTableSelectedValues( itemBaseMergeTblName, 'item_base_name' );
    
    if ( itemBaseMergeFromIDs.length == 0 ) return;
    
    itemBaseMerge( itemBaseMergeIDs[0], itemBaseMergeIDs[1], itemBaseMergeNames[0], itemBaseMergeNames[1], bDoConfirm );
}


function itemBaseMerge( intoItemBaseId, fromItemBaseId, intoItemBaseName, fromItemBaseName, bDoConfirm )
{
    if ( bDoConfirm ) {
        var r = confirm("Confirm merging!\nYou are about to merge the selected itemBase '" + fromItemBaseName + "' into this itemBase " + intoItemBaseName + " .\nOperation can not be undone!\nDo you wish to continue?");
        if ( r == false )   return;
    }    
    
        
    $.post("/ajax_handlers/MusicDbOperations_handler.php", { action: "itemBaseMerge", into_item_base_id: intoItemBaseId, from_item_base_id: fromItemBaseId } 
    ).done ( function(retVal) {
        console.log("retVal: " + retVal );
        var sMergeStatusMsg = "Merge was succesful. You might want to reload the current page (press 'F5') to see the results.\nThe merge from itemBase name has been created as an correction name to the merged into name.\nIn the rare cases where you don't want this, please delete it again from the 'Corrections' tab.";
        if ( retVal != "OK" ) sMergeStatusMsg = retVal;
        r = confirm(sMergeStatusMsg);
        if (r == true) location.reload();
    }
    ).error( function(data) {
        alert("ItemBase merge: Error trying to access server");
    }
    ) 
    ;    
    
}

// ----------------------------------
// --- QuizTheme helper functions ---
// ----------------------------------

/** Load quiz theme from server. 
\param postDataObj Has either a quiz_theme_name or a quiz_theme_id like this { quiz_theme_name: "MyTheme" }
\param onLoadedFn: Function to call, when quiz is loaded. Typically the one to actually run the quiz, 
                like for example quizDoRun(). */
function quizThemeLoadFromServer(postDataObj, onLoadedFn )
{
    $.ajax({
        type: 'POST',
        async: true,
        dataType: "json",
        url: "/ajax_handlers/QuizThemeLoad_handler.php",
        data: postDataObj,
        success: function(data) {
            onLoadedFn(data);
        },
        error: function(data) {
            console.log("Error loading quiz theme: " + postDataObj.quiz_theme_name );
            console.log( data );
        }
    });
}


function quizThemeRenderAll()
{
    console.log("quizThemeRenderAll");
    console.log( g_quizTheme );
    console.log( g_quizTheme.songs );
    $('#quizThemeAreaID').append("");
    var iNumSongs = objectSize(g_quizTheme.songs);
    for ( var i=0; i < iNumSongs; i++)
    {
        var songObj = g_quizTheme.songs[i];
        console.log("song[" + i + "]: " + songObj.item_base_name );
        quizThemeRenderSong(songObj);
    }
    
}


/** Render one song object to the end of the theme edit area. 
\param songObj: The song object.
*/
function quizThemeRenderSong(songObj)
{
    var domID = 'quizThemeAreaID';
    
    // Comment and ansver
    var sHtml = "<div>" + songObj.artist_name + " - " + songObj.item_base_name + "</div><br>";
    $('#' + domID).append(sHtml);

    
    
}

