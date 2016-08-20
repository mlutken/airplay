<?php

require_once ("agents/BaseAgent.php");


class AgentMail extends BaseAgent
{

    public function __construct( )
    {
		//print "MAIL created\n";
        parent::__construct();
    }
	
	/*public function setWebserviceURL() {
		print "AgentMAIL setWebserviceURL";
    }*/

	// -----------------------
    // --- PUBLIC: Data --- 
    // -----------------------
	
	public      $m_dbPDO;
	public 		$artist_id = 0;
	public		$agent_id = 0;
	public 		$user_name = "";
	public		$user_email = "";
	public		$timestamp_added = "";
	public		$item_base_id = 0;
	public		$max_price = 0;
}
?>