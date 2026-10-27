<?php require __DIR__.'/../layouts/header.php'; ?>
<link rel="stylesheet" href="css/fv-operaciones.css">
<link rel="stylesheet" href="css/fv-documentacion.css">
<div class="fv-operations fv-docs">
 <header class="op-heading"><div><h2>Documentación del sistema</h2><p>Manual de uso de la botica: operaciones, permisos y consultas habituales.</p></div><button type="button" class="btn btn-outline-primary d-print-none" onclick="window.print()"><i class="bi bi-printer" aria-hidden="true"></i> Imprimir manual</button></header>
 <div class="fv-docs-layout">
  <nav class="op-panel fv-docs-index d-print-none" aria-label="Índice de documentación"><h3>Contenido</h3><?php foreach ($secciones as $seccion): ?><a href="#<?= $seccion['id'] ?>"><?= $fvEsc($seccion['titulo']) ?></a><?php endforeach; ?></nav>
  <div>
   <?php foreach ($secciones as $seccion): ?>
   <section class="op-panel fv-docs-section" id="<?= $seccion['id'] ?>">
    <h3><?= $fvEsc($seccion['titulo']) ?></h3><p class="op-note"><strong>Disponible para:</strong> <?= $fvEsc($seccion['rol']) ?></p>
    <?php foreach ($seccion['texto'] as $parrafo): ?><p><?= $fvEsc($parrafo) ?></p><?php endforeach; ?>
    <?php if (!empty($seccion['pasos'])): ?><ol><?php foreach ($seccion['pasos'] as $paso): ?><li><?= $fvEsc($paso) ?></li><?php endforeach; ?></ol><?php endif; ?>
    <?php if (isset($seccion['enlace']) && (empty($seccion['admin']) || $fvIsAdmin)):
      [$controladorDoc,$accionDoc,$etiquetaDoc] = $seccion['enlace'];
      $docAdmin = $controladorDoc === 'Gestion' && $accionDoc !== 'caja';
      if (!$docAdmin || $fvIsAdmin): ?>
     <a class="btn btn-outline-primary d-print-none" href="index.php?<?= $fvEsc(http_build_query(['c'=>$controladorDoc,'a'=>$accionDoc])) ?>"><?= $fvEsc($etiquetaDoc) ?></a>
    <?php endif; endif; ?>
   </section>
   <?php endforeach; ?>
  </div>
 </div>
</div>
<?php require __DIR__.'/../layouts/footer.php'; ?>
