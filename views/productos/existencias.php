<?php require __DIR__.'/../layouts/header.php';$esc=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); ?>
<link rel="stylesheet" href="css/fv-operaciones.css?v=3">
<div class="fv-operations">
 <header class="op-heading"><div><h2>Agregar existencias</h2><p><?= $esc($producto['nombre']) ?> · Código: <?= $esc($producto['codigo_barras']??'Sin código') ?></p></div><div class="op-links"><a class="btn btn-outline-primary" href="index.php?c=Producto&amp;a=index">Volver a Productos</a><a class="btn btn-outline-primary" href="index.php?c=Gestion&amp;a=lotes&amp;producto_id=<?= (int)$id ?>">Ver lotes de este producto</a></div></header>
 <?php if($error): ?><div class="alert alert-danger" role="alert"><?= $esc($error) ?></div><?php endif; ?>
 <?php if(!empty($_SESSION['success'])): ?><div class="alert alert-success" role="status"><?= $esc($_SESSION['success']) ?></div><?php unset($_SESSION['success']);endif; ?>
 <section class="op-panel"><h3><?= $esc($producto['nombre']) ?></h3><p>Stock actual: <strong><?= (int)$producto['stock'] ?></strong> unidades mínimas.</p>
 <p class="op-note">La ficha del producto ya está registrada. Aquí agregas un nuevo lote y sus existencias. <?php if($producto['es_fraccionable']): ?>Cada <?= $esc($producto['nombre_caja']) ?> contiene <?= (int)$producto['unidades_por_caja'] ?> unidades; cada <?= $esc($producto['nombre_paquete']) ?> contiene <?= (int)$producto['unidades_por_blister'] ?>.<?php endif; ?> Ingresa cantidad y costo por unidad mínima. El pago al proveedor se registra por separado en caja cuando corresponda.</p>
 <?php if($producto['activo']): ?>
 <form method="post" class="op-form">
  <input type="hidden" name="csrf" value="<?= $esc(Seguridad::token()) ?>"><input type="hidden" name="operacion" value="existencias">
  <div><label for="stock-provider">Proveedor</label><select id="stock-provider" name="proveedor_id" class="form-select" required><option value="">Seleccionar proveedor</option><?php foreach($proveedores as $p): ?><option value="<?= (int)$p['id'] ?>" <?= (string)($_POST['proveedor_id']??'')===(string)$p['id']?'selected':'' ?>><?= $esc($p['nombre']) ?></option><?php endforeach; ?></select></div>
  <div><label for="stock-document">Documento del proveedor</label><input id="stock-document" name="documento" class="form-control" required maxlength="80" placeholder="Factura, boleta o referencia" value="<?= $esc(($_POST['operacion']??'')==='existencias'?($_POST['documento']??''):'') ?>"></div>
  <div><label for="stock-quantity">Cantidad en unidades mínimas</label><input id="stock-quantity" name="cantidad" type="number" min="1" max="1000000" step="1" class="form-control" required value="<?= $esc($_POST['cantidad']??'') ?>"></div>
  <div><label for="stock-cost">Costo por unidad mínima (S/)</label><input id="stock-cost" name="costo_unidad" type="number" step="0.01" min="0.01" class="form-control" required value="<?= $esc($_POST['costo_unidad']??'') ?>"></div>
  <div><label for="stock-lot">Código del nuevo lote</label><input id="stock-lot" name="lote" maxlength="80" class="form-control" required value="<?= $esc($_POST['lote']??'') ?>"></div>
  <div><label for="stock-expiry">Vencimiento del nuevo lote</label><input id="stock-expiry" name="vencimiento" type="date" class="form-control" value="<?= $esc($_POST['vencimiento']??'') ?>"></div>
  <button class="btn btn-primary op-full" type="submit">Agregar existencias a este producto</button>
 </form>
 <?php else: ?><p>El producto está inactivo. Reactívalo desde Productos antes de agregar existencias.</p><?php endif; ?>
 </section>
 <details class="op-panel" <?= ($_POST['operacion']??'')==='proveedor'?'open':'' ?>><summary>Agregar un proveedor nuevo</summary><form method="post" class="op-form"><input type="hidden" name="csrf" value="<?= $esc(Seguridad::token()) ?>"><input type="hidden" name="operacion" value="proveedor"><div><label for="provider-name">Nombre o razón social</label><input id="provider-name" name="nombre" class="form-control" required maxlength="150"></div><div><label for="provider-document">Documento del proveedor</label><input id="provider-document" name="documento" class="form-control" maxlength="30"></div><div><label for="provider-phone">Teléfono</label><input id="provider-phone" name="telefono" class="form-control" maxlength="30"></div><button class="btn btn-primary" type="submit">Guardar proveedor</button></form></details>
</div>
<?php require __DIR__.'/../layouts/footer.php'; ?>
