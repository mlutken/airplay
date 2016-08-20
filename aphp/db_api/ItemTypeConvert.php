<?php


/** Class that handles conversion between IDs and official AP item_type names for the 
currently 3 primary item_types we use. */
class ItemTypeConvert
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct()
    {
    }

    /** Get item_type ID from its (official AP item_type) name.
    \return AP ID of primary item_type name or zero if string is not an AP item_type name.  */
    public function nameToID ($item_type_name)
    {
        $s = mb_strtolower( $item_type_name, 'UTF-8' );
        return $this->nameToIDLowerCase($s);
    }
    

    /** Get item_type ID from its (official AP item_type) name. The \a item_type_name must be in lowercase. 
    \sa item_typeNameToID  that does not need it's argument to be in lowercase.
    \return AP ID of primary item_type name or zero if string is not an AP item_type name.  */
    public function nameToIDLowerCase ($item_type_name)
    {
        return (int)($this->m_aItemTypeToID[$item_type_name]);
    }
    
    /** Get item_type name from it's ID.
    \return AP primary item_type name or empty if ID is not an AP item_type ID.  */
    public function IDToName($item_type_id)
    {
        return (string)$this->m_aIDToItemType[$item_type_id];
    }
    
    /** Array string representing the item_types as needed in for example jeditable 'selects'.
    The array returned looks like this when converted using json_encode:
    {'0':'Unknown','1':'Album','2':'Song', ..., 'selected':'$iSelectedIndex'}
    \param $iSelectedIndex Is the index which should be selected. Eg. 1: Pop/Rock.
    \see admin_site/classes/ItemBasePageUI.php for an example of it's use. It's very simple */
// // //    public function jsonSelect($iSelectedIndex)
    public function arrayForSelect($selectedItemTypeID)
    {
        $a = array();
        $N = count($this->m_aIDToItemType);
        for ( $i = 0; $i < $N; $i++ ) {
            $a[$i] = $this->m_aIDToItemType[$i];
        }
        $a['selected'] = $selectedItemTypeID;
        return $a;
    }
    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------

    private     $m_aItemTypeToID = array (
         'album'                => 1
        ,'song'                 => 2
        ,'merchandise'          => 3
        ,'concert'              => 4
    );

    private     $m_aIDToItemType = array (
         ''   
        ,'Album'
        ,'Song'
        ,'Merchandise'
        ,'Concert' 
    );
    
}



?>