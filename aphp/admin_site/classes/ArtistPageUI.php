<?php
require_once ('db_manip/MusicDatabaseFactory.php');
require_once ('admin_site/classes/ArtistMergeTblUI.php');
require_once ('admin_site/classes/SingleArtistAliasTblUI.php');

// ------------------------
// --- ArtistPage class ---
// ------------------------

class ArtistPageUI
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $pc )
    {
        $this->m_singleArtistAliasTblMainName   = 'SingleArtistAliasTbl';
        $this->m_artistMergeTblMainName         = 'ArtistMergeTbl';
        $this->m_pc             = $pc;
 
        $fac                    = new MusicDatabaseFactory();
        $this->m_dbArtist       = $fac->createDbInterface('ArtistData');
        $this->m_dbArtistAlias  = $fac->createDbInterface('ArtistSynonymData');
       
        $this->m_artistId       = getArtistIdFromURL($this->m_dbArtist);
        $this->m_genreConvert   = new GenreConvert();
        $this->m_countryConvert = new CountryConvert();
        
        $this->m_uiSingleArtistAlias = new SingleArtistAliasTblUI( $this->m_singleArtistAliasTblMainName, 'artist_synonym' );
        $this->m_uiSingleArtistAlias->dbInterfaceSet($this->m_dbArtistAlias);
        $this->m_uiSingleArtistAlias->extraGETParametersSet("&artist_id={$this->m_artistId}");

        $this->m_uiArtistMerge = new ArtistMergeTblUI( $this->m_artistMergeTblMainName, 'artist' );
        $this->m_uiArtistMerge->dbInterfaceSet($this->m_dbArtist);
        
        $this->m_aBaseData      = array();
        $this->m_aTextData      = array();
        if ( 0 != $this->m_artistId )  {    
            $this->m_aBaseData =  $this->m_dbArtist->getBaseData($this->m_artistId);
            $this->m_aTextData =  $this->m_dbArtist->textDataGet($this->m_artistId, getCurrentArticleLanguage() );
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
        $s .= $this->artistTabsHeader();
        $s .= $this->artistFactsTab();
        $s .= $this->artistTextTab();
        $s .= $this->artistAlbumTab();
        $s .= $this->artistSongTab();
        $s .= $this->artistAliasTab();
        $s .= $this->artistMergeTab();
        return $s;
    }
    
    public function artistHeadLine()
    {
        $s =
<<<TEXT
<div>
<span class="newLine">&nbsp;</span>
<span class="edit textVeryLarge spaceRight newLine" id=artist_name style="width:500px;" >{$this->m_aBaseData['artist_name']}</span>
<span class="textNormal" id=artist_name style="font-style:italic;" >(Artist)</span>
<span class="newLine">&nbsp;</span>
</div>
TEXT;
        return $s;
    }

    public function artistTabsHeader()
    {
        $s =
<<<TEXT
<div class="textNormal" id="artistTabsID" style="width:100%" >
    <ul>
        <li><a href="#artistTabsID-Facts">Facts</a></li>
        <li><a href="#artistTabsID-Text">Text</a></li>
        <li><a href="#artistTabsID-Albums">Albums</a></li>
        <li><a href="#artistTabsID-Songs">Songs</a></li>
        <li><a href="#artistTabsID-Aliases">Aliases</a></li>
        <li><a href="#artistTabsID-Merge">Merge</a></li>
    </ul>
TEXT;
        return $s;
    }
    
    public function artistFactsTab()
    {
        $genreName      = $this->m_genreConvert->IDToName($this->m_aBaseData['genre_id']);
        $countryName    = $this->m_countryConvert->IDToName($this->m_aBaseData['country_id']);
        $s =
<<<TEXT
<div id="artistTabsID-Facts" style="width:100%" >
<table border-width=0 >
<tr>
    <td>ID</td>
    <td class="textNormalValue" id=artist_id >{$this->m_aBaseData['artist_id']}</td>
</tr><tr>    
    <td>Real name</td>
    <td class="edit textNormalValue" id=artist_real_name >{$this->m_aBaseData['artist_real_name']}</td>
</tr><tr>    
    <td>Genre</td>
    <td class="textNormalValue" id=genre_id >$genreName</td>
</tr><tr>    
    <td>Country</td>
    <td class="textNormalValue" id=country_id >$countryName</td>
</tr><tr>    
    <td>Gender</td>
    <td class="textNormalValue" id=gender >{$this->m_aBaseData['gender']}</td>
</tr><tr>    
    <td>Artist type</td>
    <td class="textNormalValue" id=artist_type >{$this->m_aBaseData['artist_type']}</td>
</tr><tr>    
    <td>Born</td>
    <td class="edit textNormalValue" id=year_born style="width:100px" >{$this->m_aBaseData['year_born']}</td>
</tr><tr>    
    <td>Died</td>
    <td class="edit textNormalValue" id=year_died style="width:100px" >{$this->m_aBaseData['year_died']}</td>
</tr><tr>    
    <td>Start</td>
    <td class="edit textNormalValue" id=year_start style="width:100px" >{$this->m_aBaseData['year_start']}</td>
</tr><tr>    
    <td>End</td>
    <td class="edit textNormalValue" id=year_end style="width:100px" >{$this->m_aBaseData['year_end']}</td>
</tr><tr>    
    <td>Url official</td>
    <td class="edit textNormalValue" id=url_artist_official style="width:500px" >{$this->m_aBaseData['url_artist_official']}</td>
</tr><tr>    
    <td>Fanpage</td>
    <td class="edit textNormalValue" id=url_fanpage style="width:500px" >{$this->m_aBaseData['url_fanpage']}</td>
</tr><tr>    
    <td>Wikipedia</td>
    <td class="edit textNormalValue" id=url_wikipedia style="width:500px" >{$this->m_aBaseData['url_wikipedia']}</td>
</tr><tr>    
    <td>Allmusic</td>
    <td class="edit textNormalValue" id=url_allmusic style="width:500px" >{$this->m_aBaseData['url_allmusic']}</td>
</tr><tr>    
    <td>Musicbrainz</td>
    <td class="edit textNormalValue" id=url_musicbrainz style="width:500px" >{$this->m_aBaseData['url_musicbrainz']}</td>
</tr><tr>    
    <td>Discogs</td>
    <td class="edit textNormalValue" id=url_discogs style="width:500px" >{$this->m_aBaseData['url_discogs']}</td>
</tr><tr>    
    <td>Facebook</td>
    <td class="edit textNormalValue" id=url_facebook style="width:500px" >{$this->m_aBaseData['url_facebook']}</td>
</tr><tr>    
    <td>artist_reliability</td>
    <td class="edit textNormalValue" id=artist_reliability style="width:50px" >{$this->m_aBaseData['artist_reliability']}</td>
</tr><tr>    
    <td>info_artist_reliability</td>
    <td class="edit textNormalValue" id=info_artist_reliability style="width:50px" >{$this->m_aBaseData['info_artist_reliability']}</td>
</tr>
</table>
</div>
TEXT;
        return $s;
    }

    public function artistTextTab()
    {
        $s  = "<div id='artistTabsID-Text'>\n";
        $s .= "<input id=artist_text_saveID type=button value=Save onclick='ckArtistTextSave( g_artist_id );' ></input>\n";
        $s .= "<input id=artist_text_reloadID type=button value=Reload onclick='ckArtistTextReload( g_artist_id );' ></input>\n";
        $s .= htmlForSelect(supportedLanguages(), getCurrentArticleLanguage(), array('id' => 'artist_text_languageID', 'onclick' => 'artistTextLanguageChanged(g_artist_id);'  ) );
        
        $s .= "<span>Article reliability:</span><input id=artist_text_reliability value='' style='width:50px' ></input>\n";
        $s .= "<textarea id=artist_text name='input' style='width:100%' ></textarea>\n";
        $s .= "</div>\n";
        return $s;
    }
 

    public function artistAlbumTab()
    {
        $s =
<<<TEXT
<div id="artistTabsID-Albums">
    <div id='ArtistAlbumsTableContainer' class='.SimpleTableContainer' ></div>
</div>
TEXT;
        return $s;
    }

    public function artistSongTab()
    {
        $s =
<<<TEXT
<div id="artistTabsID-Songs">
    <div id='ArtistSongsTableContainer' class='.SimpleTableContainer' ></div>
</div>
TEXT;
        return $s;
    }

    
    public function artistAliasTab()
    {
        $s  = "<div id='artistTabsID-Aliases'>\n";
        $s .= $this->m_uiSingleArtistAlias->containerDIV(); 
        $s .= "</div>\n";
        return $s;
    }
    
    
    public function artistMergeTab()
    {
        $s = "<div id='artistTabsID-Merge'>\n";
        $s .= $this->m_pc->pageIncrementalSearchBox($this->m_artistMergeTblMainName);
        $s .= "<input id=artistMergeBtnID type=button value='Merge Selected' onclick=\"artistMergeFromTable1({$this->m_artistId},'{$this->m_artistMergeTblMainName}TableContainer', true);\" ></input>";
        $s .= $this->m_uiArtistMerge->containerDIV(); 
        $s .= "</div>\n";
    
        return $s;
    }
    
    
    // ----------------------------
    // --- Javascript functions ---
    // ----------------------------
 
    public function makeJtableItemBaseCreateFunction($item_type, $sBaseName)
    {
        $artist_id          = $this->m_aBaseData['artist_id'];
        
        $s =
<<<TEXT
function {$sBaseName}_jtableCreate() 
{
    $('#{$sBaseName}TableContainer').jtable
    ({
        title: 'ItemBase',
        paging: 'true',
        toolbar: {
            items: [{
                icon: 'css/img/table_reload.png',
                text: 'Reload',
                click: function () {
                    $('#{$sBaseName}TableContainer').jtable('load');
                }
            }]
        },
        actions: 
        {
            listAction: 'ajax_handlers/ArtistPage_ItemBase_handler.php?action=list&artist_id={$artist_id}&item_type=$item_type'
            , createAction: 'ajax_handlers/ArtistPage_ItemBase_handler.php?action=create&artist_id={$artist_id}&item_type=$item_type'
            , updateAction: 'ajax_handlers/ArtistPage_ItemBase_handler.php?action=update&artist_id={$artist_id}&item_type=$item_type'
            , deleteAction: 'ajax_handlers/ArtistPage_ItemBase_handler.php?action=delete&artist_id={$artist_id}&item_type=$item_type'
        },
        fields: 
        {
            item_base_id: 
            {
                title: 'ID'
                , key: true
                , create: false
                , edit: false
            }, 
            item_type: 
            {
                title: 'item_type'
                , list: false
            }, 
            artist_id: {
                title: 'artist_id'
                , list: false
            }, 
            item_base_name: {
                title: 'Name'
                , width: '20%'
            }, 
            record_label_id: {
                title: 'record_label_id'
                , list: false
            },
            item_genre_id: {
                title: 'Genre ID'
            },
            item_subgenre_ids: {
                title: 'item_subgenre_ids'
                , list: false
            },
            item_year: {
                title: 'Year'
            },
            release_date: {
                title: 'Released'
                , type: 'date'          
            },
            parent_item: {
                title: 'Parent item'
            },
            item_time: {
                title: 'Time'
            },
            track_number: {
                title: 'Track #'
            }, 
            child_items: {
                title: 'child_items'
                , list: false
            },
            item_base_reliability: {
                title: 'Reliability'
            }
        }
    });
    $('#{$sBaseName}TableContainer').jtable('load');
}
TEXT;
        return $s;
    }

 
    public function makeJeditable()
    {
        $genreSelect        = json_encode($this->m_genreConvert->arrayForSelect(2));
        $countrySelect      = json_encode($this->m_countryConvert->arrayForSelect(2));
        $commonCommitData   = "{action: 'artistBaseDataValueSave', artist_id: '{$this->m_aBaseData['artist_id']}'}";
        
        $s =
<<<TEXT
function makeJeditable()
{
    $('.edit').editable('ajax_handlers/ArtistPage_handler.php', {
        indicator   : 'Saving...',
        tooltip     : 'artist_name',
        submitdata  : $commonCommitData

    });

    $('#genre_id').editable('ajax_handlers/ArtistPage_handler.php', { 
        data        : $genreSelect,
        type        : 'select',
        submitdata  : $commonCommitData,
        submit      : 'OK'
    });
    $('#country_id').editable('ajax_handlers/ArtistPage_handler.php', { 
        data        : $countrySelect,
        type        : 'select',
        submitdata  : $commonCommitData,
        submit      : 'OK'
    });
    $('#artist_type').editable('ajax_handlers/ArtistPage_handler.php', { 
        data        : { 'U' : 'Unknown', 'P': 'Person', 'G':'Group', 'selected': 'P' },
        type        : 'select',
        tooltip     : 'P: Person, G: Group, U: Unknown',
        submitdata  : $commonCommitData,
        submit      : 'OK'
    });
    $('#gender').editable('ajax_handlers/ArtistPage_handler.php', { 
        data        : { 'U' : 'Unknown', 'M': 'Male', 'F':'Female', 'selected': 'F' },
        type        : 'select',
        tooltip     : 'F: Female, M: Male, U: Unknown',
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
    $( "#artistTabsID" ).tabs();
    var g_artist_text_ckEditor = CKEDITOR.replace( 'artist_text' );
    makeJeditable();
    ckArtistTextReload(g_artist_id);
    g_artist_text_ckEditor.on('key', function(e) {
        ckArtistTextDirtyChanged();
    });
    ArtistAlbums_jtableCreate();
    ArtistSongs_jtableCreate();
    {$this->m_singleArtistAliasTblMainName}_jtableCreate(); 
    {$this->m_artistMergeTblMainName}_jtableCreate(); 

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
    var g_artist_id              = {$this->m_artistId};
</script>
TEXT;
        return $s;
    }
    
    
    
    public function pageScriptSection()
    {
        $s = "<script>\n";
        $s .= $this->makeJeditable();
        $s .= $this->makeJtableItemBaseCreateFunction(1,'ArtistAlbums');
        $s .= $this->makeJtableItemBaseCreateFunction(2,'ArtistSongs');
        $s .= $this->m_uiSingleArtistAlias->getJTableCreateFunction("{$this->m_singleArtistAliasTblMainName}_jtableCreate");
        $s .= $this->m_uiArtistMerge->getJTableCreateFunction("{$this->m_artistMergeTblMainName}_jtableCreate");
        $s .= $this->pageScriptDocumentReadyFunction();
        $s .= "</script>\n";
        return $s;
    }

    
    private     $m_artistId;
    private     $m_pc;
    private     $m_dbArtist;
    private     $m_dbArtistAlias;
    private     $m_genreConvert;
    private     $m_countryConvert;
    private     $m_aBaseData;
    private     $m_aTextData;
    private     $m_singleArtistAliasTblMainName;
    private     $m_artistMergeTblMainName;
    private     $m_uiSingleArtistAlias;
    private     $m_uiArtistMerge;
    
    
}



?>