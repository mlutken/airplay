<?php

require_once ('agents/BaseAgent.php');
require_once ('agents/AgentSMS.php');
require_once ('agents/AgentMail.php');

class AgentFactory
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public  function __construct()
    {
    }

    public function createAgent ( $sending_type )
    {
        switch ( $sending_type ) 
        {
            case 'mail'         :   return new AgentMail();
            case 'sms'         :   return new AgentSMS();
            default             :   return null;
        }
    }
}
?>