<?php
require_once ("redis_api/redis_utils.php");


class ItemBaseCorrectionDataRedis
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $redis = null )
    {
        global $g_redis;
        $this->m_r = $redis;
        if ( $redis == null ) $this->m_r = $g_redis;      
    }

    // ----------------------------------
    // --- Item ID lookup functions --- 
    // ----------------------------------
    
    public function correctionNameToBaseName ($artist_id, $name_to_correct)
    {
        if ( $artist_id == 0 ) {
            $item_base_name = $this->m_r->hget('item_base_correction', $name_to_correct );
       }
        else {
            $item_base_name = $this->m_r->hget('item_base_correction:' . $artist_id, $name_to_correct );
        }
        return $item_base_name != '' ? $item_base_name : $name_to_correct;
    }
    
    // --------------------------
    // --- Get data functions --- 
    // --------------------------
    /**  */
    public function getBaseData ($item_base_correction_id)
    {
        // ??
    }
    
    // --------------------------
    // --- Set data functions --- 
    // --------------------------
    /**  Set base data of item. Creates new item if name not found. */
    public function setBaseData ($aData)
    {
        $this->setCorrectionName ( $aData['artist_id'], $aData['name_to_correct'], $aData['correct_item_base_name']   );
    }

    
    
    /**  Create or update correction name .
    \param $artist_id The artist for which this correction is valid. If zero it will be used for all artists
    \param $name_to_correct The "wrong" base_name that needs to be "corrected".
    \param $correct_item_base_name  The correct base_name that should be used
    \return void */
    public function setCorrectionName ( $artist_id, $name_to_correct, $correct_item_base_name )
    {
        $this->m_r->hset('item_base_correction', $name_to_correct, $correct_item_base_name );
        if ( $artist_id != 0 ) {
            $this->m_r->hset('item_base_correction:' . $artist_id, $name_to_correct, $correct_item_base_name );
        }
    }
    
    
    /**  Update base data of existing item. */
    public function updateBaseData ($aData)
    {
        $this->setCorrectionName ( $aData['artist_id'], $aData['name_to_correct'], $aData['correct_item_base_name']   );
     }
    
    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private         $m_r = null;
    
}

?>