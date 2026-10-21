<?php
$progreso = $data['progreso'];
$money = static fn($v) => 'S/ ' . number_format((float)$v, 2);
$esc = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$maximo = 1;
foreach ($progreso['serie'] as $fila) $maximo = max($maximo, $fila['total'], $fila['anterior']);
?>
<section class="op-panel">
<h3>Avance del negocio</h3>
<p class="op-note">Del <?= $esc($progreso['inicio']) ?> al <?= $esc($progreso['fin']) ?>, comparado con <?= $esc($progreso['anterior_inicio']) ?> al <?= $esc($progreso['anterior_fin']) ?>. El día de hoy está en curso.</p>
<div class="op-metrics op-metrics-four">
<article><span>Ventas brutas</span><strong><?= $money($progreso['total']) ?></strong><small>Anterior: <?= $money($progreso['anterior']) ?></small></article>
<article><span>Crecimiento de ingresos</span><strong><?= $progreso['cambio'] === null ? 'Sin base' : sprintf('%+.1f%%', $progreso['cambio']) ?></strong><small><?= $progreso['cambio'] === null ? 'El período anterior no tuvo ingresos.' : 'Frente al período anterior de igual duración.' ?></small></article>
<article><span>Ventas realizadas</span><strong><?= $progreso['cantidad'] ?></strong><small>Anterior: <?= $progreso['cantidad_anterior'] ?></small></article>
<article><span>Ticket promedio</span><strong><?= $money($progreso['ticket']) ?></strong><small>Ingreso promedio por venta.</small></article>
</div>
<p class="op-note">La comparación muestra ventas brutas registradas en cada período. Los reembolsos se descuentan en la fecha en que se realizan, en el resumen operativo de abajo.</p>
</section>
<?php if(isset($data['finanzas'])):$f=$data['finanzas']; ?>
<section class="op-panel"><h3>Resultado operativo del período</h3><div class="op-metrics op-metrics-four"><article><span>Reembolsos</span><strong><?= $money($f['reembolsos']) ?></strong></article><article><span>Ventas netas de reembolsos</span><strong><?= $money($f['neto']) ?></strong></article><article><span>Gastos registrados</span><strong><?= $money($f['gastos']) ?></strong></article><article><span>Resultado operativo estimado</span><strong><?= $f['resultado']===null?'Incompleto':$money($f['resultado']) ?></strong></article></div><p class="op-note">Costo de mercadería vendida, descontando el costo reintegrado por devoluciones: <?= $money($f['costo']) ?>. <?= $f['pendientes'] ?> líneas sin costo histórico documentado. El resultado resta costos y gastos registrados; no incluye impuestos ni gastos aún no registrados. Las compras se reconocen como costo al vender, sin descontarlas dos veces.</p><a href="index.php?c=Gestion&amp;a=finanzas">Revisar o registrar gastos →</a></section>
<?php endif; ?>
<section class="op-panel"><h3>Evolución diaria de ingresos</h3><p class="op-note">Cada par compara un día del período elegido con el día equivalente del período anterior. Incluye días sin ventas. Pasa el cursor, toca una columna o usa Tab para ver el detalle.</p>
<div class="op-legend"><span><i></i> Período elegido</span><span><i class="previous"></i> Período anterior</span></div>
<div class="op-chart-scroll" tabindex="0" aria-label="Gráfico de ingresos diarios; desplázate horizontalmente para ver todos los días">
<div class="op-chart" style="--days:<?= count($progreso['serie']) ?>">
<?php foreach ($progreso['serie'] as $i => $fila): ?>
<button type="button" class="op-chart-day" aria-describedby="report-day-<?= $i ?>" aria-label="<?= $esc($fila['dia']) ?>: <?= $money($fila['total']) ?>, anterior <?= $money($fila['anterior']) ?>">
<span class="op-chart-pair" aria-hidden="true"><i style="height:<?= max(1, 100*$fila['total']/$maximo) ?>%"></i><i class="previous" style="height:<?= max(1, 100*$fila['anterior']/$maximo) ?>%"></i></span>
<small><?= date('d/m', strtotime($fila['dia'])) ?></small>
<span class="op-chart-tip" id="report-day-<?= $i ?>" role="tooltip"><strong><?= $esc($fila['dia']) ?></strong><span><?= $money($fila['total']) ?> · <?= $fila['cantidad'] ?> ventas</span><span>Anterior (<?= $esc($fila['anterior_dia']) ?>): <?= $money($fila['anterior']) ?></span></span>
</button>
<?php endforeach; ?></div></div>
<?php if ($progreso['cantidad'] === 0): ?><p class="op-note">Todavía no hay ventas registradas en el período elegido.</p><?php endif; ?>
<details><summary>Ver datos diarios en tabla</summary><div class="op-table-wrap"><table class="table"><thead><tr><th>Fecha</th><th>Ventas</th><th>Ingresos</th><th>Fecha anterior</th><th>Ingresos anteriores</th></tr></thead><tbody><?php foreach ($progreso['serie'] as $fila): ?><tr><td><?= $esc($fila['dia']) ?></td><td><?= $fila['cantidad'] ?></td><td><?= $money($fila['total']) ?></td><td><?= $esc($fila['anterior_dia']) ?></td><td><?= $money($fila['anterior']) ?></td></tr><?php endforeach; ?></tbody></table></div></details>
</section>
