<?php
require_once ('db_manip/ItemBaseAutoMerge.php');
require_once ('db_manip/MusicDatabaseManip.php');


/** Class for doing the actual work of merging albums. Uses ItemBaseAutoMerge for the bulk of the work. 
*/
class AutoMergerAlbums
{
    // --------------------------
    // --- Constructor / init --- 
    // --------------------------
    public function __construct( $dbPDO = null, $redis = null )
    {
        $iIsPrimaryThreshold        = 10; // NOTE: Not used currently as we start out with real primaries/masters
        $iMergeCandidateThreshold   = 3;

        $fac = new MusicDatabaseFactory($dbPDO, $redis);
        $this->m_dbArtistData           = $fac->createDbInterface("ArtistData");
		$this->m_itemBaseAutoMerge  = new ItemBaseAutoMerge($iIsPrimaryThreshold, $iMergeCandidateThreshold);
		$this->m_musicDatabaseManip = new MusicDatabaseManip($dbPDO, $redis);
    }

    public function mergeArtistsWithItemMasters( $start, $count )
    {
        $aArtistIds = $this->m_dbArtistData->getArtistsWithItemMasterAlbums( $start, $count );
        foreach( $aArtistIds as $artist_id ) {
            //printf("Merge: $artist_id\n");
            $this->mergeArtistById($artist_id);
        }
    }
    
    public function mergeAllArtistsWithItemMasters()
    {
        $this->mergeArtistsWithItemMasters(0,0);
    }
    
    public function mergeArtistById($artist_id)
    {
        $aItemBases = $this->m_dbArtistData->getAutoMergeData($artist_id, 1);
        //var_dump($aItemBases);
        $sHtml = $this->mergeArtistByItemBases($aItemBases);
        return $sHtml;
    }
    
    private function mergeArtistByItemBases($aItemBases)
    {
        $sHtml = "";

        //$this->m_itemBaseAutoMerge->assignPrimaries($aItemBases);

        $aPrimaries = array();
        $aCandidates = array();
        $aRest = array();
        $this->m_itemBaseAutoMerge->getPrimariesAndCandidates($aItemBases, $aPrimaries, $aCandidates, $aRest );
//        $aNotAssigned = $this->m_itemBaseAutoMerge->assignMergeCandidates($aPrimaries, $aCandidates, "ibCompare1,ibCompare2" );
        $this->m_itemBaseAutoMerge->assignMergeCandidates($aPrimaries, $aCandidates, "ibCompare1" );

        $this->mergeAssignedAlbums($aPrimaries);
        
//         usort($aNotAssigned, 'sortGreaterItemBaseName');
//         
//         $sHtml .= ibamArrayToHtmlTable($aPrimaries, "Primaries" );
//         $sHtml .= ibamArrayToHtmlTable($aNotAssigned, "Not Assigned" );
//         $sHtml .= ibamArrayToHtmlTable($aRest, "Not Attempted" );
        
        return $sHtml;
    }


    private function mergeAssignedAlbums($aPrimaries)
    {
        foreach ( $aPrimaries as $aPrimary ) {
            if ( count($aPrimary['aToMerge']) == 0 ) continue;
            printf("\n");
            printf("*** Merge for artist: {$aPrimary['artist_name']} ***\n");
            printf("    Into album      : {$aPrimary['item_base_name']}: {$aPrimary['item_base_id']}\n");
            
            $into_item_base_id = $aPrimary['item_base_id'];
            foreach( $aPrimary['aToMerge'] as $aCandidate) {
                printf("    merge album     : {$aCandidate['item_base_name']}: {$aCandidate['item_base_id']}\n");
                $from_item_base_id = $aCandidate['item_base_id'];
                $this->m_musicDatabaseManip->mergeItemBase ($into_item_base_id, $from_item_base_id);
            }
        }
        printf("\n");
    }
    
	private		$m_itemBaseAutoMerge    = null;
    private     $m_musicDatabaseManip   = null;
}



?>