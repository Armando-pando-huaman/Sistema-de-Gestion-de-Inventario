<?php require_once '../views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-cart-check"></i> Reporte de Ventas</h2>
    <div>
        <a href="?c=Reporte&a=index" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <button onclick="window.print()" class="btn btn-outline-primary">
            <i class="bi bi-printer"></i> Imprimir
        </button>
    </div>
</div>

<!-- Comparativa mensual -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card stat-card bg-primary">
            <h6>Total Este Mes</h6>
            <h2>S/ <?= number_format($data['comparativa']['mes_actual'] ?? 0, 2) ?></h2>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card stat-card bg-secondary">
            <h6>Total Mes Anterior</h6>
            <h2>S/ <?= number_format($data['comparativa']['mes_anterior'] ?? 0, 2) ?></h2>
        </div>
    </div>
</div>

<!-- Gráfico ventas por día -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-calendar-week"></i> Ventas diarias (últimos <?= $dias ?> días)
    </div>
    <div class="card-body">
        <canvas id="graficoVentasDia" height="70"></canvas>
    </div>
</div>

<!-- Gráfico ventas por hora -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white">
        <i class="bi bi-clock"></i> Horas con más ventas
    </div>
    <div class="card-body">
        <canvas id="graficoVentasHora" height="70"></canvas>
    </div>
</div>

<!-- Tablas por mes, categoría y usuario -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <i class="bi bi-calendar-month"></i> Ventas por Mes
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Mes</th><th class="text-center">Ventas</th><th class="text-end">Total</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($data['ventasPorMes']) as $v): ?>
                            <tr>
                                <td><?= $v['mes_legible'] ?></td>
                                <td class="text-center"><?= $v['cantidad'] ?></td>
                                <td class="text-end text-success">S/ <?= number_format($v['total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['ventasPorMes'])): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Sin datos</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-warning text-dark">
                <i class="bi bi-tags"></i> Ventas por Categoría
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Categoría</th><th class="text-center">Unidades</th><th class="text-end">Ingresos</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['ventasPorCategoria'] as $v): ?>
                            <tr>
                                <td><?= htmlspecialchars($v['categoria']) ?></td>
                                <td class="text-center"><?= $v['total_unidades'] ?></td>
                                <td class="text-end text-success">S/ <?= number_format($v['total_ingresos'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['ventasPorCategoria'])): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Sin datos</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-people"></i> Rendimiento por Vendedor
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th class="text-center">Ventas</th>
                            <th class="text-end">Total Vendido</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['ventasPorUsuario'] as $v): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($v['usuario']) ?></strong></td>
                                <td><span class="badge bg-secondary"><?= $v['rol'] ?></span></td>
                                <td class="text-center"><?= $v['num_ventas'] ?></td>
                                <td class="text-end text-success fw-bold">S/ <?= number_format($v['total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['ventasPorUsuario'])): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Sin datos</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ventasDia = <?= json_encode($data['ventasPorDia']) ?>;
const ventasHora = <?= json_encode($data['ventasPorHora']) ?>;

// Gráfico ventas por día
if (document.getElementById('graficoVentasDia') && ventasDia.length > 0) {
    new Chart(document.getElementById('graficoVentasDia'), {
        type: 'bar',
        data: {
            labels: ventasDia.map(v => v.dia),
            datasets: [{
                label: 'Ventas (S/)',
                data: ventasDia.map(v => parseFloat(v.total)),
                backgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
}

// Gráfico ventas por hora
if (document.getElementById('graficoVentasHora') && ventasHora.length > 0) {
    new Chart(document.getElementById('graficoVentasHora'), {
        type: 'line',
        data: {
            labels: ventasHora.map(v => v.hora + ':00'),
            datasets: [{
                label: 'Ventas',
                data: ventasHora.map(v => v.cantidad),
                borderColor: '#0dcaf0',
                backgroundColor: 'rgba(13, 202, 240, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });
}
</script>

<?php require_once '../views/layouts/footer.php'; ?>