<?php 
// $Id$

/** Checks wheter the \a $sField name can be found the \a $aUpdateFields array. 
Return true if field is found in array or array is null. If Array is not null 
and field is not found false is returned */
function ap_db_field_in_update_fields( $sField, $aUpdFields )
{
    if ( $aUpdateFields == null ) return true;
    return in_array( $sField, $aUpdateFields);
}


/** Get an array of field names which also exists in the current aTitle record and 
with values that are non zero/empty string. So we try each of the field names in order 
and see if we have a matching key in the aTitle and that the value of they key in aTitle 
is valid (non zero/empty string) */
function ap_db_get_valid_fields( $aTitle, $aFieldNames )
{
	$aValidFields = array();
    foreach ( $aFieldNames as $sFieldName )
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


/** Creates a drupal update (associative) array. */
function ap_db_get_update_values( $aTitle, $aFieldNames/*, $aUpdFields*/ )
{
// //     if ( $aUpdFields == "" ) $aUpdFields = $aTitle['do_update'][$sTable];   
    $aValidFieldNames = ap_db_get_valid_fields( $aTitle, $aFieldNames );
    if ( count($aValidFieldNames) == 0 )   return array();
    
//     //  array_walk( $aUpdFields, 'trim_value'); 
        
    $aUpdValues = array();
    $i = 0;
    
    foreach ( $aValidFieldNames as $sField )
    {
// //         if ( ap_db_field_in_update_fields( $sField, $aUpdateFields) ) {
            if  ( stripos( $sField , "timestamp_updated" ) !== false ) {
                $aUpdValues[$sField] = date("Y-m-d H:i:s", time() ); // TODO: Perhaps use UTC?
            }
            else $aUpdValues[$sField] = $aTitle[$sField];
        }
// //     }   
    
    return $aUpdValues;
}




function ap_array_union ( $a, $b )
{	
	return array_merge(
		array_intersect($a, $b),    //         2   4
		array_diff($a, $b),         //       1   3
		array_diff($b, $a)          //               5 6
	);                              //  $u = 1 2 3 4 5 6
}


