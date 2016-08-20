<?php

require_once ("db_api/SimpleTableDataMySql.php");


class QuizThemeDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'quiz_theme'
        , array(  'quiz_theme_name', 'level1_kategory_name', 'level2_kategory_name'
                , 'country_code', 'theme_keywords', 'theme_json' 
                )
        , $dbPDO );
    }
    

}

?>