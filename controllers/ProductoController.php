<?php
require_once '../models/Producto.php';
class ProductoController {
    private function checkAuth() { if (!isset($_SESSION['user_id'])) { header('Location: index.php?c=Auth&a=login');exit; } }
    private function categorias() { return (new Database())->getConnection()->query('SELECT id,nombre FROM categorias ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC); }
    private function integer($name,$min=0) { $v=filter_var($_POST[$name]??null,FILTER_VALIDATE_INT);if ($v===false || $v<$min) throw new RuntimeException('Cantidad inválida: '.$name);return $v; }
    private function price($name,$required=true) { $v=trim((string)($_POST[$name]??''));if (!$required && $v==='') return null;if (!preg_match('/^\d{1,9}(?:\.\d{1,2})?$/',$v)) throw new RuntimeException('Precio inválido: '.$name);return $v; }
    private function payload($isEdit=false,$old=null) {
        $frac=($_POST['es_fraccionable']??'0')==='1';
        $stock=$this->integer('stock');$min=$this->integer('stock_minimo');
        $blisters=$frac?$this->integer('blisters_por_caja',1):1;
        $units=$frac?$this->integer('unidades_por_blister',1):1;
        $box=$blisters*$units;
        if ($box>1000000) throw new RuntimeException('Demasiadas unidades por caja.');
        $pu=$frac?$this->price('precio_unidad'):null;
        $pb=$frac?$this->price('precio_blister'):null;
        $pc=$frac?$this->price('precio_caja'):$this->price('precio_venta');
        if ($isEdit && $old && (int)$old['stock']>0 && ((int)($old['es_fraccionable']??0)!==(int)$frac || (int)($old['unidades_por_caja']??1)!==$box || (int)($old['unidades_por_blister']??1)!==$units)) {
            throw new RuntimeException('No se puede cambiar la equivalencia con stock existente. Ajusta y concilia el inventario primero.');
        }
        $data=[
          'codigo_barras'=>trim((string)($_POST['codigo_barras']??'')) ?: null,
          'nombre'=>trim((string)($_POST['nombre']??'')),
          'descripcion'=>trim((string)($_POST['descripcion']??'')),
          'precio_compra'=>$this->price('precio_compra'),
          'precio_venta'=>$pc,
          'stock'=>$isEdit?$stock:($frac?$stock*$box:$stock),
          'stock_minimo'=>$min,
          'fecha_vencimiento'=>($_POST['fecha_vencimiento']??'') ?: null,
          'categoria_id'=>($_POST['categoria_id']??'')!==''?(int)$_POST['categoria_id']:null,
          'es_fraccionable'=>(int)$frac,
          'blisters_por_caja'=>$blisters,
          'unidades_por_blister'=>$units,
          'unidades_por_caja'=>$box,
          'precio_unidad'=>$pu,'precio_blister'=>$pb,'precio_caja'=>$frac?$pc:null
        ];
        if ($data['nombre']==='') throw new RuntimeException('Ingresa el nombre.');
        return $data;
    }
    public function index() { $this->checkAuth();$productos=(new Producto())->getAll();require '../views/productos/index.php'; }
    public function crear() {
      $this->checkAuth();$categorias=$this->categorias();
      if ($_SERVER['REQUEST_METHOD']==='POST') try { (new Producto())->crear($this->payload());header('Location: index.php?c=Producto&a=index');exit; } catch(Throwable $e) {$error=$e->getMessage();}
      require '../views/productos/crear.php';
    }
    public function editar() {
      $this->checkAuth();$model=new Producto();$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);$producto=$model->getById($id);
      if (!$producto) {http_response_code(404);echo 'Producto no encontrado';return;}
      $categorias=$this->categorias();
      if ($_SERVER['REQUEST_METHOD']==='POST') try { $d=$this->payload(true,$producto);$d['id']=$id;$model->actualizar($d);header('Location: index.php?c=Producto&a=index');exit; } catch(Throwable $e) {$error=$e->getMessage();}
      require '../views/productos/editar.php';
    }
    public function eliminar() { $this->checkAuth();$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);(new Producto())->eliminar($id);header('Location: index.php?c=Producto&a=index');exit; }
}
