<?php
require_once '../models/Venta.php';
require_once '../models/Producto.php';
class VentaController {
    private function checkAuth() { if (!isset($_SESSION['user_id'])) {header('Location: index.php?c=Auth&a=login');exit;} }
    public function index() {
        $this->checkAuth();
        if (empty($_SESSION['fv_venta_csrf'])) $_SESSION['fv_venta_csrf']=bin2hex(random_bytes(32));
        $ventaModel = new Venta();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals($_SESSION['fv_venta_csrf'],(string)($_POST['csrf'] ?? ''))) { $_SESSION['error']='Sesión de formulario inválida.';header('Location: index.php?c=Venta&a=index');exit; }
            try {
                $carrito=json_decode((string)($_POST['carrito'] ?? '[]'),true,512,JSON_THROW_ON_ERROR);
                $id=$ventaModel->registrar(trim((string)($_POST['cliente'] ?? 'Cliente General')),(int)$_SESSION['user_id'],$carrito,[
                    'telefono'=>$_POST['telefono'] ?? '', 'tipo_comprobante'=>$_POST['tipo_comprobante'] ?? 'ticket',
                    'tipo_documento'=>$_POST['tipo_documento'] ?? '', 'numero_documento'=>$_POST['numero_documento'] ?? ''
                ]);
                $_SESSION['fv_venta_csrf']=bin2hex(random_bytes(32));
                header('Location: index.php?c=Venta&a=ver&id='.$id);exit;
            } catch (Throwable $e) { $_SESSION['error']=$e->getMessage(); }
        }
        $productos=(new Producto())->getAll(); $ventas=$ventaModel->getAll();
        require_once '../views/ventas/index.php';
    }
    public function ver() {
        $this->checkAuth(); $id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
        if (!$id) {http_response_code(404);echo 'Venta no encontrada';return;}
        $m=new Venta();$venta=$m->getById($id);
        if (!$venta) {http_response_code(404);echo 'Venta no encontrada';return;}
        $detalle=$m->getDetalle($id); require_once '../views/ventas/ver.php';
    }
    public function historial() {$this->checkAuth();$ventas=(new Venta())->getAll();require_once '../views/ventas/historial.php';}
}
