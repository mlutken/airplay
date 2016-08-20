<?php

function nextGlobalID( $r )
{
    $id = $r->incr('last_id');
    return $id;
}

function toLookUpName($n)
{
    $ln = mb_strtolower( $n, 'UTF-8' );
//     $ln = str_replace  ( " " , "_" , $ln );
    return $ln;
}



/** Update/insert data fields/values in redis which also exists in the current $aData record and 
with values that are non zero/empty string. So we try each of the field names in order 
and see if we have a matching key in the $aData and that the value of they key in $aData 
is valid (non zero/empty string). 
If the \a $reliabilityField is non empty we check against the \a $oldData array like this:
  - If the old value is empty or zero we can always update it with new.
  - If the old value has data (non empty/non zero) then it is only overwritten with the new 
    if the reliability of the new is at least as good as of the old.
\return True if new data is 'better' than old data.*/
function redisHashSetData( $r, $id, $aData, $aFieldNames, $aDataOld = null, $reliabilityField = "" )
{
   if ( $aDataOld != null && count($aDataOld) != 0 ) {
        $bNewDataBetter = true;
        if ( $reliabilityField != "" ) {
            $reliabilityNew = $aData[$reliabilityField];
            $reliabilityOld = $aDataOld[$reliabilityField];
            
            if ( $reliabilityNew == '' ) $reliabilityNew = 0;  
            if ( $reliabilityOld == '' ) $reliabilityOld = 0;
            
            $bNewDataBetter = $reliabilityNew >= $reliabilityOld;
        }
        
        foreach ( $aFieldNames as $k )
        {
            $v = redisGetAddValue_checkold( $k, $aData, $aDataOld, $bNewDataBetter );
            if ( $v == '' ) continue;
            $r->hset($id . ':d', $k, $v );
        }
    }
    else {
        foreach ( $aFieldNames as $k )
        {
            $v = redisGetAddValue_always( $k, $aData );
            if ( $v == '' ) continue;
            $r->hset($id . ':d', $k, $v );
        }
    }
    return $bNewDataBetter;
}



function redisGetAddValue_checkold( $k, $aData, $aDataOld, $bNewDataBetter )
{
    $v = '';
    if ( array_key_exists($k, $aData) ) {
        $val    = $aData[$k];
        $valOld = $aDataOld[$k];
        if ( $val == $valOld ) return "";
        
        if ( is_numeric($val) ) {
            if ( $val != 0 ) {
                if ( $bNewDataBetter || $valOld == 0 ) {
                    $v = $val;
                }
            }
        }
        else if  ( $val != "" ) {
            if ( $bNewDataBetter || $valOld == "" ) {
                $v = $val;
            }
        }
    }
    else if  ( stripos( $k , "timestamp_updated" ) !== false ) {
        $v = date("Y-m-d H:i:s", time()); 
    }
    return $v;
}


function redisGetAddValue_always( $k, $aData )
{
    $v = '';
    if ( array_key_exists( $k, $aData) ) {
        $val = $aData[$k];
        if ( is_numeric($val) ) {
            if ( $val != 0 ) {
                $v = $val;
            }
        }
        else if  ( $val != "" ) {
            $v = $val;
        }
    }
    else if  ( stripos( $k, "timestamp_updated" ) !== false ) {
        $v = date("Y-m-d H:i:s", time()); 
    }
    return $v;
}


// /** Update/insert data fields/values in redis which also exists in the current $aData record and 
// with values that are non zero/empty string. So we try each of the field names in order 
// and see if we have a matching key in the $aData and that the value of they key in $aData 
// is valid (non zero/empty string) 
// \return void.*/
// function redisHashSetDataSimple( $r, $id, $aData, $aFieldNames )
// {
//     if ($id == 0) return;
// 
//     foreach ( $aFieldNames as $k )
//     {
//         $v = '';
//         if ( array_key_exists( $k, $aData) ) {
//             $val = $aData[$k];
//             if ( is_numeric($val) ) {
//                 if ( $val != 0 ) {
//                     $v = $val;
//                 }
//             }
//             else if  ( $val != "" ) {
//                  $v = $val;
//             }
//         }
//         else if  ( stripos( $k, "timestamp_updated" ) !== false ) {
//             $v = date("Y-m-d H:i:s", time()); 
//         }
//         if ( $v == '' ) continue;
//         $r->hset($id . ':d', $k, $v );
//     }
// }


?>
