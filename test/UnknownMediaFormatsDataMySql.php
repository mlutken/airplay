<?php

require_once ("db_helpers.php");

/** For writing unknown media_format names to the DB. We log the media_format name, the store ID, 
and the URL we found the unknown media_format name on. 
Later we probably should add some WebGUI where we can add new media_formats and their corresponding 
AP official media_format_id. This GUI should then delete the entry in this table.
*/
class UnknownMediaFormatsDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        global $g_MySqlPDO;
        $this->m_dbPDO = $dbPDO;
        if ( $dbPDO == null ) $this->m_dbPDO = $g_MySqlPDO;      
    }

    // ----------------------------------
    // --- Item ID lookup functions --- 
    // ----------------------------------
    
    /** Lookup (unknown) media_format_name to get it's ID.
    \return One unknown_media_format ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function media_formatNameToID ($media_format_name)
    {
        $q = "SELECT unknown_media_format_id FROM unknown_media_format WHERE media_format_name = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($media_format_name) );
    }
    

    // --------------------------
    // --- Get data functions --- 
    // --------------------------
    /** Get unknown_media_format base data. Eg. XX ... etc. */
    public function getBaseData ($unknown_media_format_id)
    {
        $q = "SELECT * FROM unknown_media_format WHERE unknown_media_format_id = $unknown_media_format_id";
        return pdoQueryAssocFirstRow($this->m_dbPDO, $q); 
    }
    

    
    /** Get all data needed to display a page with all unknown media_formats. */
    public function getPageData ()
    {
        $q = "SELECT * FROM unknown_media_format AS u INNER JOIN record_store AS r 
              WHERE u.record_store_id = r.record_store_id";
        return pdoQueryAssocRows($this->m_dbPDO, $q);
    }
    
    // --------------------------
    // --- Set data functions --- 
    // --------------------------
    /**  Set data of item. Creates new item if media_format_name not found. 
        \return ID of new or updated item. */
    public function setData ($media_format_name, $record_store_id, $buy_at_url )
    {
        $stmt = $this->m_dbPDO->prepare("
        REPLACE INTO unknown_media_format  
        SET media_format_name = ?, record_store_id = ?, buy_at_url = ?" );
        $stmt->execute( array($media_format_name, $record_store_id, $buy_at_url) );
        return (int)$this->m_dbPDO->lastInsertId();
    }
        
    
    /** Delete entry by ID.
    \return XX */
    public function deleteByID ($unknown_media_format_id)
    {
        $q = "DELETE FROM unknown_media_format WHERE unknown_media_format_id = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($unknown_media_format_id) );
    }

    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private         $m_dbPDO = null;
    
}


?>
