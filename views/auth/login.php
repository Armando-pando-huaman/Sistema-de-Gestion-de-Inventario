<?php require __DIR__.'/../layouts/identidad.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#07866b">
    <title>Iniciar sesión | <?= $fvEsc($fvNombreBotica) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script>
    // Aplicar la paleta guardada antes de pintar el login, sin modificar script.js.
    (function () {
      var allowed = ['mint', 'blue', 'lavender', 'coral'];
      var selected = 'mint';
      try { selected = localStorage.getItem('fv-palette') || 'mint'; } catch (e) {}
      if (allowed.indexOf(selected) === -1) selected = 'mint';
      document.documentElement.setAttribute('data-fv-palette', selected);
      var colors = { mint:'#087f61', blue:'#2563a8', lavender:'#6750a4', coral:'#c45c51' };
      document.querySelector('meta[name="theme-color"]').setAttribute('content', colors[selected]);
    })();
    </script>
    <link rel="stylesheet" href="css/fv-login.css?">
</head>
<body class="login-body">
<main class="fv-login-layout">
    <section class="fv-login-story" aria-label="Bienvenida">
        <div class="fv-login-brand"><span class="fv-brand-mark"><i class="bi bi-plus-lg"></i></span><span><strong><?= $fvEsc($fvNombreBotica) ?></strong><small>Gestión inteligente</small></span></div>
        <div class="fv-story-content">
            <span class="fv-eyebrow">GESTIÓN PARA TU BOTICA</span>
            <h1>Cuida tu inventario.<br><em>Haz crecer tu botica.</em></h1>
            <p>Una experiencia clara para administrar productos, ventas y el día a día de tu equipo.</p>
            <div class="fv-story-safe"><i class="bi bi-shield-check"></i> Datos protegidos <span aria-hidden="true">·</span> Simple y profesional</div>
        </div>
        <div class="fv-story-art" aria-hidden="true"><div class="fv-art-ring fv-art-ring-1"></div><div class="fv-art-ring fv-art-ring-2"></div><i class="bi bi-capsule"></i></div>
        <small class="fv-story-foot"><?= $fvEsc($fvNombreBotica) ?> · Gestión simple para cuidar mejor.</small>
    </section>
    <section class="fv-login-panel" aria-labelledby="fv-login-title">
        <div class="fv-login-panel-inner">
            <div class="fv-mobile-brand"><span class="fv-brand-mark"><i class="bi bi-plus-lg"></i></span><span><strong><?= $fvEsc($fvNombreBotica) ?></strong><small>Gestión inteligente</small></span></div>
            <span class="fv-eyebrow">BIENVENIDO DE NUEVO</span>
            <h2 id="fv-login-title">Inicia sesión</h2>
            <p class="fv-login-intro">Accede a la gestión de tu botica</p>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert"><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['registro_exito'])): ?><div class="alert alert-success" role="status"><?= htmlspecialchars($_SESSION['registro_exito'],ENT_QUOTES,'UTF-8') ?></div><?php unset($_SESSION['registro_exito']); endif; ?>
            <div id="alertaLogin" class="alert alert-danger" style="display:none;" role="alert"></div>
            <form method="POST" id="loginForm">
<input type="hidden" name="csrf" value="<?= htmlspecialchars(Seguridad::token(),ENT_QUOTES,'UTF-8') ?>">
                <div class="fv-field">
                    <label for="email">Correo electrónico</label>
                    <div class="fv-input-wrap"><i class="bi bi-envelope" aria-hidden="true"></i><input type="email" name="email" id="email" class="form-control" placeholder="tu@botica.com" autocomplete="username" required autofocus></div>
                </div>
                <div class="fv-field">
                    <label for="password">Contraseña</label>
                    <div class="fv-input-wrap"><i class="bi bi-lock" aria-hidden="true"></i><input type="password" name="password" id="password" class="form-control" placeholder="Ingresa tu contraseña" autocomplete="current-password" required><button class="fv-password-toggle" type="button" id="fvTogglePassword" aria-label="Mostrar contraseña" aria-pressed="false"><i class="bi bi-eye"></i></button></div>
                </div>
                <button type="submit" class="btn-login fv-login-submit" id="btnLogin">Iniciar sesión <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
            </form>
            <?php if ($registroDisponible): ?><p class="mt-4 text-center"><a href="index.php?c=Auth&amp;a=registro">Crear mi cuenta</a></p><?php endif; ?>
            <div class="fv-login-trust"><i class="bi bi-shield-check" aria-hidden="true"></i> Tu información está protegida y es solo para tu equipo.</div>
        </div>
    </section>
</main>
<script src="js/script.js"></script>
<script>
(function () {
  const toggle = document.getElementById('fvTogglePassword');
  const password = document.getElementById('password');
  if (toggle && password) toggle.addEventListener('click', function () {
    const show = password.type === 'password';
    password.type = show ? 'text' : 'password';
    toggle.setAttribute('aria-pressed', String(show));
    toggle.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
    toggle.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
  });
})();
</script>
</body>
</html>
