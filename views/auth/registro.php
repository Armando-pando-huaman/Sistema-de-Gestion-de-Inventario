<?php require __DIR__.'/../layouts/identidad.php'; $esc=$fvEsc; ?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Crear cuenta | <?= $fvEsc($fvNombreBotica) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="css/style.css"><link rel="stylesheet" href="css/fv-cuenta.css"></head>
<body class="fv-theme"><main class="fv-signup-shell"><section class="card fv-account-card">
<div class="card-body p-4 p-md-5"><a href="index.php?c=Auth&amp;a=login">← Iniciar sesión</a>
<h1 class="h3 mt-4">Tu cuenta de botica</h1><p class="text-muted">Primero tus datos, después tu botica.</p>
<?php if ($error): ?><div class="alert alert-danger" role="alert"><?= $esc($error) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" id="registro-form">
<input type="hidden" name="csrf" value="<?= $esc(Seguridad::token()) ?>">
<fieldset id="registro-persona"><legend class="h5">1. Crea tu usuario</legend>
<label for="nombre" class="form-label">Nombre completo</label><input id="nombre" name="nombre" class="form-control mb-3" maxlength="100" autocomplete="name" value="<?= $esc($_POST['nombre'] ?? '') ?>" required>
<label for="email" class="form-label">Correo electrónico</label><input id="email" type="email" name="email" class="form-control mb-3" maxlength="150" autocomplete="email" value="<?= $esc($_POST['email'] ?? '') ?>" required>
<label for="password" class="form-label">Contraseña (8 a 72 caracteres)</label><input id="password" type="password" name="password" class="form-control mb-3" minlength="8" maxlength="72" autocomplete="new-password" required>
<label for="confirmacion" class="form-label">Repetir contraseña</label><input id="confirmacion" type="password" name="confirmacion" class="form-control mb-3" minlength="8" maxlength="72" autocomplete="new-password" required>
<label for="foto" class="form-label">Foto de perfil (opcional)</label><input id="foto" type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="form-control" data-image-preview="preview-foto"><img id="preview-foto" class="fv-image-preview" hidden alt="Vista previa de tu foto">
<p class="form-text">JPG, PNG o WebP hasta 5 MB. La optimizamos a WebP, hasta 256 px.</p>
<button type="button" id="registro-siguiente" class="btn btn-primary" hidden>Continuar a mi botica →</button>
</fieldset>
<fieldset id="registro-botica" class="mt-4"><legend class="h5">2. Tu botica</legend>
<?php if (!$botica): ?>
<input type="hidden" name="modo" value="crear">
<p>¿Deseas crear tu botica? Al registrarla serás su administrador.</p>
<label for="botica_nombre" class="form-label">Nombre de la botica</label><input id="botica_nombre" name="botica_nombre" class="form-control mb-3" maxlength="150" value="<?= $esc($_POST['botica_nombre'] ?? '') ?>" required>
<label for="logo" class="form-label">Logo (opcional)</label><input id="logo" type="file" name="logo" accept="image/jpeg,image/png,image/webp" class="form-control" data-image-preview="preview-logo"><img id="preview-logo" class="fv-image-preview fv-image-preview--logo" hidden alt="Vista previa del logo">
<p class="form-text">Lo optimizamos a WebP hasta 512 px, conservando la transparencia.</p>
<div class="form-check my-3"><input id="registro_habilitado" class="form-check-input" type="checkbox" name="registro_habilitado" value="1" <?= isset($_POST['registro_habilitado'])?'checked':'' ?>><label class="form-check-label" for="registro_habilitado">Permitir que otros vendedores se registren en mi botica</label></div>
<?php else: ?>
<input type="hidden" name="modo" value="unir">
<label for="botica_id" class="form-label">Selecciona la botica</label><select id="botica_id" name="botica_id" class="form-select mb-3" required><option value="">Selecciona una botica</option><option value="1" <?= ($_POST['botica_id'] ?? '')==='1'?'selected':'' ?>><?= $esc($botica['nombre']) ?></option></select>
<p class="form-text">Tu cuenta tendrá el rol de vendedor.</p>
<?php endif; ?>
<button type="button" id="registro-atras" class="btn btn-outline-secondary" hidden>Volver</button>
<button type="submit" class="btn btn-primary"><?= $botica?'Crear cuenta de vendedor':'Crear mi botica y mi cuenta' ?></button>
</fieldset></form></div></section></main><script src="js/fv-cuenta.js"></script></body></html>
