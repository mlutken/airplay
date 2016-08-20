<?php
require_once ('db_manip/MusicDatabaseFactory.php');
require_once ('admin_site/classes/ItemBaseMergeTblUI.php');
require_once ('admin_site/classes/SingleItemBaseCorrectionTblUI.php');
require_once ("admin_site/classes/SimpleTableUI.php");

// --------------------------
// --- ItemBasePageUI class ---
// --------------------------

class ItemBasePageUI
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $pc )
    {
        $this->m_singleItemBaseCorrectionTblMainName    = 'SingleItemBaseCorrectionTbl';
        $this->m_itemBaseMergeTblMainName               = 'ItemBaseMergeTbl';
        $this->m_pc                 = $pc;
 
        $fac                        = new MusicDatabaseFactory();
        $this->m_dbArtist           = $fac->createDbInterface('ArtistData');
        $this->m_dbItemBase         = $fac->createDbInterface('ItemBaseData');
        $this->m_dbItemTypeConvert  = $fac->createDbInterface('ItemTypeConvert');
        
        
        $this->m_itemBaseId         = getItemBaseIdFromURL($this->m_dbItemBase);
        $this->m_genreConvert       = new GenreConvert();
        $this->m_countryConvert     = new CountryConvert();
        

        $this->m_uiItemBaseMerge = new ItemBaseMergeTblUI( $this->m_itemBaseMergeTblMainName, 'item_base' );
        $this->m_uiItemBaseMerge->dbInterfaceSet($this->m_dbItemBase);
        
        
        $this->m_aBaseData      = array();
        $this->m_aTextData      = array();
        $this->m_artistName     = '';
        $this->m_itemBaseName   = '';
        $this->m_itemTypeName   = '';
        if ( 0 != $this->m_itemBaseId )  {    
            $this->m_aBaseData      = $this->m_dbItemBase->getBaseData($this->m_itemBaseId);
            $this->m_aTextData      = $this->m_dbItemBase->textDataGet($this->m_itemBaseId, getCurrentArticleLanguage() );
            $this->m_artistName     = $this->m_dbArtist->IDToName($this->m_aBaseData['artist_id']);
            $this->m_itemBaseName   = $this->m_dbItemBase->IDToName($this->m_itemBaseId);
            $this->m_itemTypeName   = $this->m_dbItemTypeConvert->IDToName($this->m_aBaseData['item_type']);
        }
        
        $dbItemPrice = $fac->createDbInterface("ItemPriceData");
        $this->m_itemPriceTblMainName   = 'ItemBasePage_ItemPrice';
        $this->m_itemPriceTbl           = new SimpleTableUI($this->m_itemPriceTblMainName, 'item_price');
        $this->m_itemPriceTbl->dbInterfaceSet($dbItemPrice);
        $this->m_itemPriceTbl->extraGETParametersSet("&item_base_id={$this->m_itemBaseId}");
        //$this->m_itemPriceTbl->tableOptionsSet  ( array( 'paging' => ''    ) ); // Disable paging

        $dbItemBaseCorrction = $fac->createDbInterface('ItemBaseCorrectionData');
        $this->m_uiSingleItemBaseCorrection = new SingleItemBaseCorrectionTblUI( $this->m_singleItemBaseCorrectionTblMainName, 'item_base_correction' );
        $this->m_uiSingleItemBaseCorrection->dbInterfaceSet($dbItemBaseCorrction);
        $urlParamItemBaseName = urlencode($this->m_itemBaseName); // TODO: Should we do this? I Suppose!
//        $urlParamItemBaseName = $this->m_itemBaseName;
        $this->m_uiSingleItemBaseCorrection->extraGETParametersSet("&artist_id={$this->m_aBaseData['artist_id']}&item_base_name={$urlParamItemBaseName}");
        
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
        $s .= $this->itemBaseTabsHeader();
        $s .= $this->itemBaseFactsTab();
        $s .= $this->itemBaseTextTab();
        $s .= $this->baseItemPricesTab();
// //         $s .= $this->artistSongTab();
        $s .= $this->itemBaseCorrectionTab();
        $s .= $this->itemBaseMergeTab();
        return $s;
    }
    
    public function artistHeadLine()
    {
        $s =
<<<TEXT
<div>
<span class="newLine">&nbsp;</span>
<span class="edit textVeryLarge spaceRight newLine" id=item_base_name style="width:500px;" >{$this->m_itemBaseName}</span>
<span class="textNormal" id=item_base_name style="font-style:italic;" >({$this->m_itemTypeName})</span>
<span class="newLine">&nbsp;</span>
</div>
TEXT;
        return $s;
    }

    public function itemBaseTabsHeader()
    {
        $s =
<<<TEXT
<div class="textNormal" id="itemBaseTabsID" style="width:100%" >
    <ul>
        <li><a href="#itemBaseTabsID-Facts">Facts</a></li>
        <li><a href="#itemBaseTabsID-Text">Text</a></li>
        <li><a href="#itemBaseTabsID-Prices">Prices</a></li>
        <li><a href="#itemBaseTabsID-Corrections">Name corrections</a></li>
        <li><a href="#itemBaseTabsID-Merge">Merge</a></li>
    </ul>
TEXT;
        return $s;
    }
    
    
                
    public function itemBaseFactsTab()
    {
        $genreName          = $this->m_genreConvert->IDToName($this->m_aBaseData['item_genre_id']);
        $parentItemName     = $this->m_dbItemBase->IDToName($this->m_aBaseData['parent_item']);
        $s =
<<<TEXT
<div id="itemBaseTabsID-Facts" style="width:100%" >
<table border-width=0 >
<tr>
    <td>ID</td>
    <td class="textNormalValue" id=item_base_id >{$this->m_itemBaseId}</td>
</tr><tr>    
    <td>Artist name</td>
    <td class="textNormalValue" id=parent_item ><a href=/ArtistPage.php?artist_id={$this->m_aBaseData['artist_id']} >{$this->m_artistName}</a></td>
</tr><tr>    
    <td>Artist ID</td>
    <td class="textNormalValue" id=item_base_id style="width:100px" >{$this->m_aBaseData['item_base_id']}</td>
</tr><tr>    
    <td>Item type</td>
    <td class="textNormalValue" id=item_type >{$this->m_itemTypeName}</td>
</tr><tr>    
    <td>Parent item</td>
    <td class="edit textNormalValue" id=parent_item style="width:100px" >{$this->m_aBaseData['parent_item']}</td>
</tr><tr>    
    <td>Parent item name</td>
    <td class="textNormalValue" id=parent_item ><a href=/ItemBasePage.php?item_base_id={$this->m_aBaseData['parent_item']} >{$parentItemName}</a></td>
</tr><tr>    
    <td>Genre</td>
    <td class="textNormalValue" id=item_genre_id >$genreName</td>
</tr><tr>    
    <td>Record label ID</td>
    <td class="edit textNormalValue" id=record_label_id style="width:100px" >{$this->m_aBaseData['record_label_id']}</td>
</tr><tr>    
    <td>Release year</td>
    <td class="edit textNormalValue" id=item_year style="width:100px" >{$this->m_aBaseData['item_year']}</td>
</tr><tr>    
    <td>Release date</td>
    <td class="edit textNormalValue" id=release_date style="width:150px" >{$this->m_aBaseData['release_date']}</td>
</tr><tr>    
    <td>Item time (in seconds)</td>
    <td class="edit textNormalValue" id=item_time style="width:100px" >{$this->m_aBaseData['item_time']}</td>
</tr><tr>    
    <td>Track number</td>
    <td class="edit textNormalValue" id=track_number style="width:100px" >{$this->m_aBaseData['track_number']}</td>
</tr><tr>    
    <td>item_base_reliability</td>
    <td class="edit textNormalValue" id=item_base_reliability style="width:50px" >{$this->m_aBaseData['item_base_reliability']}</td>
</tr>
</tr><tr>    
    <td>item_master</td>
    <td class="edit textNormalValue" id=item_master style="width:50px" >{$this->m_aBaseData['item_master']}</td>
</tr>
</table>
</div>
TEXT;
        return $s;
    }

    public function itemBaseTextTab()
    {
        $s  = "<div id='itemBaseTabsID-Text'>\n";
        $s .= "<input id=item_base_text_saveID type=button value=Save onclick='ckItemBaseTextSave( g_item_base_id );' ></input>\n";
        $s .= "<input id=item_base_text_reloadID type=button value=Reload onclick='ckItemBaseTextReload( g_item_base_id );' ></input>\n";
        $s .= htmlForSelect(supportedLanguages(), getCurrentArticleLanguage(), array('id' => 'item_base_text_languageID', 'onclick' => 'itemBaseTextLanguageChanged(g_item_base_id);'  ) );
        
        $s .= "<span>Article reliability:</span><input id=item_base_text_reliability value='' style='width:50px' ></input>\n";
        $s .= "<textarea id=item_base_text name='input' style='width:100%' ></textarea>\n";
        $s .= "</div>\n";
        return $s;
    }
 

    public function baseItemPricesTab()
    {
        $tableContainerDIV = $this->m_itemPriceTbl->containerDIV();
        $s =
<<<TEXT
<div id="itemBaseTabsID-Prices">
    $tableContainerDIV
</div>
TEXT;
        return $s;
    }


    
    public function itemBaseCorrectionTab()
    {
        $s  = "<div id='itemBaseTabsID-Corrections'>\n";
         $s .= $this->m_uiSingleItemBaseCorrection->containerDIV(); 
        $s .= "</div>\n";
        return $s;
    }
    
    
    public function itemBaseMergeTab()
    {
        $s = "<div id='itemBaseTabsID-Merge'>\n";
        $s .= $this->m_pc->pageIncrementalSearchBox($this->m_itemBaseMergeTblMainName);
        $s .= "<input id=itemBaseMergeBtnID type=button value='Merge Selected' onclick=\"itemBaseMergeFromTable1({$this->m_itemBaseId},'{$this->m_itemBaseMergeTblMainName}TableContainer', true );\" ></input>";
        $s .= $this->m_uiItemBaseMerge->containerDIV(); 
        $s .= "</div>\n";
    
        return $s;
    }
    
    
    // ----------------------------
    // --- Javascript functions ---
    // ----------------------------
 
 
    public function makeJeditable()
    {
        $genreSelect        = json_encode($this->m_genreConvert->arrayForSelect(2));
        $itemTypeSelect     = json_encode($this->m_dbItemTypeConvert->arrayForSelect(2));
        $commonCommitData   = "{action: 'itemBaseBaseDataValueSave', item_base_id: '{$this->m_aBaseData['item_base_id']}'}";
        
        $s =
<<<TEXT
function makeJeditable()
{
    $('.edit').editable('ajax_handlers/ItemBasePage_handler.php', {
        indicator   : 'Saving...',
        tooltip     : 'item_base_name',
        submitdata  : $commonCommitData
    });

    $('#item_genre_id').editable('ajax_handlers/ItemBasePage_handler.php', { 
        data        : $genreSelect,
        type        : 'select',
        submitdata  : $commonCommitData,
        submit      : 'OK'
    });
    $('#item_type').editable('ajax_handlers/ItemBasePage_handler.php', { 
        data        : $itemTypeSelect,
        type        : 'select',
        submitdata  : $commonCommitData,
        submit      : 'OK'
    });
}
TEXT;
        return $s;
    }

    
    public function pageScriptDocumentReadyFunction()
    {
        $s =
<<<TEXT
$(document).ready(function() {
    $( "#itemBaseTabsID" ).tabs();
    var g_item_base_text_ckEditor = CKEDITOR.replace( 'item_base_text' );
    makeJeditable();
    ckItemBaseTextReload(g_item_base_id);
    g_item_base_text_ckEditor.on('key', function(e) {
        ckItemBaseTextDirtyChanged();
    });
    {$this->m_itemPriceTblMainName}_jtableCreate();
    {$this->m_singleItemBaseCorrectionTblMainName}_jtableCreate(); 
    {$this->m_itemBaseMergeTblMainName}_jtableCreate(); 
    incrementalSearchBoxMusicDb_AutoCompleteCreate();

});
TEXT;
        return $s;
    }
    
    public function pageScriptGlobalsSection()
    {
        $s =
<<<TEXT
<script>
    var g_item_base_id              = {$this->m_itemBaseId};
</script>
TEXT;
        return $s;
    }
    
    
    
    public function pageScriptSection()
    {
        $s = "<script>\n";
        $s .= $this->makeJeditable();
        $s .= $this->m_itemPriceTbl->getJTableCreateFunction( "{$this->m_itemPriceTblMainName}_jtableCreate" );
// //         $s .= $this->makeJtableItemBaseCreateFunction(1,'ArtistAlbums');
// //         $s .= $this->makeJtableItemBaseCreateFunction(2,'ArtistSongs');
        $s .= $this->m_uiSingleItemBaseCorrection->getJTableCreateFunction("{$this->m_singleItemBaseCorrectionTblMainName}_jtableCreate");
        $s .= $this->m_uiItemBaseMerge->getJTableCreateFunction("{$this->m_itemBaseMergeTblMainName}_jtableCreate");
        $s .= $this->pageScriptDocumentReadyFunction();
        $s .= "</script>\n";
        return $s;
    }

    
    private     $m_itemBaseId;
    private     $m_artistName;
    private     $m_itemBaseName;
    private     $m_itemTypeName;
    private     $m_pc;
    private     $m_dbArtist;
    private     $m_dbItemBase;
    private     $m_dbItemTypeConvert;
    private     $m_genreConvert;
    private     $m_countryConvert;
    private     $m_aBaseData;
    private     $m_aTextData;
    private     $m_singleItemBaseCorrectionTblMainName;
    private     $m_itemBaseMergeTblMainName;
    private     $m_uiSingleItemBaseCorrection;
    private     $m_uiItemBaseMerge;
    private     $m_itemPriceTblMainName;
    private     $m_itemPriceTbl;
    
    
}



?>