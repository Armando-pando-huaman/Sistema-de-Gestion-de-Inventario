<?php
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off']);
session_start();
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/Seguridad.php';
$routes=[
 'Auth'=>['login','logout'], 'Dashboard'=>['index'], 'Producto'=>['index','crear','editar','eliminar','consultarCodigo','existencias'],
 'Categoria'=>['index','guardar','eliminar'], 'Movimiento'=>['index'], 'Venta'=>['index','ver','historial','devolver','anular'],
 'Usuario'=>['index','crear','editar','eliminar'], 'Reporte'=>['index','ventas','productos','inventario'],
 'Gestion'=>['index','compras','lotes','auditoria','caja','operar','operarCaja','finanzas']
];
$c=is_string($_GET['c']??null)?ucfirst($_GET['c']):'Auth';$action=is_string($_GET['a']??null)?$_GET['a']:($c==='Auth'?'login':'index');
if(!isset($routes[$c])||!in_array($action,$routes[$c],true)){http_response_code(404);exit('Página no encontrada.');}
Seguridad::token();
try{Seguridad::verificar($c,$action);}
catch(Throwable $e){error_log($e->getMessage());http_response_code(503);exit('No se pudo preparar la operación. Contacta al administrador para revisar el servicio.');}
$file=__DIR__.'/../controllers/'.$c.'Controller.php';
if($c==='Usuario')$file=__DIR__.'/../controllers/usuariocontroller.php';
require_once $file;$class=$c.'Controller';
try {(new $class())->$action();}
catch(PDOException $e){error_log($e->getMessage());http_response_code(500);echo 'No se pudo completar la operación. Revisa los datos e inténtalo nuevamente.';}
catch(RuntimeException $e){http_response_code(400);echo htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8');}
