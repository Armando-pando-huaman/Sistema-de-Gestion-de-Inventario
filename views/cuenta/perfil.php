<?php require '../views/layouts/header.php'; ?>
<link rel="stylesheet" href="css/fv-cuenta.css">
<section class="card fv-account-card"><div class="card-body p-4">
<h1 class="h3">Mi perfil</h1>
<?php require __DIR__.'/avisos.php'; ?>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf" value="<?= $fvEsc(Seguridad::token()) ?>">
<label for="nombre" class="form-label">Nombre completo</label><input id="nombre" name="nombre" class="form-control mb-3" maxlength="100" value="<?= $fvEsc($_POST['nombre'] ?? $usuario['nombre']) ?>" required>
<p>Correo: <?= $fvEsc($usuario['email']) ?></p>
<label for="foto" class="form-label">Foto de perfil</label>
<img id="preview-foto" class="fv-image-preview" <?= empty($usuario['foto'])?'hidden':'' ?> src="<?= $fvEsc($usuario['foto'] ?? '') ?>" alt="Tu foto de perfil">
<input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp" class="form-control" data-image-preview="preview-foto">
<p class="form-text">Hasta 5 MB. Se reduce a 256 px y se guarda como WebP optimizado.</p>
<button class="btn btn-primary" type="submit">Guardar perfil</button>
</form></div></section><script src="js/fv-cuenta.js"></script>
<?php require '../views/layouts/footer.php'; ?>
