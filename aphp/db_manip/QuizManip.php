<?php

require_once ("db_manip/AllDbTables.php");


class QuizManip
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null, $redis = null )
    {
        $this->m_at = new AllDbTables($dbPDO, $redis);
    }

    // -----------------------------
    // --- Quiz: Score functions ---
    // -----------------------------
    /** Save quiz score if (quiz_id, user_id) combination does not already have a score. 
    
    For (user_id, fb_id, email) you only need to specify one and so goes for 
    the pair (quiz_id, quiz_name). In both cases the '_id' is preferred if present.
    \param $aScoreData Data example: 
    ( 'quiz_id'     => 12
    , 'quiz_name'   => 'My Quiz'
    , 'user_id'     => 2345,
    , 'fb_id'       => 123456, // Facebook ID 
    , 'email'       => 'dd@duckburg.com',
    , 'score'       => 'score' );
    */
    public function quizScoreSave($aScoreData)
    {
        return $this->quizScoreSaveImpl($aScoreData, false);
    }
    
    /** Save quiz score unconditionally - i.e. regardless whether an existing score is already present. 
    
    For (user_id, fb_id, email) you only need to specify one and so goes for 
    the pair (quiz_id, quiz_name). In both cases the '_id' is preferred if present.
    \param $aScoreData Data example: 
    ( 'quiz_id'     => 12
    , 'quiz_name'   => 'My Quiz'
    , 'user_id'     => 2345,
    , 'fb_id'       => 123456, // Facebook ID 
    , 'email'       => 'dd@duckburg.com',
    , 'score'       => 'score' );
    */
    public function quizScoreSaveOverWrite($aScoreData)
    {
        return $this->quizScoreSaveImpl($aScoreData, true);
    }

    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    /** Save quiz score implementation. To take care of two cases: overwrite existing record or not. 
    
    For (user_id, fb_id, email) you only need to specify one and so goes for 
    the pair (quiz_id, quiz_name). In both cases the '_id' is preferred if present.
    \param $aScoreData Data example: 
    ( 'quiz_id'     => 12
    , 'quiz_name'   => 'My Quiz'
    , 'user_id'     => 2345,
    , 'fb_id'       => 123456, // Facebook ID 
    , 'email'       => 'dd@duckburg.com',
    , 'score'       => 'score' );
    \param $bAllowOverwrite If true an existing record will be overwritten. If false we nver overwrite an existing.
    */
    public function quizScoreSaveImpl($aScoreData, $bAllowOverwrite)
    {
        // Get quiz_id 
        $quiz_id = (int)$aScoreData['quiz_id'];
        if ( 0 == $quiz_id ) $quiz_id = (int)$this->m_at->m_dbQuizData->toID($aScoreData);
        
        // Get user_id 
        $user_id = (int)$aScoreData['user_id'];
        if ( 0 == $user_id ) $user_id = (int)$this->m_at->m_dbUserData->toID($aScoreData);
        
        $aScoreInsertData = array('quiz_id' => $quiz_id, 'user_id' => $user_id, 'score' => $aScoreData['score'] );
        $quiz_score_id = (int)$this->m_at->m_dbQuizScoreData->toID($aScoreInsertData);
        if ( 0 == $quiz_score_id ) {
            $quiz_score_id = $this->m_at->m_dbQuizScoreData->newItemFull($aScoreInsertData);
        }
        else if ( $bAllowOverwrite ) {
            $aScoreInsertData['quiz_score_id'] = $quiz_score_id;
            $this->m_at->m_dbQuizScoreData->updateBaseData($aScoreInsertData);
        }
        return $quiz_score_id;
    }
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private         $m_at; // All Tables
    
}


// ########################################
// ########################################
////        printf("query: %s\n", $q);

?>