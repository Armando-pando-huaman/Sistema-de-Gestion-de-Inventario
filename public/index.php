<?php
chdir(__DIR__);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__.'/../config/php-error.log');
error_reporting(E_ALL);
set_exception_handler(static function (Throwable $e): void {
    error_log((string)$e);
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    echo 'No se pudo iniciar el sistema. Revisa el registro config/php-error.log desde el administrador de archivos.';
});
session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off']);
session_start();
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../config/seguridad.php';
$routes=[
 'Auth'=>['login','logout','registro'], 'Cuenta'=>['perfil','negocio'], 'Dashboard'=>['index'], 'Producto'=>['index','crear','editar','eliminar','consultarCodigo'],
 'Categoria'=>['index','guardar','eliminar'], 'Movimiento'=>['index'], 'Venta'=>['index','ver','historial','devolver','anular'],
 'Usuario'=>['index','crear','editar','eliminar'], 'Reporte'=>['index','ventas','productos','inventario','descargar'],
 'Documentacion'=>['index'],
 'Gestion'=>['index','compras','lotes','auditoria','caja','operar','operarCaja','finanzas']
];
$c=is_string($_GET['c']??null)?ucfirst($_GET['c']):'Auth';$action=is_string($_GET['a']??null)?$_GET['a']:($c==='Auth'?'login':'index');
if(!isset($routes[$c])||!in_array($action,$routes[$c],true)){http_response_code(404);exit('Página no encontrada.');}
Seguridad::token();
try{Seguridad::verificar($c,$action);}
catch(Throwable $e){error_log($e->getMessage());http_response_code(503);exit('No se pudo preparar la operación. Contacta al administrador para revisar el servicio.');}
$file=__DIR__.'/../controllers/'.strtolower($c).'controller.php';
require_once $file;$class=$c.'Controller';
try {(new $class())->$action();}
catch(PDOException $e){error_log($e->getMessage());http_response_code(500);echo 'No se pudo completar la operación. Revisa los datos e inténtalo nuevamente.';}
catch(RuntimeException $e){http_response_code(400);echo htmlspecialchars($e->getMessage(),ENT_QUOTES,'UTF-8');}
