<?php

require_once ('db_manip/AllDbTables.php');

/** Base class for quiz creators.
The primary function to override in derived classes is 
createQuiz() */
abstract class QuizCreatorBase
{
    // ------------------------------------------
    // --- PUBLIC: Constructor/init functions --- 
    // ------------------------------------------
    public  function    __construct( $dbPDO = null, $redis = null )
    {
        $this->m_dbAll = new AllDbTables($dbPDO, $redis);
    }
    // -------------------------------------------
    // --- PUBLIC: Primary interface functions --- 
    // -------------------------------------------
    
    abstract public function createQuiz($aCreateParams);
    
    // -----------------------
    // --- PROTECTED: Data --- 
    // -----------------------
    protected $m_dbAll = null;
}
?>