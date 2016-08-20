<?php

class PagesCommon
{
    public function pageStart($name)
    {
        $s  = $this->htmlHeader("Admin: {$name}");
        $s .= $this->pageTopContents($name);
        return $s;
    }

    public function pageEnd()
    {
        $s =  $this->pageBottomContents();
        $s .= $this->htmlEnd();
        return $s;
    }

    public function htmlHeader($pageTitle)
    {
        $s  = $this->htmlHeaderStart($pageTitle);
        $s .= $this->htmlHeaderEnd();
        return $s;
    }

//     <link href="js/CLEditor1_3_0/jquery.cleditor.css" rel="stylesheet" type="text/css" />
//     <script src="js/jquery-migrate-1.1.1.js"></script>
//     <script src="js/CLEditor1_3_0/jquery.cleditor.js" type="text/javascript" ></script>
    
    public function htmlHeaderStart($pageTitle)
    {
        $s =
<<<TEXT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{$pageTitle}</title>
    <link href="js/jquery-ui-1.10.2.custom/css/ui-lightness/jquery-ui-1.10.2.custom.css" rel="stylesheet" type="text/css" />
    <link href="js/jtable.2.3.0/themes/lightcolor/blue/jtable.css" rel="stylesheet" type="text/css" />
    <link href="css/admin_site.css" rel="stylesheet" type="text/css" />
    <script src="js/jquery-1.9.1.min.js"></script>
    <script src="js/ckeditor/ckeditor.js" type="text/javascript" ></script>   
    <script src="js/jquery-ui-1.10.2.custom/js/jquery-ui-1.10.2.custom.min.js"></script>
    <script src="js/jtable.2.3.0/jquery.jtable.js" type="text/javascript"></script>
    <script src="js/jeditable.js" type="text/javascript"></script>
    <script src="js/jquery.autocomplete.js" type="text/javascript"></script>
    <script src="js/site.js" type="text/javascript"></script>
TEXT;
        return $s;
    }
    
    public function htmlHeaderEnd()
    {
        $s =
<<<TEXT
</head>
<body>
TEXT;
        return $s;
    }

    public function pageTopContents($name)
    {
        $s = '';
        if ( 1 == $_SESSION['logged_in'] ) $s = $this->pageTopMenu($name);
        else $s = $this->pageTopLogin();
        return $s;
    }
    
    public function pageTopLogin()
    {
        $s =
<<<TEXT
<form id=loginID onkeyup="onKeyUpLogin(event, $('#userID')[0].value, $('#passwordID')[0].value);" > 
User: <input id=userID type=text size=14 ></input>
Password: <input id=passwordID type=password size=14 ></input>
<input id=loginButtonID type=button value=Login onclick="login( $('#userID')[0].value, $('#passwordID')[0].value );"</input>
</form>
<br />
TEXT;
        return $s;
    }
    
    public function TESTpageTopLogin()
    {
        $s =
<<<TEXT
<form id=loginID action="" onsubmit="console.log('sdsafdasfaf'); login( $('#userID')[0].value, $('#passwordID')[0].value );" > 
User: <input id=userID type=text size=14 ></input>
Password: <input id=passwordID type=password size=14 ></input>
<input id=loginButtonID type="submit" value=Login onclick="login( $('#userID')[0].value, $('#passwordID')[0].value );" </input>
</form>
<br />
<form name="frm1" action="/" onsubmit="console.log('sdfds');">
<input type="text" name="fname">
<input type="submit" value="Submit">
</form>
<br>
TEXT;
        return $s;
    }
    
    
    public function pageTopMenu($nameCurrent)
    {
        $sRawTableMenu =
<<<TEXT
<span >Menu: <a href=/ />Home</a> </span> 
<select name="select" onchange="selectGotoPage(this)" size="1">
<option value="">--- Tables ---</option>
<option value="ArtistTbl.php">Artist table</option>
<option value="ArtistTblCompact.php">Artist table compact</option>
<option value="ArtistSynonymTbl.php">Artist synonym/alias table</option>
<option value="ArtistVariousTbl.php">'Various artists' names table</option>
<option value="CurrencyTbl.php">Currency table</option>
<option value="CurrencyToEuroTbl.php">CurrencyToEuro table</option>
<option value="GenreTbl.php">Genre table</option>
<option value="FavoriteArtistTbl.php">FavoriteArtist table</option>
<option value="FriendsTbl.php">Friends table</option>
<option value="ItemBaseTbl.php">ItemBase table</option>
<option value="ItemBaseCorrectionTbl.php">ItemBaseCorrection table</option>
<option value="ItemBaseReviewTbl.php">ItemBaseReview table</option>
<option value="ItemPriceTbl.php">ItemPrice table</option>
<option value="JobTbl.php">Job table</option>
<option value="MediaFormatTbl.php">MediaFormat table</option>
<option value="QuizTbl.php">Quiz table</option>
<option value="QuizScoreTbl.php">QuizScore table</option>
<option value="QuizThemeTbl.php">QuizTheme table</option>
<option value="SettingsTbl.php">Settings table</option>
<option value="RecordStoreTbl.php">RecordStore table</option>
<option value="UnknownGenresTbl.php">UnknownGenres table</option>
<option value="UnknownMediaFormatsTbl.php">UnknownMediaFormats table</option>
<option value="UnknownMediaTypesTbl.php">UnknownMediaTypes table</option>
<option value="UserTbl.php">User table</option>
</select>

<select name="select" onchange="selectGotoPage(this)" size="1">
<option value="">--- Music DB ---</option>
<option value="ArtistPage.php">Artist Page</option>
<option value="ItemBasePage.php">ItemBase Page</option>
<option value="ItemBaseMergeTestPage.php">ItemBase Merge Test Page</option>
<option value="QuizThemeEditPage.php">Quiz themes edit Page</option>
<option value="JobsPage.php">Miner jobs Page</option>
</select>


<input type=button value=Logout onclick="logout();"</input>
<br />
TEXT;
        $s = $sRawTableMenu;
        return $s;
    }

    /** Incremental/autocomplete search in 'raw' tables */
    public function pageIncrementalSearchBox($mainName)
    {
        if ( 1 != $_SESSION['logged_in'] ) return '';
        $s =
<<<TEXT
<div id=incrementalSearchID > 
Search: <input id=incrementalSearchInputID type=text size=40 onkeyup="incrementalSearch(this,'$mainName', false);" ></input>
&nbsp;<input id=incrementalSearchClearID  type=button value='Clear search' onclick="incrementalSearch(0,'$mainName', true);" ></input>
</div>
<br />
TEXT;
        return $s;
    }

    
    /** Incremental/autocomplete search in MusicDB. */
    public function pageIncrementalSearchBoxMusicDB()
    {
        if ( 1 != $_SESSION['logged_in'] ) return '';
        $s =
<<<TEXT
<div id=incrementalSearchBoxMusicDbID > 
Search: <input id=incrementalSearchBoxMusicDbInputID type=text name="q" size=70 style="width:600px" ></input>
&nbsp;<input id=incrementalSearchBoxMusicDbClearID  type=button value='Clear search' onclick="$('#incrementalSearchBoxMusicDbInputID')[0].value='';" ></input>
</div>
<br />
TEXT;

// &nbsp;<input id=incrementalSearchBoxMusicDbSubmitID  type=button value='Search' ></input>

    return $s;
    }
    
    public function pageBottomContents()
    {
        $s =
<<<TEXT
TEXT;
        return $s;
    }
    
    public function htmlEnd()
    {
        $s =
<<<TEXT
  </body>
</html>
TEXT;
        return $s;
    }
    
}



?>