<?php
if(PHP_SAPI!=='cli'||!preg_match('/^fv_test_[a-f0-9]{12}$/',getenv('DB_NAME')?:''))exit(2);
require __DIR__.'/../config/database.php';
require __DIR__.'/../models/Venta.php';
$_SESSION=['user_id'=>(int)$argv[2],'user_rol'=>'vendedor'];
$limite=microtime(true)+10;
while(!is_file($argv[3])&&microtime(true)<$limite)usleep(10000);
try{(new Venta())->registrar('Concurrencia',(int)$argv[2],[['id'=>(int)$argv[1],'cantidad'=>1,'presentacion'=>'unidad']]);echo 'OK';}
catch(RuntimeException $e){echo 'RECHAZADA';}
