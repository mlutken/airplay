<?php
    
require_once ("db_api/SimpleTableDataMySql.php");
// 
/** For storing miner job primary info.*/
class JobDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'job'
        , array(  'job_name', 'created_date', 'last_run', 'record_store_id', 'job_run_interval_minutes'
				, 'job_force_restart', 'job_status_id', 'estimated_runtime', 'script_path', 'parameters'
				, 'job_priority', 'items_mined', 'nav_current_state_index', 'nav_last_state_index', 'host_name'
				, 'job_approved', 'enabled' ) 
        , $dbPDO );
    }

    
}


  

?>