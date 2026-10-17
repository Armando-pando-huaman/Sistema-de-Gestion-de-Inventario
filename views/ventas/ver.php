<?php
require_once '../views/layouts/header.php';
$esc = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$fecha = date('d/m/Y H:i', strtotime($venta['fecha']));
$esBoleta = ($venta['tipo_comprobante'] ?? 'ticket') === 'boleta';
$texto = "FarmaVidaHC - RESUMEN DE COMPRA\nVenta #{$venta['id']} - {$fecha}\n";
foreach ($detalle as $d) {
    $texto .= $d['producto'] . ' - ' . $d['cantidad'] . ' ' . ($d['presentacion'] ?? 'unidad') . ' x S/ ' . number_format((float)$d['precio_unitario'], 2) . ' = S/ ' . number_format((float)$d['subtotal'], 2) . "\n";
}
$texto .= 'TOTAL: S/ ' . number_format((float)$venta['total'], 2) . "\n";
$texto .= $esBoleta ? 'Boleta electrónica pendiente de emisión/validación SUNAT.' : 'Ticket interno sin validez tributaria.';
$telefono = preg_replace('/\D+/', '', (string)($venta['telefono'] ?? ''));
if (strlen($telefono) === 9) $telefono = '51' . $telefono;
$waUrl = $telefono ? 'https://wa.me/' . $telefono : ''; // El destinatario puede editarse en la vista
?>
<link rel="stylesheet" href="css/fv-ventas.css">
<style>
@media print {
 body * {visibility:hidden!important}
 #fv-ticket,#fv-ticket * {visibility:visible!important}
 #fv-ticket {position:absolute;left:0;top:0;width:80mm!important;border:0!important;box-shadow:none!important;padding:3mm!important;font-size:11px!important}
 .fv-ticket-actions {display:none!important}
 @page {size:80mm auto;margin:2mm}
}
.fv-ticket {width:min(100%,440px);margin:1.5rem auto;padding:1.6rem;border:1px dashed #9ca3af;border-radius:12px;background:white;color:#111}
.fv-ticket h3 {text-align:center;font-size:1.25rem}
.fv-ticket table {width:100%;font-size:.85rem}
.fv-ticket td {padding:5px 2px;vertical-align:top}
.fv-ticket .right {text-align:right;white-space:nowrap}
.fv-ticket-total {border-top:1px dashed #777;margin-top:12px;padding-top:10px;display:flex;justify-content:space-between;font-size:1.2rem;font-weight:700}
</style>
<div class="fv-ticket-actions fv-ticket-toolbar d-flex flex-wrap justify-content-between align-items-center gap-2">
 <h2 class="mb-0">Venta #<?= (int)$venta['id'] ?></h2>
 <div class="d-flex flex-wrap gap-2">
  <button type="button" id="fv-ticket-compartir" class="btn btn-success"><i class="bi bi-whatsapp"></i> <span id="fv-share-label">Compartir ticket</span></button>
  <button type="button" onclick="window.print()" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Imprimir</button>
  <a class="btn btn-outline-secondary" href="index.php?c=Venta&amp;a=index"><i class="bi bi-plus-circle"></i> Nueva venta</a>
 </div>
</div>
<div class="fv-ticket-actions fv-share-options mx-auto" style="max-width:440px">
 <details id="fv-contacto-opciones" class="small">
  <summary>WhatsApp del cliente: <strong id="fv-numero-visible"><?= $esc($venta['telefono'] ?: 'Sin número') ?></strong> · Cambiar número</summary>
  <label class="form-label mt-2" for="fv-telefono-cliente">Número de WhatsApp</label>
  <input type="tel" id="fv-telefono-cliente" class="form-control" inputmode="tel" autocomplete="tel" placeholder="987654321" value="<?= $esc($venta['telefono'] ?? '') ?>">
 </details>
</div>
<div id="fv-ticket" class="fv-ticket">
 <h3>FarmaVidaHC</h3>
 <p class="text-center small">Comprobante <?= $esBoleta ? 'solicitado: boleta electrónica' : 'interno de venta' ?></p>
 <hr>
 <p><strong>Venta:</strong> #<?= (int)$venta['id'] ?><br>
 <strong>Fecha:</strong> <?= $esc($fecha) ?><br>
 <strong>Cliente:</strong> <?= $esc($venta['cliente']) ?><br>
 <?php if (!empty($venta['numero_documento'])): ?><strong>Documento:</strong> <?= $esc($venta['tipo_documento']) ?> <?= $esc($venta['numero_documento']) ?><br><?php endif; ?>
 <strong>Cajero:</strong> <?= $esc($venta['usuario'] ?? '—') ?></p>
 <hr>
 <table><tbody>
 <?php foreach ($detalle as $d): ?>
  <tr><td><?= $esc($d['producto']) ?><br><small><?= (int)$d['cantidad'] ?> <?= $esc($d['presentacion'] ?? 'unidad') ?> × S/ <?= number_format((float)$d['precio_unitario'], 2) ?></small></td><td class="right">S/ <?= number_format((float)$d['subtotal'], 2) ?></td></tr>
 <?php endforeach; ?>
 </tbody></table>
 <div class="fv-ticket-total"><span>TOTAL</span><span>S/ <?= number_format((float)$venta['total'], 2) ?></span></div>
 <hr>
 <?php if ($esBoleta): ?>
 <p class="small"><strong>Estado SUNAT:</strong> <?= $esc($venta['estado_sunat'] ?? 'pendiente') ?><br><strong>ATENCIÓN:</strong> Este documento es solo una constancia interna. No es una boleta electrónica válida hasta completar emisión, firma y aceptación según el procedimiento tributario aplicable.</p>
 <?php else: ?>
 <p class="small text-center">Ticket interno. No es comprobante de pago tributario.</p>
 <?php endif; ?>
 <p class="text-center small">Gracias por su compra</p>
</div>
<div class="fv-ticket-actions text-center mb-3">
 <small id="fv-ticket-estado" class="text-muted" role="status" aria-live="polite">Pulsa Compartir ticket para descargar la imagen PNG. Luego podrás abrir WhatsApp del cliente.</small>
</div>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
(() => {
 'use strict';
 const ticket = document.getElementById('fv-ticket');
 const share = document.getElementById('fv-ticket-compartir');
 const label = document.getElementById('fv-share-label');
 const phoneInput = document.getElementById('fv-telefono-cliente');
 const contactOptions = document.getElementById('fv-contacto-opciones');
 const phoneDisplay = document.getElementById('fv-numero-visible');
 const status = document.getElementById('fv-ticket-estado');
 const filename = <?= json_encode('FarmaVidaHC_Ticket_' . (int)$venta['id'] . '.png') ?>;
 const message = 'Ticket de compra FarmaVidaHC';
 let readyPhone = null;
 let busy = false;
 const setStatus = text => { status.textContent = text; };
 const normalizePhone = () => {
   let digits = phoneInput.value.replace(/\D/g, '');
   if (digits.length === 9 && digits.startsWith('9')) digits = '51' + digits;
   return /^\d{10,15}$/.test(digits) ? digits : null;
 };
 const reset = () => {
   readyPhone = null;
   label.textContent = 'Compartir ticket';
   share.classList.add('btn-success');
   share.classList.remove('btn-primary');
 };
 phoneInput.addEventListener('input', () => {
   phoneDisplay.textContent = phoneInput.value.trim() || 'Sin número';
   reset();
 });
 const download = blob => {
   const url = URL.createObjectURL(blob);
   const link = document.createElement('a');
   link.href = url;
   link.download = filename;
   document.body.appendChild(link);
   link.click();
   link.remove();
   setTimeout(() => URL.revokeObjectURL(url), 60000);
 };
 const capture = async () => {
   if (typeof html2canvas !== 'function') throw new Error('No se cargó la biblioteca de imagen. Verifica Internet.');
   if (document.fonts?.ready) await document.fonts.ready;
   const canvas = await html2canvas(ticket, {
     backgroundColor: '#ffffff', scale: 2, useCORS: true,
     scrollX: 0, scrollY: -window.scrollY,
     onclone: doc => {
       const el = doc.getElementById('fv-ticket');
       el.style.width = '440px';
       el.style.maxWidth = '440px';
       el.style.margin = '0';
       el.style.boxShadow = 'none';
     }
   });
   return await new Promise((resolve, reject) => canvas.toBlob(
     blob => blob ? resolve(blob) : reject(new Error('No se pudo generar PNG')), 'image/png'
   ));
 };
 share.addEventListener('click', async () => {
   if (busy) return;
   // Segunda pulsación: el PNG ya fue descargado, ahora sí abrir WhatsApp.
   if (readyPhone) {
     const url = 'https://wa.me/' + readyPhone + '?text=' + encodeURIComponent(message);
     // Navegar en la misma pestaña evita bloqueos de ventanas emergentes en Android.
     window.location.assign(url);
     return;
   }
   const phone = normalizePhone();
   if (!phone) {
     contactOptions.open = true;
     phoneInput.focus();
     setStatus('Ingresa el WhatsApp del cliente antes de generar el ticket.');
     return;
   }
   busy = true;
   share.disabled = true;
   label.textContent = 'Generando PNG...';
   setStatus('Generando el ticket para descargar...');
   try {
     const blob = await capture();
     download(blob);
     readyPhone = phone;
     label.textContent = 'Abrir WhatsApp del cliente';
     share.classList.remove('btn-success');
     share.classList.add('btn-primary');
     setStatus('Se solicitó la descarga de ' + filename + '. Comprueba que esté en Descargas y pulsa «Abrir WhatsApp del cliente». El mensaje estará preparado: «' + message + '». Adjunta la imagen manualmente.');
   } catch (error) {
     console.error('FarmaVidaHC: no se pudo generar el ticket', error);
     reset();
     setStatus('No se pudo generar la imagen: ' + error.message);
   } finally {
     busy = false;
     share.disabled = false;
   }
 });
})();
</script>
<?php require_once '../views/layouts/footer.php'; ?>
