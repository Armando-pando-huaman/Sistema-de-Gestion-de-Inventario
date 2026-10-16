<?php require_once '../views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-clock-history"></i> Historial de Ventas</h2>
    <a href="index.php?c=Venta&a=index" class="btn btn-primary">
        <i class="bi bi-cart-plus"></i> Nueva Venta
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Usuario</th>
                    <th class="text-end">Total</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventas as $v): ?>
                <tr>
                    <td><strong>#<?= $v['id'] ?></strong></td>
                    <td><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
                    <td><?= htmlspecialchars($v['cliente']) ?></td>
                    <td><?= htmlspecialchars($v['usuario'] ?? '-') ?></td>
                    <td class="text-end text-success fw-bold">S/ <?= number_format($v['total'], 2) ?></td>
                    <td class="text-center">
                        <a href="index.php?c=Venta&a=ver&id=<?= $v['id'] ?>" 
                           class="btn btn-sm btn-info" title="Ver detalle">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($ventas)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay ventas registradas.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../views/layouts/footer.php'; ?>