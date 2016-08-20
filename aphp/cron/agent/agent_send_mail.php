<?php
	require_once ( __DIR__ . '/../../aphp_fix_include_path.php' );
	require_once ('../../airplay_globals.php');
	require_once ('db_manip/AllDbTables.php');
	require_once ( '../../db_api/AgentDataMySql.php' );
	require_once ( '../../agents/AgentFactory.php' );
	
	require_once ( '../../PHPMailer/AirplayMusicMailer.php' );
	
	$concert_agent_queue_count = 0;
	
	$oAgentFactory = new AgentFactory();
	$oAgent = $oAgentFactory->createAgent('mail');
	
	$agent_data = new AgentDataMySql( $m_dbAll );
	$aConcertAgentsQueue = $agent_data->getAllAgentsFromQueue();
	$concert_agent_queue_count = count($aConcertAgentsQueue);
	
	
	print "Sendmail agents queue length : {$concert_agent_queue_count} \n";
	
	if ($concert_agent_queue_count > 0) {
		// Get All Agents from Queue
		foreach ($aConcertAgentsQueue AS $ConcertAgentQueue) {
			$agent_queue_id = $ConcertAgentQueue["agent_queue_id"];
			$agent_id = $ConcertAgentQueue["agent_id"];
			$user_name = $ConcertAgentQueue["user_name"];
			$user_email = $ConcertAgentQueue["user_email"];
			$send_text = $ConcertAgentQueue["send_text"];
			$send_alternative_text = $ConcertAgentQueue["send_alternative_text"];
			$subject = $ConcertAgentQueue["subject"];
			$oAgent->ap_sendmail($user_name, $user_email, $send_text, $send_alternative_text, $subject);
			$agent_data->addAgentToLog($agent_id);
			$agent_data->removeItemFromAgentQueue($agent_queue_id);
		}
	}
?>