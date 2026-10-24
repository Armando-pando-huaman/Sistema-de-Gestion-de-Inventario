<?php require_once '../views/layouts/header.php'; ?>
<link rel="stylesheet" href="css/fv-productos.css?v=10">
<div class="fv-products fv-product-editor">
<?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?>
<section class="fv-page-head">
    <div>
        <span class="fv-eyebrow"><?= $fvEsc($fvNombreBotica) ?> · Gestión de productos</span>
        <h2><i class="bi bi-plus-circle me-2"></i> Nuevo Producto</h2>
        <p>Registra un nuevo producto en el inventario.</p>
    </div>
    <a href="index.php?c=Producto&a=index" class="fv-btn-light"><i class="bi bi-arrow-left"></i> Volver al listado</a>
</section>
<div class="fv-panel fv-editor-card">
    <div class="fv-form-body">
        <form method="POST" class="row g-3 fv-product-form" data-product-autofill data-initial-stock-units>
<input type="hidden" name="csrf" value="<?= htmlspecialchars(Seguridad::token(),ENT_QUOTES,'UTF-8') ?>">
            <div class="col-12"><h5 class="fv-section-title"><i class="bi bi-capsule"></i> Información del producto</h5></div>
            <div class="col-md-4 fv-field-code">
                <label class="form-label" for="codigo_barras">Código de Barras</label>
                <div class="fv-scan-field"><input id="codigo_barras" type="text" name="codigo_barras" class="form-control" autocomplete="off" inputmode="text" value="<?= htmlspecialchars((string)($_POST['codigo_barras']??''),ENT_QUOTES,'UTF-8') ?>"><button class="fv-scan-btn" type="button" data-fv-scan-open title="Escanear código QR o barras"><i class="bi bi-upc-scan"></i><span>Escanear</span></button></div>
            </div>
            <div class="col-md-8 fv-field-name">
                <label class="form-label" for="nombre">Nombre *</label>
                <input id="nombre" type="text" name="nombre" class="form-control" value="<?= htmlspecialchars((string)($_POST['nombre']??''),ENT_QUOTES,'UTF-8') ?>" required>
            </div>
  <div class="col-md-4 fv-field-date"><label class="form-label" for="fecha_vencimiento">Fecha de vencimiento</label><input id="fecha_vencimiento" type="date" name="fecha_vencimiento" class="form-control" value="<?= htmlspecialchars((string)($_POST['fecha_vencimiento']??''),ENT_QUOTES,'UTF-8') ?>"><small class="form-text">Fecha indicada en el envase. No depende del proveedor; si agregas existencias iniciales, se aplica a ese lote.</small></div>
            <div class="col-12 fv-field-description">
                <label class="form-label" for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="2"><?= htmlspecialchars((string)($_POST['descripcion']??''),ENT_QUOTES,'UTF-8') ?></textarea>
            </div>
            <div class="col-12"><small id="fv-product-data-status" class="text-muted" role="status" aria-live="polite">Escanea un código o QR. Los datos disponibles se completan automáticamente; revisa el vencimiento antes de guardar.</small> <a id="fv-existing-product" href="#" hidden>Abrir producto existente</a></div>

            <div class="col-12 mt-4"><h5 class="fv-section-title"><i class="bi bi-currency-exchange"></i> Precio de venta y stock inicial</h5></div>


<div class="col-12 fv-initial-pricing">
            <div class="col-6 col-md-3 fv-field-sale">
                <label class="form-label" for="precio_venta">Precio Venta *</label>
                <input id="precio_venta" type="number" step="0.01" name="precio_venta" class="form-control" value="<?= htmlspecialchars((string)($_POST['precio_venta']??''),ENT_QUOTES,'UTF-8') ?>" required>
            </div>
  <div class="col-6 col-md-3"><label class="form-label" for="stock" id="fv-stock-label">Stock inicial (productos) *</label><input id="stock" type="number" name="stock" class="form-control" min="0" max="1000000" step="1" value="<?= htmlspecialchars((string)($_POST['stock']??'0'),ENT_QUOTES,'UTF-8') ?>"></div>
  <div class="col-6 col-md-3"><label class="form-label" for="costo_inicial">Costo por unidad (opcional, S/)</label><input id="costo_inicial" type="number" step="0.01" min="0.01" name="costo_inicial" class="form-control" value="<?= htmlspecialchars((string)($_POST['costo_inicial']??''),ENT_QUOTES,'UTF-8') ?>"></div>
  <div class="col-6 col-md-3"><label class="form-label" for="lote_inicial">Código de lote</label><input id="lote_inicial" name="lote_inicial" class="form-control" maxlength="80" value="<?= htmlspecialchars((string)($_POST['lote_inicial']??''),ENT_QUOTES,'UTF-8') ?>"></div>
            <div class="col-6 col-md-2 fv-field-minimum">
                <label class="form-label" for="stock_minimo">Stock mínimo para alertas</label>
                <input id="stock_minimo" type="number" name="stock_minimo" class="form-control" value="<?= htmlspecialchars((string)($_POST['stock_minimo']??'5'),ENT_QUOTES,'UTF-8') ?>" min="0" step="1" required aria-describedby="fv-minimum-help">
                <small id="fv-minimum-help" class="form-text">Recibirás una alerta cuando el stock sea igual o menor a esta cantidad. No agrega existencias.</small>
            </div>

