
<?php
/*  Dashboard estilo BoticaCare
   Paso 1: estructura PHP y HTML.*/

require_once '../views/layouts/header.php';

$totalProductosVista = (int) ($totalProductos ?? 0);
$totalMovimientosVista = (int) ($totalMovimientos ?? 0);
$stockBajoVista = is_array($productosBajos ?? null) ? $productosBajos : [];
$movimientosVista = is_array($ultimosMovimientos ?? null) ? $ultimosMovimientos : [];
$porVencerVista = (int) ($porVencer ?? 0);

$nombreOriginal = (string) ($_SESSION['user_nombre'] ?? 'Usuario');
$nombreVista = htmlspecialchars($nombreOriginal, ENT_QUOTES, 'UTF-8');
$esAdminVista = ($_SESSION['user_rol'] ?? '') === 'admin';

$nombreCorto = trim(explode(' ', trim($nombreOriginal))[0] ?? '');
$nombreCorto = htmlspecialchars($nombreCorto ?: 'Usuario', ENT_QUOTES, 'UTF-8');

$horaActual = (int) date('G');
$saludo = $horaActual < 12 ? 'Buenos días' : ($horaActual < 19 ? 'Buenas tardes' : 'Buenas noches');

$diasSemana = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
$meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
          'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

$fechaTexto = ucfirst($diasSemana[(int) date('w')]) . ', ' .
              date('j') . ' de ' . $meses[(int) date('n')] .
              ' de ' . date('Y');

$totalAlertas = count($stockBajoVista) + $porVencerVista;

$entradasRecientes = 0;
$salidasRecientes = 0;

foreach ($movimientosVista as $movimiento) {
    $tipo = strtolower((string) ($movimiento['tipo'] ?? ''));
    if ($tipo === 'entrada') $entradasRecientes++;
    if ($tipo === 'salida') $salidasRecientes++;
}
?>

