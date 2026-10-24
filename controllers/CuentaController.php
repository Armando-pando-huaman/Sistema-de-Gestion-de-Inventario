<?php
require_once '../models/Botica.php';
require_once '../models/Usuario.php';
require_once '../config/Imagen.php';
class CuentaController {
    public function perfil(): void {
        $modelo=new Botica(); $error=null;
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $foto=null;
            try {
                $foto=Imagen::guardar($_FILES['foto'] ?? [],'perfil');
                $nombre=Operacion::texto($_POST['nombre'] ?? '',100);
                $anterior=$modelo->guardarPerfil((int)$_SESSION['user_id'],$nombre,$foto);
                $_SESSION['user_nombre']=$nombre;
                if ($foto) { $_SESSION['user_foto']=$foto; Imagen::eliminar($anterior); }
                $_SESSION['success']='Perfil actualizado.';
                header('Location: index.php?c=Cuenta&a=perfil'); exit;
            } catch (Throwable $e) { Imagen::eliminar($foto); error_log($e->getMessage()); $error=$e instanceof RuntimeException && !($e instanceof PDOException) ? $e->getMessage() : 'No se pudo guardar el perfil.'; }
        }
        $usuario=(new Usuario())->getById($_SESSION['user_id']);
        require '../views/cuenta/perfil.php';
    }
    public function negocio(): void {
        $modelo=new Botica(); $error=null;
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            $logo=null;
            try {
                $logo=Imagen::guardar($_FILES['logo'] ?? [],'logo');
                $anterior=$modelo->configurar((int)$_SESSION['user_id'],(string)($_POST['nombre'] ?? ''),isset($_POST['registro_habilitado']),$logo);
                if ($logo) Imagen::eliminar($anterior);
                $_SESSION['success']='Configuracion de la botica actualizada.';
                header('Location: index.php?c=Cuenta&a=negocio'); exit;
            } catch (Throwable $e) { Imagen::eliminar($logo); error_log($e->getMessage()); $error=$e instanceof RuntimeException && !($e instanceof PDOException) ? $e->getMessage() : 'No se pudo guardar la botica.'; }
        }
        $botica=$modelo->actual();
        require '../views/cuenta/negocio.php';
    }
}
