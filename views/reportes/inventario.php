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

<div class="alert alert-info"><strong>Valoración de existencias:</strong> costo documentado por lote: S/ <?= number_format($data['estado']['valor_compra'],2) ?>. <?= (int)$data['estado']['unidades_sin_costo'] ?> unidades todavía sin costo documentado. El valor de venta es una referencia a precios actuales, no una ganancia realizada.</div>
<?php require __DIR__.'/lotes.php'; ?>
<?php require_once '../views/layouts/footer.php'; ?>
