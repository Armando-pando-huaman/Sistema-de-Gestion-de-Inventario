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
                    'tipo_documento'=>$_POST['tipo_documento'] ?? '', 'numero_documento'=>$_POST['numero_documento'] ?? '',
                    'metodo_pago'=>$_POST['metodo_pago']??'efectivo','clave_unica'=>$_SESSION['fv_venta_csrf']
                ]);
                $_SESSION['fv_venta_csrf']=bin2hex(random_bytes(32));
                header('Location: index.php?c=Venta&a=ver&id='.$id);exit;
            } catch (PDOException $e) { error_log($e->getMessage());$_SESSION['error']='No se pudo registrar la venta. Revisa si ya figura en el historial antes de reintentar.'; }
            catch (Throwable $e) { $_SESSION['error']=$e->getMessage(); }
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
    public function historial() {$this->checkAuth();$q=trim((string)($_GET['q']??''));$listado=(new Venta())->pagina($q,(int)($_GET['pagina']??1));$ventas=$listado['filas'];require_once '../views/ventas/historial.php';}
    public function devolver() { $this->reembolso(false); }
    public function anular() { $this->reembolso(true); }
    private function reembolso(bool $anular): void {
        require_once '../models/Devolucion.php';
        $id=Operacion::entero($_POST['id']??null);
        try { Devolucion::registrar((new Database())->getConnection(),$id,(int)$_SESSION['user_id'],$_POST['cantidades']??[],(string)($_POST['motivo']??''),(string)($_POST['metodo_pago']??'efectivo'),$anular,isset($_POST['reintegrar']));$_SESSION['success']='Reembolso registrado. El historial original se conserva.'; }
        catch(PDOException $e){error_log($e->getMessage());$_SESSION['error']='No se pudo registrar el reembolso.';}
        catch(RuntimeException $e){$_SESSION['error']=$e->getMessage();}
        header('Location: index.php?c=Venta&a=ver&id='.$id);exit;
    }
}