<link rel="stylesheet" href="css/fv-dashboard.css?v=2">
<div class="fv-dashboard fv-dashboard-care">
    <!-- BIENVENIDA -->
    <section class="fv-care-greeting">
        <div>
            <span class="fv-care-date">
                <i class="bi bi-calendar3"></i>
                <?= $fechaTexto ?>
            </span>

            <h1><?= $saludo ?>, <?= $nombreCorto ?>
                <i class="bi bi-stars"></i>
            </h1>

            <p>Aquí tienes un resumen de lo que ocurre en tu botica hoy.</p>
        </div>

        <div class="fv-care-greeting-icon">
            <i class="bi bi-heart-pulse"></i>
        </div>
    </section>

    <!-- CUATRO INDICADORES -->
    <section class="fv-care-stats" aria-label="Indicadores de la botica">

        <article class="fv-care-stat fv-care-stat--products">
            <div class="fv-care-stat-top">
                <span class="fv-care-stat-icon">
                    <i class="bi bi-capsule-pill"></i>
                </span>
                <span class="fv-care-stat-tag">Catálogo</span>
            </div>
            <div class="fv-care-stat-content">
                <span class="fv-care-stat-label">Productos registrados</span>
                <strong class="fv-care-stat-value">
                    <?= number_format($totalProductosVista, 0, ',', '.') ?>
                </strong>
                <small>Productos en el sistema</small>
            </div>
            <div class="fv-care-stat-decoration">
                <i class="bi bi-box-seam"></i>
            </div>
        </article>

        <article class="fv-care-stat fv-care-stat--moves">
            <div class="fv-care-stat-top">
                <span class="fv-care-stat-icon">
                    <i class="bi bi-arrow-left-right"></i>
                </span>
                <span class="fv-care-stat-tag">Actividad</span>
            </div>
            <div class="fv-care-stat-content">
                <span class="fv-care-stat-label">Movimientos registrados</span>
                <strong class="fv-care-stat-value">
                    <?= number_format($totalMovimientosVista, 0, ',', '.') ?>
                </strong>
                <small>Entradas y salidas del inventario</small>
            </div>
            <div class="fv-care-stat-decoration">
                <i class="bi bi-activity"></i>
            </div>
        </article>

        <article class="fv-care-stat fv-care-stat--low">
            <div class="fv-care-stat-top">
                <span class="fv-care-stat-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </span>
                <span class="fv-care-stat-tag">Revisar</span>
            </div>
            <div class="fv-care-stat-content">
                <span class="fv-care-stat-label">Alertas de stock</span>
                <strong class="fv-care-stat-value">
                    <?= count($stockBajoVista) ?>
                </strong>
                <small>Productos con existencias bajas</small>
            </div>
            <div class="fv-care-stat-decoration">
                <i class="bi bi-exclamation-circle"></i>
            </div>
        </article>

        <article class="fv-care-stat fv-care-stat--expiry">
            <div class="fv-care-stat-top">
                <span class="fv-care-stat-icon">
                    <i class="bi bi-calendar-x"></i>
                </span>
                <span class="fv-care-stat-tag">30 días</span>
            </div>
            <div class="fv-care-stat-content">
                <span class="fv-care-stat-label">Próximos a vencer</span>
                <strong class="fv-care-stat-value">
                    <?= number_format($porVencerVista, 0, ',', '.') ?>
                </strong>
                <small>Productos por verificar</small>
            </div>
            <div class="fv-care-stat-decoration">
                <i class="bi bi-calendar-event"></i>
            </div>
        </article>

    </section>

    <!-- RESUMEN PRINCIPAL Y ACTIVIDAD -->
    <section class="fv-care-main-grid">
        <article class="fv-care-panel fv-care-chart-panel">
          <header class="fv-care-panel-header"><div><h3>Movimientos de inventario</h3><p>Actividad registrada durante los últimos 7 días</p></div><span class="fv-care-chart-tag">Esta semana</span></header>
          <?php
            $fvWeekCounts=array_fill(0,7,0);
            $fvDates=[];
            $fvDayDetails=[];
            foreach (($movimientosPorDia ?? []) as $fvRow) { $fvDayDetails[$fvRow['dia']]=$fvRow; }
            for($fvI=0;$fvI<7;$fvI++){$fvDates[$fvI]=date('Y-m-d',strtotime('-'.(6-$fvI).' days'));}
            foreach($fvDates as $fvI=>$fvDay){$fvWeekCounts[$fvI]=(int)($fvDayDetails[$fvDay]['total']??0);}
            $fvMax=max(1,max($fvWeekCounts));
          ?>
          <div class="fv-care-chart-body"><div class="fv-care-chart-summary"><strong><?= array_sum($fvWeekCounts) ?></strong><span>movimientos en los últimos 7 días. Pasa el cursor o toca una barra para ver el detalle.</span></div><div class="fv-care-chart-bars">
          <?php foreach($fvWeekCounts as $fvI=>$fvCount):
              $fvDateLabel=date('d/m/Y',strtotime($fvDates[$fvI]));
              $fvEntries=(int)($fvDayDetails[$fvDates[$fvI]]['entradas']??0);
              $fvExits=(int)($fvDayDetails[$fvDates[$fvI]]['salidas']??0);
          ?>
            <button type="button" class="fv-care-chart-column" aria-label="<?= $fvDateLabel ?>: <?= $fvCount ?> movimientos, <?= $fvEntries ?> entradas y <?= $fvExits ?> salidas" aria-describedby="fv-day-<?= $fvI ?>">
                <span class="fv-care-chart-bar" style="height:<?= max(4,round(100*$fvCount/$fvMax)) ?>%" aria-hidden="true"></span>
                <small><?= ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'][(int)date('w',strtotime($fvDates[$fvI]))] ?></small>
                <span class="fv-chart-tooltip" id="fv-day-<?= $fvI ?>" role="tooltip"><strong><?= $fvDateLabel ?></strong><span><?= $fvCount ?> movimientos</span><span>Entradas: <?= $fvEntries ?></span><span>Salidas: <?= $fvExits ?></span></span>
            </button>
          <?php endforeach; ?>
          </div></div>
        </article>
        <!-- ACTIVIDAD RECIENTE -->
        <article class="fv-care-panel fv-care-activity">
            <header class="fv-care-panel-header">
                <div>
                    <h3>Actividad reciente</h3>
                    <p>Últimos movimientos registrados</p>
                </div>
                <a href="index.php?c=Movimiento&a=index"
                   class="fv-care-panel-action">
                    Ver todo <i class="bi bi-arrow-up-right"></i>
                </a>
            </header>

            <div class="fv-care-activity-list">
                <?php if (!empty($movimientosVista)): ?>
                    <?php foreach (array_slice($movimientosVista, 0, 5) as $m): ?>
                        <?php
                        $tipo = strtolower((string) ($m['tipo'] ?? ''));
                        $esEntrada = $tipo === 'entrada';
                        $fechaMovimiento = strtotime((string) ($m['fecha'] ?? ''));
                        ?>
                        <div class="fv-care-activity-item">
                            <span class="fv-care-activity-icon <?= $esEntrada ? 'is-entry' : 'is-exit' ?>">
                                <i class="bi <?= $esEntrada ? 'bi-box-arrow-in-down' : 'bi-box-arrow-up-right' ?>"></i>
                            </span>

                            <div class="fv-care-activity-details">
                                <strong><?= $esEntrada ? 'Ingreso de inventario' : 'Salida de inventario' ?></strong>
                                <span>
                                    <?= htmlspecialchars((string) ($m['producto'] ?? 'Producto'), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <small>
                                    <?= $fechaMovimiento !== false ? date('d/m/Y H:i', $fechaMovimiento) : 'Sin fecha' ?>
                                </small>
                            </div>

                            <strong class="fv-care-activity-quantity <?= $esEntrada ? 'is-entry' : 'is-exit' ?>">
                                <?= $esEntrada ? '+' : '-' ?>
                                <?= htmlspecialchars((string) ($m['cantidad'] ?? 0), ENT_QUOTES, 'UTF-8') ?>
                            </strong>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="fv-care-empty">
                        <i class="bi bi-inbox"></i>
                        <p>No hay movimientos registrados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    </section>

    <!-- TABLA DE INVENTARIO -->
    <section class="fv-care-panel fv-care-inventory">
        <header class="fv-care-panel-header">
            <div>
                <h3>Inventario por revisar</h3>
                <p>Productos que necesitan atención</p>
            </div>

            <div class="fv-care-inventory-tools">
                <div class="fv-care-search">
                    <i class="bi bi-search"></i>
                    <input type="search"
                           id="fv-care-search-product"
                           placeholder="Buscar producto..."
                           aria-label="Buscar productos en la tabla">
                </div>

                <a href="index.php?c=Producto&a=index"
                   class="fv-care-panel-action">
                    Ver inventario <i class="bi bi-arrow-up-right"></i>
                </a>
            </div>
        </header>

        <div class="fv-care-table-wrapper">
            <table class="fv-care-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Stock mínimo</th>
                        <th>Stock actual</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="fv-care-products-body">
                    <?php if (!empty($stockBajoVista)): ?>
                        <?php foreach (array_slice($stockBajoVista, 0, 8) as $p): ?>
                            <?php
                            $stockActual = (float) ($p['stock'] ?? 0);
                            $stockMinimo = (float) ($p['stock_minimo'] ?? 0);
                            $esCritico = $stockActual <= 0;
                            ?>
                            <tr>
                                <td>
                                    <div class="fv-care-product-cell">
                                        <span class="fv-care-product-icon">
                                            <i class="bi bi-capsule"></i>
                                        </span>
                                        <strong>
                                            <?= htmlspecialchars((string) ($p['nombre'] ?? 'Producto'), ENT_QUOTES, 'UTF-8') ?>
                                        </strong>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars((string) ($p['stock_minimo'] ?? 0), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <strong><?= htmlspecialchars((string) ($p['stock'] ?? 0), ENT_QUOTES, 'UTF-8') ?></strong>
                                </td>
                                <td>
                                    <span class="fv-care-status <?= $esCritico ? 'is-critical' : 'is-low' ?>">
                                        <span class="fv-care-status-dot"></span>
                                        <?= $esCritico ? 'Sin stock' : 'Stock bajo' ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="index.php?c=Producto&a=index"
                                       class="fv-care-table-action"
                                       title="Ver inventario">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="fv-care-empty">
                                    <i class="bi bi-check-circle"></i>
                                    <p>Todos los productos tienen stock suficiente.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- ACCESOS RÁPIDOS -->
    <section class="fv-care-shortcuts">
        <div class="fv-care-shortcuts-heading">
            <h3>Accesos rápidos</h3>
            <p>Operaciones frecuentes de tu botica</p>
        </div>

        <div class="fv-care-shortcuts-grid">
            <?php if($esAdminVista): ?><a href="index.php?c=Producto&a=crear"
               class="fv-care-shortcut fv-care-shortcut--product">
                <i class="bi bi-plus-circle"></i>
                <span>Nuevo producto</span>
                <i class="bi bi-arrow-up-right"></i>
            </a>

            <?php endif; ?><a href="index.php?c=Venta&a=index"
               class="fv-care-shortcut fv-care-shortcut--sale">
                <i class="bi bi-cart-plus"></i>
                <span>Nueva venta</span>
                <i class="bi bi-arrow-up-right"></i>
            </a>

            <a href="index.php?c=Movimiento&a=index"
               class="fv-care-shortcut fv-care-shortcut--movement">
                <i class="bi bi-arrow-left-right"></i>
                <span>Movimientos</span>
                <i class="bi bi-arrow-up-right"></i>
            </a>

            <?php if ($esAdminVista): ?>
                <a href="index.php?c=Usuario&a=crear"
                   class="fv-care-shortcut fv-care-shortcut--user">
                    <i class="bi bi-person-plus"></i>
                    <span>Nuevo usuario</span>
                    <i class="bi bi-arrow-up-right"></i>
                </a>
            <?php else: ?>
                <a href="index.php?c=Producto&a=index"
                   class="fv-care-shortcut fv-care-shortcut--inventory">
                    <i class="bi bi-box-seam"></i>
                    <span>Ver inventario</span>
                    <i class="bi bi-arrow-up-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </section>

</div>

<?php require_once '../views/layouts/footer.php'; ?>
