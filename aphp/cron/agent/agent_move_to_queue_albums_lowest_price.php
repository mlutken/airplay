<?php
	require_once ( __DIR__ . '/../../aphp_fix_include_path.php' );
	require_once ('../../airplay_globals.php');
	require_once ('../../db_manip/AllDbTables.php');
	require_once ( '../../db_api/AgentDataMySql.php' );
	require_once ( '../../agents/AgentFactory.php' );
	
	// TODO - loop concerts ......

	
	// Variables for running this script.
	$concert_agent_count = 0;
	$agent_count_inserted = 0;
	$agent_count_updated = 0;
	
	$aAlbumAgents = array();
	
	$oAgentFactory = new AgentFactory();
	$oAgent = $oAgentFactory->createAgent('mail');
	
	$agent_data = new AgentDataMySql( $m_dbAll );
	$aAlbumAgents = $agent_data->getBaseDataForMaxPriceAlbums();
	$concert_agent_count = count($aAlbumAgents);
	
	print "Album agents to test: {$concert_agent_count} \n";
	
	if ($concert_agent_count > 0) {
		// Get All Concert items
		foreach ($aAlbumAgents AS $aAlbumAgent) {
			$oAgent->artist_id = $aAlbumAgent["artist_id"];
			$oAgent->agent_id = $aAlbumAgent["agent_id"];
			$oAgent->user_name = $aAlbumAgent["firstname"] . " " . $aAlbumAgent["lastname"];
			$oAgent->user_email = $aAlbumAgent["mail"];
			$oAgent->item_base_id = $aAlbumAgent["item_base_id"];
			$oAgent->timestamp_last_sent = $aAlbumAgent["timestamp_last_sent"];
			$oAgent->max_price = $aAlbumAgent["max_price"];

			$aItems = $agent_data->getItemBaseForMaxPrice( $oAgent->agent_id, $oAgent->max_price, $oAgent->timestamp_last_sent, $oAgent->item_base_id ); 
			//var_dump($aItems);
			if (count($aItems) > 0) {
				$artist_name = $aItems[0]["artist_name"];
				$item_name = $aItems[0]["item_base_name"];
				
				$content_html = $oAgent->getMaxPriceAlbumMailHTML($aItems, $oAgent->user_name);
				$send_alternative_text = "";
				// Get agent from queue
				$agent_queue = $agent_data->getAgentQueueFromMySql($oAgent->agent_id);
				if (count($agent_queue) > 0) {
					$agent_data->updateAgentDataInQueue($oAgent->agent_id, $text, $send_alternative_text);
					$agent_count_updated++;
					print "Update agent .... artist id: {$oAgent->artist_id} agent id: {$oAgent->agent_id}\n";
				} else {
					$agent_data->insertIntoAgentQueue($oAgent->agent_id, $oAgent->user_name, $oAgent->user_email, $content_html, $send_alternative_text, "Nye priser for Album Agent for {$item_name} af {$artist_name}");
					$agent_count_inserted++;
					print "Create agent .... artist id: {$oAgent->artist_id} agent id: {$oAgent->agent_id}\n";
				}
				// Update agent - timestamp for sent now.
				$agent_data->updateAgentLastSent($oAgent->agent_id);
			}
		}
	}
	
	print "************************************\n";
	print "*********    SCRIPT DONE   *********\n";
	print "Moved to queue: {$agent_count_inserted}\n";
	print "Updated in queue: {$agent_count_updated}\n";
	
?>