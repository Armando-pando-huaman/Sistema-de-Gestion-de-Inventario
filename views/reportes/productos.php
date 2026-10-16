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
                        <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
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
    <div class="col-md-6">
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

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-calendar-x"></i> Productos Próximos a Vencer (90 días)
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Producto</th><th>Vence</th><th class="text-center">Días</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['porVencer'] as $p): ?>
                            <tr class="<?= $p['dias_restantes'] <= 30 ? 'table-danger' : ($p['dias_restantes'] <= 60 ? 'table-warning' : '') ?>">
                                <td><small><?= htmlspecialchars($p['nombre']) ?></small></td>
                                <td><small><?= date('d/m/Y', strtotime($p['fecha_vencimiento'])) ?></small></td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $p['dias_restantes'] <= 30 ? 'danger' : ($p['dias_restantes'] <= 60 ? 'warning' : 'secondary') ?>">
                                        <?= $p['dias_restantes'] ?> días
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['porVencer'])): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">No hay productos por vencer</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../views/layouts/footer.php'; ?>