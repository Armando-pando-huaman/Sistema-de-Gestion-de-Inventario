<?php require '../views/layouts/header.php'; ?>
<link rel="stylesheet" href="css/fv-cuenta.css?v=1">
<section class="card fv-account-card"><div class="card-body p-4"><h1 class="h3">Mi botica</h1>
<?php require __DIR__.'/avisos.php'; ?>
<?php if (!$botica): ?><p>Completa los datos de tu botica actual. Tus usuarios, productos y ventas se conservarán.</p><?php endif; ?>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf" value="<?= $fvEsc(Seguridad::token()) ?>">
<label for="nombre" class="form-label">Nombre de la botica</label><input id="nombre" name="nombre" class="form-control mb-3" maxlength="150" value="<?= $fvEsc($_POST['nombre'] ?? ($botica['nombre'] ?? '')) ?>" required>
<label for="logo" class="form-label">Logo de la botica</label><img id="preview-logo" class="fv-image-preview fv-image-preview--logo" <?= empty($botica['logo'])?'hidden':'' ?> src="<?= $fvEsc($botica['logo'] ?? '') ?>" alt="Logo de tu botica">
<input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp" class="form-control" data-image-preview="preview-logo"><p class="form-text">Hasta 5 MB. Se guarda como WebP hasta 512 px conservando la transparencia.</p>
<div class="form-check form-switch my-4"><input id="registro_habilitado" class="form-check-input" name="registro_habilitado" type="checkbox" value="1" <?= ($_SERVER['REQUEST_METHOD']==='POST'?isset($_POST['registro_habilitado']):!empty($botica['registro_habilitado']))?'checked':'' ?>><label class="form-check-label" for="registro_habilitado">Habilitar registro de vendedores</label><p class="form-text">Permite el registro desde el inicio de sesión y desde Personal. Al desactivarlo se ocultan las opciones y se bloquean los registros nuevos.</p></div>
<button type="submit" class="btn btn-primary">Guardar configuración</button></form></div></section><script src="js/fv-cuenta.js?v=1"></script>
<?php require '../views/layouts/footer.php'; ?>
