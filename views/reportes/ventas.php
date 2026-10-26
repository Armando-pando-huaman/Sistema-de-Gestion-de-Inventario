<?php require_once '../views/layouts/header.php'; ?>
<link rel="stylesheet" href="css/fv-operaciones.css">
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
<?php
 $distribucion=$data['distribucionProductos'];
 $unidadesTotal=(int)array_sum(array_column($distribucion,'unidades'));
 $porciones=array_slice($distribucion,0,5);
 if(count($distribucion)>5){$otros=array_slice($distribucion,5);$porciones[]=['nombre'=>'Otros productos','unidades'=>array_sum(array_column($otros,'unidades')),'ingresos'=>array_sum(array_column($otros,'ingresos'))];}
?>
<section class="op-panel">
 <h3><i class="bi bi-pie-chart"></i> Productos más vendidos · últimos <?= $dias ?> días</h3>
 <p class="op-note">Participación por unidades mínimas vendidas, descontando devoluciones registradas. Los cinco productos principales se muestran por separado y los demás se agrupan en “Otros productos”.</p>
 <?php if($unidadesTotal>0): ?>
 <div class="op-donut-layout">
  <div class="op-donut-canvas"><canvas id="graficoProductosVendidos" role="img" aria-label="Distribución de <?= $unidadesTotal ?> unidades vendidas por producto. Los valores están disponibles en la tabla contigua."></canvas></div>
  <div class="op-table-wrap"><table class="table table-sm"><thead><tr><th>Producto</th><th class="text-end">Unidades</th><th class="text-end">Porcentaje</th><th class="text-end">Ingresos</th></tr></thead><tbody>
  <?php foreach($porciones as $i=>$producto): ?><tr><td><span class="op-donut-key" data-donut-key="<?= $i ?>" aria-hidden="true"></span><?= htmlspecialchars($producto['nombre'],ENT_QUOTES,'UTF-8') ?></td><td class="text-end"><?= number_format($producto['unidades'],0) ?></td><td class="text-end"><?= number_format(100*$producto['unidades']/$unidadesTotal,1) ?>%</td><td class="text-end">S/ <?= number_format($producto['ingresos'],2) ?></td></tr><?php endforeach; ?>
  </tbody><tfoot><tr><th>Total</th><th class="text-end"><?= number_format($unidadesTotal,0) ?></th><th class="text-end">100%</th><th class="text-end">S/ <?= number_format(array_sum(array_column($distribucion,'ingresos')),2) ?></th></tr></tfoot></table></div>
 </div>
 <details><summary>Ver cantidades y porcentajes de todos los productos</summary><div class="op-table-wrap"><table class="table table-sm"><thead><tr><th>Producto</th><th class="text-end">Unidades</th><th class="text-end">Porcentaje</th></tr></thead><tbody><?php foreach($distribucion as $producto): ?><tr><td><?= htmlspecialchars($producto['nombre'],ENT_QUOTES,'UTF-8') ?></td><td class="text-end"><?= number_format($producto['unidades'],0) ?></td><td class="text-end"><?= number_format(100*$producto['unidades']/$unidadesTotal,1) ?>%</td></tr><?php endforeach; ?></tbody></table></div></details>
 <?php else: ?><p class="op-note">No hay unidades vendidas en este período para generar el gráfico.</p><?php endif; ?>
</section>
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
<script type="application/json" id="fv-product-sales-data"><?= json_encode(['porciones'=>$porciones,'total'=>$unidadesTotal],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_THROW_ON_ERROR) ?></script>
<script src="js/fv-reporte-productos.js"></script>
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
