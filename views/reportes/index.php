<?php require_once '../views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-graph-up-arrow"></i> Reportes y Estadísticas</h2>
    <div class="btn-group">
        <a href="?c=Reporte&a=ventas" class="btn btn-outline-primary">
            <i class="bi bi-cart-check"></i> Ventas
        </a>
        <a href="?c=Reporte&a=productos" class="btn btn-outline-success">
            <i class="bi bi-box-seam"></i> Productos
        </a>
        <a href="?c=Reporte&a=inventario" class="btn btn-outline-warning">
            <i class="bi bi-clipboard-data"></i> Inventario
        </a>
    </div>
</div>

<!-- Filtro de período -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="c" value="Reporte">
            <input type="hidden" name="a" value="index">
            <div class="col-auto">
                <label class="form-label mb-0"><strong>Período:</strong></label>
            </div>
            <div class="col-auto">
                <select name="dias" class="form-select" onchange="this.form.submit()">
                    <option value="7" <?= ($dias ?? 30) == 7 ? 'selected' : '' ?>>Últimos 7 días</option>
                    <option value="15" <?= ($dias ?? 30) == 15 ? 'selected' : '' ?>>Últimos 15 días</option>
                    <option value="30" <?= ($dias ?? 30) == 30 ? 'selected' : '' ?>>Últimos 30 días</option>
                    <option value="60" <?= ($dias ?? 30) == 60 ? 'selected' : '' ?>>Últimos 60 días</option>
                    <option value="90" <?= ($dias ?? 30) == 90 ? 'selected' : '' ?>>Últimos 90 días</option>
                    <option value="365" <?= ($dias ?? 30) == 365 ? 'selected' : '' ?>>Último año</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Tarjetas KPI -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-primary">
            <i class="bi bi-cash-coin"></i>
            <h6>Ventas Hoy</h6>
            <h2>S/ <?= number_format($data['ventasHoy']['total'] ?? 0, 2) ?></h2>
            <small><?= $data['ventasHoy']['cantidad'] ?? 0 ?> transacciones</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-success">
            <i class="bi bi-calendar-month"></i>
            <h6>Este Mes</h6>
            <h2>S/ <?= number_format($data['comparativa']['mes_actual'] ?? 0, 2) ?></h2>
            <?php 
                $actual = $data['comparativa']['mes_actual'] ?? 0;
                $anterior = $data['comparativa']['mes_anterior'] ?? 0;
                $cambio = $anterior > 0 ? (($actual - $anterior) / $anterior) * 100 : 0;
            ?>
            <small>
                <?php if ($cambio >= 0): ?>
                    <i class="bi bi-arrow-up"></i> +<?= number_format($cambio, 1) ?>%
                <?php else: ?>
                    <i class="bi bi-arrow-down"></i> <?= number_format($cambio, 1) ?>%
                <?php endif; ?>
                vs mes anterior
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-info">
            <i class="bi bi-box-seam"></i>
            <h6>Valor Inventario</h6>
            <h2>S/ <?= number_format($data['estadoInventario']['valor_venta'] ?? 0, 2) ?></h2>
            <small><?= $data['estadoInventario']['total_unidades'] ?? 0 ?> unidades</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-warning">
            <i class="bi bi-exclamation-triangle"></i>
            <h6>Stock Bajo</h6>
            <h2><?= $data['estadoInventario']['stock_bajo'] ?? 0 ?></h2>
            <small><?= $data['estadoInventario']['agotados'] ?? 0 ?> agotados</small>
        </div>
    </div>
</div>

<!-- Gráfico de ventas por día -->
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-graph-up"></i> Ventas últimos <?= $dias ?> días
            </div>
            <div class="card-body">
                <canvas id="graficoVentasDia" height="80"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <i class="bi bi-pie-chart"></i> Ventas por Categoría
            </div>
            <div class="card-body">
                <?php if (!empty($data['ventasPorCategoria'])): ?>
                    <canvas id="graficoCategorias" height="200"></canvas>
                <?php else: ?>
                    <p class="text-muted text-center py-5">No hay datos de ventas en este período.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Top productos -->
<div class="row g-3">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white">
                <i class="bi bi-trophy"></i> Top 5 Productos más Vendidos (<?= $dias ?> días)
            </div>
            <div class="card-body p-0">
                <?php if (!empty($data['productosTop'])): ?>
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th class="text-center">Unidades</th>
                                <th class="text-end">Ingresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['productosTop'] as $i => $p): ?>
                                <tr>
                                    <td>
                                        <?php if ($i == 0): ?>
                                            <span class="badge bg-warning text-dark">🥇 1</span>
                                        <?php elseif ($i == 1): ?>
                                            <span class="badge bg-secondary">🥈 2</span>
                                        <?php elseif ($i == 2): ?>
                                            <span class="badge bg-danger">🥉 3</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark"><?= $i + 1 ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?= htmlspecialchars($p['nombre']) ?></strong></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($p['categoria'] ?? '-') ?></small></td>
                                    <td class="text-center"><?= $p['total_vendido'] ?></td>
                                    <td class="text-end text-success fw-bold">S/ <?= number_format($p['total_ingresos'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted text-center py-4">No hay ventas en este período.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Preparar datos
const ventasDia = <?= json_encode($data['ventasPorDia']) ?>;
const categorias = <?= json_encode($data['ventasPorCategoria']) ?>;

// Gráfico de ventas por día
const ctx1 = document.getElementById('graficoVentasDia');
if (ctx1 && ventasDia.length > 0) {
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: ventasDia.map(v => v.dia),
            datasets: [{
                label: 'Ventas (S/)',
                data: ventasDia.map(v => parseFloat(v.total)),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointBackgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => 'S/ ' + ctx.parsed.y.toFixed(2)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (v) => 'S/ ' + v
                    }
                }
            }
        }
    });
}

// Gráfico de categorías
const ctx2 = document.getElementById('graficoCategorias');
if (ctx2 && categorias.length > 0) {
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: categorias.map(c => c.categoria),
            datasets: [{
                data: categorias.map(c => parseFloat(c.total_ingresos)),
                backgroundColor: [
                    '#0d6efd', '#198754', '#ffc107', '#dc3545', 
                    '#6f42c1', '#fd7e14', '#20c997', '#d63384'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 10 } }
                }
            }
        }
    });
}
</script>

<?php require_once '../views/layouts/footer.php'; ?>