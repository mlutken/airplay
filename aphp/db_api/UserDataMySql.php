<?php

require_once ("db_api/SimpleTableDataMySql.php");


class UserDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'user'
        , array(  'user_name', 'email', 'fb_id', 'profile_image_url'
                )
        , $dbPDO );
    }

    // -----------------------------
    // --- Lookup user functions ---
    // -----------------------------
    
    /**  Lookup/autocreate new user. Lookup user or try autocreate if not found. 
    \return ID of new user. */
    public function lookupAutoCreate ( $aData )
    {
        $user_id = $this->toID($aData);
        if ( 0 == $user_id ) {
            ///printf("lookupAutoCreate -> newUser\n");
            $user_id = $this->newUser($aData);
        }
        if ( 0 != $user_id ) {
            $aData['user_id'] = $user_id;
            $this->updateBaseData($aData);
        }
        
        return $user_id;
    }
    
    /** Lookup ID from $aData.
    Tris first to lookup by Facebook ID, then by email.
    \return One ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function toID ($aData)
    {
        $user_id = $this->lookupFromFacebookID ( $aData['fb_id'] );
        if ( 0 == $user_id ) $user_id = $this->lookupFromEmail ( $aData['email'] );
        return $user_id;
    }
    
    /** Lookup user ID from Facebook ID.
    \return One ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function lookupFromFacebookID ($fb_id)
    {
        $q = "SELECT {$this->m_baseTableName}_id FROM {$this->m_baseTableName} WHERE fb_id = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($fb_id) );
    }

    /** Lookup user ID from email.
    \return One ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function lookupFromEmail ($email)
    {
        $q = "SELECT {$this->m_baseTableName}_id FROM {$this->m_baseTableName} WHERE email = ?";
        return pdoLookupSingleIntQuery($this->m_dbPDO, $q, array($email) );
    }
    
    // -----------------------------
    // --- Create user functions ---
    // -----------------------------

    /**  Create new user. 
    \return ID of new user. */
    public function newUser ( $aData )
    {
        //var_dump($aData);
        $id = (int)0;
        $name   = $aData['user_name'];
        $email  = $aData['email'];
        $fb_id  = $aData['fb_id'];
        
        if ( '' != $fb_id && '' != $email ) {
//            printf("newUser: INSERT INTO {$this->m_baseTableName} (user_name, email, fb_id) VALUES (?,?,?)\n");
            $stmt = $this->m_dbPDO->prepare("INSERT INTO {$this->m_baseTableName} (user_name, email, fb_id) VALUES (?,?,?)" );
            $stmt->execute( array("$name", $email, $fb_id) );
            $id = (int)$this->m_dbPDO->lastInsertId();
        }
        
        // TODO: Currently we only support FB login
//         else if ( '' != $email && '' != $password_hashed) {
//         }

        return $id;
    }

    
    
}

?>