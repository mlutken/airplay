<?php

	/* 
		TODO: Error handler, database cleanup
		
	*/
	require_once("include_db_functions.php");
	$dbconnection = open_db_v1();
	
	$xml_output = false; // Return xml true/false
	
	/* Create a job */ 
	if ($_REQUEST['get_type'] == "admin_create_or_edit_job" ) {
		
		if (isset($_REQUEST['job_run_interval_minutes']) && $_REQUEST['job_run_interval_minutes'] == "") {
			$return['error'] = true;
			$return['msg'] = 'Fejl - i job_run_interval_minutes.';
		} else if (isset($_REQUEST['job_name']) && $_REQUEST['job_name'] == "") {
			$return['error'] = true;
			$return['msg'] = 'Fejl - Jobnavn.';
		} else if (isset($_REQUEST['estimated_runtime']) && $_REQUEST['estimated_runtime'] == "") {
			$return['error'] = true;
			$return['msg'] = 'Fejl i estimated_runtime.';
		} else if (isset($_REQUEST['script_path']) && $_REQUEST['script_path'] == "") {
			$return['error'] = true;
			$return['msg'] = 'Fejl i Script sti.';
		} else {
			/* New Jobs are always ready for running */
			if (isset($_REQUEST['job_id']) && $_REQUEST['job_id'] == "") {
				$job_approved = 1;
				if ($_REQUEST['job_status_id'] == 3) {
					$job_approved = 0;
				}
				$sql = "INSERT INTO job (job_name, job_approved, job_status_id,  estimated_runtime, script_path, parameters, job_priority, job_run_interval_minutes) VALUES ( ";
				$sql .= ":job_name, :job_approved, :job_status_id, :estimated_runtime, :script_path, :parameters, :job_priority, :job_run_interval_minutes )";
				$status = sql_non_return_query($dbconnection, $sql, array(':job_name' => $_REQUEST['job_name'], ':job_approved' => $job_approved, ':job_status_id' => $_REQUEST['job_status_id'], ':estimated_runtime' => $_REQUEST['estimated_runtime'], ':script_path' => $_REQUEST['script_path'], ':parameters' => $_REQUEST['parameters'], ':job_priority' => $_REQUEST['job_priority'], ':job_run_interval_minutes' => $_REQUEST['job_run_interval_minutes']));
            }
			/* Dont change status for jobs */
			if (isset($_REQUEST['job_id']) && $_REQUEST['job_id'] != "") {
				/* forcing restart of job */
				$job_force_restart = 0;
				$job_approved = 1;
				if ($_REQUEST["job_force_restart"] == "true") {
					$job_force_restart = 1;
				}
				if ($_REQUEST['job_status_id'] == 3) {
					$job_approved = 0;
				}
				$sql = "UPDATE job SET job_approved = :job_approved, job_force_restart = :job_force_restart, job_status_id = :job_status_id, job_name = :job_name, estimated_runtime = :estimated_runtime, script_path = :script_path, parameters = :parameters, job_priority = :job_priority, job_run_interval_minutes = :job_run_interval_minutes, items_mined = 0, nav_current_state_index = '', nav_last_state_index = '', host_name = '' WHERE job_id = :job_id";
				$status = sql_non_return_query($dbconnection, $sql, array(':job_approved' => $job_approved, ':job_force_restart' => $job_force_restart, ':job_status_id' => $_REQUEST['job_status_id'], ':job_name' => $_REQUEST['job_name'], ':estimated_runtime' => $_REQUEST['estimated_runtime'], ':script_path' => $_REQUEST['script_path'], ':parameters' => $_REQUEST['parameters'], ':job_priority' => $_REQUEST['job_priority'], ':job_run_interval_minutes' => $_REQUEST['job_run_interval_minutes'], ':job_id' => $_REQUEST['job_id']));
                }
			
			if ($status <> "") {
				$return['error'] = false;
				$return['msg'] = "Job oprettet/ændret.";

			} else {
				$return['error'] = true;
				$return['msg'] = "Job IKKE oprettet/ændret.";
			}
		}
	} else if ($_REQUEST['get_type'] == "admin_show_status_for_job" ) {
		$return['error'] = false;
		$return['msg'] = sql_return_query($dbconnection,"SELECT created_date, description, items_mined, total_pages_loaded, host_name FROM job_status_log WHERE job_id = ".$_REQUEST['job_id']." ORDER BY created_date desc LIMIT 0, 1000", array());
	
	} else if ($_REQUEST['get_type'] == "admin_delete_job" ) {
		if (isset($_REQUEST['job_id']) && $_REQUEST['job_id'] == "") {
			$return['error'] = true;
			$return['msg'] = 'Fejl i job id.';
		} else {
			$status = sql_non_return_query($dbconnection, "DELETE FROM job_status_log WHERE job_id = :job_id", array(':job_id' => $_REQUEST['job_id']));
			$status = sql_non_return_query($dbconnection, "DELETE FROM job WHERE job_id = :job_id", array(':job_id' => $_REQUEST['job_id']));

			if ($status == "") {
				$return['error'] = false;
				$return['msg'] = 'Job slettet.';
			} else {
				$return['error'] = true;
				$return['msg'] = "Job IKKE slettet.";
			}
		}
	
	} else if ($_REQUEST['get_type'] == "admin_get_job_list" ) {
		$return['error'] = false;
		$return['msg'] = sql_return_query($dbconnection,"SELECT TIMEDIFF(now(),last_run) AS mined_min_ago, TIMEDIFF(now(),DATE_ADD(last_run, INTERVAL job_run_interval_minutes MINUTE)) AS start_run_in, job_force_restart, job_run_interval_minutes, job_name, description, job.job_status_id, job_id, job_priority, estimated_runtime, script_path, items_mined, nav_current_state_index, nav_last_state_index, host_name, job_approved FROM job INNER JOIN job_status ON job_status.job_status_id = job.job_status_id ORDER BY job_name", array());
	} else if ($_REQUEST['get_type'] == "admin_get_search_form_to_edit_job" ) {
		$return['error'] = false;
		$return['msg'] = sql_return_query($dbconnection,"SELECT job_name, job_id, job_run_interval_minutes, estimated_runtime, script_path, parameters, job_priority FROM job WHERE job_id = :job_id", array(':job_id' => $_REQUEST['job_id']));
	
	/* Client Robot servers */
	} else if ($_REQUEST['get_type'] == 'client_get_next_job' ) {
			
		/* 
			Priority 1 robots: (Daily)
			Make sure that we get todays jobs or last run job - and only get one job 
			weekday: -1 no specific day otherwise MySQL format - 1 = Sunday, 2 = Monday, …, 7 = Saturday
			job_status_id: OK to run
			
			Priority 2 robots: (Always run)
			job_status_id: OK to run
			
			job_status_id: 3 = Disabled
		*/
		/* Prioritet 1 bruges ikke pt. */
		if ($_REQUEST['job_priority'] == 1)  {
			$sql = "SELECT * FROM ( ";
			$sql .= "SELECT 1 AS type, last_run, job_id, script_path, parameters FROM job WHERE job_priority = :job_priority AND DAYOFWEEK( now( ) ) = run_at_weekday AND run_at_weekday <> -1 AND DATEDIFF(last_run,now()) < 0 AND job_status_id = 1 ";
			$sql .= "UNION SELECT 2 AS type, last_run, job_id, script_path, parameters FROM job WHERE job_priority = :job_priority AND DAYOFWEEK( now( ) ) <> run_at_weekday AND job_status_id = 1 ORDER BY last_run ASC ";
			$sql .= ") AS T1 ORDER BY type ASC LIMIT 0, 1;";
		/* Kun denne prioritet 2 brruges */
		} else if ($_REQUEST['job_priority'] == 2) {
		/* kør kun hvis ikke kørt inden for den sidste uge */
			$sql = "SELECT last_run, job_id, script_path, parameters FROM job WHERE job_priority = :job_priority AND job_approved = 1 AND job_status_id = 1 AND (TIMESTAMPDIFF(MINUTE , last_run, NOW( ) ) > job_run_interval_minutes OR job_force_restart = 1) ORDER BY last_run ASC LIMIT 0, 1;";
		}
		
		$array = sql_return_query($dbconnection,$sql, array(':job_priority' => $_REQUEST['job_priority']));

		if (sizeof($array) > 0) {
			$xml_output = true;
			for ($i = 0; $i < sizeof($array); $i++) {
				extract($array[$i]);
				sql_non_return_query($dbconnection,"UPDATE job SET last_run = NOW(), job_status_id = 2, job_force_restart = 0 WHERE job_id = :job_id", array(':job_id' => $job_id));
				sql_non_return_query($dbconnection,"INSERT INTO job_status_log ( job_id, description ) VALUES (:job_id, 'Started')", array(':job_id' => $job_id));
				$return = array();
				$return["job_id"] = $job_id;
				$return["script_path"] = $script_path;
				$return["script_params"] = $parameters ;
				$return["crawler_params"] = "--run-mode=crawler";
			}
		} else {
			$xml_output = true;
			$return = array();
			$return["job_id"] = "";
		}
		
	} else if ($_REQUEST['get_type'] == 'client_save_job_status') {
		$description = "";
        
        $job_forced = sql_return_query($dbconnection,"SELECT job_force_restart FROM job WHERE job_id = :job_id", array(":job_id" => $_REQUEST['job_id']));
        
		if ($_REQUEST['mining_done'] == 1) {
			$description = "Finished";
        } else if ($_REQUEST['mining_done'] == 2) {
            $description = "Max page loads reached";
        }
        else if ($_REQUEST['mining_done'] == 3) {
            $description = "Force exit by user";
        }
        else if ($_REQUEST['mining_done'] == 4) {
            $description = "Crawler was killed (process hanging or no network activity)";
        }
        else if ($_REQUEST['mining_done'] == 5) {
            $description = "Crawler crashed";
        }
        else if ($_REQUEST['mining_done'] == 6) {
            $description = "Force stop by user";
        }
        else if ($_REQUEST['mining_done'] == 7) {
            $description = "Continue";
        }
        else if ($_REQUEST['mining_done'] == 8) {
            $description = "Continue after crash";
        }
        else if ($_REQUEST['mining_done'] == 9) {
            $description = "Continue after kill";
        }
		$sql = "INSERT INTO job_status_log ( job_id, description ";
		if (isset($_REQUEST["items_mined"]) && is_numeric($_REQUEST["items_mined"])) {
			$sql .= ", items_mined";
		}
		if (isset($_REQUEST["total_pages_loaded"]) && is_numeric($_REQUEST["total_pages_loaded"])) {
			$sql .= ", total_pages_loaded";
		}
		if (isset($_REQUEST["host_name"])) {
			$sql .= ", host_name";
		}
		$sql .=  ") VALUES ";
		$sql .= " (".$_REQUEST['job_id']." , '".$description."' ";
		if (isset($_REQUEST["items_mined"]) && is_numeric($_REQUEST["items_mined"])) {
			$sql .= ", " . $_REQUEST["items_mined"];
		}
		if (isset($_REQUEST["total_pages_loaded"]) && is_numeric($_REQUEST["total_pages_loaded"])) {
			$sql .= ", " . $_REQUEST["total_pages_loaded"];
		}
		if (isset($_REQUEST["host_name"])) {
			$sql .= ", '" . $_REQUEST["host_name"] . "'";
		}
		$sql .= ")";
        
        sql_non_return_query($dbconnection,$sql, array());
        
		sql_non_return_query($dbconnection,"UPDATE job SET job_status_id = 2, items_mined = :items_mined, nav_current_state_index = :nav_current_state_index, nav_last_state_index = :nav_last_state_index, host_name = :host_name WHERE job_id = :job_id", array(':job_id' => $_REQUEST['job_id'], ':host_name' => $_REQUEST['host_name'], ':items_mined' => 0, ':nav_current_state_index' => $_REQUEST['nav_current_state_index'], ':nav_last_state_index' => $_REQUEST['nav_last_state_index']));
		
		/* Set for queueing */

        if ($_REQUEST['mining_done'] != 0) {
			sql_non_return_query($dbconnection,"UPDATE job SET job_status_id = 1, items_mined = :items_mined, nav_current_state_index = :nav_current_state_index, nav_last_state_index = :nav_last_state_index, host_name = :host_name WHERE job_id = :job_id", array(':job_id' => $_REQUEST['job_id'], ':host_name' => $_REQUEST['host_name'], ':items_mined' => 0, ':nav_current_state_index' => $_REQUEST['nav_current_state_index'], ':nav_last_state_index' => $_REQUEST['nav_last_state_index']));
        }

        $xml_output = true;
        
        if (isset($job_forced[0]["job_force_restart"]) && $job_forced[0]["job_force_restart"] == 1) {
            $return = array();
            $return["job_id"] = $_REQUEST['job_id'];
            $return["command"] = "Kill";
        } else {
            $return = array();
            $return["job_id"] = $_REQUEST['job_id'];
            $return["command"] = "NoCommand";
        }
            
		// Save stats for the job.
	} else {
		$return['error'] = true;
		$return['msg'] = 'Error in "get_type".';
	}
	
	if ($xml_output == false) {
		echo json_encode($return);
	} else {
		echo array_1lvl_to_xml_string($return);
	}
	close_db($dbconnection);
?>