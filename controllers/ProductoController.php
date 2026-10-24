<?php
require_once '../models/Producto.php';
class ProductoController {
    private function checkAuth() { if (!isset($_SESSION['user_id'])) { header('Location: index.php?c=Auth&a=login');exit; } }
    private function categorias() { return (new Database())->getConnection()->query('SELECT id,nombre FROM categorias ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC); }
    private function integer($name,$min=0) { $v=filter_var($_POST[$name]??null,FILTER_VALIDATE_INT);if ($v===false || $v<$min) throw new RuntimeException('Cantidad inválida: '.$name);return $v; }
    private function price($name,$required=true) { $v=trim((string)($_POST[$name]??''));if (!$required && $v==='') return null;if (!preg_match('/^\d{1,9}(?:\.\d{1,2})?$/',$v)) throw new RuntimeException('Precio inválido: '.$name);return $v; }
    private function payload($isEdit=false,$old=null) {
        $frac=($_POST['es_fraccionable']??'0')==='1';
        $incluir=!$isEdit&&($_POST['incluir_existencias']??'')==='1';
        $stock=$isEdit||array_key_exists('stock',$_POST)?$this->integer('stock'):0;$min=$this->integer('stock_minimo');
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
          'precio_compra'=>$isEdit?($old['precio_compra']??'0.00'):'0.00',
          'precio_venta'=>$pc,
          'stock'=>$isEdit||!$incluir?$stock:0,
          'stock_minimo'=>$min,
          'fecha_vencimiento'=>($_POST['fecha_vencimiento']??'') ?: null,
          'categoria_id'=>($_POST['categoria_id']??'')!==''?(int)$_POST['categoria_id']:null,
          'es_fraccionable'=>(int)$frac,
          'blisters_por_caja'=>$blisters,
          'unidades_por_blister'=>$units,
          'unidades_por_caja'=>$box,
          'precio_unidad'=>$pu,'precio_blister'=>$pb,'precio_caja'=>$frac?$pc:null,
          'nombre_unidad'=>Operacion::texto($_POST['nombre_unidad']??'Unidad',30),
          'nombre_paquete'=>Operacion::texto($_POST['nombre_paquete']??'Paquete / blíster',30),
          'nombre_caja'=>Operacion::texto($_POST['nombre_caja']??'Caja',30)
        ];
        $data['nombre']=Operacion::texto($data['nombre'],150);
        if(!$isEdit&&!$incluir&&$stock>0){
          $costo=$this->price('costo_inicial',false);
          $data['precio_compra']=$costo===null?'0.00':number_format((float)$costo*$box,2,'.','');
        }
        $data['fecha_vencimiento']=Operacion::fecha($data['fecha_vencimiento']);
        if($isEdit&&isset($_POST['vencimientos_lotes'])){
          if(!is_array($_POST['vencimientos_lotes']))throw new RuntimeException('Fechas de lotes invalidas.');
          $data['vencimientos_lotes']=$_POST['vencimientos_lotes'];
        }
        if(!$isEdit){$lote=trim((string)($_POST['lote_inicial']??''));$data['lote_inicial']=$lote===''?null:Operacion::texto($lote,80);}
        if($incluir){
          if($stock<1)throw new RuntimeException('Indica un stock inicial mayor a cero para registrar el documento de compra.');
          $data['lote_inicial']=Operacion::texto($_POST['lote_inicial']??'',80);
          $documento=trim((string)($_POST['documento_inicial']??''));
          if(($_POST['crear_proveedor']??'')!=='1'&&$documento==='')$documento='ING-'.date('Ymd').'-'.bin2hex(random_bytes(8));
          $initial=['proveedor_id'=>($_POST['crear_proveedor']??'')==='1'?0:Operacion::entero($_POST['proveedor_id']??0),'documento'=>Operacion::texto($documento,80),'cantidad'=>Operacion::entero($stock),'costo_unidad'=>$this->price('costo_inicial')];
          if(($_POST['crear_proveedor']??'')==='1'){$initial['nuevo_proveedor']=['nombre'=>Operacion::texto($_POST['proveedor_nombre']??''),'documento'=>trim((string)($_POST['proveedor_documento']??'')),'telefono'=>trim((string)($_POST['proveedor_telefono']??''))];}
          $data['existencias_iniciales']=$initial;
        }
        return $data;
    }
    public function index() { $this->checkAuth();$busqueda=trim((string)($_GET['q']??''));$inactivos=($_GET['inactivos']??'')==='1';$listado=(new Producto())->pagina($busqueda,(int)($_GET['pagina']??1),$inactivos);$productos=$listado['filas'];require '../views/productos/index.php'; }
    public function crear() {
      $this->checkAuth();$categorias=$this->categorias();
      $proveedores=(new Database())->getConnection()->query('SELECT id,nombre FROM proveedores WHERE activo=1 ORDER BY nombre')->fetchAll();
      if ($_SERVER['REQUEST_METHOD']==='POST') try {
        (new Producto())->crear($this->payload());header('Location: index.php?c=Producto&a=index');exit;
      } catch(PDOException $e){
        error_log($e->getMessage());
        if((int)($e->errorInfo[1]??0)===1062){
          $error=str_contains($e->getMessage(),'codigo_barras')
            ? 'Este codigo de barras ya esta registrado. Busca el producto y registra la entrada desde Movimientos; no crees otra ficha con el mismo codigo.'
            : 'Este documento de compra ya esta registrado para el proveedor seleccionado. Revisa Entradas de productos y utiliza el documento real que corresponda.';
        }else $error='No se pudo guardar el producto. Revisa los datos ingresados.';
      }catch(RuntimeException $e) {$error=$e->getMessage();}
      require '../views/productos/crear.php';
    }
    public function consultarCodigo() {
      $this->checkAuth();header('Content-Type: application/json; charset=UTF-8');header('Cache-Control: no-store');
      $codigo=$_GET['codigo']??'';
      if(!is_string($codigo)||strlen($codigo)>50||trim($codigo)===''){http_response_code(400);echo json_encode(['error'=>'Código inválido.']);return;}
      echo json_encode((new Producto())->porCodigo(trim($codigo)),JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
    }
    public function editar() {
      $this->checkAuth();$model=new Producto();$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);$producto=$model->getById($id);
      if (!$producto) {http_response_code(404);echo 'Producto no encontrado';return;}
      $lotesProducto=$model->lotesConStock((int)$id);
      if(count($lotesProducto)===1)$producto['fecha_vencimiento']=$lotesProducto[0]['vencimiento'];
      $categorias=$this->categorias();
      if ($_SERVER['REQUEST_METHOD']==='POST') try { $d=$this->payload(true,$producto);$d['id']=$id;$model->actualizar($d);header('Location: index.php?c=Producto&a=index');exit; } catch(Throwable $e) {$error=$e->getMessage();}
      require '../views/productos/editar.php';
    }
    public function eliminar() { $this->checkAuth();$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);if($id)(new Producto())->eliminar($id);header('Location: index.php?c=Producto&a=index');exit; }
}