</div>
            <div class="col-12 mt-4"><h5 class="fv-section-title"><i class="bi bi-tags"></i> Clasificación</h5></div>
            <div class="col-md-8 fv-field-category">
                <label class="form-label" for="categoria_id">Categoría</label>
                <select id="categoria_id" name="categoria_id" class="form-select">
                    <option value="">-- Seleccione --</option>
                    <?php foreach (($categorias ?? []) as $cat):
                        $catId = (int)($cat['id'] ?? 0);
                        $catNombre = (string)($cat['nombre'] ?? '');
                        $seleccionada = (string)($_POST['categoria_id'] ?? '') === (string)$catId;
                    ?>
                    <option value="<?= $catId ?>" <?= $seleccionada ? 'selected' : '' ?>><?= htmlspecialchars($catNombre, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="fv-category-hint">Selecciona una categoría existente o crea una nueva.</small>
            </div>
            <div class="col-md-4 fv-category-create-action"><a class="fv-category-add-btn" href="index.php?c=Categoria&a=index"><i class="bi bi-tags"></i> Administrar categorías</a></div>
            
<div class="col-12 mt-3"><h5 class="fv-section-title"><i class="bi bi-boxes"></i> Presentaciones de venta</h5></div>
<div class="col-12"><label class="form-check"><input class="form-check-input" type="checkbox" id="fv-frac" name="es_fraccionable" value="1" <?= (int)($producto['es_fraccionable'] ?? ($_POST['es_fraccionable'] ?? 0)) === 1 ? 'checked' : '' ?>><span class="form-check-label">Permitir venta por caja, paquete o unidad</span></label></div>

<div class="col-12" id="fv-frac-fields" hidden>
 <p class="fv-fraction-note">Configura cuántas unidades contiene la caja. Ejemplo: 10 paquetes de 10 guantes = 100 unidades. Si no hay paquete intermedio, usa 1 paquete por caja. En ventas puedes elegir caja, paquete o unidad.</p>
 <div class="row g-3">
<div class="col-12"><p class="fv-note">Nombres que verá el vendedor para este producto:</p></div>
<?php foreach(['unidad'=>'Unidad','paquete'=>'Paquete / blíster','caja'=>'Caja'] as $clave=>$defecto): ?>
<div class="col-sm-4"><label class="form-label" for="nombre_<?= $clave ?>">Nombre de <?= $clave ?></label><input class="form-control" id="nombre_<?= $clave ?>" name="nombre_<?= $clave ?>" maxlength="30" required value="<?= htmlspecialchars((string)($_POST['nombre_'.$clave]??($producto['nombre_'.$clave]??$defecto)),ENT_QUOTES,'UTF-8') ?>"></div>
<?php endforeach; ?>
  <div class="col-sm-4"><label class="form-label">Paquetes / blísteres por caja</label><input class="form-control" type="number" min="1" name="blisters_por_caja" value="<?= htmlspecialchars((string)($producto['blisters_por_caja'] ?? ($_POST['blisters_por_caja'] ?? 1)),ENT_QUOTES,'UTF-8') ?>"></div>
  <div class="col-sm-4"><label class="form-label">Unidades por paquete / blíster</label><input class="form-control" type="number" min="1" name="unidades_por_blister" value="<?= htmlspecialchars((string)($producto['unidades_por_blister'] ?? ($_POST['unidades_por_blister'] ?? 1)),ENT_QUOTES,'UTF-8') ?>"></div>
  <div class="col-sm-4"><label class="form-label">Total de unidades por caja</label><output id="fv-frac-total" class="form-control bg-light"></output></div>
  <div class="col-sm-4"><label class="form-label">Precio por unidad (S/)</label><input class="form-control" name="precio_unidad" type="number" step="0.01" min="0" value="<?= htmlspecialchars((string)($producto['precio_unidad'] ?? ($_POST['precio_unidad'] ?? '')),ENT_QUOTES,'UTF-8') ?>"></div>
  <div class="col-sm-4"><label class="form-label">Precio por paquete / blíster (S/)</label><input class="form-control" name="precio_blister" type="number" step="0.01" min="0" value="<?= htmlspecialchars((string)($producto['precio_blister'] ?? ($_POST['precio_blister'] ?? '')),ENT_QUOTES,'UTF-8') ?>"></div>
  <div class="col-sm-4"><label class="form-label">Precio por caja (S/)</label><input class="form-control" name="precio_caja" type="number" step="0.01" min="0" value="<?= htmlspecialchars((string)($producto['precio_caja'] ?? ($_POST['precio_caja'] ?? '')),ENT_QUOTES,'UTF-8') ?>"></div>
 </div>
</div>
<div class="col-12"><small id="fv-stock-hint" class="text-muted" hidden></small></div>
<script src="js/fv-fraccion-productos.js?v=7" defer></script>

<div class="col-12 mt-4"><h5 class="fv-section-title"><i class="bi bi-box-arrow-in-down"></i> Proveedor del ingreso (opcional)</h5></div>
<div class="col-12"><label class="form-check"><input class="form-check-input" type="checkbox" id="fv-initial-stock" name="incluir_existencias" value="1" <?= ($_POST['incluir_existencias']??'')==='1'?'checked':'' ?>><span class="form-check-label">Vincular el ingreso a un proveedor</span></label><p class="fv-note">El stock inicial se ingresa arriba. Activa esta opción para asociar el ingreso a un proveedor. Si ya existe, se asigna una referencia interna automáticamente.</p></div>
<div class="col-12" id="fv-initial-stock-fields">
 <div class="row g-3">
  <div class="col-md-6"><label class="form-label" for="proveedor_id">Proveedor</label><select id="proveedor_id" name="proveedor_id" class="form-select"><option value="">Seleccionar proveedor</option><?php foreach($proveedores as $p): ?><option value="<?= (int)$p['id'] ?>" <?= (string)($_POST['proveedor_id']??'')===(string)$p['id']?'selected':'' ?>><?= htmlspecialchars($p['nombre'],ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select></div>
  <div class="col-md-6" data-provider-document hidden><label class="form-label" for="documento_inicial">Documento del proveedor</label><input id="documento_inicial" name="documento_inicial" class="form-control" maxlength="80" value="<?= htmlspecialchars((string)($_POST['documento_inicial']??''),ENT_QUOTES,'UTF-8') ?>"></div>
  <div class="col-12"><label class="form-check"><input type="checkbox" class="form-check-input" id="fv-new-provider" name="crear_proveedor" value="1" <?= ($_POST['crear_proveedor']??'')==='1'?'checked':'' ?>><span class="form-check-label">El proveedor es nuevo</span></label></div>
  <div class="col-12" id="fv-new-provider-fields"><div class="row g-3"><?php foreach(['nombre'=>'Nombre o razón social','documento'=>'Documento de identidad / RUC','telefono'=>'Teléfono'] as $k=>$label): ?><div class="col-sm-4"><label class="form-label" for="proveedor_<?= $k ?>"><?= $label ?></label><input class="form-control" id="proveedor_<?= $k ?>" name="proveedor_<?= $k ?>" maxlength="<?= $k==='nombre'?150:30 ?>" value="<?= htmlspecialchars((string)($_POST['proveedor_'.$k]??''),ENT_QUOTES,'UTF-8') ?>"></div><?php endforeach; ?></div></div>



 </div>
 <p class="fv-note mt-2">El documento se vincula al stock inicial indicado arriba, sin duplicar el ingreso. No repitas un documento ya registrado para este proveedor.</p>
</div>
<div class="col-12">
                <div class="fv-form-actions">
                    <a href="index.php?c=Producto&a=index" class="fv-cancel"><i class="bi bi-x-circle me-1"></i> Cancelar</a>
                    <button type="submit" class="fv-save"><i class="bi bi-check-circle me-1"></i> Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>

<div class="fv-scan-modal" id="fv-scan-modal" hidden role="dialog" aria-modal="true" aria-labelledby="fv-scan-title">
 <div class="fv-scan-dialog">
  <div class="fv-scan-heading"><h3 id="fv-scan-title"><i class="bi bi-upc-scan"></i> Escanear código</h3><button type="button" data-fv-scan-close aria-label="Cerrar"><i class="bi bi-x-lg"></i></button></div>
  <p>Apunta la cámara al código de barras o QR del producto.</p>
  <video id="fv-scan-video" autoplay playsinline muted></video>
  <div id="fv-scan-fallback" hidden></div>
  <p id="fv-scan-status" role="status" aria-live="polite"></p>
  <button type="button" class="fv-cancel" data-fv-scan-close>Cerrar cámara</button>
 </div>
</div>
<script src="js/fv-existencias-iniciales.js?v=4"></script><script src="js/fv-product-data.js?v=4"></script>
<script src="js/fv-product-scan.js?v=2"></script>
<?php require_once '../views/layouts/footer.php'; ?>
