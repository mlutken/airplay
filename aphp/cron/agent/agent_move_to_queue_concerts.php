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
	
	$aConcertAgents = array();
	
	$oAgentFactory = new AgentFactory();
	$oAgent = $oAgentFactory->createAgent('mail');
	
	$agent_data = new AgentDataMySql( $m_dbAll );
	$aConcertAgents = $agent_data->getBaseDataForValidatedConcerts();
	$concert_agent_count = count($aConcertAgents);
	
	print "Concert agents to test: {$concert_agent_count} \n";
	
	if ($concert_agent_count > 0) {
		// Get All Concert items
		foreach ($aConcertAgents AS $ConcertAgent) {
			$oAgent->artist_id = $ConcertAgent["artist_id"];
			$oAgent->agent_id = $ConcertAgent["agent_id"];
			$oAgent->user_name = $ConcertAgent["firstname"] . " " . $ConcertAgent["lastname"];
			$oAgent->user_email = $ConcertAgent["mail"];
			$oAgent->timestamp_last_sent = $ConcertAgent["timestamp_last_sent"];
			
			$aConcert = $agent_data->getItemBaseForConcerts( $oAgent->agent_id, $oAgent->artist_id, $oAgent->timestamp_last_sent ); 
			if (count($aConcert) > 0) {
				$artist_name = $aConcert[0]["artist_name"];
				
				$content_html = $oAgent->getConcertMailHTML($aConcert, $oAgent->user_name);
				$send_alternative_text = "";
				// Get agent from queue
				$agent_queue = $agent_data->getAgentQueueFromMySql($oAgent->agent_id);
				if (count($agent_queue) > 0) {
					$agent_data->updateAgentDataInQueue($oAgent->agent_id, $content_html, $send_alternative_text);
					$agent_count_updated++;
					print "Update agent .... artist id: {$oAgent->artist_id} agent id: {$oAgent->agent_id}\n";
				} else {
					$agent_data->insertIntoAgentQueue($oAgent->agent_id, $oAgent->user_name, $oAgent->user_email, $content_html, $send_alternative_text, "Nyt fra Koncert Agent for {$artist_name}");
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