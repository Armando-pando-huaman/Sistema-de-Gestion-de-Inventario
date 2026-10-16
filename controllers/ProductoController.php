<?php
require_once '../models/Producto.php';
require_once '../models/Categoria.php';

class ProductoController {
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?c=Auth&a=login");
            exit;
        }
    }

    public function index() {
        $this->checkAuth();
        $model = new Producto();
        $productos = $model->getAll();
        require_once '../views/productos/index.php';
    }

    public function crear() {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                ':codigo' => $_POST['codigo_barras'],
                ':nombre' => $_POST['nombre'],
                ':descripcion' => $_POST['descripcion'],
                ':p_compra' => $_POST['precio_compra'],
                ':p_venta' => $_POST['precio_venta'],
                ':stock' => $_POST['stock'],
                ':stock_min' => $_POST['stock_minimo'],
                ':fecha_venc' => $_POST['fecha_vencimiento'] ?: null,
                ':categoria' => (($_POST['categoria_id'] ?? '') !== '' ? (int)$_POST['categoria_id'] : null)
            ];
            $model = new Producto();
            if ($model->crear($data)) {
                header("Location: index.php?c=Producto&a=index");
                exit;
            }
        }
        $categorias = (new Categoria())->getAll();
        require_once '../views/productos/crear.php';
    }

    public function editar() {
        $this->checkAuth();
        $model = new Producto();
        $id = $_GET['id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                ':codigo' => $_POST['codigo_barras'],
                ':nombre' => $_POST['nombre'],
                ':descripcion' => $_POST['descripcion'],
                ':p_compra' => $_POST['precio_compra'],
                ':p_venta' => $_POST['precio_venta'],
                ':stock' => $_POST['stock'],
                ':stock_min' => $_POST['stock_minimo'],
                ':fecha_venc' => $_POST['fecha_vencimiento'] ?: null,
                ':categoria' => (($_POST['categoria_id'] ?? '') !== '' ? (int)$_POST['categoria_id'] : null),
                ':id' => $id
            ];
            $model->actualizar($data);
            header("Location: index.php?c=Producto&a=index");
            exit;
        }

        $producto = $model->getById($id);
        if (!$producto) { http_response_code(404); exit('Producto no encontrado.'); }
        $categorias = (new Categoria())->getAll();
        require_once '../views/productos/editar.php';
    }

    public function eliminar() {
        $this->checkAuth();
        $id = $_GET['id'] ?? 0;
        $model = new Producto();
        $model->eliminar($id);
        header("Location: index.php?c=Producto&a=index");
        exit;
    }
}
?>