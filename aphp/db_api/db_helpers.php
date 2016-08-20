<?php


/** Get an UPDATE DB string of field names which also exists in the current $aData record and 
with values that are non zero/empty string. So we try each of the field names in order 
and see if we have a matching key in the $aData and that the value of they key in $aData 
is valid (non zero/empty string) 
\return array( sQueryString, aParametersArray ).*/
function pdoGetUpdate( $aData, $aFieldNames, $aDataOld = null, $reliabilityField = "" )
{
    $s = "";
    $i = 0;
    $aParams = array();
    
    if ( $aDataOld != null && count($aDataOld) != 0 ) {
        $bNewDataBetter = true;
        if ( $reliabilityField != "" ) {
            $reliabilityNew = (int)$aData[$reliabilityField];
            $reliabilityOld = (int)$aDataOld[$reliabilityField];
            $bNewDataBetter = $reliabilityNew >= $reliabilityOld;
        }
        
        foreach ( $aFieldNames as $sFieldName )
        {
            $sAddValue = pdoGetAddValue_checkold( $aParams, $sFieldName, $aData, $aDataOld, $bNewDataBetter );
            if ( $sAddValue != '' ) {
                if ( $i > 0 ) $s .= ',';
                $s .= $sAddValue;
                $i++;
            }
        }
    }
    else {
        foreach ( $aFieldNames as $sFieldName )
        {
            $sAddValue = pdoGetAddValue_always( $aParams, $sFieldName, $aData );
            if ( $sAddValue != '' ) {
                if ( $i > 0 ) $s .= ',';
                $s .= $sAddValue;
                $i++;
            }
        }
    }
    return array($s, $aParams);
}


function pdoGetAddValue_checkold( &$aParams, $sFieldName, $aData, $aDataOld, $bNewDataBetter )
{
    $sAddValue = '';
    if ( array_key_exists( $sFieldName, $aData) ) {
        $val    = $aData[$sFieldName];
        $valOld = $aDataOld[$sFieldName];
        if ( $val == $valOld ) return "";
        
        if ( is_numeric($val) ) {
            if ( $val != 0 ) {
                if ( $bNewDataBetter || $valOld == 0 ) {
                    $sAddValue = "$sFieldName=?";
                    $aParams[] = $val;
                }
            }
        }
        else if  ( $val != "" ) {
            if ( $bNewDataBetter || $valOld == "" ) {
                $sAddValue = "$sFieldName=?";
                $aParams[] = $val;
            }
        }
    }
    else if  ( stripos( $sFieldName , "timestamp_updated" ) !== false ) {
        $sAddValue = "$sFieldName=?";
        $aParams[] = date("Y-m-d H:i:s", time()); 
    }
    return $sAddValue;
}


function pdoGetAddValue_always( &$aParams, $sFieldName, $aData )
{
    $sAddValue = '';
    if ( array_key_exists( $sFieldName, $aData) ) {
        $val = $aData[$sFieldName];
		$sAddValue = "$sFieldName=?";
		$aParams[] = $val;
    }
    else if  ( stripos( $sFieldName , "timestamp_updated" ) !== false ) {
        $sAddValue = "$sFieldName=?";
        $aParams[] = date("Y-m-d H:i:s", time());
    }
    return $sAddValue;
}

// function pdoGetAddValue_always( &$aParams, $sFieldName, $aData )
// {
//     $sAddValue = '';
//     if ( array_key_exists( $sFieldName, $aData) ) {
//         $val = $aData[$sFieldName];
//         if ( is_numeric($val) ) {
//             if ( $val != 0 ) {
//                 $sAddValue = "$sFieldName=?";
//                 $aParams[] = $val;
//             }
//         }
//         else if  ( $val != "" ) {
//             $sAddValue = "$sFieldName=?";
//             $aParams[] = $val;
//         }
//     }
//     else if  ( stripos( $sFieldName , "timestamp_updated" ) !== false ) {
//         $sAddValue = "$sFieldName=?";
//         $aParams[] = date("Y-m-d H:i:s", time());
//     }
//     return $sAddValue;
// }


/** Get an INSERT DB string of field names which also exists in the current $aData record and 
with values that are non zero/empty string. So we try each of the field names in order 
and see if we have a matching key in the $aData and that the value of they key in $aData 
is valid (non zero/empty string) 
\return array( sQueryString, aParametersArray ).*/
function pdoGetInsert( $aData, $aFieldNames )
{
    $s              = '';
    $sFields        = '';
    $sQuestionMarks = '';
    $i              = 0;
    $aParams = array();
    
    foreach ( $aFieldNames as $sFieldName )
    {
        $sAddValue = '';
        if ( array_key_exists( $sFieldName, $aData) ) {
            $val = $aData[$sFieldName];
            if ( is_numeric($val) ) {
                if ( $val != 0 ) {
                    $sAddValue = $sFieldName;
                    $aParams[] = $val;
                }
            }
            else if  ( $val != "" ) {
                $sAddValue = $sFieldName;
                $aParams[] = $val;
            }
        }
        else if  ( stripos( $sFieldName , "timestamp_updated" ) !== false ) {
            $sAddValue = $sFieldName;
            $aParams[] = date("Y-m-d H:i:s", time()); 
        }
    
        if ( $sAddValue != '' ) {
            if ( $i > 0 ) {
                $sFields        .= ',';
                $sQuestionMarks .= ',';
            }
            $sFields        .= $sAddValue;
            $sQuestionMarks .= '?';
            $i++;
        }
    }
    if ( $sFields != '' && $sQuestionMarks != '' ) {
        $s = '(' . $sFields . ') VALUES (' . $sQuestionMarks . ')'; 
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
    if ( $row_count > 1 ) {
		return -$row_count;
    }
    $row = $stmt->fetch(PDO::FETCH_NUM);
    if ($row)   return (int)$row[0];
    else return 0;
}

function pdoQueryAllRowsFirstElemAsInt($db, $q, $aArgs = array() )
{
    $stmt = $db->prepare($q);
    $stmt->execute( $aArgs );
    $aRes = array();
    while($row = $stmt->fetch(PDO::FETCH_NUM) ) {
        $aRes[] = (int)$row[0];
    }    
    return $aRes;
}


function pdoQueryAllRowsFirstElem($db, $q, $aArgs = array() )
{
    $stmt = $db->prepare($q);
    $stmt->execute( $aArgs );
    $aRes = array();
    while($row = $stmt->fetch(PDO::FETCH_NUM) ) {
        $aRes[] = $row[0];
    }    
    return $aRes;
}


function pdoQueryAssocFirstRow($db, $q, $aArgs = array() )
{
    $stmt = $db->prepare($q);
    $stmt->execute( $aArgs );
    $aRes = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$aRes) return array();
    return $aRes;
}

function pdoQueryAssocRows($db, $q, $aArgs = array() )
{
// //     debug_print_backtrace();
    $stmt = $db->prepare($q);
    $stmt->execute( $aArgs );
    $aRes = array();
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $aRes[] = $row;
    }
    return $aRes;
}




?>