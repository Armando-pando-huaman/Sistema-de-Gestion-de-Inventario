<?php
if(PHP_SAPI!=='cli')exit;
require __DIR__.'/../config/database.php';

require __DIR__.'/../config/Seguridad.php';
require __DIR__.'/../models/Producto.php';
require __DIR__.'/../models/Venta.php';
require __DIR__.'/../models/Devolucion.php';
require __DIR__.'/../models/Gestion.php';
require __DIR__.'/../models/Reporte.php';
function comprobar($ok,string $mensaje):void{if(!$ok)throw new RuntimeException('FAIL: '.$mensaje);echo 'PASS: '.$mensaje.PHP_EOL;}
function rechaza(callable $fn,string $mensaje):void{$error=false;try{$fn();}catch(Throwable $e){$error=true;}comprobar($error,$mensaje);}
$origen=(new Database())->getConnection();$base='fv_test_'.bin2hex(random_bytes(6));$testDir=sys_get_temp_dir().DIRECTORY_SEPARATOR.$base;mkdir($testDir);
$db=null;
try{
    $origen->exec("CREATE DATABASE `$base` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    foreach($origen->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $tabla){
        $origen->exec('CREATE TABLE `'.$base.'`.`'.$tabla.'` LIKE `'.$tabla.'`');
        $origen->exec('INSERT INTO `'.$base.'`.`'.$tabla.'` SELECT * FROM `'.$tabla.'`');
    }
    putenv('DB_NAME='.$base);$db=(new Database())->getConnection();    $finanzasInicial=(new Reporte())->finanzas(7);
    $db->prepare('INSERT INTO usuarios(nombre,email,password,rol) VALUES(?,?,?,?)')->execute(['Test','test-'.bin2hex(random_bytes(4)).'@local.test',password_hash('Prueba-12345',PASSWORD_DEFAULT),'admin']);$uid=(int)$db->lastInsertId();$_SESSION=['user_id'=>$uid,'user_rol'=>'admin'];
    $db->prepare('INSERT INTO productos(nombre,precio_compra,precio_venta,stock,stock_minimo,es_fraccionable,unidades_por_blister,unidades_por_caja,precio_unidad,precio_blister,precio_caja) VALUES(?,1,2,0,1,1,5,10,2,9,18)')->execute(['Producto de prueba']);$pid=(int)$db->lastInsertId();
    $db->prepare('UPDATE productos SET codigo_barras=? WHERE id=?')->execute(['036000291452',$pid]);
    $consulta=(new Producto($db))->porCodigo('00036000291452');
    comprobar((int)($consulta['producto']['id']??0)===$pid,'lector encuentra UPC mediante su GTIN equivalente');
    $hoy=$db->query('SELECT CURDATE()')->fetchColumn();$manana=date('Y-m-d',strtotime($hoy.' +1 day'));$pasado=date('Y-m-d',strtotime($hoy.' +10 days'));$ayer=date('Y-m-d',strtotime($hoy.' -1 day'));
    $nuevo=(new Producto($db))->getById($pid);unset($nuevo['id'],$nuevo['created_at']);$nuevo['codigo_barras']='SCAN-TEST-'.bin2hex(random_bytes(4));$nuevo['nombre']='Producto leído de QR';$nuevo['stock']=3;$nuevo['fecha_vencimiento']=$pasado;$nuevo['lote_inicial']='QR-LOTE-123';
    (new Producto($db))->crear($nuevo);$s=$db->prepare('SELECT l.codigo,l.vencimiento FROM lotes l JOIN productos p ON p.id=l.producto_id WHERE p.codigo_barras=?');$s->execute([$nuevo['codigo_barras']]);$leido=$s->fetch();
    comprobar($leido['codigo']==='QR-LOTE-123'&&$leido['vencimiento']===$pasado,'guardar nuevo producto conserva lote y vencimiento leídos');
    $db->beginTransaction();$l1=Stock::entrada($db,$pid,10,'FEFO-1',$manana,1);$l2=Stock::entrada($db,$pid,10,'FEFO-2',$pasado,1.5);$exp=Stock::entrada($db,$pid,5,'VENCIDO',$ayer,1);$pend=Stock::entrada($db,$pid,7,'SIN-VERIFICAR',null,null,true);$db->commit();
    Caja::operar($db,$uid,['accion'=>'abrir','importe'=>'100.00']);
    rechaza(fn()=>Caja::operar($db,$uid,['accion'=>'abrir','importe'=>'0']),'no abre dos cajas simultáneas por usuario');
    $v=new Venta($db);$venta=$v->registrar('Test',$uid,[['id'=>$pid,'cantidad'=>1,'presentacion'=>'caja'],['id'=>$pid,'cantidad'=>2,'presentacion'=>'unidad']],['metodo_pago'=>'efectivo','clave_unica'=>bin2hex(random_bytes(32))]);
    $s=$db->prepare('SELECT stock FROM lotes WHERE id=?');$s->execute([$l1]);comprobar((int)$s->fetchColumn()===0,'FEFO consume el lote que vence antes');$s->execute([$l2]);comprobar((int)$s->fetchColumn()===8,'venta con dos presentaciones descuenta 12 unidades');
    $s->execute([$exp]);comprobar((int)$s->fetchColumn()===5,'venta no toca lotes vencidos');$s->execute([$pend]);comprobar((int)$s->fetchColumn()===7,'FEFO prioriza lotes con vencimiento antes que los lotes sin fecha');
    $detalle=$v->getDetalle($venta);$primero=$detalle[0];$n=(int)$primero['cantidad_unidades'];$cantidadParcial=min(2,$n);
    Devolucion::registrar($db,$venta,$uid,[$primero['id']=>$cantidadParcial],'Devolución parcial','efectivo');
    Devolucion::registrar($db,$venta,$uid,[],'Anulación del saldo','efectivo',true);
    rechaza(fn()=>Devolucion::registrar($db,$venta,$uid,[],'Intento repetido','efectivo',true),'no permite reembolsar la venta dos veces');
    comprobar($v->getById($venta)['estado']==='anulada','anulación conserva la venta y su estado');
    $s=$db->prepare('SELECT stock FROM productos WHERE id=?');$s->execute([$pid]);comprobar((int)$s->fetchColumn()===32,'devolución parcial más anulación restaura el stock exacto');
    $c=Caja::abierta($db,$uid);comprobar(Caja::esperado($db,$c)===100.0,'caja descuenta exactamente todos los reembolsos');
    $g=new Gestion($db);$g->proveedor(['nombre'=>'Proveedor test','documento'=>'TEST'], $uid);$proveedor=(int)$db->query('SELECT MAX(id) FROM proveedores')->fetchColumn();
    $compra=['proveedor_id'=>$proveedor,'documento'=>'FACT-1','lineas'=>[['producto_id'=>$pid,'cantidad'=>3,'costo_unidad'=>'1.25','lote'=>'COMPRA-1','vencimiento'=>$pasado],['producto_id'=>$pid,'cantidad'=>2,'costo_unidad'=>'1.50','lote'=>'COMPRA-2','vencimiento'=>$pasado]]];
    $g->compra($compra,$uid);$s->execute([$pid]);comprobar((int)$s->fetchColumn()===37,'compra de varias líneas crea lotes y aumenta stock');
    rechaza(fn()=>$g->compra($compra,$uid),'rechaza documento de compra duplicado');$s->execute([$pid]);comprobar((int)$s->fetchColumn()===37,'compra rechazada no deja stock parcial');
    $p=new Producto($db);$p->eliminar($pid);rechaza(fn()=>$v->registrar('Test',$uid,[['id'=>$pid,'cantidad'=>1,'presentacion'=>'unidad']]),'no vende productos desactivados');comprobar((bool)$p->getById($pid),'desactivación conserva el producto histórico');$p->eliminar($pid);
    rechaza(fn()=>$v->registrar('Test',$uid,[['id'=>$pid,'cantidad'=>100,'presentacion'=>'unidad']]),'rechaza venta sin suficientes lotes');
    $before=(int)$db->query('SELECT COUNT(*) FROM ventas')->fetchColumn();rechaza(fn()=>$v->registrar('Test',$uid,[['id'=>$pid,'cantidad'=>1,'presentacion'=>'unidad'],['id'=>$pid,'cantidad'=>100,'presentacion'=>'unidad']]),'fallo de segunda línea revierte la primera');comprobar((int)$db->query('SELECT COUNT(*) FROM ventas')->fetchColumn()===$before,'fallo no crea venta parcial');$s->execute([$pid]);comprobar((int)$s->fetchColumn()===37,'rollback conserva el stock');
    Caja::operar($db,$uid,['accion'=>'gasto','importe'=>'5.00','motivo'=>'Servicio test','metodo'=>'efectivo']);Caja::operar($db,$uid,['accion'=>'cerrar','importe'=>'95.00']);
    $s=$db->prepare('SELECT diferencia FROM cajas WHERE usuario_id=? ORDER BY id DESC LIMIT 1');$s->execute([$uid]);comprobar((float)$s->fetchColumn()===0.0,'cierre concilia efectivo, reembolsos y gastos');
    rechaza(fn()=>$v->registrar('Test',$uid,[['id'=>$pid,'cantidad'=>1,'presentacion'=>'unidad']]),'no vende con caja cerrada');
    $r=new Reporte();$f=$r->finanzas(7);comprobar(is_array($f),'reporte calcula reembolsos, costos y gastos');
    foreach([7,30,365] as $dias)comprobar(count($r->progreso($dias)['serie'])===$dias,'reporte incluye todos los días: '.$dias);
    $s=$db->prepare('SELECT COUNT(*) FROM auditoria WHERE usuario_id=?');$s->execute([$uid]);comprobar((int)$s->fetchColumn()>=8,'auditoría conserva operaciones');
    // Costos y redondeos de devoluciones parciales se concilian con el total original.
    $db->prepare('UPDATE productos SET precio_blister=9.99 WHERE id=?')->execute([$pid]);
    Caja::operar($db,$uid,['accion'=>'abrir','importe'=>'0']);$venta=$v->registrar('Test',$uid,[['id'=>$pid,'cantidad'=>1,'presentacion'=>'blister']],['metodo_pago'=>'yape']);$d=$v->getDetalle($venta)[0];
    for($i=0;$i<5;$i++)Devolucion::registrar($db,$venta,$uid,[$d['id']=>1],'Retorno unitario','yape');
    $s=$db->prepare('SELECT SUM(total) FROM devoluciones WHERE venta_id=?');$s->execute([$venta]);comprobar((float)$s->fetchColumn()===9.99,'cinco devoluciones unitarias concilian los centavos del paquete');
    // Dos procesos compiten por la última unidad: solo uno puede venderla.
    $db->exec("INSERT INTO productos(nombre,precio_compra,precio_venta,stock,stock_minimo) VALUES('Última unidad',1,2,0,0)");$ultimo=(int)$db->lastInsertId();
    $db->beginTransaction();Stock::entrada($db,$ultimo,1,'UNICO',null,1);$db->commit();
    $workers=[];$gate=$testDir.'/venta.gate';
    for($i=0;$i<2;$i++){
        $db->prepare('INSERT INTO usuarios(nombre,email,password,rol) VALUES(?,?,?,?)')->execute(['Concurrente',$base.$i.'@local.test','no-login','vendedor']);$workerUser=(int)$db->lastInsertId();
        Caja::operar($db,$workerUser,['accion'=>'abrir','importe'=>'0']);
        $proc=proc_open([PHP_BINARY,__DIR__.'/venta_worker.php',(string)$ultimo,(string)$workerUser,$gate],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$workerPipes);
        $workers[]=[$proc,$workerPipes];
    }
    touch($gate);$resultados=[];
    foreach($workers as [$proc,$workerPipes]){$resultados[]=stream_get_contents($workerPipes[1]);$errores=stream_get_contents($workerPipes[2]);foreach($workerPipes as $pipe)fclose($pipe);proc_close($proc);if($errores)throw new RuntimeException($errores);}
    unlink($gate);sort($resultados);comprobar($resultados===['OK','RECHAZADA'],'dos vendedores no pueden vender la misma última unidad');
    $f=(new Reporte())->finanzas(7);
    comprobar(abs($f['neto']-$finanzasInicial['neto']-2)<0.001&&abs($f['costo']-$finanzasInicial['costo']-1)<0.001&&abs($f['gastos']-$finanzasInicial['gastos']-5)<0.001,'finanzas no duplica compras y concilia ventas, reembolsos, costos y gastos');
    require __DIR__.'/web.php';
    echo "Todas las pruebas de integración y web pasaron.\n";
}finally{
    $db=null;
    if(preg_match('/^fv_test_[a-f0-9]{12}$/',$base))$origen->exec("DROP DATABASE IF EXISTS `$base`");
    foreach(glob($testDir.'/*')?:[] as $temporal)if(is_file($temporal))unlink($temporal);if(is_dir($testDir))rmdir($testDir);
}
