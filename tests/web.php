<?php
/** Se invoca desde integracion.php con una base aislada ya configurada. */
if(PHP_SAPI!=='cli'||!preg_match('/^fv_test_[a-f0-9]{12}$/',getenv('DB_NAME')?:''))exit(2);
$puerto=random_int(21000,29000);$url='http://127.0.0.1:'.$puerto.'/index.php';$log=$testDir.'/server.log';$sesiones=[];
$server=proc_open([PHP_BINARY,'-d','display_errors=1','-d','session.save_path='.$testDir,'-S','127.0.0.1:'.$puerto,'-t',__DIR__.'/../public'],[0=>['pipe','r'],1=>['file',$log,'a'],2=>['file',$log,'a']],$pipes,__DIR__.'/../public');
if(!is_resource($server))throw new RuntimeException('No se pudo iniciar el servidor de prueba.');
$request=function($curl,string $ruta,array $post=null)use($url){curl_setopt_array($curl,[CURLOPT_URL=>$url.$ruta,CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_TIMEOUT=>8,CURLOPT_COOKIEFILE=>'']);if($post!==null)curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($post)]);else curl_setopt($curl,CURLOPT_HTTPGET,true);$html=curl_exec($curl);if($html===false)throw new RuntimeException(curl_error($curl));return [curl_getinfo($curl,CURLINFO_HTTP_CODE),$html];};
$token=function($html){if(!preg_match('/name="csrf" value="([a-f0-9]+)"/',$html,$m))throw new RuntimeException('Falta token del formulario.');return $m[1];};
$handles=[];
try{
    for($i=0;$i<50;$i++){$socket=@fsockopen('127.0.0.1',$puerto,$errno,$error,.1);if($socket){fclose($socket);break;}usleep(100000);}
    $admin=curl_init();$handles[]=$admin;[$status,$html]=$request($admin,'?c=Auth&a=login');comprobar($status===200,'login se renderiza');
    $s=$db->prepare('SELECT email FROM usuarios WHERE id=?');$s->execute([$uid]);$email=$s->fetchColumn();
    [$status]=$request($admin,'?c=Auth&a=login',['csrf'=>$token($html),'email'=>$email,'password'=>'Prueba-12345']);comprobar($status===302,'login válido inicia sesión');
    foreach(['Dashboard/index','Producto/index','Producto/crear','Producto/editar&id='.$pid,'Movimiento/index','Venta/index','Venta/historial','Venta/ver&id='.$venta,'Gestion/compras','Gestion/lotes','Gestion/caja','Gestion/auditoria','Gestion/finanzas','Reporte/index','Reporte/ventas','Reporte/productos','Reporte/inventario','Usuario/index','Usuario/crear'] as $pagina){
        [$c,$a]=explode('/',$pagina,2);[$status,$html]=$request($admin,'?c='.$c.'&a='.$a);
        comprobar($status===200&&!preg_match('/(?:Fatal error|Warning:|Parse error)/',$html),'vista admin: '.$pagina);
    }
    [$status]=$request($admin,'?c=Producto&a=eliminar&id='.$pid);comprobar($status===405,'GET no elimina ni desactiva productos');
    [$status]=$request($admin,'?c=Producto&a=eliminar',['id'=>$pid,'csrf'=>'invalido']);comprobar($status===403,'POST sin CSRF válido es rechazado');
    [$status]=$request($admin,'?c=Producto&a=payload');comprobar($status===404,'router impide invocar métodos internos');
    [$status,$html]=$request($admin,'?c=Producto&a=consultarCodigo&codigo=036000291452');$encontrado=json_decode($html,true);
    comprobar($status===200&&(int)($encontrado['producto']['id']??0)===$pid&&!array_key_exists('fecha_vencimiento',$encontrado['producto']),'consulta de escaneo devuelve ficha sin copiar vencimiento de otro lote');
    [$status,$html]=$request($admin,'?c=Producto&a=index');$csrf=$token($html);
    comprobar(strpos($html,'Agregar existencias')!==false&&strpos($html,'Ver lotes')!==false&&strpos($html,'>Compras</span>')===false,'Productos ofrece existencias y lotes sin Compras en el menú principal');
    [$status,$html]=$request($admin,'?c=Producto&a=existencias&id='.$pid);
    comprobar($status===200&&strpos($html,'Producto de prueba')!==false&&strpos($html,'name="lineas[0][producto_id]"')===false,'agregar existencias ya tiene el producto seleccionado');
    $stockAntes=(int)$p->getById($pid)['stock'];
    $entrada=['csrf'=>$token($html),'operacion'=>'existencias','proveedor_id'=>$proveedor,'documento'=>'WEB-ENTRADA-1','cantidad'=>'4','costo_unidad'=>'1.25','lote'=>'WEB-LOTE-1','vencimiento'=>$pasado];
    [$status]=$request($admin,'?c=Producto&a=existencias&id='.$pid,$entrada);
    comprobar($status===302&&(int)$p->getById($pid)['stock']===$stockAntes+4,'agregar existencias registra entrada y actualiza stock');
    $baseProducto=['csrf'=>$csrf,'nombre'=>'Ficha unificada sin stock','codigo_barras'=>'UNIFICADO-'.bin2hex(random_bytes(4)),'precio_venta'=>'2.00','stock_minimo'=>'5'];
    [$status]=$request($admin,'?c=Producto&a=crear',$baseProducto);
    $s=$db->prepare('SELECT id,stock FROM productos WHERE codigo_barras=?');$s->execute([$baseProducto['codigo_barras']]);$creado=$s->fetch();
    comprobar($status===302&&$creado&&(int)$creado['stock']===0,'nuevo producto sin existencias crea solo la ficha');
    $conStock=array_merge($baseProducto,['nombre'=>'Ficha unificada con stock','codigo_barras'=>'UNIFICADO-'.bin2hex(random_bytes(4)),'incluir_existencias'=>'1','stock'=>'7','proveedor_id'=>$proveedor,'documento_inicial'=>'UNIFICADO-DOC-1','costo_inicial'=>'1.50','lote_inicial'=>'UNIFICADO-LOTE','fecha_vencimiento'=>$pasado,'es_fraccionable'=>'1','blisters_por_caja'=>'2','unidades_por_blister'=>'5','precio_unidad'=>'2.00','precio_blister'=>'9.00','precio_caja'=>'18.00']);
    [$status]=$request($admin,'?c=Producto&a=crear',$conStock);$s->execute([$conStock['codigo_barras']]);$creado=$s->fetch();
    comprobar($status===302&&$creado&&(int)$creado['stock']===7,'nuevo producto fraccionable registra unidades mínimas una sola vez');
    $sLote=$db->prepare('SELECT l.stock,l.codigo,c.proveedor_id,c.documento,c.total FROM lotes l JOIN compra_detalles cd ON cd.lote_id=l.id JOIN compras c ON c.id=cd.compra_id WHERE l.producto_id=?');$sLote->execute([$creado['id']]);$documento=$sLote->fetch();
    comprobar((int)$documento['stock']===7&&$documento['codigo']==='UNIFICADO-LOTE'&&(int)$documento['proveedor_id']===$proveedor&&(float)$documento['total']===10.5,'ficha, proveedor, documento y lote quedan vinculados');
    $antesProductos=(int)$db->query('SELECT COUNT(*) FROM productos')->fetchColumn();$repetido=$conStock;$repetido['codigo_barras']='UNIFICADO-REPETIDO';
    [$status]=$request($admin,'?c=Producto&a=crear',$repetido);
    comprobar($status===200&&(int)$db->query('SELECT COUNT(*) FROM productos')->fetchColumn()===$antesProductos,'documento inicial duplicado revierte la ficha y todo el ingreso');
    $nuevoProveedor=$conStock;$nuevoProveedor['codigo_barras']='UNIFICADO-NUEVO-PROV';$nuevoProveedor['crear_proveedor']='1';unset($nuevoProveedor['proveedor_id']);$nuevoProveedor['proveedor_nombre']='Proveedor desde ficha';$nuevoProveedor['documento_inicial']='UNIFICADO-DOC-2';
    [$status]=$request($admin,'?c=Producto&a=crear',$nuevoProveedor);
    comprobar($status===302,'nuevo producto permite crear proveedor dentro del mismo formulario');
    [$status,$html]=$request($admin,'?c=Gestion&a=compras');
    comprobar($status===200&&strpos($html,'purchase-lines')===false&&strpos($html,'Registrar compra')===false,'Compras presenta solo historial sin formulario duplicado');
    [$status,$html]=$request($admin,'?c=Producto&a=existencias&id='.$pid,$entrada);
    comprobar($status===200&&(int)$p->getById($pid)['stock']===$stockAntes+4&&strpos($html,'documento no esté registrado')!==false,'documento repetido no duplica existencias');
    [$status,$html]=$request($admin,'?c=Gestion&a=lotes&producto_id='.$pid);
    comprobar($status===200&&strpos($html,'WEB-LOTE-1')!==false&&strpos($html,'INICIAL-')===false&&strpos($html,'name="producto_id" value="'.$pid.'"')!==false,'Ver lotes filtra exactamente el producto y conserva el filtro');
    [$status]=$request($admin,'?c=Producto&a=existencias&id=0');comprobar($status===404,'existencias rechaza productos inexistentes');
    [$status]=$request($admin,'?c=Producto&a=eliminar',['id'=>$pid,'csrf'=>$csrf]);comprobar($status===302&&(int)$p->getById($pid)['activo']===0,'formulario admin desactiva conservando historial');$p->eliminar($pid);
    $email='vendedor-'.bin2hex(random_bytes(4)).'@local.test';$db->prepare('INSERT INTO usuarios(nombre,email,password,rol) VALUES(?,?,?,?)')->execute(['Vendedor test',$email,password_hash('Prueba-12345',PASSWORD_DEFAULT),'vendedor']);$vendedorId=(int)$db->lastInsertId();
    $staff=curl_init();$handles[]=$staff;[$status,$html]=$request($staff,'?c=Auth&a=login');[$status]=$request($staff,'?c=Auth&a=login',['csrf'=>$token($html),'email'=>$email,'password'=>'Prueba-12345']);
    foreach(['Producto/crear','Producto/editar&id='.$pid,'Producto/existencias&id='.$pid,'Reporte/index','Usuario/index','Gestion/compras','Gestion/lotes','Gestion/auditoria'] as $pagina){[$c,$a]=explode('/',$pagina,2);[$status]=$request($staff,'?c='.$c.'&a='.$a);comprobar($status===403,'vendedor no accede a '.$pagina);}
    [$status,$html]=$request($staff,'?c=Movimiento&a=index');comprobar($status===200&&strpos($html,'id="mov-type"')===false,'vendedor consulta movimientos sin formulario administrativo');
    [$status,$html]=$request($staff,'?c=Gestion&a=caja');comprobar($status===200,'vendedor puede operar su caja');
    $db->prepare('UPDATE usuarios SET activo=0 WHERE id=?')->execute([$vendedorId]);[$status]=$request($staff,'?c=Venta&a=index');comprobar($status===302,'desactivación revoca la sesión existente');
}finally{
    foreach($handles as $handle){foreach(curl_getinfo($handle,CURLINFO_COOKIELIST)?:[] as $cookie){$parts=explode("\t",$cookie);if(($parts[5]??'')==='PHPSESSID'&&preg_match('/^[a-zA-Z0-9,-]+$/',$parts[6]))$sesiones[]=$parts[6];}curl_close($handle);}
    proc_terminate($server);proc_close($server);
    foreach(array_unique($sesiones) as $id){$f=$testDir.'/sess_'.$id;if(is_file($f))unlink($f);}
    if(is_file($log))unlink($log);
}
