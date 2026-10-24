<?php
class Seguridad {
    public static function token(): string { return $_SESSION['csrf_global'] ?? ($_SESSION['csrf_global']=bin2hex(random_bytes(32))); }
    public static function verificar(string $controlador,string $accion): void {
        $post=($_SERVER['REQUEST_METHOD']??'GET')==='POST';
        if($post){
            $token=$_POST['csrf']??null;$valido=false;
            foreach(['csrf_global','fv_venta_csrf','fv_categoria_csrf','mov_csrf'] as $key) if(is_string($token)&&isset($_SESSION[$key])&&hash_equals($_SESSION[$key],$token))$valido=true;
            if(!$valido){http_response_code(403);exit('Formulario vencido. Recarga la página.');}
        }
        if($controlador==='Auth' && in_array($accion,['login','registro'],true))return;
        if(!isset($_SESSION['user_id'])){header('Location: index.php?c=Auth&a=login');exit;}
        $db=(new Database())->getConnection();$s=$db->prepare('SELECT activo,rol FROM usuarios WHERE id=?');$s->execute([$_SESSION['user_id']]);$usuario=$s->fetch();
        if(!$usuario || !$usuario['activo']){session_unset();header('Location: index.php?c=Auth&a=login');exit;}
        $_SESSION['user_rol']=$usuario['rol'];
        $admin=in_array($controlador,['Reporte','Usuario','Gestion'],true)
            || ($controlador==='Producto'&&$accion!=='index')
            || ($controlador==='Categoria'&&$accion!=='index')
            || ($controlador==='Movimiento'&&$post);
        if($controlador==='Gestion' && in_array($accion,['caja','operarCaja'],true))$admin=false;
        if($controlador==='Venta' && in_array($accion,['devolver','anular'],true))$admin=true;
        if($controlador==='Cuenta' && $accion==='negocio')$admin=true;
        if($admin&&$usuario['rol']!=='admin'){http_response_code(403);exit('Esta operación requiere un administrador.');}
        if(in_array($accion,['eliminar','guardar','devolver','anular','operar','operarCaja','logout'],true)&&!$post){http_response_code(405);header('Allow: POST');exit('Esta acción requiere un formulario POST.');}
    }
}
