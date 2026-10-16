<?php require_once '../views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-clipboard-data"></i> Estado del Inventario</h2>
    <div>
        <a href="?c=Reporte&a=index" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary">
            <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>
</div>

<!-- KPIs de inventario -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-primary">
            <i class="bi bi-box-seam"></i>
            <h6>Total Productos</h6>
            <h2><?= $data['estado']['total_productos'] ?? 0 ?></h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-info">
            <i class="bi bi-stack"></i>
            <h6>Unidades en Stock</h6>
            <h2><?= number_format($data['estado']['total_unidades'] ?? 0) ?></h2>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-success">
            <i class="bi bi-cash-stack"></i>
            <h6>Valor a Precio Venta</h6>
            <h2>S/ <?= number_format($data['estado']['valor_venta'] ?? 0, 2) ?></h2>
            <small>Costo: S/ <?= number_format($data['estado']['valor_compra'] ?? 0, 2) ?></small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <h6>Stock Bajo</h6>
            <h2><?= $data['estado']['stock_bajo'] ?? 0 ?></h2>
            <small><?= $data['estado']['agotados'] ?? 0 ?> agotados</small>
        </div>
    </div>
</div>

<!-- Margen de ganancia estimado -->
<div class="alert alert-success">
    <i class="bi bi-graph-up-arrow"></i>
    <strong>Margen de ganancia estimado:</strong>
    S/ <?= number_format(($data['estado']['valor_venta'] ?? 0) - ($data['estado']['valor_compra'] ?? 0), 2) ?>
    (<?= ($data['estado']['valor_compra'] ?? 0) > 0 
        ? number_format((($data['estado']['valor_venta'] - $data['estado']['valor_compra']) / $data['estado']['valor_compra']) * 100, 1) 
        : 0 ?>% sobre el costo)
</div>

<!-- Productos próximos a vencer -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-danger text-white">
        <i class="bi bi-calendar-x"></i> Medicamentos Próximos a Vencer (90 días) - 
        <span class="badge bg-light text-dark"><?= count($data['porVencer']) ?></span>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th class="text-center">Stock</th>
                    <th>Vencimiento</th>
                    <th class="text-center">Días Restantes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['porVencer'] as $p): ?>
                    <tr class="<?= $p['dias_restantes'] <= 30 ? 'table-danger' : ($p['dias_restantes'] <= 60 ? 'table-warning' : '') ?>">
                        <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                        <td><small class="text-muted"><?= htmlspecialchars($p['categoria'] ?? '-') ?></small></td>
                        <td class="text-center"><?= $p['stock'] ?></td>
                        <td><?= date('d/m/Y', strtotime($p['fecha_vencimiento'])) ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $p['dias_restantes'] <= 30 ? 'danger' : ($p['dias_restantes'] <= 60 ? 'warning' : 'secondary') ?>">
                                <?= $p['dias_restantes'] ?> días
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($data['porVencer'])): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">
                        ✅ No hay productos próximos a vencer en los siguientes 90 días.
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../views/layouts/footer.php'; ?>