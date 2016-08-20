<?php

require_once ('quiz/QuizCreatorBase.php');

/** Base class for quiz creators.
The primary function to override in derived classes is 
createQuiz() */
class QuizCreatorGuessSongPlaying extends QuizCreatorBase
{
    // ------------------------------------------
    // --- PUBLIC: Constructor/init functions --- 
    // ------------------------------------------
    public  function    __construct( $dbPDO = null, $redis = null )
    {
        parent::__construct($dbPDO, $redis); 
    }
    // -------------------------------------------
    // --- PUBLIC: Primary interface functions --- 
    // -------------------------------------------
    
    public function createQuiz($aCreateParams) 
    {
        var_dump($aCreateParams);
        $this->getAllSongsFromArtists($aCreateParams['aArtistNames']);
    }
    
    
    // ---------------------------------
    // --- PRIVATE: Helper functions --- 
    // ---------------------------------
    private function getAllSongsFromArtists($aArtistNames)
    {
        $aAllSongs = array();
        var_dump($aArtistNames);
        foreach( $aArtistNames as $artist_name) {
            $artist_id = $this->m_dbAll->m_dbArtistData->lookupID($artist_name);
            if ( 0 != $artist_id) {
                $a = $this->m_dbAll->m_dbItemBaseData->getItemNamesForArtist($artist_id, 2);
                var_dump($a);
            }
        }
        return $aAllSongs;
    }
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
}
?>    