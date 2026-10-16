<?php require_once '../views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-receipt"></i> Venta #<?= $venta['id'] ?></h2>
    <div>
        <button onclick="window.print()" class="btn btn-outline-primary">
            <i class="bi bi-printer"></i> Imprimir
        </button>
        <a href="index.php?c=Venta&a=historial" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">Información de la Venta</div>
            <div class="card-body">
                <p><strong>Número:</strong> #<?= $venta['id'] ?></p>
                <p><strong>Fecha:</strong> <?= date('d/m/Y H:i:s', strtotime($venta['fecha'])) ?></p>
                <p><strong>Cliente:</strong> <?= htmlspecialchars($venta['cliente']) ?></p>
                <p><strong>Usuario:</strong> <?= htmlspecialchars($venta['usuario'] ?? '-') ?></p>
                <hr>
                <h4 class="text-success">TOTAL: S/ <?= number_format($venta['total'], 2) ?></h4>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">Detalle de Productos</div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cant</th>
                            <th class="text-end">P. Unit</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalle as $d): ?>
                        <tr>
                            <td><?= htmlspecialchars($d['producto']) ?></td>
                            <td class="text-center"><?= $d['cantidad'] ?></td>
                            <td class="text-end">S/ <?= number_format($d['precio_unitario'], 2) ?></td>
                            <td class="text-end">S/ <?= number_format($d['subtotal'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">TOTAL:</th>
                            <th class="text-end text-success">S/ <?= number_format($venta['total'], 2) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../views/layouts/footer.php'; ?>