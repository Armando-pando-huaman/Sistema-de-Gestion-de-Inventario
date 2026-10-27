<?php
require_once '../models/categoria.php';
class CategoriaController {
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?c=Auth&a=login'); exit;
        }
    }
    private function csrf() {
        if (empty($_SESSION['fv_categoria_csrf'])) $_SESSION['fv_categoria_csrf'] = bin2hex(random_bytes(32));
        return $_SESSION['fv_categoria_csrf'];
    }
    private function checkPost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($this->csrf(), (string)($_POST['csrf'] ?? ''))) {
            http_response_code(403); exit('Solicitud no autorizada.');
        }
    }
    public function index() {
        $this->checkAuth();
        $model = new Categoria();
        $categorias = $model->getAll();
        $csrf = $this->csrf();
        $mensaje = $_SESSION['fv_categoria_mensaje'] ?? null;
        unset($_SESSION['fv_categoria_mensaje']);
        require_once '../views/categorias/index.php';
    }
    public function guardar() {
        $this->checkAuth(); $this->checkPost();
        $model = new Categoria();
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $nombre = trim((string)($_POST['nombre'] ?? ''));
        if ($nombre === '' || strlen($nombre) > 100) $mensaje = ['danger', 'El nombre debe tener entre 1 y 100 caracteres.'];
        elseif ($model->existeNombre($nombre, $id)) $mensaje = ['warning', 'Ya existe una categoría con ese nombre.'];
        elseif ($id && !$model->getById($id)) $mensaje = ['danger', 'No se encontró la categoría.'];
        else {
            try {
                $ok = $id ? $model->actualizar($id, $nombre) : $model->crear($nombre);
                $mensaje = $ok ? ['success', $id ? 'Categoría actualizada.' : 'Categoría creada.'] : ['danger', 'No se pudo guardar.'];
            } catch (PDOException $e) { $mensaje = ['danger', 'No se pudo guardar la categoría. Revisa si el nombre ya existe.']; }
        }
        $_SESSION['fv_categoria_mensaje'] = $mensaje;
        header('Location: index.php?c=Categoria&a=index'); exit;
    }
    public function eliminar() {
        $this->checkAuth(); $this->checkPost();
        $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
        $model = new Categoria();
        try {
            $ok = $id > 0 && $model->eliminar($id);
            $mensaje = $ok ? ['success', 'Categoría eliminada.'] : ['warning', 'No se puede eliminar: no existe o tiene productos asociados.'];
        } catch (PDOException $e) { $mensaje = ['danger', 'No se pudo eliminar esta categoría.']; }
        $_SESSION['fv_categoria_mensaje'] = $mensaje;
        header('Location: index.php?c=Categoria&a=index'); exit;
    }
}
