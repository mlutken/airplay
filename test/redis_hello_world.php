<?php

printf("Hello redis 1\n");

$r = new Redis();
printf("Hello redis 2\n");

$r->connect("127.0.0.1");
$r->set("hello", "world");
printf( "GET hello: %s\n", $r->get("hello") );

$date1 = date("Y-m-d H:i:s", time());
printf ( "date: %s\n", $date1);


?>
