<?php 
// $Id$

/** trims a string in-place. */
function trim_value(&$value) 
{ 
    $value = trim($value); 
}


// function trim_array(&$a) 
// {	
// 	array_walk( $a, 'trim_value');
// }
