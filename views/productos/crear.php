<?php require_once '../views/layouts/header.php'; ?>
<div class="fv-products">
<section class="fv-page-head">
    <div>
        <span class="fv-eyebrow">FarmaVidaHC · Gestión de productos</span>
        <h2><i class="bi bi-plus-circle me-2"></i> Nuevo Producto</h2>
        <p>Registra un nuevo producto en el inventario.</p>
    </div>
    <a href="index.php?c=Producto&a=index" class="fv-btn-light"><i class="bi bi-arrow-left"></i> Volver al listado</a>
</section>
<div class="fv-panel">
    <div class="fv-form-body">
        <form method="POST" class="row g-3">
            <div class="col-12"><h5 class="fv-section-title"><i class="bi bi-capsule"></i> Información del producto</h5></div>
            <div class="col-md-4">
                <label class="form-label" for="codigo_barras">Código de Barras</label>
                <input id="codigo_barras" type="text" name="codigo_barras" class="form-control" value="">
            </div>
            <div class="col-md-8">
                <label class="form-label" for="nombre">Nombre *</label>
                <input id="nombre" type="text" name="nombre" class="form-control" value="" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="2"></textarea>
            </div>
            <div class="col-12 mt-4"><h5 class="fv-section-title"><i class="bi bi-currency-exchange"></i> Precios y existencias</h5></div>
            <div class="col-6 col-md-3">
                <label class="form-label" for="precio_compra">Precio Compra</label>
                <input id="precio_compra" type="number" step="0.01" name="precio_compra" class="form-control" value="0.00">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label" for="precio_venta">Precio Venta *</label>
                <input id="precio_venta" type="number" step="0.01" name="precio_venta" class="form-control" value="" required>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="stock">Stock *</label>
                <input id="stock" type="number" name="stock" class="form-control" value="0" required>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label" for="stock_minimo">Stock Mínimo</label>
                <input id="stock_minimo" type="number" name="stock_minimo" class="form-control" value="5">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="fecha_vencimiento">Vencimiento</label>
                <input id="fecha_vencimiento" type="date" name="fecha_vencimiento" class="form-control" value="">
            </div>
            <div class="col-12 mt-4"><h5 class="fv-section-title"><i class="bi bi-tags"></i> Clasificación</h5></div>
            <div class="col-md-6">
                <label class="form-label" for="categoria_id">Categoría</label>
                <select id="categoria_id" name="categoria_id" class="form-select">
                    <option value="">-- Seleccione --</option>
                    <option value="1">Analgésicos</option>
            <option value="2">Antibióticos</option>
            <option value="3">Antiinflamatorios</option>
            <option value="4">Vitaminas</option>
            <option value="5">Cuidado Personal</option>
                </select>
            </div>
            <div class="col-12">
                <div class="fv-form-actions">
                    <a href="index.php?c=Producto&a=index" class="fv-cancel"><i class="bi bi-x-circle me-1"></i> Cancelar</a>
                    <button type="submit" class="fv-save"><i class="bi bi-check-circle me-1"></i> Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
<?php require_once '../views/layouts/footer.php'; ?>
