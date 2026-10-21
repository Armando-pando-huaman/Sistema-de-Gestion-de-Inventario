<?php require_once '../views/layouts/header.php'; ?>
<link rel="stylesheet" href="css/fv-operaciones.css?v=1">
<div class="fv-operations">
<form method="get" class="op-panel op-period"><input type="hidden" name="c" value="Reporte"><input type="hidden" name="a" value="ventas"><label for="ventas-days">Período</label><select id="ventas-days" name="dias" class="form-select"><?php foreach ([7,15,30,60,90,365] as $n): ?><option value="<?= $n ?>" <?= $dias === $n ? 'selected' : '' ?>>Últimos <?= $n ?> días</option><?php endforeach; ?></select><button class="btn btn-primary">Actualizar</button></form>

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

<?php require '../views/reportes/progreso.php'; ?>
<!-- Gráfico ventas por hora -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-info text-white">
        <i class="bi bi-clock"></i> Horas con más ventas
    </div>
    <div class="card-body op-hour-chart">
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
const ventasHora = <?= json_encode($data['ventasPorHora']) ?>;
// Gráfico ventas por hora
if (document.getElementById('graficoVentasHora') && ventasHora.length > 0) {
    const hourlyChart = new Chart(document.getElementById('graficoVentasHora'), {
        type: 'line',
        data: {
            labels: ventasHora.map(v => v.hora + ':00'),
            datasets: [{
                label: 'Ventas',
                data: ventasHora.map(v => v.cantidad),
                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--care-primary').trim(),
                backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--care-soft').trim(),
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
    new MutationObserver(() => {
        const colors = getComputedStyle(document.documentElement);
        hourlyChart.data.datasets[0].borderColor = colors.getPropertyValue('--care-primary').trim();
        hourlyChart.data.datasets[0].backgroundColor = colors.getPropertyValue('--care-soft').trim();
        hourlyChart.update();
    }).observe(document.documentElement, {attributes: true, attributeFilter: ['data-fv-palette']});
}
</script>

</div>
<?php require_once '../views/layouts/footer.php'; ?>
