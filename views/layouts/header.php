<?php
require __DIR__.'/identidad.php';
$fvRegistroHabilitado=!empty($fvBotica['registro_habilitado']);
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $fvEsc($fvNombreBotica) ?> | Gestión farmacéutica</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script>try{document.documentElement.dataset.fvPalette=localStorage.getItem('fv-palette')||'mint';document.documentElement.dataset.fvLayout=localStorage.getItem('fv-layout')||'vertical'}catch(e){document.documentElement.dataset.fvPalette='mint';document.documentElement.dataset.fvLayout='vertical'}</script>
<script>
(() => {
 let mode = 'light';
 try { mode = localStorage.getItem('fv-mode') === 'dark' ? 'dark' : 'light'; } catch (_) {}
 document.documentElement.dataset.fvMode = mode;
 document.documentElement.dataset.bsTheme = mode;
})();
</script>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/fv-theme-mode.css"><link rel="stylesheet" href="css/fv-cuenta.css"><link rel="stylesheet" href="css/fv-useanimations.css"></head>
<body class="fv-theme fv-app">
<?php
$fvController = strtolower((string)($_GET['c'] ?? 'Dashboard'));
$fvAction = strtolower((string)($_GET['a'] ?? 'index'));
$fvIsAdmin = (($_SESSION['user_rol'] ?? '') === 'admin');
$fvEsc = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$fvMenu = [
 ['Dashboard','index','bi-grid-1x2','Dashboard'],
 ['Producto','index','bi-capsule','Productos'],
 ['Venta','index','bi-cart3','Ventas'],
 ['Venta','historial','bi-clock-history','Historial'],
 ['Movimiento','index','bi-arrow-left-right','Movimientos'],
 ['Gestion','caja','bi-wallet2','Mi caja'],
];
if ($fvIsAdmin) { $fvMenu[]=['Gestion','lotes','bi-boxes','Lotes'];$fvMenu[]=['Gestion','auditoria','bi-journal-text','Auditoría']; $fvMenu[]=['Reporte','index','bi-bar-chart','Reportes'];$fvMenu[]=['Usuario','index','bi-people','Personal']; }
?>
<div class="fv-shell">
<aside class="fv-sidebar" id="fv-sidebar" aria-label="Navegación lateral">
 <div class="fv-side-brand">
  <a href="index.php?c=Dashboard&amp;a=index" class="fv-side-logo">
   <span class="fv-logo-mark<?= !empty($fvBotica['logo'])?' fv-logo-mark--image':'' ?>">
    <?php if (!empty($fvBotica['logo'])): ?><img src="<?= $fvEsc($fvBotica['logo']) ?>" alt="Logo de <?= $fvEsc($fvBotica['nombre']) ?>"><?php else: ?>+<?php endif; ?>
   </span>
   <span class="fv-side-brand-copy"><strong><?= $fvEsc($fvBotica['nombre'] ?? 'Mi botica') ?></strong><small>Gestión inteligente</small></span>
  </a>
  <button type="button" class="fv-close-side" id="fv-close-side" aria-label="Cerrar menú"><i class="bi bi-x-lg"></i></button>
 </div>
 <div class="fv-side-caption">MENÚ PRINCIPAL</div>
 <nav class="fv-side-nav">
 <?php foreach ($fvMenu as [$controller,$action,$icon,$label]): $active = strtolower($controller)===$fvController && (!in_array($fvController,['venta','gestion'],true) || $action===$fvAction); ?>
 <?php if($controller==='Gestion'&&$action==='lotes'): ?><div class="fv-nav-admin-divider" role="separator" aria-label="Administración"><span>Administración</span></div><?php endif; ?>
 <a class="fv-side-link <?= $active?'active':'' ?>" href="index.php?c=<?= $fvEsc($controller) ?>&a=<?= $fvEsc($action) ?>" <?= $active?'aria-current="page"':'' ?>><i class="bi <?= $fvEsc($icon) ?>"></i><span><?= $fvEsc($label) ?></span></a>
 <?php endforeach; ?>
 </nav>
 <div class="fv-side-bottom"><div class="fv-side-help"><i class="bi bi-question-circle"></i><strong>¿Necesitas ayuda?</strong><small>Consulta al administrador de tu botica.</small></div><div class="fv-side-profile"><span class="fv-avatar"><?php if (!empty($_SESSION['user_foto'])): ?><img class="fv-user-photo" src="<?= $fvEsc($_SESSION['user_foto']) ?>" alt="Tu foto"><?php else: ?><?= $fvEsc(strtoupper(mb_substr((string)($_SESSION['user_nombre']??'U'),0,1))) ?><?php endif; ?></span><span><strong><?= $fvEsc($_SESSION['user_nombre']??'Usuario') ?></strong><small><?= $fvIsAdmin?'Administrador':'Personal' ?></small></span><form method="post" action="index.php?c=Auth&amp;a=logout" style="display:inline"><input type="hidden" name="csrf" value="<?= $fvEsc(Seguridad::token()) ?>"><button type="submit" title="Cerrar sesión" aria-label="Cerrar sesión" style="border:0;background:transparent;color:inherit"><i class="bi bi-box-arrow-right"></i></button></form></div></div>
