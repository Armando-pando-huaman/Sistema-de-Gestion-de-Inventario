<?php $escLote=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); ?>
<section class="card shadow-sm mb-4">
    <header class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <strong><i class="bi bi-boxes"></i> Todos los lotes con existencias · <?= count($data['lotes']) ?></strong>
        <form method="get" class="d-flex align-items-center flex-wrap gap-2">
            <input type="hidden" name="c" value="Reporte">
            <input type="hidden" name="a" value="<?= $accionLotes ?>">
            <?php if(isset($dias)): ?><input type="hidden" name="dias" value="<?= (int)$dias ?>"><?php endif; ?>
            <label for="orden-lotes" class="small mb-0">Ordenar por</label>
            <select id="orden-lotes" name="orden_lotes" class="form-select form-select-sm" style="width:auto">
                <option value="vencimiento" <?= $ordenLotes==='vencimiento'?'selected':'' ?>>Más próximos a vencer</option>
                <option value="recientes" <?= $ordenLotes==='recientes'?'selected':'' ?>>Ingresados recientemente</option>
            </select>
            <button class="btn btn-sm btn-primary" type="submit">Aplicar</button>
        </form>
    </header>
    <p class="small text-muted px-3 pt-3 mb-2">Incluye vencidos, próximos a vencer, vigentes y lotes sin fecha, sin límite de 90 días. La fecha de compra aparece cuando hay un documento de compra registrado.</p>
    <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead><tr><th>Producto / categoría</th><th>Lote</th><th>Unidades</th><th>Ingreso</th><th>Compra</th><th>Vencimiento</th><th>Estado</th></tr></thead>
            <tbody>
            <?php foreach($data['lotes'] as $p):
                $restantes=$p['dias_restantes']===null?null:(int)$p['dias_restantes'];
                if($restantes===null){$estado='Sin fecha';$color='secondary';}
                elseif($restantes<0){$estado='Vencido hace '.abs($restantes).' días';$color='danger';}
                elseif($restantes===0){$estado='Vence hoy';$color='danger';}
                else{$estado=($restantes<=90?'Por vencer · ':'Vigente · ').$restantes.' días';$color=$restantes<=30?'danger':($restantes<=90?'warning':'success');}
            ?>
                <tr>
                    <td><strong><?= $escLote($p['nombre']) ?></strong><br><small class="text-muted"><?= $escLote($p['categoria']??'Sin categoría') ?></small></td>
                    <td><?= $escLote($p['lote']) ?><?php if($p['por_verificar']): ?><br><small class="text-muted">Datos por verificar</small><?php endif; ?></td>
                    <td><?= (int)$p['stock'] ?></td>
                    <td class="text-nowrap"><?= date('d/m/Y H:i',strtotime($p['fecha_ingreso'])) ?></td>
                    <td class="text-nowrap"><?= $p['fecha_compra']?date('d/m/Y',strtotime($p['fecha_compra'])):'Sin compra asociada' ?></td>
                    <td class="text-nowrap"><?= $p['fecha_vencimiento']?date('d/m/Y',strtotime($p['fecha_vencimiento'])):'Sin fecha' ?></td>
                    <td><span class="badge bg-<?= $color ?> <?= $color==='warning'?'text-dark':'' ?>"><?= $estado ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if(!$data['lotes']): ?><tr><td colspan="7" class="text-center text-muted py-4">No hay lotes con existencias.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
