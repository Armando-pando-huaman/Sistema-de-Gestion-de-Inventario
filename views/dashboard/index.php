
<?php
/*  Dashboard estilo BoticaCare
   Paso 1: estructura PHP y HTML.*/

require_once '../views/layouts/header.php';

$totalProductosVista = (int) ($totalProductos ?? 0);
$totalMovimientosVista = (int) ($totalMovimientos ?? 0);
$stockBajoVista = is_array($productosBajos ?? null) ? $productosBajos : [];
$movimientosVista = is_array($ultimosMovimientos ?? null) ? $ultimosMovimientos : [];
$porVencerVista = (int) ($porVencer ?? 0);
$totalVendidoHoyVista = (float) ($totalVendidoHoy ?? 0);
$ventasHoyVista = (int) ($ventasHoy ?? 0);

$nombreOriginal = (string) ($_SESSION['user_nombre'] ?? 'Usuario');
$nombreVista = htmlspecialchars($nombreOriginal, ENT_QUOTES, 'UTF-8');
$esAdminVista = ($_SESSION['user_rol'] ?? '') === 'admin';

$nombreCorto = trim(explode(' ', trim($nombreOriginal))[0] ?? '');
$nombreCorto = htmlspecialchars($nombreCorto ?: 'Usuario', ENT_QUOTES, 'UTF-8');

$ahoraPeru = new DateTimeImmutable('now', new DateTimeZone('America/Lima'));
$horaActual = (int) $ahoraPeru->format('G');
$saludo = ($horaActual >= 5 && $horaActual < 12) ? 'Buenos días'
    : (($horaActual >= 12 && $horaActual < 18) ? 'Buenas tardes' : 'Buenas noches');

