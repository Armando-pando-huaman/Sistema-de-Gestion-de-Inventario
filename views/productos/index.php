<?php require_once '../views/layouts/header.php'; ?>
<div class="fv-products">
<section class="fv-page-head">
    <div>
        <span class="fv-eyebrow">FarmaVidaHC · Inventario farmacéutico</span>
        <h2><i class="bi bi-capsule-pill me-2"></i> Productos</h2>
        <p>Consulta y administra los productos de tu botica.</p>
    </div>
    <a href="index.php?c=Producto&a=crear" class="fv-btn-light"><i class="bi bi-plus-circle"></i> Nuevo Producto</a>
</section>

<div class="fv-panel">
    <div class="fv-panel-top">
        <h5><i class="bi bi-box-seam me-2 text-success"></i>Listado de productos</h5>
        <small><?= count($productos ?? []) ?> registrados</small>
    </div>
    <div class="fv-table-wrap">
        <table class="table table-hover fv-table">
            <thead><tr><th>ID</th><th>Código</th><th>Nombre</th><th>Categoría</th><th>P. Venta</th><th>Stock</th><th>Vence</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><span class="fv-code"><?= htmlspecialchars($p['codigo_barras'] ?? '-') ?></span></td>
                        <td><span class="fv-product-name"><?= htmlspecialchars($p['nombre']) ?></span></td>
                        <td><span class="fv-category"><?= htmlspecialchars($p['categoria'] ?? '-') ?></span></td>
                        <td><strong>S/ <?= number_format($p['precio_venta'], 2) ?></strong></td>
                        <td><span class="fv-stock <?= $p['stock'] <= $p['stock_minimo'] ? 'low' : 'ok' ?>">
                            <i class="bi bi-circle-fill" style="font-size:.42rem"></i> <?= $p['stock'] ?>
                        </span></td>
                        <td><?= $p['fecha_vencimiento'] ?? '-' ?></td>
                        <td class="text-nowrap">
                            <a href="index.php?c=Producto&a=editar&id=<?= $p['id'] ?>" class="fv-icon-btn edit" title="Editar producto" aria-label="Editar producto"><i class="bi bi-pencil-square"></i></a>
                            <a href="index.php?c=Producto&a=eliminar&id=<?= $p['id'] ?>" class="fv-icon-btn delete" title="Eliminar producto" aria-label="Eliminar producto" onclick="return confirm('¿Eliminar producto?')"><i class="bi bi-trash3"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($productos)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-5"><i class="bi bi-inbox fs-3 d-block mb-2"></i>No hay productos registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
<?php require_once '../views/layouts/footer.php'; ?>
