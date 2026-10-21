<?php require_once '../views/layouts/header.php'; ?>
<link rel="stylesheet" href="css/fv-productos.css?v=9"><link rel="stylesheet" href="css/fv-operaciones.css?v=2">
<div class="fv-products fv-product-catalog">
<section class="fv-page-head">
    <div>
        <span class="fv-eyebrow">FarmaVidaHC · Inventario farmacéutico</span>
        <h2><i class="bi bi-capsule-pill me-2"></i> Productos</h2>
        <p>Consulta y administra los productos de tu botica.</p>
    </div>
    <div class="fv-product-head-actions"><a href="index.php?c=Categoria&a=index" class="fv-btn-light"><i class="bi bi-tags"></i> Categorías</a><?php if($fvIsAdmin): ?><a href="index.php?c=Gestion&amp;a=compras" class="fv-btn-light"><i class="bi bi-journal-text"></i> Historial de entradas</a><a href="index.php?c=Producto&a=crear" class="fv-btn-light"><i class="bi bi-plus-circle"></i> Nuevo Producto</a><?php endif; ?></div>
</section>

<form method="get" class="op-filters mb-3"><input type="hidden" name="c" value="Producto"><input type="hidden" name="a" value="index"><input name="q" class="form-control" placeholder="Buscar por nombre o código" aria-label="Buscar producto" value="<?= htmlspecialchars($busqueda,ENT_QUOTES,'UTF-8') ?>"><select name="inactivos" class="form-select" aria-label="Estado"><option value="0">Activos</option><option value="1" <?= $inactivos?'selected':'' ?>>Inactivos</option></select><button class="btn btn-primary">Buscar</button></form><div class="fv-panel">
    <div class="fv-panel-top">
        <h5><i class="bi bi-box-seam me-2 text-success"></i>Listado de productos</h5>
        <small><?= (int)$listado['total'] ?> registrados</small>
    </div>
    <div class="fv-table-wrap">
        <table class="table table-hover fv-table fv-product-list" aria-label="Listado de productos">
            <thead><tr><th>ID</th><th>Código</th><th>Nombre</th><th>Categoría</th><th>P. Venta</th><th>Stock</th><th>Vence</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php foreach ($productos as $p): ?>
                    <tr>
                        <td class="fv-product-secondary"><?= $p['id'] ?></td>
                        <td class="fv-product-secondary"><span class="fv-code"><?= htmlspecialchars($p['codigo_barras'] ?? '-') ?></span></td>
                        <td class="fv-product-title"><span class="fv-product-name"><?= htmlspecialchars($p['nombre']) ?></span>
                            <?php if($fvIsAdmin): ?><div class="fv-product-stock-links">
                            <?php if($p['activo']): ?><a href="index.php?c=Producto&amp;a=existencias&amp;id=<?= (int)$p['id'] ?>"><i class="bi bi-plus-circle" aria-hidden="true"></i> Agregar existencias</a><?php endif; ?>
                            <a href="index.php?c=Gestion&amp;a=lotes&amp;producto_id=<?= (int)$p['id'] ?>"><i class="bi bi-boxes" aria-hidden="true"></i> Ver lotes</a></div><?php endif; ?>
                        </td>
                        <td class="fv-product-secondary"><span class="fv-category"><?= htmlspecialchars($p['categoria'] ?? '-') ?></span></td>
                        <td data-label="Precio de venta"><strong>S/ <?= number_format($p['precio_venta'], 2) ?></strong></td>
                        <td data-label="Stock"><span class="fv-stock <?= $p['stock'] <= $p['stock_minimo'] ? 'low' : 'ok' ?>">
                            <i class="bi bi-circle-fill" style="font-size:.42rem"></i> <?= $p['stock'] ?>
                        </span></td>
                        <td data-label="Vencimiento"><?= htmlspecialchars($p['fecha_vencimiento'] ?? '-') ?></td>
                        <td class="text-nowrap fv-product-actions" data-label="Acciones">
                            <?php if($fvIsAdmin): ?><a href="index.php?c=Producto&a=editar&id=<?= $p['id'] ?>" class="fv-icon-btn edit" title="Editar producto" aria-label="Editar producto"><i class="bi bi-pencil-square"></i></a><?php endif; ?>
                            <?php if($fvIsAdmin): ?><form method="post" action="index.php?c=Producto&amp;a=eliminar" class="d-inline"><input type="hidden" name="csrf" value="<?= $fvEsc(Seguridad::token()) ?>"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="fv-icon-btn delete" aria-label="<?= $inactivos?'Reactivar':'Desactivar' ?> producto" title="<?= $inactivos?'Reactivar':'Desactivar' ?> producto" onclick="return confirm('¿Cambiar el estado del producto? Se conservará su historial.')"><i class="bi bi-power"></i></button></form><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($productos)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-5 fv-product-empty"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No hay productos registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
<nav class="op-pagination mt-3"><span>Página <?= $listado['pagina'] ?> de <?= $listado['paginas'] ?></span><?php foreach([-1=>'Anterior',1=>'Siguiente'] as $delta=>$label): $page=$listado['pagina']+$delta; if($page>=1&&$page<=$listado['paginas']): ?><a class="btn btn-outline-primary" href="index.php?<?= htmlspecialchars(http_build_query(['c'=>'Producto','a'=>'index','q'=>$busqueda,'pagina'=>$page,'inactivos'=>(int)$inactivos]),ENT_QUOTES,'UTF-8') ?>"><?= $label ?></a><?php endif;endforeach; ?></nav><?php require_once '../views/layouts/footer.php'; ?>
