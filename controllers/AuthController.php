<?php
require_once '../models/usuario.php';
require_once '../models/Botica.php';
require_once '../config/Imagen.php';

class AuthController {
    public function login() {
        // Si ya está logueado, redirigir al dashboard
        if (isset($_SESSION['user_id'])) {
            header("Location: index.php?c=Dashboard&a=index");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = "Por favor completa todos los campos.";
            } else {
                $usuarioModel = new Usuario();
                try {
                    $user = $usuarioModel->login($email, $password);
                } catch (AccesoBloqueado $e) {
                    http_response_code(429);
                    header('Retry-After: '.$e->espera);
                    $error = $e->getMessage();
                    $registroDisponible=(new Botica())->permiteRegistro();
                    require '../views/auth/login.php';
                    return;
                }

                if ($user) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_nombre'] = $user['nombre'];
                    $_SESSION['user_rol'] = $user['rol'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_foto'] = $user['foto'] ?? null;
                    header("Location: index.php?c=Dashboard&a=index");
                    exit;
                } else {
                    $error = "Correo o contraseña incorrectos.";
                }
            }
        }
        $registroDisponible=(new Botica())->permiteRegistro();
        require_once '../views/auth/login.php';
    }

    public function registro(): void {
        if (isset($_SESSION['user_id'])) { header('Location: index.php?c=Cuenta&a=perfil'); exit; }
        $modelo=new Botica(); $botica=$modelo->actual();
        if (!$modelo->permiteRegistro()) { http_response_code(403); require '../views/auth/registro-cerrado.php'; return; }
        $error=null;
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $foto=null; $logo=null;
            try {
                $foto=Imagen::guardar($_FILES['foto'] ?? [],'perfil');
                if (!$botica) $logo=Imagen::guardar($_FILES['logo'] ?? [],'logo');
                $modelo->registrar($_POST,$foto,$logo);
                $_SESSION['registro_exito']='Cuenta creada. Ya puedes iniciar sesion.';
                header('Location: index.php?c=Auth&a=login'); exit;
            } catch (Throwable $e) {
                Imagen::eliminar($foto); Imagen::eliminar($logo); error_log($e->getMessage());
                $error=$e instanceof RuntimeException && !($e instanceof PDOException) ? $e->getMessage() : 'No se pudo registrar la cuenta. Verifica el correo e intenta nuevamente.';
                $botica=$modelo->actual();
                if (!$modelo->permiteRegistro()) { http_response_code(403); require '../views/auth/registro-cerrado.php'; return; }
            }
        }
        require '../views/auth/registro.php';
    }

    public function logout() {
        session_unset();
        session_destroy();
        header("Location: index.php?c=Auth&a=login");
        exit;
    }
}
?>
