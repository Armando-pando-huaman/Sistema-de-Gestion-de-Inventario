<?php
require_once '../models/usuario.php';

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
                $user = $usuarioModel->login($email, $password);

                if ($user) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_nombre'] = $user['nombre'];
                    $_SESSION['user_rol'] = $user['rol'];
                    $_SESSION['user_email'] = $user['email'];
                    header("Location: index.php?c=Dashboard&a=index");
                    exit;
                } else {
                    $error = "Correo o contraseña incorrectos.";
                }
            }
        }
        require_once '../views/auth/login.php';
    }

    public function logout() {
        session_unset();
        session_destroy();
        header("Location: index.php?c=Auth&a=login");
        exit;
    }
}
?>
