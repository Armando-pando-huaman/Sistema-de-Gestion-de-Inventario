<?php
require_once __DIR__.'/Stock.php';
class Producto {
 private PDO $conn;
 public function __construct(?PDO $db=null){$this->conn=$db??(new Database())->getConnection();}
 public function getAll(){return $this->conn->query('SELECT p.*,c.nombre AS categoria,(SELECT COALESCE(SUM(l.stock),0) FROM lotes l WHERE l.producto_id=p.id AND (l.vencimiento IS NULL OR l.vencimiento>=CURDATE())) AS stock_disponible FROM productos p LEFT JOIN categorias c ON p.categoria_id=c.id WHERE p.activo=1 ORDER BY p.id DESC')->fetchAll();}
 public function pagina(string $busqueda='',int $pagina=1,bool $inactivos=false):array{
  $where=' WHERE p.activo=?';$args=[$inactivos?0:1];
  if($busqueda!==''){$where.=' AND (p.nombre LIKE ? OR p.codigo_barras LIKE ?)';$args[]='%'.$busqueda.'%';$args[]='%'.$busqueda.'%';}
  $s=$this->conn->prepare('SELECT COUNT(*) FROM productos p'.$where);$s->execute($args);$total=(int)$s->fetchColumn();$paginas=max(1,(int)ceil($total/30));$pagina=max(1,min($pagina,$paginas));$offset=($pagina-1)*30;
  $s=$this->conn->prepare('SELECT p.*,c.nombre AS categoria,CASE WHEN EXISTS(SELECT 1 FROM lotes l WHERE l.producto_id=p.id) THEN (SELECT MIN(l.vencimiento) FROM lotes l WHERE l.producto_id=p.id AND l.stock>0) ELSE p.fecha_vencimiento END AS fecha_vencimiento FROM productos p LEFT JOIN categorias c ON c.id=p.categoria_id'.$where." ORDER BY p.nombre,p.id LIMIT 30 OFFSET $offset");$s->execute($args);
  return ['filas'=>$s->fetchAll(),'total'=>$total,'pagina'=>$pagina,'paginas'=>$paginas];
 }
 public function getById($id){$s=$this->conn->prepare('SELECT * FROM productos WHERE id=?');$s->execute([$id]);return $s->fetch();}
 public function lotesConStock(int $id): array {$s=$this->conn->prepare('SELECT id,codigo,vencimiento,stock FROM lotes WHERE producto_id=? AND stock>0 ORDER BY id');$s->execute([$id]);return $s->fetchAll();}
 public function porCodigo(string $codigo){
  $codigos=[$codigo];
  if(preg_match('/^\d{8,14}$/',$codigo)){
   $gtin=str_pad($codigo,14,'0',STR_PAD_LEFT);$codigos[]=$gtin;
   foreach([8,12,13] as $largo)if(substr($gtin,0,14-$largo)===str_repeat('0',14-$largo))$codigos[]=substr($gtin,-$largo);
  }
  $codigos=array_unique($codigos);$s=$this->conn->prepare('SELECT id,nombre,descripcion,codigo_barras,activo FROM productos WHERE codigo_barras IN ('.implode(',',array_fill(0,count($codigos),'?')).') ORDER BY activo DESC,id LIMIT 2');$s->execute(array_values($codigos));
  $filas=$s->fetchAll();return count($filas)===1?['producto'=>$filas[0],'ambiguo'=>false]:['producto'=>null,'ambiguo'=>count($filas)>1];
 }
 public function crear($d){return Operacion::transaccion($this->conn,function()use($d){
  $inicial=$d['existencias_iniciales']??null;unset($d['existencias_iniciales']);
  $codigoLote=$d['lote_inicial']??null;unset($d['lote_inicial']);
  $stock=(int)$d['stock'];$d['stock']=0;$cols=array_keys($d);$s=$this->conn->prepare('INSERT INTO productos (`'.implode('`,`',$cols).'`) VALUES ('.implode(',',array_fill(0,count($d),'?')).')');$s->execute(array_values($d));$id=(int)$this->conn->lastInsertId();
  if($stock){$lote=Stock::entrada($this->conn,$id,$stock,$codigoLote??'ALTA-'.$id,$d['fecha_vencimiento'],(float)$d['precio_compra']/max(1,(int)$d['unidades_por_caja']));Stock::movimiento($this->conn,$id,'entrada',$stock,'Inventario inicial',(int)$_SESSION['user_id'],null,$lote);}
  if($inicial){
   require_once __DIR__.'/Gestion.php';
   if(isset($inicial['nuevo_proveedor'])){
    $p=$inicial['nuevo_proveedor'];$this->conn->prepare('INSERT INTO proveedores(nombre,documento,telefono) VALUES(?,?,?)')->execute([Operacion::texto($p['nombre']??''),$p['documento']??'',$p['telefono']??'']);
    $inicial['proveedor_id']=(int)$this->conn->lastInsertId();Operacion::audit($this->conn,'crear','proveedores',$inicial['proveedor_id']);
   }
   (new Gestion($this->conn))->compra(['proveedor_id'=>$inicial['proveedor_id'],'documento'=>$inicial['documento'],'lineas'=>[['producto_id'=>$id,'cantidad'=>$inicial['cantidad'],'costo_unidad'=>$inicial['costo_unidad'],'lote'=>$codigoLote,'vencimiento'=>$d['fecha_vencimiento']]]],(int)$_SESSION['user_id']);
  }
  Operacion::audit($this->conn,'crear','productos',$id,$d);return true;
 });}
 public function actualizar($d){return Operacion::transaccion($this->conn,function()use($d){
  $id=(int)$d['id'];$antes=Stock::producto($this->conn,$id);
  if((int)$antes['stock']!==(int)$d['stock'])throw new RuntimeException('El stock cambió o intentas modificarlo directamente. Usa Movimientos y recarga la ficha.');
  if((int)$antes['stock']>0&&((int)$antes['unidades_por_caja']!==(int)$d['unidades_por_caja']||(int)$antes['unidades_por_blister']!==(int)$d['unidades_por_blister']||(int)$antes['es_fraccionable']!==(int)$d['es_fraccionable']))throw new RuntimeException('No puedes cambiar equivalencias con existencias.');
  $s=$this->conn->prepare('SELECT id,vencimiento FROM lotes WHERE producto_id=? AND stock>0 ORDER BY id FOR UPDATE');$s->execute([$id]);$lotes=$s->fetchAll();
  $fechas=$d['vencimientos_lotes']??null;unset($d['vencimientos_lotes']);
  if($fechas!==null){
   if(!is_array($fechas))throw new RuntimeException('Fechas de lotes invalidas.');
   $ids=array_map('intval',array_keys($fechas));sort($ids);$actuales=array_map('intval',array_column($lotes,'id'));sort($actuales);
   if($ids!==$actuales)throw new RuntimeException('Los lotes cambiaron. Recarga el producto antes de corregir sus vencimientos.');
  }elseif(count($lotes)>1){throw new RuntimeException('Este producto tiene varios lotes. Revisa la fecha de cada lote.');}
  foreach($lotes as $lote){
   $fecha=Operacion::fecha($fechas!==null?($fechas[$lote['id']]??''):($d['fecha_vencimiento']??null));
   if($fecha!==$lote['vencimiento']){
    $this->conn->prepare('UPDATE lotes SET vencimiento=? WHERE id=? AND producto_id=?')->execute([$fecha,$lote['id'],$id]);
    Operacion::audit($this->conn,'corregir_vencimiento','lotes',(int)$lote['id'],['antes'=>$lote['vencimiento'],'despues'=>$fecha,'producto_id'=>$id]);
   }
  }
  if($lotes){$s=$this->conn->prepare('SELECT MIN(vencimiento) FROM lotes WHERE producto_id=? AND stock>0');$s->execute([$id]);$d['fecha_vencimiento']=$s->fetchColumn()?:null;}
  else $d['fecha_vencimiento']=Operacion::fecha($d['fecha_vencimiento']??null);
  unset($d['id'],$d['stock']);$cols=array_keys($d);$sets=implode(',',array_map(fn($c)=>"`$c`=?",$cols));$this->conn->prepare("UPDATE productos SET $sets WHERE id=?")->execute([...array_values($d),$id]);
  Operacion::audit($this->conn,'editar','productos',$id,['antes'=>$antes,'despues'=>$d]);return true;
 });}
 public function eliminar($id){return Operacion::transaccion($this->conn,function()use($id){$antes=Stock::producto($this->conn,(int)$id);$estado=$antes['activo']?0:1;$this->conn->prepare('UPDATE productos SET activo=? WHERE id=?')->execute([$estado,$id]);Operacion::audit($this->conn,$estado?'reactivar':'desactivar','productos',(int)$id);return true;});}
 public function contar(){return $this->conn->query('SELECT COUNT(*) FROM productos WHERE activo=1')->fetchColumn();}
 public function stockBajo(){return $this->conn->query('SELECT * FROM productos WHERE activo=1 AND stock<=stock_minimo')->fetchAll();}
}
