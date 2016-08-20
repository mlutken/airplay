<?php


/** Class to facilitate fast lookup of item_base aliases by reading everything from DB and keeping it in memory. 
You cant add new aliases using this interface. For this you need to use ItemBaseAliasDataMySql. */
class ItemBaseAliasLookup
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbItemBaseAliasData )
    {
		if ( null != $dbItemBaseAliasData ) $this->initialiseFromTableDb( $dbItemBaseAliasData );
    }

    function 	initialiseFromTableDb( $dbItemBaseAliasData )
    {
		$aItemBaseAliases = $dbItemBaseAliasData->getBaseDataRows(0,0);	// Get all rows
		foreach ( $aItemBaseAliases as $aItemBaseAliasRow ) {
			$item_base_name = $aItemBaseAliasRow['item_base_name'];
			$artist_name_lower_case	= $aItemBaseAliasRow['artist_name'];
			$alias_name_lower_case  = $aItemBaseAliasRow['item_base_alias_name'];
			$this->m_aAliasNameToItemBaseName["{$artist_name_lower_case}^{$alias_name_lower_case}"] = $item_base_name;
		}
    }

    function 	aliasNameToItemBaseName( $artist_name_lower_case, $alias_name_lower_case, $item_base_name )
    {
		$alias_name = $this->m_aAliasNameToItemBaseName["{$artist_name_lower_case}^{$alias_name_lower_case}"];
		$retName = $alias_name != '' ? $alias_name : $item_base_name;
// // 		printf("aliasNameToItemBaseName($artist_name_lower_case, $alias_name_lower_case, $item_base_name): '$retName'\n");
		return $retName;
    }
    
    
    // ---------------------
    // --- PUBLIC: Data --- 
    // ---------------------
	// We have made these public on purpose!
    
    public     $m_aAliasNameToItemBaseName 	= array ();

    
}



?>