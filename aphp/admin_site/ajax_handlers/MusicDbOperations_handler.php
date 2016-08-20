<?php
require_once ( __DIR__ . '/../../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('admin_site/classes/session_start.php');
require_once ('utils/general_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');
require_once ('db_manip/MusicDatabaseManip.php');

$ui = new MusicDbOperationsHandler();


// $sj = pretty_json (json_encode($_POST) );
// $sj .= "articleLanguageCode: '" . $_SESSION['articleLanguageCode'] . "'\n";
// file_put_contents("/tmp/_ajax_dbg.txt", $sj );


print $ui->ajaxHandler();


class MusicDbOperationsHandler
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( )
    {
        $fac = new MusicDatabaseFactory();
        $this->m_musicDbManip = new MusicDatabaseManip();
    }

    
    public function ajaxHandler()
    {
        $retVal     = '';
        $action     = $_POST['action'];
        if ( '' == $action )    $action = $_GET['action'];
        switch ( $action )
        {
            case 'artistMerge' :
                $into_artist_id  = $_POST['into_artist_id'];
                $from_artist_id  = $_POST['from_artist_id'];
                if ( '' == $into_artist_id )    $into_artist_id = $_GET['into_artist_id'];
                if ( '' == $from_artist_id )    $from_artist_id = $_GET['from_artist_id'];
                $retVal     = $this->artistMerge( $into_artist_id, $from_artist_id );
                break;
            case 'artistDelete' :
                break;
            case 'itemBaseMerge' :
                $into_item_base_id  = $_POST['into_item_base_id'];
                $from_item_base_id  = $_POST['from_item_base_id'];
                if ( '' == $into_item_base_id )    $into_item_base_id = $_GET['into_item_base_id'];
                if ( '' == $from_item_base_id )    $from_item_base_id = $_GET['from_item_base_id'];
                $retVal     = $this->itemBaseMerge( $into_item_base_id, $from_item_base_id );
                break;
            case 'itemBaseDelete' :
                break;
        }
        return $retVal;
    }

    private function artistMerge( $into_artist_id, $from_artist_id )
    {
        $retVal     = '';
        //$retVal     = "into_artist_id, from_artist_id => $into_artist_id, $from_artist_id";

        $retVal = $this->m_musicDbManip->mergeArtist ($into_artist_id, $from_artist_id);
        return $retVal;
    }

    private function itemBaseMerge( $into_item_base_id, $from_item_base_id )
    {
        $retVal     = '';
        //$retVal     = "into_item_base_id, from_item_base_id => $into_item_base_id, $from_item_base_id";

        $retVal = $this->m_musicDbManip->mergeItemBase ($into_item_base_id, $from_item_base_id);
        return $retVal;
    }
    
    private     $m_musicDbManip;
    
}


?>