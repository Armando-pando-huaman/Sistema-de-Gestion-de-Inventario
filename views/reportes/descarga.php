<?php
$vistaDescarga = $fvAction;
$opcionesDescarga = [
 'index'=>['finanzas'=>'Resumen financiero','diario'=>'Ventas diarias y comparativa','movimientos'=>'Resumen de movimientos','categorias'=>'Ingresos por categoría','top'=>'Top 5 productos'],
 'ventas'=>['diario'=>'Ventas diarias y comparativa','finanzas'=>'Resumen financiero','distribucion'=>'Todos los productos vendidos','horas'=>'Ventas por hora','meses'=>'Ventas por mes (últimos 12 meses)','categorias'=>'Ventas por categoría','vendedores'=>'Ventas por vendedor'],
 'productos'=>['top'=>'Top 20 productos','bajos'=>'10 productos menos vendidos (histórico)','lotes'=>'Todos los lotes con existencias (actual)'],
 'inventario'=>['lotes'=>'Todos los lotes con existencias (actual)','estado'=>'Resumen del inventario (actual)','bajos'=>'20 productos menos vendidos (histórico)']
];
?>
<form method="get" class="card card-body mb-4 d-print-none" aria-label="Descargar reporte">
 <input type="hidden" name="c" value="Reporte"><input type="hidden" name="a" value="descargar">
 <input type="hidden" name="vista" value="<?= htmlspecialchars($vistaDescarga,ENT_QUOTES,'UTF-8') ?>">
 <input type="hidden" name="dias" value="<?= (int)($dias ?? 30) ?>">
 <input type="hidden" name="orden_lotes" value="<?= htmlspecialchars($ordenLotes ?? 'vencimiento',ENT_QUOTES,'UTF-8') ?>">
 <div class="d-flex align-items-end flex-wrap gap-2">
  <div class="flex-grow-1"><label for="descarga-tipo" class="form-label">Datos para analizar fuera del sistema</label>
   <select id="descarga-tipo" name="tipo" class="form-select"><?php foreach ($opcionesDescarga[$vistaDescarga] ?? [] as $valor=>$texto): ?><option value="<?= $valor ?>"><?= $texto ?></option><?php endforeach; ?></select>
  </div>
  <button class="btn btn-primary" type="submit"><i class="bi bi-box-arrow-in-down" aria-hidden="true"></i> Descargar CSV</button>
 </div>
 <small class="text-muted mt-2">Compatible con Excel y LibreOffice. Importes en soles (PEN), columnas separadas por punto y coma. Aplica primero los filtros: la descarga usa el período y el orden de esta pantalla. Los inventarios son actuales y los datos históricos lo indican en su nombre.</small>
</form>
