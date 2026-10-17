<?php 
require_once '../views/layouts/header.php'; ?>

<link rel="stylesheet" href="css/fv-ventas.css">
<div class="fv-pos">
 <header class="fv-pos-head"><div><small>FarmaVidaHC · Caja</small><h2><i class="bi bi-cart-check"></i> Punto de venta</h2><p>Escanea, selecciona la presentación y cobra con control de stock.</p></div><a class="btn btn-outline-primary" href="index.php?c=Venta&a=historial"><i class="bi bi-clock-history"></i> Historial</a><a class="btn btn-outline-success" href="index.php?c=Producto&a=index"><i class="bi bi-capsule"></i> Configurar productos</a></header>
 <?php foreach (['error'=>'danger','success'=>'success'] as $key=>$style): if (!empty($_SESSION[$key])): ?><div class="alert alert-<?= $style ?>"><?= htmlspecialchars($_SESSION[$key],ENT_QUOTES,'UTF-8') ?></div><?php unset($_SESSION[$key]); endif; endforeach; ?>
 <div class="fv-pos-grid">
  <section class="fv-pos-card"><h5><i class="bi bi-box-seam"></i> Catálogo</h5>
   <div class="fv-search"><input type="search" id="codigo_barras" class="form-control" placeholder="Escanea o busca nombre / código" autocomplete="off"><button type="button" class="btn btn-primary" data-fv-scan-open><i class="bi bi-upc-scan"></i> Escanear</button></div>
   <div class="fv-pos-products" id="listaProductos">
    <?php foreach ($productos as $p): if ((int)$p['stock']<=0) continue;
      $json=['id'=>(int)$p['id'],'nombre'=>$p['nombre'],'codigo'=>(string)($p['codigo_barras']??''),'stock'=>(int)$p['stock'],'fraccionable'=>(int)($p['es_fraccionable']??0),'unidad'=>(int)($p['unidades_por_blister']??1),'caja'=>(int)($p['unidades_por_caja']??1),'precio_unidad'=>$p['precio_unidad']??null,'precio_blister'=>$p['precio_blister']??null,'precio_caja'=>$p['precio_caja']??null,'precio_venta'=>$p['precio_venta']]; ?>
    <article class="fv-pos-product" data-search="<?= htmlspecialchars(strtolower($p['nombre'].' '.($p['codigo_barras']??'')),ENT_QUOTES,'UTF-8') ?>" data-producto="<?= htmlspecialchars(json_encode($json,JSON_UNESCAPED_UNICODE|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_TAG|JSON_HEX_AMP),ENT_QUOTES,'UTF-8') ?>"><div><strong><?= htmlspecialchars($p['nombre'],ENT_QUOTES,'UTF-8') ?></strong><small>Stock: <?= (int)$p['stock'] ?> <?= !empty($p['es_fraccionable']) ? 'unidades mínimas' : 'productos' ?> · Código: <?= htmlspecialchars($p['codigo_barras']??'—',ENT_QUOTES,'UTF-8') ?></small></div><button type="button" class="btn btn-sm btn-outline-primary fv-add">Agregar</button></article>
    <?php endforeach; ?>
   </div>
  </section>
  <section class="fv-pos-card"><h5><i class="bi bi-basket"></i> Venta actual</h5>
   <form method="POST" id="formVenta"><input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['fv_venta_csrf'] ?? '',ENT_QUOTES,'UTF-8') ?>"><input type="hidden" name="carrito" id="inputCarrito">
    <div class="row g-2 mb-3"><div class="col-sm-7"><label class="form-label">Cliente</label><input name="cliente" class="form-control" value="Cliente General" maxlength="150"></div><div class="col-sm-5"><label class="form-label">Celular WhatsApp</label><input name="telefono" class="form-control" placeholder="999888777" inputmode="tel"></div></div>
    <div class="row g-2 mb-3"><div class="col-sm-6"><label class="form-label">Comprobante</label><select class="form-select" name="tipo_comprobante" id="tipoComprobante"><option value="ticket">Ticket interno</option><option value="boleta">Boleta electrónica (pendiente SUNAT)</option></select></div><div class="col-sm-3"><label class="form-label">Documento</label><select class="form-select" name="tipo_documento"><option value="">Sin doc.</option><option>DNI</option><option>RUC</option><option>CE</option><option>PASAPORTE</option></select></div><div class="col-sm-3"><label class="form-label">Número</label><input class="form-control" name="numero_documento" maxlength="20"></div></div>
    <div class="small text-muted mb-2">La opción de fraccionar aparece únicamente para productos marcados como fraccionables.</div><div class="table-responsive"><table class="table align-middle table-sm"><thead><tr><th>Producto / presentación</th><th>Cant.</th><th>Subtotal</th><th></th></tr></thead><tbody id="carritoBody"><tr><td colspan="4" class="text-muted text-center py-4">Escanea o agrega productos</td></tr></tbody></table></div>
    <div class="fv-pos-total"><span>Total a cobrar</span><strong>S/ <span id="totalTexto">0.00</span></strong></div><p class="small text-muted mt-2">Los importes y el stock se validan nuevamente en el servidor al confirmar.</p><button type="submit" id="btnVender" class="btn btn-success w-100" disabled><i class="bi bi-check-circle"></i> Registrar venta</button>
   </form>
  </section>
 </div>
</div>
<div class="fv-scan-modal" id="fv-scan-modal" hidden role="dialog" aria-modal="true" aria-labelledby="fv-scan-title"><div class="fv-scan-dialog"><div class="fv-scan-heading"><h3 id="fv-scan-title">Escanear producto</h3><button type="button" data-fv-scan-close aria-label="Cerrar">×</button></div><p>Apunta la cámara al QR o código de barras.</p><video id="fv-scan-video" autoplay playsinline muted></video><div id="fv-scan-fallback" hidden></div><p id="fv-scan-status" role="status" aria-live="polite"></p><button type="button" class="btn btn-outline-secondary" data-fv-scan-close>Cerrar</button></div></div>
<script src="js/fv-ventas.js?v=20261017-2"></script>
<script src="js/fv-product-scan.js"></script>
<?php require_once '../views/layouts/footer.php'; ?>
