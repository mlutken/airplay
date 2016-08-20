<?php

class PagesCommon
{
    public function pageStart($name)
    {
        $s  = $this->htmlHeader("MusicQuiz: {$name}");
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

//     <script src="/js/jeditable.js" type="text/javascript"></script>
//     <script src="/js/jquery.autocomplete.js" type="text/javascript"></script>
//     <script src="/js/ckeditor/ckeditor.js" type="text/javascript" ></script>   
//     <script src="/js/jtable.2.3.0/jquery.jtable.js" type="text/javascript"></script>
//     <link href="js/CLEditor1_3_0/jquery.cleditor.css" rel="stylesheet" type="text/css" />
//     <script src="js/jquery-migrate-1.1.1.js"></script>
//     <script src="js/CLEditor1_3_0/jquery.cleditor.js" type="text/javascript" ></script>
//     <script type='text/javascript' src='../js/jQuery.tubeplayer.min.js'></script>
    
    public function htmlHeaderStart($pageTitle)
    {
		$timeStamp = time();
        $s =
<<<TEXT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{$pageTitle}</title>
    <link href="/js/jquery-ui-1.10.2.custom/css/ui-lightness/jquery-ui-1.10.2.custom.css" rel="stylesheet" type="text/css" />
    <link href="/css/jquery.mobile-1.3.1.min.css" rel="stylesheet" type="text/css" />
    <link href="/css/public_files.css" rel="stylesheet" type="text/css" />
    <script src="/js/jquery-1.9.1.min.js"></script>
    <script src="/js/jquery-ui-1.10.2.custom/js/jquery-ui-1.10.2.custom.min.js"></script>
    <script src="/js/jquery.sharrre.min.js"></script>
    <script src="/js/jQuery.tubeplayer.js" type="text/javascript"></script>
    <script src="/js/public_files.js?cache=$timeStamp" type="text/javascript"></script>
    <script src="/js/ap_media_player.js" type="text/javascript"></script>
    <script src="/js/quiz.js" type="text/javascript"></script>
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
        $s = $this->pageTopMenu($name);
        return $s;
    }
    
    
    
    
    public function pageTopMenu($nameCurrent)
    {
        $sRawTableMenu =
<<<TEXT
<span >Menu: <a href=/quiz/index.php />Home</a> </span> 
<select name="select" onchange="selectGotoPage(this)" size="1">
<option value="">--- Menu ---</option>
<option value="youtube_simple_test.php">Youtube simple test</option>
<option value="ap_media_player_sandbox.php">AP Media Player Sandbox</option>
<option value="text_quiz_test.php">Text quiz test</option>
<option value="media_quiz_test.php">Media quiz test</option>
<option value="auto_song_quiz_test.php">Auto generate song quiz test</option>
<option value="mobile_app_playground.php">Mobile App/page playground</option>
<option value="index.php">FB Quiz</option>
<option value="jc_jquery_mobile_playground.php">Jacob playground</option>
<option value="song_quiz.php">Airplay Music quiz</option>
</select>

<br />
TEXT;
        $s = $sRawTableMenu;
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