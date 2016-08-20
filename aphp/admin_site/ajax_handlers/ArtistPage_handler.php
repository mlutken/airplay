<?php
require_once ( __DIR__ . '/../../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('admin_site/classes/session_start.php');
require_once ('utils/general_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');
require_once ('db_api/GenreLookup.php');
require_once ('db_api/CountryLookup.php');

$fac = new MusicDatabaseFactory();
$dbArtist = $fac->createDbInterface("ArtistData");
$genreConvert = new GenreConvert();
$countryConvert = new CountryConvert();
$ui = new ArtistPageHandler( $dbArtist, $genreConvert, $countryConvert );


// $sj = pretty_json (json_encode($_POST) );
// $sj .= "articleLanguageCode: '" . $_SESSION['articleLanguageCode'] . "'\n";
// file_put_contents("/tmp/_ajax_dbg.txt", $sj );


print $ui->ajaxHandler();


class ArtistPageHandler
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbArtist, $genreConvert, $countryConvert )
    {
        $this->m_dbArtist       = $dbArtist;
        $this->m_genreConvert   = $genreConvert;
        $this->m_countryConvert = $countryConvert;
    }

    
    public function ajaxHandler()
    {
        $retVal     = '';
        $artist_id  = $_POST['artist_id'];
        $value      = $_POST['value'];
        $action     = $_POST['action'];
        switch ( $action )
        {
            case 'artistTextSave' :
                $retVal = $this->artistTextSave($artist_id, $value);
                break;
            case 'artistTextReload' :
                $retVal = $this->artistTextReload($artist_id);
                break;
            case 'artistBaseDataValueSave' :
                $retVal = $this->artistBaseDataValueSave($artist_id, $value);
                break;
        }
        return $retVal;
    }

    private function artistBaseDataValueSave( $artist_id, $value )
    {
        $keyNameId = $_POST['id'];
        $aData = array('artist_id' => $artist_id, $keyNameId => $value );
        
        $this->m_dbArtist->updateBaseData($aData);
        $aData = $this->m_dbArtist->getBaseData($artist_id);
        $retVal = $aData[$keyNameId];
        switch ( $keyNameId )
        {
            case 'genre_id' :
                $retVal = $this->m_genreConvert->IDToName($retVal);
                break;
            case 'country_id' :
                $retVal = $this->m_countryConvert->IDToName($retVal);
                break;
        }
        return $retVal;
    }
    
    
    private function artistTextSave( $artist_id, $artist_article )
    {
        $language_code = $_POST['language_code'];
        $_SESSION['articleLanguageCode'] = $language_code;

        $aData = array();
        $aData['artist_id']                 = $artist_id;
        $aData['language_code']             = $language_code;
        $aData['artist_article']            = $artist_article;
        $aData['artist_text_reliability']   = $_POST['artist_text_reliability'];
       
        $result = $this->m_dbArtist->updateBaseData($aData);
        $aDataVerify = $this->m_dbArtist->textDataGet($artist_id, $language_code);
        $bOk = $artist_article == $aDataVerify['artist_article'];
        return $bOk == false ? "Error" : "OK";
    }

    private function artistTextReload($artist_id)
    {
        $language_code = $_POST['language_code'];
        $_SESSION['articleLanguageCode'] = $language_code;
        
        $aTextData = $this->m_dbArtist->textDataGet($artist_id, $language_code);
        
        $s  = $aTextData['artist_text_reliability'] . "\n" ;
        $s .= $aTextData['artist_article'];
        return $s;
    }

    
// //     private     $m_artistId;
    private     $m_dbArtist;
    private     $m_genreConvert;
    private     $m_countryConvert;
    
}


?>