$diasSemana = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
$meses = [1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
          'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

$fechaTexto = ucfirst($diasSemana[(int) $ahoraPeru->format('w')]) . ', ' .
              $ahoraPeru->format('j') . ' de ' . $meses[(int) $ahoraPeru->format('n')] .
              ' de ' . $ahoraPeru->format('Y');

$totalAlertas = count($stockBajoVista) + $porVencerVista;

$entradasRecientes = 0;
$salidasRecientes = 0;

foreach ($movimientosVista as $movimiento) {
    $tipo = strtolower((string) ($movimiento['tipo'] ?? ''));
    if ($tipo === 'entrada') $entradasRecientes++;
    if ($tipo === 'salida') $salidasRecientes++;
}
?>

<link rel="stylesheet" href="css/fv-dashboard.css">
<div class="fv-dashboard fv-dashboard-care">
    <!-- BIENVENIDA -->
    <section class="fv-care-greeting">
        <span class="fv-care-date">
            <i class="bi bi-calendar3"></i>
            <?= $fechaTexto ?>
        </span>

        <div class="fv-care-welcome">
            <div class="fv-care-welcome-inner">
                <div class="fv-care-welcome-copy">
                    <h1><?= $saludo ?>, <?= $nombreCorto ?>
                        <i class="bi bi-stars"></i>
                    </h1>

                    <p>Aquí tienes un resumen de lo que ocurre en tu botica hoy.</p>
                </div>

                <div class="fv-care-greeting-icon" aria-hidden="true">
                    <i class="bi bi-heart-pulse"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- INDICADORES -->
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

        <article class="fv-care-stat fv-care-stat--sales">
            <div class="fv-care-stat-top">
                <span class="fv-care-stat-icon"><i class="bi bi-cash-stack"></i></span>
                <span class="fv-care-stat-tag">Hoy</span>
            </div>
            <div class="fv-care-stat-content">
                <span class="fv-care-stat-label">Ventas acumuladas</span>
                <strong class="fv-care-stat-value">S/ <?= number_format($totalVendidoHoyVista, 2, ',', '.') ?></strong>
                <small><?= number_format($ventasHoyVista, 0, ',', '.') ?> <?= $ventasHoyVista === 1 ? 'venta realizada' : 'ventas realizadas' ?></small>
            </div>
            <div class="fv-care-stat-decoration"><i class="bi bi-graph-up-arrow"></i></div>
        </article>

    </section>

    <!-- RESUMEN PRINCIPAL Y ACTIVIDAD -->
    <section class="fv-care-main-grid">
        <article class="fv-care-panel fv-care-chart-panel">
          <header class="fv-care-panel-header"><div><h3>Movimientos de inventario</h3><p>Actividad por día y hora durante las últimas 24 horas</p></div><span class="fv-care-chart-tag">Últimas 24 h</span></header>
          <?php
            $fvHourDetails=[];
            foreach (($movimientosPorHora ?? []) as $fvRow) { $fvHourDetails[$fvRow['momento']]=$fvRow; }
            $fvChartHours=[];$fvHourCounts=[];$fvEntries=[];$fvExits=[];
            $fvChartStart=$ahoraPeru->setTime((int)$ahoraPeru->format('H'),0)->modify('-23 hours');
            for($fvI=0;$fvI<24;$fvI++){
                $fvMoment=$fvChartStart->modify('+'.$fvI.' hours');
                $fvKey=$fvMoment->format('Y-m-d H:00:00');
                $fvChartHours[]=$fvMoment;
                $fvHourCounts[]=(int)($fvHourDetails[$fvKey]['total']??0);
                $fvEntries[]=(int)($fvHourDetails[$fvKey]['entradas']??0);
                $fvExits[]=(int)($fvHourDetails[$fvKey]['salidas']??0);
            }
            $fvMax=max(1,max($fvHourCounts));
            $fvPoints=[];$fvAreaPoints=['10,190'];
            foreach($fvHourCounts as $fvI=>$fvCount){
                $fvX=10+($fvI*(700/23));$fvY=190-(($fvCount/$fvMax)*160);
                $fvPoint=round($fvX,2).','.round($fvY,2);
                $fvPoints[]=$fvPoint;$fvAreaPoints[]=$fvPoint;
            }
            $fvAreaPoints[]='710,190';
          ?>
          <div class="fv-care-chart-body">
            <div class="fv-care-chart-summary"><strong><?= array_sum($fvHourCounts) ?></strong><span>movimientos en las últimas 24 horas. Selecciona un punto para ver día, hora y detalle.</span></div>
            <div class="fv-care-line-chart">
                <svg viewBox="0 0 720 210" role="img" aria-label="Línea de movimientos de inventario por hora" preserveAspectRatio="none">
                    <defs><linearGradient id="fv-chart-area" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="var(--care-primary)" stop-opacity=".28"/><stop offset="1" stop-color="var(--care-primary)" stop-opacity="0"/></linearGradient></defs>
                    <line x1="10" y1="190" x2="710" y2="190" class="fv-chart-axis"/>
                    <line x1="10" y1="110" x2="710" y2="110" class="fv-chart-grid"/>
                    <line x1="10" y1="30" x2="710" y2="30" class="fv-chart-grid"/>
                    <polygon points="<?= implode(' ', $fvAreaPoints) ?>" class="fv-chart-area"/>
                    <polyline points="<?= implode(' ', $fvPoints) ?>" class="fv-chart-line"/>
                </svg>
                <?php foreach($fvHourCounts as $fvI=>$fvCount):
                    $fvLeft=($fvI/23)*100;$fvBottom=9.5+(($fvCount/$fvMax)*76.2);
                    $fvDateLabel=$fvChartHours[$fvI]->format('d/m/Y');$fvTimeLabel=$fvChartHours[$fvI]->format('H:00');
                ?>
                <button type="button" class="fv-line-point" style="left:<?= round($fvLeft,2) ?>%;bottom:<?= round($fvBottom,2) ?>%" aria-label="<?= $fvDateLabel ?> a las <?= $fvTimeLabel ?>: <?= $fvCount ?> movimientos">
                    <span class="fv-chart-tooltip" role="tooltip"><strong><?= $fvDateLabel ?> · <?= $fvTimeLabel ?></strong><span><?= $fvCount ?> movimientos</span><span>Entradas: <?= $fvEntries[$fvI] ?></span><span>Salidas: <?= $fvExits[$fvI] ?></span></span>
                </button>
                <?php endforeach; ?>
            </div>
            <div class="fv-line-labels"><?php foreach([0,5,11,17,23] as $fvI): ?><span><?= $fvChartHours[$fvI]->format('d/m H:i') ?></span><?php endforeach; ?></div>
          </div>
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
    <section class="fv-care-shortcuts" aria-labelledby="fv-shortcuts-title">
        <div class="fv-care-shortcuts-heading">
            <span class="fv-shortcuts-badge"><i class="bi bi-lightning-charge-fill" aria-hidden="true"></i> A un clic</span>
            <h3 id="fv-shortcuts-title">Accesos r&#225;pidos</h3>
            <p>Todo lo que necesitas para atender mejor.</p>
        </div>
        <div class="fv-care-shortcuts-grid">
            <?php
            $accesosVista = [
                ['sale', 'Venta', 'index', 'bi-cart-plus', 'sale.json', 'Nueva venta', 'Atiende a tus clientes'],
            ];
            if ($esAdminVista) {
                $accesosVista[] = ['product', 'Producto', 'crear', 'bi-plus-circle', 'product.json', 'Nuevo producto', 'Ampl&#237;a tu cat&#225;logo'];
            }
            $accesosVista[] = ['movement', 'Movimiento', 'index', 'bi-arrow-left-right', 'movement.json', 'Movimientos', 'Controla entradas y salidas'];
            $accesosVista[] = ['cash', 'Gestion', 'caja', 'bi-wallet2', 'cash.json', 'Mi caja', 'Revisa tu dinero del d&#237;a'];
            foreach ($accesosVista as [$estilo, $controlador, $accion, $icono, $animacion, $titulo, $detalle]):
            ?>
                <a href="index.php?c=<?= $controlador ?>&amp;a=<?= $accion ?>"
                   class="fv-care-shortcut fv-care-shortcut--<?= $estilo ?>" id="fv-shortcut-<?= $estilo ?>">
                    <span class="fv-shortcut-icon" aria-hidden="true">
                        <i class="bi <?= $icono ?> fv-lord-fallback"></i>
                        <lord-icon
                            src="icons/lordicon/<?= rawurlencode($animacion) ?>"
                            trigger="hover"
                            target="#fv-shortcut-<?= $estilo ?>"
                            colors="primary:#ffffff,secondary:#ffffff"
                            style="width:42px;height:42px">
                        </lord-icon>
                    </span>
                    <span class="fv-shortcut-copy"><strong><?= $titulo ?></strong><small><?= $detalle ?></small></span>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="fv-shortcuts-footer">
            <span><i class="bi bi-shield-check" aria-hidden="true"></i> Tu botica, siempre al d&#237;a</span>
            <a href="https://lordicon.com/" target="_blank" rel="noopener noreferrer">Iconos animados por Lordicon</a>
        </div>
    </section>

</div>

<?php require_once '../views/layouts/footer.php'; ?>
