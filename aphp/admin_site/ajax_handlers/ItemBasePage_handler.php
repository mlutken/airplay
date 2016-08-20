<?php
require_once ( __DIR__ . '/../../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('admin_site/classes/session_start.php');
require_once ('utils/general_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');
require_once ('db_api/GenreLookup.php');
require_once ('db_api/CountryLookup.php');

$ui = new ItemBasePageHandler();


$sj = pretty_json (json_encode($_POST) );
$sj .= pretty_json (json_encode($_GET) );
$sj .= "articleLanguageCode: '" . $_SESSION['articleLanguageCode'] . "'\n";
file_put_contents("/tmp/_ItemBasePage_ajax_dbg.txt", $sj );


print $ui->ajaxHandler();


class ItemBasePageHandler
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( )
    {

        $fac                        = new MusicDatabaseFactory();
// //         $this->m_dbArtist           = $fac->createDbInterface('ArtistData');
        $this->m_dbItemBase         = $fac->createDbInterface('ItemBaseData');
        $this->m_dbItemTypeConvert  = $fac->createDbInterface('ItemTypeConvert');
        $this->m_genreConvert       = new GenreConvert();
    
    }

    
    public function ajaxHandler()
    {
        $retVal         = '';
        $item_base_id   = $_POST['item_base_id'];
        $value          = $_POST['value'];
        $action         = $_POST['action'];
        switch ( $action )
        {
            case 'itemBaseTextSave' :
                $retVal = $this->itemBaseTextSave($item_base_id, $value);
                break;
            case 'itemBaseTextReload' :
                $retVal = $this->itemBaseTextReload($item_base_id);
                break;
            case 'itemBaseBaseDataValueSave' :
                $retVal = $this->itemBaseBaseDataValueSave($item_base_id, $value);
                break;
        }
        return $retVal;
    }

    private function itemBaseBaseDataValueSave( $item_base_id, $value )
    {
        $keyNameId = $_POST['id'];
        $aData = array('item_base_id' => $item_base_id, $keyNameId => $value );
        
        $this->m_dbItemBase->updateBaseData($aData);
        $aData = $this->m_dbItemBase->getBaseData($item_base_id);
        $retVal = $aData[$keyNameId];
        switch ( $keyNameId )
        {
            case 'item_genre_id' :
                $retVal = $this->m_genreConvert->IDToName($retVal);
                break;
            case 'item_type' :
                $retVal = $this->m_dbItemTypeConvert->IDToName($retVal);
                break;
        }
        return $retVal;
    }
    
    
    private function itemBaseTextSave( $item_base_id, $itemBase_article )
    {
        $language_code = $_POST['language_code'];
        $_SESSION['articleLanguageCode'] = $language_code;

        $aData = array();
        $aData['item_base_id']                  = $item_base_id;
        $aData['language_code']                 = $language_code;
        $aData['item_base_article']             = $itemBase_article;
        $aData['item_base_text_reliability']    = $_POST['item_base_text_reliability'];
       
        $result = $this->m_dbItemBase->updateBaseData($aData);
        $aDataVerify = $this->m_dbItemBase->textDataGet($item_base_id, $language_code);
        $bOk = $itemBase_article == $aDataVerify['item_base_article'];
        return $bOk == false ? "Error" : "OK";
    }

    private function itemBaseTextReload($item_base_id)
    {
        $language_code = $_POST['language_code'];
        $_SESSION['articleLanguageCode'] = $language_code;
        
        $aTextData = $this->m_dbItemBase->textDataGet($item_base_id, $language_code);
        
        $s  = $aTextData['item_base_text_reliability'] . "\n" ;
        $s .= $aTextData['item_base_article'];
        return $s;
    }

    
// //     private     $m_itemBaseId;
    private     $m_dbItemBase;
    private     $m_genreConvert;
    private     $m_dbItemTypeConvert;
    
}


?>