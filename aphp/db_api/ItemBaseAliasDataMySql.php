<?php

require_once ("db_api/SimpleTableDataMySql.php");


class ItemBaseAliasDataMySql extends SimpleTableDataMySql
{
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'item_base_alias'
        //            lowercase        Normal casing
        , array(  'item_base_alias_name', 'artist_name', 'item_base_name' ) 
        , $dbPDO );
    }

    
    /** Creates a new alias for an item_base name. The alias name is converted to lowercase, since we read them all into memory 
		for fast lookup in ItemBaseAliasLookup and don't want to waste time doing that conversion there. */  
    public function createNewAlias( $item_base_alias_name, $artist_name, $item_base_name )
    {
		// TODO: Create stored procedure for this, so it can be transaction based (thread safe). Unless a simple INSERT like this already is.
		//       We could also just start the transaction directly here if we are lazy.
		$alias_name_lower_case 		= mb_strtolower( $item_base_alias_name, 'UTF-8' );
		$artist_name_lower_case 	= mb_strtolower( $artist_name, 'UTF-8' );
		
        $stmt = $this->m_dbPDO->prepare("INSERT INTO {$this->m_baseTableName} (item_base_alias_name, artist_name, item_base_name) VALUES (?,?,?)" );
        $stmt->execute( array($alias_name_lower_case, $artist_name_lower_case, $item_base_name) );
        $id = (int)$this->m_dbPDO->lastInsertId();
        return $id;
    }
    
    
    
}


?>