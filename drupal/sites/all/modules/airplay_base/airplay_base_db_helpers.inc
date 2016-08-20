<?php 
// $Id$

/** Get an array of field names which also exists in the current aTitle record and 
with values that are non zero/empty string. So we try each of the field names in order 
and see if we have a matching key in the aTitle and that the value of they key in aTitle 
is valid (non zero/empty string) */
function ap_db_get_valid_fields( $aTitle, $aFieldNames )
{
	$aValidFields = array();
	foreach ( $aFieldNames as $sFieldName => $notUsed )
	{
		if ( array_key_exists( $sFieldName, $aTitle) ) {
			$val = $aTitle[$sFieldName];
			if ( is_numeric($val) ) {
				if ( $val == 0 ) 	continue;
			}
			else if ( is_string($val) ) {
				if 	( $val == "" ) 	continue;
			}
		}
        else if  ( stripos( $sFieldName , "timestamp_updated" ) !== false ) {
            $aValidFields[] = $sFieldName;
            continue;
		}
		else continue;
		$aValidFields[] = $sFieldName;
	}
	
	return $aValidFields;
}


/** Creates an drupal sql (ie. for use with db_query ) string for updating 
fields in a table. Only the WHERE clause is omitted. It is easier to append 
this after. */
function ap_db_create_update_string( $aTitle, $sTable, $aFieldsOrder,  $aUpdFields = "" )
{
	if ( $aUpdFields == "" ) $aUpdFields = $aTitle['do_update'][$sTable];	
	
// 	$aUpdateFields = array();
// 	// Only update if field exist in aTitle and the corresponding 'incoming' value is non zero/empty string
// 	foreach ( $aFieldsOrder as $sField => $sFormatString )
// 	{
// 		if ( array_key_exists( $sField, $aTitle) ) {
// 			$val = $aTitle[$sField];
// 			if ( is_numeric($val) ) {
// 				if ( $val == 0 ) 	continue;
// 			}
// 			else if ( is_string($val) ) {
// 				if 	( $val == "" ) 	continue;
// 			}
// 		}
// 		else continue;
// 		$aUpdateFields[] = $sField;
// 	}	

	$aUpdateFields = ap_db_get_valid_fields( $aTitle, $aFieldsOrder );
	
	if ( count($aUpdateFields) == 0 )	return null;

	
	
	// 	array_walk( $aUpdFields, 'trim_value');	
		
	$aArgs = array();
	$i = 0;
//  	dfb ($aUpdFields, "update song aUpdFields"  );
// 	dfb ( $aTitle['song_id'], "update song" );
// 	dfb ( $aFieldsOrder, "aFieldsOrder" );
	
	$sQuery = "UPDATE {" . $sTable . "}";
	$sQuery .= " SET ";
	foreach ( $aFieldsOrder as $sField => $sFormatString )
	{
 		//echo ("$sField : $sFormatString\n"); 
		if ( in_array( $sField, $aUpdateFields) ) {
			if 	( stripos( $sField , "timestamp_updated" ) !== false ) {
				$sFormatString = "NOW()";
			}
			else $aArgs[] = $aTitle[$sField];
			$sQuery .= " $sField=$sFormatString";
			if ( ++$i < count($aUpdateFields) )	$sQuery .= ", ";
		}
	}	
	
	//dfb ("update song query: $sQuery" );
	return array( 'query' => $sQuery, 'args' => $aArgs );
}

function ap_array_union ( $a, $b )
{	
	return array_merge(
		array_intersect($a, $b),    //         2   4
		array_diff($a, $b),         //       1   3
		array_diff($b, $a)          //               5 6
	);                              //  $u = 1 2 3 4 5 6
}


