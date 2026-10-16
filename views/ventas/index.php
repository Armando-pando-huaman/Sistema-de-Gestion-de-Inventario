<?php require_once '../views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-cart-check"></i> Punto de Venta</h2>
    <a href="index.php?c=Venta&a=historial" class="btn btn-outline-primary">
        <i class="bi bi-clock-history"></i> Ver Historial
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle"></i> <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="row">
    <!-- Catálogo de Productos -->
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-box-seam"></i> Productos Disponibles
            </div>
            <div class="card-body">
                <input type="text" id="buscarProducto" class="form-control mb-3" 
                       placeholder="🔍 Buscar producto por nombre...">
                
                <div class="row g-2" id="listaProductos" style="max-height: 500px; overflow-y: auto;">
                    <?php foreach ($productos as $p): ?>
                        <?php if ($p['stock'] > 0): ?>
                        <div class="col-md-6 producto-item" data-nombre="<?= strtolower($p['nombre']) ?>">
                            <div class="border rounded p-2 h-100">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <strong class="d-block"><?= htmlspecialchars($p['nombre']) ?></strong>
                                        <small class="text-muted d-block">
                                            Stock: <span class="badge bg-<?= $p['stock'] <= $p['stock_minimo'] ? 'warning' : 'success' ?>">
                                                <?= $p['stock'] ?>
                                            </span>
                                        </small>
                                        <span class="text-success fw-bold">S/ <?= number_format($p['precio_venta'], 2) ?></span>
                                    </div>
                                    <button class="btn btn-sm btn-primary btn-agregar" 
                                        data-id="<?= $p['id'] ?>" 
                                        data-nombre="<?= htmlspecialchars($p['nombre']) ?>" 
                                        data-precio="<?= $p['precio_venta'] ?>"
                                        data-stock="<?= $p['stock'] ?>">
                                        <i class="bi bi-cart-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Carrito -->
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <i class="bi bi-cart"></i> Carrito de Venta
            </div>
            <div class="card-body">
                <form method="POST" id="formVenta">
                    <input type="hidden" name="carrito" id="inputCarrito">
                    <input type="hidden" name="total" id="inputTotal">
                    
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-person"></i> Cliente</label>
                        <input type="text" name="cliente" class="form-control" 
                               placeholder="Cliente General" value="Cliente General">
                    </div>

                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cant</th>
                                    <th class="text-end">Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="carritoBody">
                                <tr><td colspan="4" class="text-center text-muted">Carrito vacío</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-3">
                        <h5 class="mb-0">TOTAL:</h5>
                        <h4 class="text-success mb-0">S/ <span id="totalTexto">0.00</span></h4>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100 mt-3" id="btnVender" disabled>
                        <i class="bi bi-check-circle"></i> Registrar Venta
                    </button>
                </form>
            </div>
        </div>

        <!-- Últimas ventas -->
        <div class="card shadow-sm mt-3">
            <div class="card-header bg-secondary text-white">
                <i class="bi bi-clock"></i> Últimas Ventas
            </div>
            <div class="card-body p-2">
                <?php if (!empty($ventas)): ?>
                    <?php foreach (array_slice($ventas, 0, 3) as $v): ?>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <div>
                                <small class="text-muted">#<?= $v['id'] ?></small><br>
                                <small><?= htmlspecialchars($v['cliente']) ?></small>
                            </div>
                            <div class="text-end">
                                <strong class="text-success">S/ <?= number_format($v['total'], 2) ?></strong><br>
                                <small class="text-muted"><?= date('H:i', strtotime($v['fecha'])) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">No hay ventas aún</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../views/layouts/footer.php'; ?>