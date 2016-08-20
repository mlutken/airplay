<?php

require_once ("db_api/SimpleTableDataMySql.php");


class AgentDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'agent'
        , array(  'user_id', 'artist_id', 'item_base_id', 'item_type', 'max_price', 'currency_id', 'timestamp_added', 'timestamp_updated', 'timestamp_last_sent' ) 
        , $dbPDO );
    }

    // -------------------------------------
    // --- Erase/delete/merge functions ----
    // -------------------------------------
    /** Completely erase an entry. */
   /* public function erase ($agent_id)
    {
        $aArgs = array($agent_id);
        $stmt = $this->m_dbPDO->prepare( 'DELETE FROM agent WHERE agent_id = ?' );
        $stmt->execute( $aArgs );
    
        $stmt = $this->m_dbPDO->prepare( 'DELETE FROM agent_log WHERE agent_id = ?' );
        $stmt->execute( $aArgs );
		
    }*/


	
    /** Get agent base data,. Info like name, genre, wiki info etc. */
    public function getBaseDataForValidatedConcerts()
    {
		$q = "SELECT airplay_music_v1.user_settings.firstname, airplay_music_v1.user_settings.lastname, airplay_music_v1.user_settings.mail, airplay_music_v1.agent.agent_id, 
		airplay_music_v1.agent.artist_id, airplay_music_v1.agent.item_base_id, airplay_music_v1.agent.timestamp_last_sent
		FROM airplay_music_v1.agent
		INNER JOIN airplay_drupal7.users ON airplay_drupal7.users.uid = airplay_music_v1.agent.user_id
		INNER JOIN airplay_music_v1.user_settings ON airplay_music_v1.user_settings.user_id = airplay_music_v1.agent.user_id
		WHERE airplay_music_v1.user_settings.mail_approved = 1 AND airplay_music_v1.agent.item_type = 4";
		$aData = pdoQueryAssocRows($this->m_dbPDO, $q, array() );
		return $aData;
    }
	
	/** Get agent base data,. Info like name, genre, wiki info etc. */
    public function getBaseDataForMaxPriceAlbums()
    {
		$q = "SELECT airplay_music_v1.user_settings.firstname, airplay_music_v1.user_settings.lastname, airplay_drupal7.users.mail, airplay_music_v1.agent.agent_id, 
		airplay_music_v1.agent.artist_id, airplay_music_v1.agent.item_base_id, airplay_music_v1.agent.timestamp_last_sent, airplay_music_v1.agent.item_base_id, airplay_music_v1.agent.max_price
		FROM airplay_music_v1.agent
		INNER JOIN airplay_drupal7.users ON airplay_drupal7.users.uid = airplay_music_v1.agent.user_id
		INNER JOIN airplay_music_v1.user_settings ON airplay_music_v1.user_settings.user_id = airplay_music_v1.agent.user_id
		WHERE airplay_music_v1.user_settings.mail_approved = 1 AND airplay_music_v1.agent.item_type = 1";
		$aData = pdoQueryAssocRows($this->m_dbPDO, $q, array() );
		return $aData;
    }
	
	/**
	*	Get Data from album agent - with max price
	*/
	public function getItemBaseForMaxPrice( $agent_id, $max_price, $newer_then, $item_base_id )
	{
		$q = "
		SELECT * FROM (
			SELECT item_price.item_base_id, item_price_name, artist_name, item_base_name, agent_media_format_rel.media_format_id,
			CEIL(price_local * currency_to_euro.to_euro * currency.from_euro) as price_local
			FROM item_price
			INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id   
			INNER JOIN item_base ON item_base.item_base_id = item_price.item_base_id
			INNER JOIN artist ON artist.artist_id = item_price.artist_id 
			INNER JOIN agent ON agent.item_base_id = item_base.item_base_id
			INNER JOIN agent_media_format_rel ON agent_media_format_rel.agent_id = agent.agent_id
			INNER JOIN currency ON currency.currency_id = agent.currency_id
			WHERE item_price.item_base_id = :item_base_id AND item_price.item_type = 1 AND agent.agent_id = :agent_id
			AND item_price.timestamp_added > :newer_then
		 	AND agent_media_format_rel.media_format_id = item_price.media_format_id
		) AS Res
		WHERE Res.price_local <= :max_price
		ORDER BY Res.price_local ASC
		";
		$aData = pdoQueryAssocRows($this->m_dbPDO, $q, array( ":agent_id" => $agent_id, ":item_base_id" => $item_base_id, ":max_price" => $max_price, ":newer_then" => $newer_then));
		return $aData;
	}
	
	/*
		TODO remove join to 
	*/
	public function getItemBaseForConcerts( $agent_id, $artist_id, $newer_then )
	{
		$q = "
		SELECT item_price.item_base_id, item_price_name, artist_name, item_event_time, CEIL(price_local * currency_to_euro.to_euro * currency.from_euro) as price_local,
		IF (item_event_date = '0000-00-00', IF (record_store_event_date_text = '', '0000-00-00', SUBSTR(record_store_event_date_text, 1, 10)), item_event_date) AS item_event_date
		FROM item_price
		INNER JOIN artist ON artist.artist_id = item_price.artist_id
		INNER JOIN record_store ON record_store.record_store_id = item_price.record_store_id
		INNER JOIN agent_media_format_rel ON agent_media_format_rel.media_format_id = item_price.media_format_id
		INNER JOIN agent ON agent.agent_id = agent_media_format_rel.agent_id
		INNER JOIN currency_to_euro ON currency_to_euro.currency_id = item_price.currency_id
		INNER JOIN currency ON currency.currency_id = agent.currency_id
		WHERE ((item_event_date >= :newer_then) OR (item_event_date = '0000-00-00' AND item_price.timestamp_updated >= DATE_ADD(now(), INTERVAL -10 DAY)) )
		AND item_price.artist_id = :artist_id
		AND agent.agent_id = :agent_id
		AND item_price.item_type = 4
		AND item_price.timestamp_added >= :newer_then
		ORDER BY item_event_date ASC, item_event_time ASC
		";
		$aData = pdoQueryAssocRows($this->m_dbPDO, $q, array( ":artist_id" => $artist_id, ":newer_then" => $newer_then, ":agent_id" => $agent_id ) );
		return $aData;
	}

	public function getAgentTextFromMySql($text_type, $language) {
		$q = "SELECT text, alternative_text FROM agent_text WHERE text_type = ? AND language = ?";
		$aData = pdoQueryAssocRows($this->m_dbPDO, $q, array($text_type, $language) );
		return $aData;
	}
	
	/*
		Get all data from Queue for an agent_id
	*/
	public function getAgentQueueFromMySql($agent_id)
	{
		$q = "SELECT * FROM agent_queue WHERE agent_id = ?";
		$aData = pdoQueryAssocRows($this->m_dbPDO, $q, array($agent_id) );
		return $aData;
	}
	
	/*
		Update data like text in the Agent Queue, for a specific agent_id
	*/
	public function updateAgentDataInQueue($agent_id, $send_text, $send_alternative_text) {
		$result = 0;
		$q = "UPDATE agent_queue SET send_text = ?, send_alternative_text = ?, timestamp_created = CURRENT_TIMESTAMP WHERE agent_id = ?";
		$stmt = $this->m_dbPDO->prepare($q);
		$stmt->execute(array($send_text, $send_alternative_text, $agent_id));
        $result += $stmt->rowCount();
		return $result;
	}
	
	/*
		Insert a new item into the Agent Queue.
	*/
	public function insertIntoAgentQueue($agent_id, $user_name, $user_email, $send_text, $send_alternative_text, $subject) {
        $stmt = $this->m_dbPDO->prepare("INSERT INTO agent_queue ( agent_id, user_name, user_email, send_text, send_alternative_text, subject ) VALUES ( ?, ?, ?, ?, ?, ? )" );
        $stmt->execute( array($agent_id, $user_name, $user_email, $send_text, $send_alternative_text, $subject) );
        $id = (int)$this->m_dbPDO->lastInsertId();
        return $id;
	}
	
	/* Get All items from agent queue */
	public function getAllAgentsFromQueue( )
	{
		$q = "SELECT agent_queue_id, agent_id, user_name, user_email, send_text, subject FROM agent_queue";
		$aData = pdoQueryAssocRows($this->m_dbPDO, $q, array( ) );
		return $aData;
	}
	
	/* Remove agent from agent queue */
	public function removeItemFromAgentQueue($agent_queue_id)
	{
        $stmt = $this->m_dbPDO->prepare( 'DELETE FROM agent_queue WHERE agent_queue_id = ?' );
		$stmt->execute( array($agent_queue_id) );
	}
	
	/* Add agent to log */
	public function addAgentToLog($agent_id)
	{
        $stmt = $this->m_dbPDO->prepare("INSERT INTO agent_sent_log ( agent_id ) VALUES ( ? )" );
        $stmt->execute( array($agent_id ) );
        $id = (int)$this->m_dbPDO->lastInsertId();
        return $id;
	}
	
	public function updateAgentLastSent($agent_id) {
		$result = 0;
		$q = "UPDATE agent SET timestamp_last_sent = CURRENT_TIMESTAMP WHERE agent_id = ?";
		$stmt = $this->m_dbPDO->prepare($q);
		$stmt->execute(array( $agent_id));
        $result += $stmt->rowCount();
		return $result;
	}					
	
	
    /** Get all base data rows from table, obeying the limits given. */
   /* public function getBaseDataRows ( $start, $count )
    {
    }*/

    
    /** Get all data needed to display an agent page */
  /*  public function getPageData ($artist_id)
    {
        // TODO: Implement this!
    }*/

	
    // --------------------------
    // --- Set data functions --- 
    // --------------------------
    /**  Set base data of agent. Creates new agent if name not found. */
    /*public function setBaseData ($aData)
    {

    }*/
        
    
    /**  Create new agent. 
    \return ID of new agent. */
  /*  public function createNew ($user_id)
    {

    }*/

     /**  Create new agent with all fields
    \return ID of new agent. */
  /*  public function createNewFull ($user_id, $artist_id, $item_base_id, $item_type, $approved)
    {
	
	// SELECT -> UPDATE
        $stmt = $this->m_dbPDO->prepare("INSERT INTO agent (user_id, artist_id, item_base_id, item_type, timestamp_updated, approved) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE user_id=user_id ");
        $stmt->execute( array($user_id, $artist_id, $item_base_id, $item_type, date('Y-m-d H:i:s'), $approved) );
        $agent_id = (int)$this->m_dbPDO->lastInsertId();
   
        return $agent_id;
    }*/
    
    /**  Update base data of existing agent. */
  /*  public function updateBaseData ($aData)
    {
        $result = 0;
        $agent_id = (int)$aData['agent_id'];

        $aUpd = pdoGetUpdate ($aData, ArtistDataMySql::$m_aArtistTblFields );
        if ($aUpd[0] != "" ) {
            $q = 'UPDATE agent SET ' . $aUpd[0] . ' WHERE agent_id = ?';
            $aUpd[1][] = $agent_id;
            $stmt = $this->m_dbPDO->prepare($q);
            $stmt->execute($aUpd[1]);
            $result += $stmt->rowCount();
        }

        return $result;
    }*/
    

    
    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    static          $m_aArtistTblFields     = array( 'user_id', 'artist_id', 'item_base_id', 'item_type', 'max_price', 'currency_id', 'timestamp_added', 'timestamp_updated', 'timestamp_last_sent' );

    
}


?>