<?php require_once '../views/layouts/header.php';
$esc = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); ?>
<div class="fv-products fv-categories-page">
  <section class="fv-page-head"><div><span class="fv-eyebrow"><?= $fvEsc($fvNombreBotica) ?> · Inventario</span><h2><i class="bi bi-tags me-2"></i> Categorías</h2><p>Organiza y administra las categorías de tus productos.</p></div><a class="fv-btn-light" href="index.php?c=Producto&a=index"><i class="bi bi-arrow-left"></i> Volver a productos</a></section>
  <?php if ($mensaje): ?><div class="alert alert-<?= $esc($mensaje[0]) ?>" role="alert"><?= $esc($mensaje[1]) ?></div><?php endif; ?>
  <div class="fv-categories-grid">
    <?php if($fvIsAdmin): ?><section class="fv-panel fv-category-form-panel"><div class="fv-panel-top"><h5><i class="bi bi-plus-circle me-2"></i><span id="fv-category-form-title">Nueva categoría</span></h5></div>
      <form method="POST" action="index.php?c=Categoria&a=guardar" class="fv-form-body" id="fv-category-form">
        <input type="hidden" name="csrf" value="<?= $esc($csrf) ?>"><input type="hidden" name="id" id="fv-category-id" value="">
        <label class="form-label" for="fv-category-name">Nombre de la categoría *</label>
        <input class="form-control" id="fv-category-name" name="nombre" maxlength="100" required placeholder="Ej.: Dermocosmética" autocomplete="off">
        <div class="fv-form-actions"><button type="button" class="fv-cancel" id="fv-category-reset" hidden>Cancelar edición</button><button type="submit" class="fv-save"><i class="bi bi-check-circle me-1"></i> Guardar categoría</button></div>
      </form>
    </section>
    <?php endif; ?><section class="fv-panel"><div class="fv-panel-top"><h5><i class="bi bi-list-ul me-2"></i> Categorías registradas</h5><small><?= count($categorias) ?> categorías</small></div>
      <div class="fv-table-wrap"><table class="table fv-table"><thead><tr><th>Nombre</th><th>Productos</th><th>Acciones</th></tr></thead><tbody>
      <?php foreach ($categorias as $c): ?><tr><td><span class="fv-category"><?= $esc($c['nombre']) ?></span></td><td><?= (int)$c['total_productos'] ?></td><td class="text-nowrap"><?php if($fvIsAdmin): ?><button type="button" class="fv-icon-btn edit fv-category-edit" data-id="<?= (int)$c['id'] ?>" data-name="<?= $esc($c['nombre']) ?>" title="Editar categoría"><i class="bi bi-pencil-square"></i></button>
      <form class="fv-inline-form" method="POST" action="index.php?c=Categoria&a=eliminar" onsubmit="return confirm('¿Eliminar esta categoría?')"><input type="hidden" name="csrf" value="<?= $esc($csrf) ?>"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><button type="submit" class="fv-icon-btn delete" title="Eliminar categoría" <?= (int)$c['total_productos'] > 0 ? 'disabled title="No se puede eliminar: tiene productos"' : '' ?>><i class="bi bi-trash3"></i></button></form><?php endif; ?></td></tr><?php endforeach; ?>
      <?php if (!$categorias): ?><tr><td colspan="3" class="text-center py-4 text-muted">Todavía no hay categorías. Crea la primera.</td></tr><?php endif; ?>
      </tbody></table></div>
    </section>
  </div>
</div>
<script>
(() => { const id=document.getElementById('fv-category-id'),name=document.getElementById('fv-category-name'),title=document.getElementById('fv-category-form-title'),reset=document.getElementById('fv-category-reset');document.querySelectorAll('.fv-category-edit').forEach(b=>b.addEventListener('click',()=>{id.value=b.dataset.id;name.value=b.dataset.name;title.textContent='Editar categoría';reset.hidden=false;name.focus();window.scrollTo({top:0,behavior:'smooth'});}));reset?.addEventListener('click',()=>{id.value='';name.value='';title.textContent='Nueva categoría';reset.hidden=true;});})();
</script>
<?php require_once '../views/layouts/footer.php'; ?>
