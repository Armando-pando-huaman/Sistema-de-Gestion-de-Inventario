<?php
/* FarmaVidaHC · Dashboard Clínico Premium
   Vista compatible con style.css Premium Integrado.
   Mantiene variables y rutas del controlador original. */
require_once '../views/layouts/header.php';

$totalProductosVista = (int) ($totalProductos ?? 0);
$totalMovimientosVista = (int) ($totalMovimientos ?? 0);
$stockBajoVista = is_array($productosBajos ?? null) ? $productosBajos : [];
$movimientosVista = is_array($ultimosMovimientos ?? null) ? $ultimosMovimientos : [];
$porVencerVista = (int) ($porVencer ?? 0);
$nombreVista = htmlspecialchars((string) ($_SESSION['user_nombre'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$esAdminVista = ($_SESSION['user_rol'] ?? '') === 'admin';
?>

<!-- El tema Clínico Premium detecta Dashboard desde index.php?c=Dashboard -->
<div class="fv-dashboard" aria-label="Panel principal de FarmaVidaHC">

    <!-- 01. CABECERA PRINCIPAL -->
    <section class="fv-welcome">
        <div>
            <span class="fv-eyebrow"><i class="bi bi-heart-pulse-fill me-1"></i> FARMA VIDAHC · CENTRO DE CONTROL</span>
            <h2><i class="bi bi-clipboard2-pulse me-2"></i> Panel principal</h2>
            <p>Bienvenido, <strong><?= $nombreVista ?></strong>. Consulta el estado de tu botica en tiempo real.</p>
        </div>
        <div class="fv-date"><i class="bi bi-calendar3"></i> <?= date('d/m/Y H:i') ?></div>
    </section>

    <!-- 02. INDICADORES: cuatro tarjetas compactas, dos en móvil -->
    <section class="row g-3 mb-3" aria-label="Indicadores del sistema">
        <div class="col-6 col-lg-3">
            <div class="card stat-card fv-stat fv-stat--products h-100">
                <i class="bi bi-capsule-pill fv-stat-icon" aria-hidden="true"></i>
                <div><h6>Total de productos</h6><h2><?= number_format($totalProductosVista, 0, ',', '.') ?></h2></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card fv-stat fv-stat--moves h-100">
                <i class="bi bi-arrow-left-right fv-stat-icon" aria-hidden="true"></i>
                <div><h6>Movimientos</h6><h2><?= number_format($totalMovimientosVista, 0, ',', '.') ?></h2></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card fv-stat fv-stat--low h-100">
                <i class="bi bi-exclamation-triangle-fill fv-stat-icon" aria-hidden="true"></i>
                <div><h6>Stock bajo</h6><h2><?= count($stockBajoVista) ?></h2></div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card fv-stat fv-stat--expiry h-100">
                <i class="bi bi-calendar-x-fill fv-stat-icon" aria-hidden="true"></i>
                <div><h6>Por vencer (30 días)</h6><h2><?= number_format($porVencerVista, 0, ',', '.') ?></h2></div>
            </div>
        </div>
    </section>

    <!-- 03. ALERTAS Y ACTIVIDAD: dos columnas en escritorio -->
    <section class="row g-3 mb-3" aria-label="Alertas y últimos movimientos">
        <div class="col-12 col-xl-6">
            <div class="card fv-panel h-100">
                <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                    <span><i class="bi bi-exclamation-triangle-fill"></i> Productos con stock bajo</span>
                    <span class="badge bg-danger rounded-pill"><?= count($stockBajoVista) ?> alertas</span>
                </div>
                <div class="card-body p-0">
                    <?php if ($stockBajoVista): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($stockBajoVista, 0, 6) as $p): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                    <div class="flex-grow-1" style="min-width:0">
                                        <strong><?= htmlspecialchars((string) ($p['nombre'] ?? 'Producto'), ENT_QUOTES, 'UTF-8') ?></strong>
                                        <div><small class="text-muted">Stock mínimo: <?= htmlspecialchars((string) ($p['stock_minimo'] ?? 0), ENT_QUOTES, 'UTF-8') ?></small></div>
                                    </div>
                                    <span class="badge bg-danger rounded-pill">Stock: <?= htmlspecialchars((string) ($p['stock'] ?? 0), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-footer bg-white text-end py-2">
                            <a class="fw-semibold text-decoration-none" href="index.php?c=Producto&a=index">
                                Ver productos<?= count($stockBajoVista) > 6 ? ' (' . count($stockBajoVista) . ' alertas)' : '' ?> <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="fv-empty"><i class="bi bi-check-circle-fill"></i><p class="mt-2 mb-0">Inventario saludable: no hay productos con stock bajo.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card fv-panel fv-panel--moves h-100">
                <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                    <span><i class="bi bi-clock-history"></i> Últimos movimientos</span>
                    <a href="index.php?c=Movimiento&a=index" class="small fw-semibold text-decoration-none">Ver movimientos <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="card-body p-0">
                    <?php if ($movimientosVista): ?>
                        <div class="fv-table-scroll">
                            <table class="table table-sm mb-0">
                                <thead><tr><th scope="col">Fecha</th><th scope="col">Producto</th><th scope="col">Tipo</th><th scope="col">Cant.</th></tr></thead>
                                <tbody>
                                <?php foreach (array_slice($movimientosVista, 0, 6) as $m):
                                    $tipoVista = strtolower((string) ($m['tipo'] ?? ''));
                                    $entradaVista = $tipoVista === 'entrada';
                                    $fechaTimestamp = strtotime((string) ($m['fecha'] ?? ''));
                                ?>
                                    <tr>
                                        <td class="text-nowrap"><small><?= $fechaTimestamp !== false ? date('d/m H:i', $fechaTimestamp) : '—' ?></small></td>
                                        <td><small><?= htmlspecialchars((string) ($m['producto'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></small></td>
                                        <td><span class="badge <?= $entradaVista ? 'bg-success' : 'bg-danger' ?>"><?= htmlspecialchars(ucfirst($tipoVista ?: 'Otro'), ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td><strong><?= htmlspecialchars((string) ($m['cantidad'] ?? 0), ENT_QUOTES, 'UTF-8') ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="fv-empty"><i class="bi bi-inbox"></i><p class="mt-2 mb-0">Todavía no hay movimientos registrados.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- 04. ACCESOS RÁPIDOS: mantienen rutas MVC y permisos -->
    <section class="card fv-panel fv-panel--quick" aria-label="Accesos rápidos">
        <div class="card-header"><i class="bi bi-lightning-charge-fill"></i> Accesos rápidos</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <a href="index.php?c=Producto&a=crear" class="fv-quick-link fv-quick-link--product">
                        <i class="bi bi-capsule-pill" aria-hidden="true"></i><span>Nuevo producto</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="index.php?c=Venta&a=index" class="fv-quick-link fv-quick-link--sale">
                        <i class="bi bi-cart-plus-fill" aria-hidden="true"></i><span>Nueva venta</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="index.php?c=Movimiento&a=index" class="fv-quick-link fv-quick-link--movement">
                        <i class="bi bi-arrow-left-right" aria-hidden="true"></i><span>Movimientos</span>
                    </a>
                </div>
                <?php if ($esAdminVista): ?>
                    <div class="col-6 col-md-3">
                        <a href="index.php?c=Usuario&a=crear" class="fv-quick-link fv-quick-link--user">
                            <i class="bi bi-person-plus-fill" aria-hidden="true"></i><span>Nuevo usuario</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="col-6 col-md-3">
                        <a href="index.php?c=Producto&a=index" class="fv-quick-link fv-quick-link--inventory">
                            <i class="bi bi-box-seam" aria-hidden="true"></i><span>Ver inventario</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<?php require_once '../views/layouts/footer.php'; ?>
