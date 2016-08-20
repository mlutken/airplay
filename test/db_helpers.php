<?php


/** Get an update DB string of field names which also exists in the current $aData record and 
with values that are non zero/empty string. So we try each of the field names in order 
and see if we have a matching key in the $aData and that the value of they key in $aData 
is valid (non zero/empty string) 
\return array( sQueryString, aParametersArray ).*/
function pdoGetUpdate( $aData, $aFieldNames )
{
    $s = "";
    $i = 0;
    $aParams = array();
    foreach ( $aFieldNames as $sFieldName )
    {
        $sAddValue = '';
        if ( array_key_exists( $sFieldName, $aData) ) {
            $val = $aData[$sFieldName];
            if ( is_numeric($val) ) {
                if ( $val != 0 ) {
                    $sAddValue = "$sFieldName=?";
                    $aParams[] = $val;
                }
            }
            else if  ( $val != "" ) {
                $sAddValue = "$sFieldName=?";
                $aParams[] = $val;
            }
        }
        else if  ( stripos( $sFieldName , "timestamp_updated" ) !== false ) {
            $sAddValue = "$sFieldName=?";
            $aParams[] = date("Y-m-d H:i:s", time()) . "'"; 
        }
        
        if ( $sAddValue != '' ) {
            if ( $i > 0 ) $s .= ',';
            $s .= $sAddValue;
            $i++;
        }
    }
    
    return array($s, $aParams);
}



// --------------------------------------
// --- PDO Perform doQuery variations ---
// --------------------------------------
function pdoLookupSingleStringQuery($db, $q, $aArgs)
{
    $stmt = $db->prepare($q);
    $stmt->execute( $aArgs );
    $row = $stmt->fetch(PDO::FETCH_NUM);
    if ($row)   return $row[0];
    else return "";
}


function pdoLookupSingleIntQuery($db, $q, $aArgs)
{
    $stmt = $db->prepare($q);
    $stmt->execute( $aArgs );
    $row_count = (int)$stmt->rowCount();
    if ( $row_count > 1 ) return -$row_count;
    $row = $stmt->fetch(PDO::FETCH_NUM);
    if ($row)   return (int)$row[0];
    else return 0;
}


function pdoQueryAllRowsFirstElem($db, $q)
{
    $stmt = $db->query($q);
    $aRes = array();
    while($row = $stmt->fetch(PDO::FETCH_NUM) ) {
        $aRes[] = $row[0];
    }    
    return $aRes;
}


function pdoQueryAssocFirstRow($db, $q)
{
    $stmt = $db->query($q);
    $aRes = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$aRes) return array();
    return $aRes;
}

function pdoQueryAssocRows($db, $q)
{
    $stmt = $db->query($q);
    $aRes = array();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $aRes[] = $row;
    }
    return $aRes;
}



// // // ----------------------------------
// // // --- Perform doQuery variations ---
// // // ----------------------------------
// // function doQueryRows($q)
// // {
// //     $result = mysql_query($q);
// //     if (!$result) return array();
// //     $aRes = array();
// //     while ($row = mysql_fetch_row($result)) {
// //         $aRes[] = $row[0];
// //     }
// //     return $aRes;
// // }
// // 
// // 
// // function doQueryAssocFirstRow($q)
// // {
// //     $result = mysql_query($q);
// //     if (!$result) return array();
// //     return mysql_fetch_assoc($result);
// // }
// // 
// // function doQueryAssocRows($q)
// // {
// //     $result = mysql_query($q);
// //     if (!$result) return array();
// //     $aRes = array();
// //     
// //     while ($row = mysql_fetch_assoc($result)) {
// //         $aRes[] = $row;
// //     }
// //     return $aRes;
// // }

//////////////




?>
