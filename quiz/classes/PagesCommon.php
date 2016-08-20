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

    
    public function htmlHeaderStart($aPartnerSettings)
    {
        $jsPartnerSettings = partnerSettingsToJavascript($aPartnerSettings);
    
		$timeStamp = time(); // TODO: Temporary I hope. To force FB caching to reload js script
		
		// JAC Changes 15-01-2014 
		// MOVED <link href="/js/jquery-ui-1.10.2.custom/css/ui-lightness/jquery-ui-1.10.2.custom.css" rel="stylesheet" type="text/css" /> => <link href="/css/ap.drop.down.css" rel="stylesheet" type="text/css" />
        $s =
<<<TEXT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{$aPartnerSettings['page_title']}</title>

    <link href="/css/{$aPartnerSettings['css_path']}/ap.drop.down.css" rel="stylesheet" type="text/css" />
    <link href="/css/{$aPartnerSettings['css_path']}/ap.jquery.mobile-1.4.0.min.css" rel="stylesheet" type="text/css" />
    <link href="/css/{$aPartnerSettings['css_path']}/jquery.mobile.structure-1.4.0.min.css" rel="stylesheet" type="text/css" />
    <link href="/css/{$aPartnerSettings['css_path']}/jquery.mobile.icons.min.css" rel="stylesheet" type="text/css" />
    <link href="/css/{$aPartnerSettings['css_path']}/public_files.css" rel="stylesheet" type="text/css" />

    <script src="/js/jquery-1.10.2.min.js"></script>
    <script src="/js/jquery-ui-1.10.2.custom/js/jquery-ui-1.10.2.custom.min.js"></script>
    <script src="/js/jquery.mobile-1.4.0.min.js"></script>
    
    <script src="/js/jQuery.tubeplayer.js" type="text/javascript"></script>
    <script src="/js/public_files.js?cache=$timeStamp" type="text/javascript"></script>
    <script src="/js/user.js?cache=$timeStamp" type="text/javascript"></script>
    <script src="/js/ap_media_player.js" type="text/javascript"></script>
    <script src="/js/quiz.js" type="text/javascript"></script>
    <script>
        var g_apPartnerSettings = {$jsPartnerSettings};
    </script>
TEXT;
        return $s;
    }

//////         var g_apSettings = { cssPath: "{$aPartnerSettings['css_path']}", imgPath: {$aPartnerSettings['img_path']} };

    
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