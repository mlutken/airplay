<?php
require_once ('db_manip/MusicDatabaseFactory.php');
require_once ('utils/string_utils.php');
require_once ("admin_site/utils/admin_site_utils.php");

require_once ('db_manip/ItemBaseAutoMerge.php');


// -------------------------------------
// --- QuizThemeEditPageUI class ---
// -------------------------------------

class QuizThemeEditPageUI
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $pc )
    {
        $item_type              = 1;
        $this->m_iIsPrimaryThreshold = 10;
        $this->g_isMergeCandidateThreshold = 3;

        $this->m_pc             = $pc;
 
        $fac                    = new MusicDatabaseFactory();
        $this->m_dbQuizTheme    = $fac->createDbInterface('QuizThemeData');
        $this->m_dbItemBase     = $fac->createDbInterface('ItemBaseData');
        
        
        $this->m_quizThemeId    = getQuizThemeIdFromURL($this->m_dbQuizTheme);
        
        $this->m_aBaseData      = array();
        $this->m_quizThemeName  = '';
        
        if ( 0 != $this->m_quizThemeId )  {    
            $this->m_aBaseData  = $this->m_dbQuizTheme->getBaseData($this->m_quizThemeId);
        }
    }

    
    
    public function pageContents()
    {
        if ( 1 != $_SESSION['logged_in'] ) return '';
        $s = '';
        $s .= $this->pageScriptGlobalsSection();
        $s .= $this->pageHtml();
        $s .= $this->pageScriptSection();
        return $s;
    }
    

    // ----------------------
    // --- HTML functions ---
    // ----------------------
    
    public function pageHtml()
    {
        $s = '';
        $s .= $this->artistHeadLine();
        $s .= $this->pageMainContentHtml();
        return $s;
    }
    
    public function artistHeadLine()
    {
        $s =
<<<TEXT
<div>
<span class="edit textVeryLarge spaceRight newLine" id=item_base_name style="width:500px;" >{$this->m_aBaseData['quiz_theme_name']}</span>
<span class="newLine">&nbsp;</span>
</div>
TEXT;
        return $s;
    }

	public function pageMainContentHtml()
	{
        $s =
<<<TEXT
<br><br><br>
<div id=quizThemeAreaID ></div>
TEXT;
        return $s;
	}

    // ----------------------------
    // --- Javascript functions ---
    // ----------------------------
 
    public function pageScriptGeneralSection()
    {
        $s =
<<<TEXT

function onQuizThemeLoaded(quizThemeData)
{
    g_quizTheme = quizThemeData;
    quizThemeRenderAll();
}

TEXT;
        return $s;
    }

    public function jsPageRedirectFunction()
    {
        $s =
<<<TEXT
function incrementalSearchRedirect( res )
{
    var quiz_theme_id   = res.data[0];
    var sPageUrl = "/QuizThemeEditPage.php?quiz_theme_id=" + quiz_theme_id;
    console.log("incrementalSearchRedirect: " + sPageUrl);
    document.location.href=sPageUrl;
}
TEXT;
        return $s;
    }

    
    public function pageScriptDocumentReadyFunction()
    {
        $s =
<<<TEXT
$(document).ready(function() {
    console.log('READY: g_quiz_theme_id: ' + g_quiz_theme_id );
    var a = $('#incrementalSearchBoxMusicDbInputID').autocomplete({
        serviceUrl:'ajax_handlers/QuizThemeEditAutoComplete_handler.php',
        minChars:2,
        delimiter: /(,|;)\s*/, // regex or character
        maxHeight:400,
        width:700,
        zIndex: 9999,
        deferRequestBy: 0, //miliseconds
        params: { country:'Yes' }, //aditional parameters
        noCache: true, //default is false, set to true to disable caching
        // callback function:
        onSelect: incrementalSearchRedirect
    });    
    if (g_quiz_theme_id > 0) {
        var postObj = { quiz_theme_id: g_quiz_theme_id };

        console.log("Load quiz_theme_id: " + g_quiz_theme_id);
        console.log(postObj);
        quizThemeLoadFromServer(postObj, onQuizThemeLoaded);
        
    }
    
});
TEXT;
        return $s;
    }

 
    public function pageScriptGlobalsSection()
    {
        $s =
<<<TEXT
<script>
    var g_quiz_theme_id = {$this->m_quizThemeId};
    var g_quizTheme = null;
</script>
TEXT;
        return $s;
    }
    
    
    
    public function pageScriptSection()
    {
        $s = "<script>\n";
        $s .= $this->pageScriptGeneralSection();
        $s .= $this->jsPageRedirectFunction();
        $s .= $this->pageScriptDocumentReadyFunction();
        $s .= "</script>\n";
        return $s;
    }
    
    private     $m_pc;
    private     $m_dbQuizTheme;
    private     $m_ibam;
    private     $m_aBaseData;
    private     $m_iIsPrimaryThreshold;
    private     $g_isMergeCandidateThreshold;

    private     $m_aPrimaries = array();
    private     $m_aCandidates = array();
    private     $m_aRest = array();
    private     $m_aNotAssigned = array();
    
}



?>