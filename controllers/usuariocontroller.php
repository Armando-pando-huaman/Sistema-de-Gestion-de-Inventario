<?php
require_once '../models/usuario.php';

class UsuarioController {

    /**
     * Verifica que el usuario esté logueado Y sea administrador.
     * Si no cumple, lo redirige.
     */
    private function checkAdmin() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?c=Auth&a=login");
            exit;
        }
        if ($_SESSION['user_rol'] !== 'admin') {
            $_SESSION['error'] = "No tienes permisos para acceder a esta sección.";
            header("Location: index.php?c=Dashboard&a=index");
            exit;
        }
    }

    /**
     * Lista todos los usuarios
     */
    public function index() {
        $this->checkAdmin();
        $registroHabilitado=!empty((new Botica())->actual()['registro_habilitado']);
        
        $model = new Usuario();
        $usuarios = $model->getAll();
        
        require_once '../views/usuarios/index.php';
    }

    /**
     * Muestra el formulario y procesa la creación de un usuario
     */
    public function crear() {
        $this->checkAdmin();
        if (empty((new Botica())->actual()['registro_habilitado'])) {
            http_response_code(403);
            echo 'El registro de usuarios esta deshabilitado. Puedes configurarlo en Mi botica.';
            return;
        }
        
        $errores = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $rol = 'vendedor';

            // Validaciones
            if (empty($nombre)) $errores[] = "El nombre es obligatorio.";
            if (empty($email)) $errores[] = "El email es obligatorio.";
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = "El email no es válido.";
            if (strlen($password) < 6) $errores[] = "La contraseña debe tener al menos 6 caracteres.";
            if (!in_array($rol, ['admin', 'vendedor'])) $errores[] = "Rol no válido.";

            // Verificar si el email ya existe
            $model = new Usuario();
            if ($model->existeEmail($email)) {
                $errores[] = "El email ya está registrado.";
            }

            if (empty($errores)) {
                if ($model->crear($nombre, $email, $password, $rol)) {
                    $_SESSION['success'] = "Usuario creado correctamente.";
                    header("Location: index.php?c=Usuario&a=index");
                    exit;
                } else {
                    $errores[] = "Error al crear el usuario.";
                }
            }
        }
        
        require_once '../views/usuarios/crear.php';
    }

    /**
     * Muestra el formulario y procesa la edición de un usuario
     */
    public function editar() {
        $this->checkAdmin();
        
        $id = $_GET['id'] ?? 0;
        if (!$id) {
            header("Location: index.php?c=Usuario&a=index");
            exit;
        }

        $model = new Usuario();
        $usuario = $model->getById($id);

        if (!$usuario) {
            $_SESSION['error'] = "Usuario no encontrado.";
            header("Location: index.php?c=Usuario&a=index");
            exit;
        }

        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $rol = $_POST['rol'] ?? 'vendedor';

            // Validaciones
            if (empty($nombre)) $errores[] = "El nombre es obligatorio.";
            if (empty($email)) $errores[] = "El email es obligatorio.";
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = "El email no es válido.";
            if (!empty($password) && strlen($password) < 6) $errores[] = "La contraseña debe tener al menos 6 caracteres.";
            if (!in_array($rol, ['admin', 'vendedor'])) $errores[] = "Rol no válido.";

            // Verificar email duplicado (excluyendo el actual)
            if ($model->existeEmail($email, $id)) {
                $errores[] = "El email ya está registrado por otro usuario.";
            }

            // Evitar que el admin se quite a sí mismo el rol de admin
            if ($_SESSION['user_id'] == $id && $rol !== 'admin') {
                $errores[] = "No puedes quitarte a ti mismo el rol de administrador.";
            }

            if (empty($errores)) {
                if ($model->actualizar($id, $nombre, $email, $rol, $password ?: null)) {
                    $_SESSION['success'] = "Usuario actualizado correctamente.";
                    // Actualizar sesión si es el mismo usuario
                    if ($_SESSION['user_id'] == $id) {
                        $_SESSION['user_nombre'] = $nombre;
                        $_SESSION['user_rol'] = $rol;
                    }
                    header("Location: index.php?c=Usuario&a=index");
                    exit;
                } else {
                    $errores[] = "Error al actualizar el usuario.";
                }
            }
        }
        
        require_once '../views/usuarios/editar.php';
    }

    /**
     * Elimina un usuario
     */
    public function eliminar() {
        $this->checkAdmin();
        
        $id = $_POST['id'] ?? 0;

        if (!$id) {
            header("Location: index.php?c=Usuario&a=index");
            exit;
        }

        // Evitar que el admin se elimine a sí mismo
        if ($_SESSION['user_id'] == $id) {
            $_SESSION['error'] = "No puedes eliminar tu propio usuario.";
            header("Location: index.php?c=Usuario&a=index");
            exit;
        }

        $model = new Usuario();
        if ($model->eliminar($id)) {
            $_SESSION['success'] = "Usuario eliminado correctamente.";
        } else {
            $_SESSION['error'] = "Error al eliminar el usuario.";
        }

        header("Location: index.php?c=Usuario&a=index");
        exit;
    }
}
?>
