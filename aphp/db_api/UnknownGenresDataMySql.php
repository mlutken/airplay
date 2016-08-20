<?php
    
require_once ("db_api/SimpleTableDataMySql.php");
// 
/** For writing unknown genre names to the DB. We log the genre name, the store ID, 
and the URL we found the unknown genre name on. 
Later we probably should add some WebGUI where we can add new genres and their corresponding 
AP official genre_id. This GUI should then delete the entry in this table.
*/
class UnknownGenresDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'unknown_genre'
        , array( 'unknown_genre_name', 'record_store_id', 'buy_at_url' ) 
        , $dbPDO );
    }

    // -------------------------------
    // --- Erase/delete functions ----
    // -------------------------------
// //     /** Completely erase an entry. */
// //     public function erase ($unknown_genre_id)
// //     {
// //         $aArgs = array($unknown_genre_id);
// //         $stmt = $this->m_dbPDO->prepare( 'DELETE FROM unknown_genre WHERE unknown_genre_id = ?' );
// //         $stmt->execute( $aArgs );
// //     }
    
    // ----------------------------------
    // --- Item ID lookup functions --- 
    // ----------------------------------
    
//     /** Lookup (unknown) genre_name to get it's ID.
//     \return One unknown_genre ID if found one and only one matching. Zero if not found any or more than one found.  */
//     public function genreNameToID ($genre_name)
//     {
//         $q = "SELECT unknown_genre_id FROM unknown_genre WHERE genre_name = ?";
//         return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($genre_name) );
//     }
    

    // --------------------------
    // --- Get data functions --- 
    // --------------------------
// //     /** Get unknown_genre base data. Eg. XX ... etc. */
// //     public function getBaseData ($unknown_genre_id)
// //     {
// //         $q = "SELECT * FROM unknown_genre WHERE unknown_genre_id = $unknown_genre_id";
// //         return pdoQueryAssocFirstRow($this->m_dbPDO, $q); 
// //     }
    

    
    /** Get all data needed to display a page with all unknown genres. */
    public function getPageData ()
    {
        $q = "SELECT * FROM unknown_genre AS u INNER JOIN record_store AS r 
              WHERE u.record_store_id = r.record_store_id";
        return pdoQueryAssocRows($this->m_dbPDO, $q);
    }
    
    // --------------------------
    // --- Set data functions --- 
    // --------------------------
    /**  Set data of item. Creates new item if genre_name not found. 
        \return ID of new or updated item. */
    public function setData ( $unknown_genre_name, $record_store_id, $buy_at_url )
    {
        $stmt = $this->m_dbPDO->prepare("
        REPLACE INTO unknown_genre  
        SET unknown_genre_name = ?, record_store_id = ?, buy_at_url = ?" );
        $stmt->execute( array($unknown_genre_name, $record_store_id, $buy_at_url) );
        return (int)$this->m_dbPDO->lastInsertId();
    }
        
    
// //     /** Delete entry by ID.
// //     \return XX */
// //     public function deleteByID ($unknown_genre_id)
// //     {
// //         $q = "DELETE FROM unknown_genre WHERE unknown_genre_id = ?";
// //         return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($unknown_genre_id) );
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