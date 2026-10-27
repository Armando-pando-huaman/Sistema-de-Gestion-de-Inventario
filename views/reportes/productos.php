<?php require_once '../views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam"></i> Reporte de Productos</h2>
    <div>
        <a href="?c=Reporte&a=index" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary">
            <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>
</div>

<?php require '../views/reportes/descarga.php'; ?>
<form method="get" class="d-flex align-items-end flex-wrap gap-2 mb-4 d-print-none">
 <input type="hidden" name="c" value="Reporte"><input type="hidden" name="a" value="productos">
 <input type="hidden" name="orden_lotes" value="<?= htmlspecialchars($ordenLotes,ENT_QUOTES,'UTF-8') ?>">
 <div><label for="productos-days" class="form-label">Período de ventas</label><select id="productos-days" name="dias" class="form-select"><?php foreach ([7,15,30,60,90,365] as $n): ?><option value="<?= $n ?>" <?= $dias === $n ? 'selected' : '' ?>>Últimos <?= $n ?> días</option><?php endforeach; ?></select></div><button class="btn btn-outline-primary">Actualizar</button>
</form>
<!-- Top 20 productos más vendidos -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-success text-white">
        <i class="bi bi-trophy"></i> Top 20 Productos Más Vendidos (<?= $dias ?> días)
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th class="text-center">Unidades Vendidas</th>
                    <th class="text-center">N° Ventas</th>
                    <th class="text-end">Ingresos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['productosTop'] as $i => $p): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($p['nombre']) ?></strong><?php if(isset($p['lote'])): ?><br><small>Lote: <?= htmlspecialchars($p['lote'],ENT_QUOTES,'UTF-8') ?></small><?php endif; ?></td>
                        <td><small class="text-muted"><?= htmlspecialchars($p['categoria'] ?? '-') ?></small></td>
                        <td class="text-center"><span class="badge bg-primary"><?= $p['total_vendido'] ?></span></td>
                        <td class="text-center"><?= $p['num_ventas'] ?></td>
                        <td class="text-end text-success fw-bold">S/ <?= number_format($p['total_ingresos'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($data['productosTop'])): ?>
                    <tr><td colspan="6" class="text-center text-muted py-3">Sin ventas en este período</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Productos menos vendidos + Por vencer -->
<div class="row g-3">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-arrow-down-circle"></i> Productos con Menos Rotación
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Producto</th><th class="text-center">Vendidos</th><th class="text-center">Stock</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['productosBajos'] as $p): ?>
                            <tr>
                                <td><small><?= htmlspecialchars($p['nombre']) ?></small></td>
                                <td class="text-center"><?= $p['total_vendido'] ?></td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $p['stock'] > 0 ? 'success' : 'danger' ?>">
                                        <?= $p['stock'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<?php require __DIR__.'/lotes.php'; ?>
<?php require_once '../views/layouts/footer.php'; ?>
