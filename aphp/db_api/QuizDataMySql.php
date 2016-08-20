<?php

require_once ("db_api/SimpleTableDataMySql.php");


class QuizDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'quiz'
        , array(  'quiz_name', 'quiz_keywords', 'quiz_json'
                , 'author_user_id', 'author_email', 'author_fb_id' 
                )
        , $dbPDO );
    }
    

    /** Given \a $quiz_name we count the number of names in the quiz table that begins 
    with $quiz_name-- . So in order to avoid names clashing when auto generating a quiz, we 
    always append '--NUMBER' to the name. For example:
    We create the first quiz with 'Michael Jackson'. Then it gets the autoname 'Michael Jackson--0'.
    Next time we create a Michael Jackson quiz it will get the name 'Michael Jackson--1' and so on...
    */
    public function autoNameFirstFreeNumber($quiz_name)
    {
        $q = "SELECT COUNT(*) FROM {$this->m_baseTableName} WHERE {$this->m_baseTableName}_name LIKE ?";
        return pdoLookupSingleIntQuery( $this->m_dbPDO, $q, array("{$quiz_name}--%")) +1;
    }
    
    
    /** Get auto quiz name from name. 
    \see Explanation in autoNameFirstFreeNumber() function
    */
    public function autoGenerateName($quiz_name)
    {
        return $quiz_name . '--' . $this->autoNameFirstFreeNumber($quiz_name);
    }
    
    
    /** Insert a new quiz in the table using autonaming. See autoNameFirstFreeNumber() function
        \a $aData is updated to it's new autogenerated name and the quiz_id is inserted in $aData as well.
    \return ID of new item. */
    public function autoSaveNewQuiz ( &$aData )
    {
        $aData["{$this->m_baseTableName}_name"] = $this->autoGenerateName($aData["{$this->m_baseTableName}_name"]);
        $id = $this->newItem ( $aData );
        $aData["{$this->m_baseTableName}_id"] = $id;
        $this->updateBaseData($aData);
        return $id;
    }
    

}

?>