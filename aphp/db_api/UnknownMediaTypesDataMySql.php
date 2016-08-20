<?php

require_once ("db_api/SimpleTableDataMySql.php");

// 'unknown_media_type_name', 'record_store_id', 'buy_at_url'
/** For writing unknown media_type names to the DB. We log the media_type name, the store ID, 
and the URL we found the unknown media_type name on. 
Later we probably should add some WebGUI where we can add new media_types and their corresponding 
AP official media_type_id. This GUI should then delete the entry in this table.
*/
class UnknownMediaTypesDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'unknown_media_type'
        , array( 'unknown_media_type_name', 'record_store_id', 'buy_at_url' ) 
        , $dbPDO );
    }

    // -------------------------------
    // --- Erase/delete functions ----
    // -------------------------------
// //     /** Completely erase an entry. */
// //     public function erase ($media_type_id)
// //     {
// //         $aArgs = array($media_type_id);
// //         $stmt = $this->m_dbPDO->prepare( 'DELETE FROM media_type WHERE media_type_id = ?' );
// //         $stmt->execute( $aArgs );
// //     }

    // ----------------------------------
    // --- Item ID lookup functions --- 
    // ----------------------------------
    
//     /** Lookup (unknown) media_type_name to get it's ID.
//     \return One unknown_media_type ID if found one and only one matching. Zero if not found any or more than one found.  */
//     public function media_typeNameToID ($media_type_name)
//     {
//         $q = "SELECT unknown_media_type_id FROM unknown_media_type WHERE media_type_name = ?";
//         return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($media_type_name) );
//     }
    

    // --------------------------
    // --- Get data functions --- 
    // --------------------------
// //     /** Get unknown_media_type base data. Eg. XX ... etc. */
// //     public function getBaseData ($unknown_media_type_id)
// //     {
// //         $q = "SELECT * FROM unknown_media_type WHERE unknown_media_type_id = $unknown_media_type_id";
// //         return pdoQueryAssocFirstRow($this->m_dbPDO, $q); 
// //     }
    

    
    /** Get all data needed to display a page with all unknown media_types. */
    public function getPageData ()
    {
        $q = "SELECT * FROM unknown_media_type AS u INNER JOIN record_store AS r 
              WHERE u.record_store_id = r.record_store_id";
        return pdoQueryAssocRows($this->m_dbPDO, $q);
    }
    
    // --------------------------
    // --- Set data functions --- 
    // --------------------------
    /**  Set data of item. Creates new item if media_type_name not found. 
        \return ID of new or updated item. */
    public function setData ($unknown_media_type_name, $record_store_id, $buy_at_url )
    {
        $stmt = $this->m_dbPDO->prepare("
        REPLACE INTO unknown_media_type  
        SET unknown_media_type_name = ?, record_store_id = ?, buy_at_url = ?" );
        $stmt->execute( array($unknown_media_type_name, $record_store_id, $buy_at_url) );
        return (int)$this->m_dbPDO->lastInsertId();
    }
        
    
// //     /** Delete entry by ID.
// //     \return XX */
// //     public function deleteByID ($unknown_media_type_id)
// //     {
// //         $q = "DELETE FROM unknown_media_type WHERE unknown_media_type_id = ?";
// //         return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($unknown_media_type_id) );
// //     }

    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
// //     private         $m_dbPDO = null;
    
}


?>