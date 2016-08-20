<?php
require_once ( __DIR__ . '/../../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('admin_site/classes/session_start.php');
require_once ('utils/general_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');
require_once ("db_manip/MusicDatabaseManip.php");
require_once ('db_api/GenreLookup.php');
require_once ('db_api/CountryLookup.php');

$jTableResult       = array();


$fac = new MusicDatabaseFactory();
$dbItemBase = $fac->createDbInterface("ItemBaseData");
$genreConvert = new GenreConvert();
$countryConvert = new CountryConvert();
$ui = new ArtistPageItemBaseHandler( $dbItemBase, $genreConvert, $countryConvert );

$sj = pretty_json (json_encode($_POST) );
$sj .= pretty_json (json_encode($_GET) );
$sj .= "ArtistPageItemBaseHandler: action: '" . $_GET['action'] . "'\n";
file_put_contents("/tmp/_ajax_dbg.txt", $sj );


print $ui->ajaxHandler();


class ArtistPageItemBaseHandler
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbItemBase, $genreConvert, $countryConvert )
    {
        $this->m_dbItemBase     = $dbItemBase;
        $this->m_genreConvert   = $genreConvert;
        $this->m_countryConvert = $countryConvert;
        $this->m_musicDbManip   = new MusicDatabaseManip();
    }

    
    public function ajaxHandler()
    {
        $jTableResult       = array();

        try
        {
            $aData = array();
            foreach ( $_POST as $k => $v ) $aData[ trim($k) ] = trim($v);
            $action = $_GET['action'];
            $artist_id = $_GET['artist_id'];
            $item_type = $_GET['item_type'];
 
            // ------------------------------------
            // --- Getting records (listAction) ---
            // ------------------------------------
            if( 'list' == $action )  {
                $this->actionList( $jTableResult, $artist_id, $item_type );
            }
            // --------------------------------------------
            // --- Creating a new record (createAction) ---
            // --------------------------------------------
            else if ( 'create' == $action )
            {
                $this->actionCreate( $jTableResult, $aData, $artist_id, $item_type );
            }
            // ----------------------------------------
            // --- Updating a record (updateAction) ---
            // ----------------------------------------
            else if ( 'update' == $action )
            {
                $this->actionUpdate( $jTableResult, $aData, $artist_id, $item_type );
            }
            // ----------------------------------------
            // --- Deleting a record (deleteAction) ---
            // ----------------------------------------
            else if ( 'delete' == $action )
            {
                $this->actionDelete( $jTableResult, $aData, $artist_id, $item_type );
            }
        }
        // ------------------------------
        // --- Other (unknown) errors ---
        // ------------------------------
        catch ( Exception $ex )
        {
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
        }
    
        return json_encode($jTableResult);
    }
    
    
    // ------------------------------------------------
    // --- PROTECTED: Ajax handler action functions --- 
    // ------------------------------------------------
    protected function actionList( &$jTableResult, $artist_id, $item_type )
    {
        $jTableResult['Result'] = "OK";
        $rows = $this->m_dbItemBase->getItemsForArtist( $artist_id, $item_type);
        $jTableResult['TotalRecordCount'] = count($rows);
        $jTableResult['Records'] = $rows;
    }

    protected function actionCreate( &$jTableResult, $aData, $artist_id, $item_type )
    {
        $id = 0;
        $jTableResult['Result'] = 'ERROR';
        if ( $this->m_dbItemBase->dbToID($aData) != 0 ) $jTableResult['Message'] = 'Record already exists';
        else {
            $id = $this->m_dbItemBase->newItem($aData);
            if ( $id == 0 ) $jTableResult['Message'] = 'Error creating new record';
            else {
                $aData["item_base_id"] = $id;
                $result = $this->m_dbItemBase->updateBaseData($aData);
                if ( $result < 0 ) $jTableResult['Message'] = 'Error updating fields in new record';
                else $jTableResult['Result'] = 'OK';
            }
        }
        $row = $this->dbGetBaseData($id);
        $jTableResult['Record'] = $row;
    }

    protected function actionUpdate( &$jTableResult, $aData, $artist_id, $item_type )
    {
        $jTableResult['Result'] = 'ERROR';
        $id = $aData["item_base_id"];
        if ( $id == 0 ) $jTableResult['Message'] = 'Error updating due to id = 0';
        else {
            $result = $this->m_dbItemBase->updateBaseData($aData);
            if ( $result < 0 ) $jTableResult['Message'] = 'Error updating fields in new record';
            else $jTableResult['Result'] = 'OK';
        }
        $jTableResult['Result'] = "OK";
    }

    protected function actionDelete( &$jTableResult, $aData, $artist_id, $item_type )
    {
        $jTableResult['Result'] = 'ERROR';
        $id = $aData["item_base_id"];
        if ( $id == 0 ) $jTableResult['Message'] = 'Error deleting due to id = 0';
        else {
            $result = $this->m_dbItemBase->erase($id);
            if ( $result == 0 ) $jTableResult['Message'] = 'Error deleting record';
            else $jTableResult['Result'] = 'OK';
        }
        $jTableResult['Result'] = "OK";
    }
    
    // -------------------------------------------------------------
    // --- PROTECTED: Ajax handler dbInterface wrapper functions --- 
    // -------------------------------------------------------------
    // Override these in derived class for easy customization

    protected function dbCountTotal()
    {
        return $this->m_dbItemBase->getSize();
    }

    protected function dbListAll($startIndex, $count)
    {
        return $this->m_dbItemBase->getBaseDataRows($startIndex, $count);
    }

    protected function dbIncrSearch($searchString)
    {
        return $this->m_dbItemBase->lookupSimilarBaseData($searchString);
    }

    protected function dbToID($aData)
    {
        return $this->m_dbItemBase->toID($aData);
    }
    
    protected function dbCreate($aData)
    {
        return $this->m_dbItemBase->newItem($aData);
    }
    
    protected function dbGetBaseData($id)
    {
        return $this->m_dbItemBase->getBaseData($id);
    }
    
    protected function dbUpdateBaseData($aData)
    {
        return $this->m_dbItemBase->updateBaseData($aData);
    }
    
    protected function dbDelete($id)
    {
        return $this->m_musicDbManip->eraseItemBase($id);
    }

    
    private     $m_artistId;
    private     $m_dbItemBase;
    private     $m_genreConvert;
    private     $m_countryConvert;
    private     $m_musicDbManip;
    
}


?>