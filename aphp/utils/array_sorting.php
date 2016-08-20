<?php

$inventory = 
    array(array("type"=>"fruit", "price"=>3.50),
          array("type"=>"milk", "price"=>2.90),
          array("type"=>"pork", "price"=>5.43),
          );

function pricesort($a, $b) {
  $a = $a['price'];
  $b = $b['price'];
  if ($a == $b) return 0;
  return ($a > $b) ? -1 : 1;
}

usort($inventory, "pricesort");
// uksort($inventory, "pricesort");

print("first: ".$inventory[0]['type']."\n\n");
// for usort(): prints milk (item with lowest price)
// for uksort(): prints fruit (item with key 0 in the original $inventory)

// foreach prints the same for usort and uksort.
foreach($inventory as $i){
  print($i['type'].": ".$i['price']."\n");
}



function invenDescSort($item1,$item2)
{
    if ($item1['price'] == $item2['price']) return 0;
    return ($item1['price'] < $item2['price']) ? 1 : -1;
}
usort($inventory,'invenDescSort');
print_r($inventory);

?>