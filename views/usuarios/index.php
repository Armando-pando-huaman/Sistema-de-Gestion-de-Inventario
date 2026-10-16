<?php require_once '../views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><i class="bi bi-people"></i> Gestión de Usuarios</h2>
    <a href="index.php?c=Usuario&a=crear" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Nuevo Usuario
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

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Registro</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['nombre']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="badge bg-<?= $u['rol'] == 'admin' ? 'danger' : 'info' ?>">
                            <i class="bi bi-<?= $u['rol'] == 'admin' ? 'shield-lock' : 'person' ?>"></i>
                            <?= ucfirst($u['rol']) ?>
                        </span>
                    </td>
                    <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                    <td class="text-center">
                        <a href="index.php?c=Usuario&a=editar&id=<?= $u['id'] ?>" 
                           class="btn btn-sm btn-warning" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <a href="index.php?c=Usuario&a=eliminar&id=<?= $u['id'] ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('¿Eliminar al usuario <?= htmlspecialchars($u['nombre']) ?>?')"
                               title="Eliminar">
                                <i class="bi bi-trash"></i>
                            </a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-secondary" disabled title="No puedes eliminarte">
                                <i class="bi bi-lock"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($usuarios)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-3">No hay usuarios registrados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../views/layouts/footer.php'; ?>