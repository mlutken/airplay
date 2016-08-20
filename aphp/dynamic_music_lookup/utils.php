<?php
/*
    Convert recordstore URL Parameter to recordstorename
*/
function getRecordStoreNameFromURL ()
{
    $record_store_name = "";
    if (isset($_REQUEST["s"])) { 
        $record_store_name = strtolower($_REQUEST["s"]);
    }
    return $record_store_name;
}

?>