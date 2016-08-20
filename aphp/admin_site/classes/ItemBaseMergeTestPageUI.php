<?php
require_once ('db_manip/MusicDatabaseFactory.php');
require_once ('utils/string_utils.php');
require_once ('db_manip/ItemBaseAutoMerge.php');


// -------------------------------------
// --- ItemBaseMergeTestPageUI class ---
// -------------------------------------

class ItemBaseMergeTestPageUI
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
        $this->m_dbArtist       = $fac->createDbInterface('ArtistData');
        $this->m_dbItemBase     = $fac->createDbInterface('ItemBaseData');
        
        
        $this->m_artistId       = getArtistIdFromURL($this->m_dbArtist);
        
        $this->m_aBaseData      = array();
        $this->m_artistName     = '';
        
        if ( 0 != $this->m_artistId )  {    
            $this->m_aBaseData  = $this->m_dbArtist->getBaseData($this->m_artistId);
            $this->m_aItemBases = $this->m_dbArtist->getAutoMergeData($this->m_artistId, $item_type);
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
        $s .= $this->pageMainContentHtml($this->m_aItemBases);
        return $s;
    }
    
    public function artistHeadLine()
    {
        $s =
<<<TEXT
<div>
<span class="edit textVeryLarge spaceRight newLine" id=item_base_name style="width:500px;" >{$this->m_aBaseData['artist_name']}</span>
<span class="newLine">&nbsp;</span>
</div>
TEXT;
        return $s;
    }

	public function pageMainContentHtml($aItemBases)
	{
		$sHtml = "";
		$ibam = new ItemBaseAutoMerge(10, 3);

		///$ibam->assignPrimaries($aItemBases);

		$aPrimaries = array();
		$aCandidates = array();
		$aRest = array();
		$ibam->getPrimariesAndCandidates($aItemBases, $aPrimaries, $aCandidates, $aRest );
		$aNotAssigned = $ibam->assignMergeCandidates($aPrimaries, $aCandidates, "ibCompare1" );

		usort($aNotAssigned, 'sortGreaterItemBaseName');
		
		$sHtml .= ibamArrayToHtmlTable($aPrimaries, "Primaries" );
		$sHtml .= ibamArrayToHtmlTable($aNotAssigned, "Not Assigned" );
		$sHtml .= ibamArrayToHtmlTable($aRest, "Not Attempted" );
		
		return $sHtml;
	}

    
    // ----------------------------
    // --- Javascript functions ---
    // ----------------------------
 

    public function jsPageRedirectFunction()
    {
        $s =
<<<TEXT
function incrementalSearchRedirect( res )
{
    var artist_id       = res.data[0];
    var item_base_id    = res.data[1];
    var sPageUrl = "/ItemBaseMergeTestPage.php?artist_id=" + artist_id;
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
    incrementalSearchBoxMusicDb_AutoCompleteCreate(function( res ) { return incrementalSearchRedirect( res );});
});
TEXT;
        return $s;
    }

 
    public function pageScriptGlobalsSection()
    {
        $s =
<<<TEXT
<script>
    var g_artist_id              = {$this->m_artistId};
</script>
TEXT;
        return $s;
    }
    
    
    
    public function pageScriptSection()
    {
        $s = "<script>\n";
        $s .= $this->jsPageRedirectFunction();
        $s .= $this->pageScriptDocumentReadyFunction();
        $s .= "</script>\n";
        return $s;
    }
    
    private     $m_pc;
    private     $m_dbArtist;
    private     $m_ibam;
    private     $m_aBaseData;
    private     $m_aItemBases;
    private     $m_iIsPrimaryThreshold;
    private     $g_isMergeCandidateThreshold;

    private     $m_aPrimaries = array();
    private     $m_aCandidates = array();
    private     $m_aRest = array();
    private     $m_aNotAssigned = array();
    
}



?>