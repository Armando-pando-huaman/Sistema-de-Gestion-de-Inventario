<?php
require_once __DIR__.'/../models/Gestion.php';
class GestionController {
    public function index(){ $this->compras(); }
    public function compras(){ $this->vista('compras'); }
    public function lotes(){ $this->vista('lotes'); }
    public function auditoria(){ $this->vista('auditoria'); }
    public function caja(){ $this->vista('caja'); }
    public function finanzas(){ $this->vista('finanzas'); }
    private function vista(string $seccion): void {
        $db=(new Database())->getConnection();$usuario=(int)$_SESSION['user_id'];$pagina=max(1,(int)($_GET['pagina']??1));
        $proveedores=$db->query('SELECT * FROM proveedores WHERE activo=1 ORDER BY nombre')->fetchAll();
        $productos=$db->query('SELECT id,nombre FROM productos WHERE activo=1 ORDER BY nombre')->fetchAll();
        $filtro=trim((string)($_GET['q']??''));$args=[];
        $productoContexto=null;$productoFiltro=null;
        if($seccion==='lotes'&&isset($_GET['producto_id'])){
            $productoFiltro=filter_var($_GET['producto_id'],FILTER_VALIDATE_INT);
            $s=$db->prepare('SELECT id,nombre FROM productos WHERE id=?');$s->execute([$productoFiltro?:0]);$productoContexto=$s->fetch();
            if(!$productoContexto){http_response_code(404);echo 'Producto no encontrado.';return;}
        }
        switch($seccion){
            case 'compras': $sql='SELECT c.*,p.nombre AS proveedor,u.nombre AS usuario FROM compras c JOIN proveedores p ON p.id=c.proveedor_id LEFT JOIN usuarios u ON u.id=c.usuario_id';break;
            case 'lotes': $sql='SELECT l.*,p.nombre FROM lotes l JOIN productos p ON p.id=l.producto_id WHERE '.($productoFiltro?'l.producto_id=?':'l.stock>0');if($productoFiltro)$args[]=$productoFiltro;if($filtro!==''){$sql.=' AND (p.nombre LIKE ? OR l.codigo LIKE ?)';$args[]='%'.$filtro.'%';$args[]='%'.$filtro.'%';}break;
            case 'auditoria': $sql='SELECT a.*,u.nombre AS usuario FROM auditoria a LEFT JOIN usuarios u ON u.id=a.usuario_id';break;
            case 'caja': $sql='SELECT c.*,u.nombre AS usuario FROM cajas c LEFT JOIN usuarios u ON u.id=c.usuario_id';if($_SESSION['user_rol']!=='admin'){$sql.=' WHERE c.usuario_id=?';$args=[$usuario];}break;
            default: $sql='SELECT g.*,u.nombre AS usuario FROM gastos g LEFT JOIN usuarios u ON u.id=g.usuario_id';break;
        }
        $s=$db->prepare('SELECT COUNT(*) FROM ('.$sql.') listado');$s->execute($args);$total=(int)$s->fetchColumn();$paginas=max(1,(int)ceil($total/30));$pagina=min($pagina,$paginas);$offset=($pagina-1)*30;
        $s=$db->prepare($sql.' ORDER BY 1 DESC LIMIT 30 OFFSET '.$offset);$s->execute($args);$filas=$s->fetchAll();
        $s=$db->prepare('SELECT * FROM cajas WHERE usuario_id=? AND cierre IS NULL ORDER BY id DESC LIMIT 1');$s->execute([$usuario]);$caja=$s->fetch();
        $esperado=$caja?Caja::esperado($db,$caja):0;$movCaja=[];
        if($caja){$s=$db->prepare('SELECT metodo,tipo,SUM(importe) AS total FROM caja_movimientos WHERE caja_id=? GROUP BY metodo,tipo');$s->execute([$caja['id']]);$movCaja=$s->fetchAll();}
        require __DIR__.'/../views/gestion/index.php';
    }
    public function operar(){
        $modelo=new Gestion();$accion=$_POST['accion']??'';$destino='compras';
        try {
            if($accion==='proveedor')$modelo->proveedor($_POST,(int)$_SESSION['user_id']);
            elseif($accion==='compra')$modelo->compra($_POST,(int)$_SESSION['user_id']);
            elseif($accion==='lote'){$destino='lotes';$modelo->verificarLote($_POST,(int)$_SESSION['user_id']);}
            else throw new RuntimeException('Acción inválida.');
            $_SESSION['success']='Operación registrada.';
        }catch(PDOException $e){error_log($e->getMessage());$_SESSION['error']='No se pudo guardar. Comprueba que el documento del proveedor no esté repetido.';}
        catch(RuntimeException $e){$_SESSION['error']=$e->getMessage();}
        $contexto=filter_var($_POST['producto_contexto']??null,FILTER_VALIDATE_INT);
        header('Location: index.php?c=Gestion&a='.$destino.($destino==='lotes'&&$contexto?'&producto_id='.$contexto:''));exit;
    }
    public function operarCaja(){
        try{Caja::operar((new Database())->getConnection(),(int)$_SESSION['user_id'],$_POST);$_SESSION['success']='Operación de caja registrada.';}
        catch(PDOException $e){error_log($e->getMessage());$_SESSION['error']='No se pudo guardar la operación de caja.';}
        catch(RuntimeException $e){$_SESSION['error']=$e->getMessage();}
        header('Location: index.php?c=Gestion&a=caja');exit;
    }
}
