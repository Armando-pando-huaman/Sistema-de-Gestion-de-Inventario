<?php require_once '../views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-pencil"></i> Editar Usuario</h2>
    <a href="index.php?c=Usuario&a=index" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
</div>

<?php if (!empty($errores)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> <strong>Errores:</strong>
        <ul class="mb-0 mt-2">
            <?php foreach ($errores as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" class="row g-3" style="max-width: 700px;">
            <div class="col-md-6">
                <label class="form-label">Nombre Completo *</label>
                <input type="text" name="nombre" class="form-control" 
                       value="<?= htmlspecialchars($_POST['nombre'] ?? $usuario['nombre']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" 
                       value="<?= htmlspecialchars($_POST['email'] ?? $usuario['email']) ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Nueva Contraseña (dejar vacío para mantener)</label>
                <input type="password" name="password" class="form-control" minlength="6">
                <small class="text-muted">Solo si deseas cambiarla</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Rol *</label>
                <select name="rol" class="form-select" required>
                    <option value="vendedor" <?= $usuario['rol'] == 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                    <option value="admin" <?= $usuario['rol'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>
            <div class="col-12 mt-3">
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-save"></i> Actualizar Usuario
                </button>
                <a href="index.php?c=Usuario&a=index" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../views/layouts/footer.php'; ?>