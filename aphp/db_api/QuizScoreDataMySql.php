<?php

require_once ("db_api/SimpleTableDataMySql.php");


class QuizScoreDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'quiz_score'
        , array(  'quiz_id', 'user_id', 'score' 
                )
        , $dbPDO );
    }
    
    public function highScoreListFull($quiz_id)
    {
        $q =
<<<TEXT
        SELECT user_name, score, profile_image_url 
        FROM quiz_score 
        LEFT JOIN user ON quiz_score.user_id = user.user_id
        WHERE quiz_id = ?
        ORDER BY score DESC
TEXT;
        return pdoQueryAssocRows( $this->m_dbPDO, $q, array($quiz_id) );
    }
    
    
    /** Lookup quiz_score_id from $aData.
    \return One ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function toID ($aData)
    {
        $quiz_id = $aData['quiz_id'];
        $user_id = $aData['user_id'];
        $q = "SELECT {$this->m_baseTableName}_id FROM {$this->m_baseTableName} WHERE quiz_id = ? AND user_id = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($quiz_id,$user_id) );
    }

    /**  Create new item. 
    \return ID of new item. */
    public function newItem ( $aData )
    {
        $quiz_id = (int)$aData['quiz_id'];
        $user_id = (int)$aData['user_id'];
        $stmt = $this->m_dbPDO->prepare("INSERT INTO {$this->m_baseTableName} (quiz_id, user_id) VALUES (?, ?)" );
        $stmt->execute( array($quiz_id, $user_id) );
        $id = (int)$this->m_dbPDO->lastInsertId();
        return $id;
    }

    
    /**  Create new item - with all fields. 
    \return ID of new item. */
    public function newItemFull ( $aData )
    {
        $quiz_id    = (int)$aData['quiz_id'];
        $user_id    = (int)$aData['user_id'];
        $score      = (int)$aData['score'];
        $stmt = $this->m_dbPDO->prepare("INSERT INTO {$this->m_baseTableName} (quiz_id, user_id, score) VALUES (?, ?, ?)" );
        $stmt->execute( array($quiz_id, $user_id, $score) );
        $id = (int)$this->m_dbPDO->lastInsertId();
        return $id;
    }

    
    
    
}

?>