</aside>
<div class="fv-side-overlay" id="fv-side-overlay"></div>
<div class="fv-app-main">
<header class="fv-topbar">
 <div class="fv-topbar-start"><button class="fv-top-icon" id="fv-open-side" type="button" aria-label="Abrir menú"><i class="bi bi-list"></i></button><div class="fv-breadcrumb"><span>Inicio</span><i class="bi bi-chevron-right"></i><strong><?= $fvEsc(ucfirst($fvController==='producto'?'Productos':($fvController==='venta'&&$fvAction==='historial'?'Historial':$fvController))) ?></strong></div></div>
 <div class="fv-topbar-actions">
 <div class="fv-top-dropdown"><button type="button" class="fv-top-control" data-fv-toggle="palette" aria-expanded="false"><i class="bi bi-palette"></i><span>Paleta</span><i class="bi bi-chevron-down"></i></button><div class="fv-top-popover" data-fv-popover="palette"><div class="palette-menu-title">Colores de enfermería</div>
 <?php foreach ([['mint','Verde menta','Fresco y clínico','#087f61','#36b88f','#e8f7f1'],['blue','Azul enfermería','Sereno y confiable','#2563a8','#56a7d8','#eaf4fb'],['lavender','Lavanda salud','Suave y humano','#6750a4','#9b86d4','#f1edfb'],['coral','Coral bienestar','Cálido y cercano','#c45c51','#e49279','#fff0eb']] as [$key,$label,$desc,$c1,$c2,$c3]): ?>
 <button type="button" class="palette-option" data-fv-palette="<?= $key ?>" aria-pressed="false"><span class="palette-swatches"><i style="background:<?= $c1 ?>"></i><i style="background:<?= $c2 ?>"></i><i style="background:<?= $c3 ?>"></i></span><span><strong><?= $label ?></strong><small><?= $desc ?></small></span><i class="bi bi-check-lg fv-option-check"></i></button>
 <?php endforeach; ?></div></div>
 <div class="fv-top-dropdown"><button type="button" class="fv-top-control" data-fv-toggle="layout" aria-expanded="false"><i class="bi bi-layout-sidebar"></i><span>Menú</span><i class="bi bi-chevron-down"></i></button><div class="fv-top-popover fv-layout-popover" data-fv-popover="layout"><div class="palette-menu-title">Posición del menú</div><button type="button" class="fv-layout-option" data-fv-layout="horizontal"><i class="bi bi-layout-text-window"></i> Horizontal <i class="bi bi-check-lg fv-option-check"></i></button><button type="button" class="fv-layout-option" data-fv-layout="vertical"><i class="bi bi-layout-sidebar-inset"></i> Vertical <i class="bi bi-check-lg fv-option-check"></i></button><small>En móviles se usa el menú desplegable.</small></div></div>
 <div class="fv-top-dropdown fv-user-dropdown"><button type="button" class="fv-top-avatar" data-fv-toggle="user" aria-expanded="false" aria-label="Abrir menú de usuario" title="Mi cuenta"><?php if (!empty($_SESSION['user_foto'])): ?><img class="fv-user-photo" src="<?= $fvEsc($_SESSION['user_foto']) ?>" alt="Tu foto de perfil"><?php else: ?><i class="bi bi-person-fill"></i><?php endif; ?></button><div class="fv-top-popover fv-user-popover"><div class="fv-user-info"><strong><?= $fvEsc($_SESSION['user_nombre'] ?? 'Usuario') ?></strong><small><?= $fvIsAdmin ? 'Administrador' : 'Personal' ?></small></div><hr><a href="index.php?c=Cuenta&amp;a=perfil" class="fv-layout-option"><i class="bi bi-person-circle"></i> Mi perfil</a><?php if ($fvIsAdmin): ?><a href="index.php?c=Cuenta&amp;a=negocio" class="fv-layout-option"><i class="bi bi-shop"></i> Mi botica</a><?php endif; ?><hr><div class="fv-mode-picker" role="group" aria-label="Modo de apariencia"><button type="button" data-fv-mode="light" aria-pressed="true"><i class="bi bi-sun" aria-hidden="true"></i> Día</button><button type="button" data-fv-mode="dark" aria-pressed="false"><i class="bi bi-moon-stars" aria-hidden="true"></i> Noche</button></div><hr><form method="post" action="index.php?c=Auth&amp;a=logout" style="display:inline"><input type="hidden" name="csrf" value="<?= $fvEsc(Seguridad::token()) ?>"><button type="submit" class="fv-user-logout" style="border:0;background:transparent;color:inherit"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button></form></div></div>
 </div>
</header>
<main class="container-fluid fv-content-area">
