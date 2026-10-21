
(() => {
  'use strict';
  const modal = document.getElementById('fv-scan-modal');
  const input = document.getElementById('codigo_barras');
  const video = document.getElementById('fv-scan-video');
  const fallback = document.getElementById('fv-scan-fallback');
  const status = document.getElementById('fv-scan-status');
  if (!modal || !input || !video || !fallback || !status) return;

  let running = false, generation = 0, stream = null, scanner = null;
  let detector = null, frame = 0, busy = false, lastAttempt = 0;
  let torchOn = false, track = null, zoom = null;
  let pendingScript = null;
  const canvas = document.createElement('canvas');
  const ctx = canvas.getContext('2d', { willReadFrequently: true });
  const say = message => { status.textContent = message; };

  // Controles generados desde JS: no requiere editar CSS ni PHP.
  const controls = document.createElement('div');
  controls.style.cssText = 'display:flex;align-items:center;flex-wrap:wrap;gap:10px;margin:10px 0;';
  const torchButton = document.createElement('button');
  torchButton.type = 'button';
  torchButton.className = 'fv-scan-btn';
  torchButton.textContent = 'Linterna';
  torchButton.hidden = true;
  const zoomLabel = document.createElement('label');
  zoomLabel.textContent = 'Zoom ';
  zoomLabel.style.cssText = 'display:flex;align-items:center;gap:8px;font-size:.9rem;';
  const zoomInput = document.createElement('input');
  zoomInput.type = 'range';
  zoomInput.setAttribute('aria-label', 'Zoom de la cámara');
  zoomInput.style.width = '130px';
  zoomLabel.appendChild(zoomInput);
  zoomLabel.hidden = true;
  controls.append(torchButton, zoomLabel);
  status.before(controls);

  // Guía visual: el vídeo permanece sin filtros; solo se procesa una copia interna.
  const videoWrapper = document.createElement('div');
  videoWrapper.style.cssText = 'position:relative;width:100%;overflow:hidden;border-radius:12px;background:#111;';
  video.parentNode.insertBefore(videoWrapper, video);
  videoWrapper.appendChild(video);
  const guide = document.createElement('div');
  guide.setAttribute('aria-hidden', 'true');
  guide.style.cssText = 'pointer-events:none;position:absolute;left:10%;right:10%;top:30%;height:40%;border:2px dashed rgba(78,255,170,.85);border-radius:10px;box-shadow:0 0 0 999px rgba(0,0,0,.05) inset;';
  videoWrapper.appendChild(guide);

  function save(value) {
    if (!running || !value) return;
    const handled = !input.dispatchEvent(new CustomEvent('fv:scan', { bubbles: true, cancelable: true, detail: { value: String(value) } }));
    if (!handled) {
      input.value = String(value);
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new Event('change', { bubbles: true }));
    }
    if (navigator.vibrate) navigator.vibrate(80);
    close();
    input.focus();
  }

  function close() {
    ++generation;
    running = false;
    if (frame) cancelAnimationFrame(frame);
    frame = 0;
    busy = false;
    const previous = scanner;
    scanner = null;
    if (previous) {
      Promise.resolve().then(() => previous.stop()).catch(() => {}).then(() => previous.clear()).catch(() => {});
    }
    if (stream) stream.getTracks().forEach(t => t.stop());
    stream = null;
    track = null;
    detector = null;
    torchOn = false;
    torchButton.hidden = true;
    zoomLabel.hidden = true;
    video.pause();
    video.srcObject = null;
    modal.hidden = true;
  }

  function prepareCanvas() {
    if (!ctx || !video.videoWidth || !video.videoHeight) return false;
    // Evitar imágenes enormes en móviles; 960px mantiene suficiente detalle.
    const factor = Math.min(1, 960 / Math.max(video.videoWidth, video.videoHeight));
    const width = Math.max(1, Math.round(video.videoWidth * factor));
    const height = Math.max(1, Math.round(video.videoHeight * factor));
    if (canvas.width !== width || canvas.height !== height) {
      canvas.width = width;
      canvas.height = height;
    }
    ctx.drawImage(video, 0, 0, width, height);
    return true;
  }

  function filtered(mode) {
    const image = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const pixels = image.data;
    // Umbral automático aproximado basado en el brillo medio muestreado.
    let sum = 0, count = 0;
    for (let i = 0; i < pixels.length; i += 32) {
      sum += (pixels[i] * 77 + pixels[i + 1] * 150 + pixels[i + 2] * 29) >> 8;
      count++;
    }
    const threshold = Math.max(65, Math.min(195, sum / Math.max(1, count)));
    for (let i = 0; i < pixels.length; i += 4) {
      const gray = (pixels[i] * 77 + pixels[i + 1] * 150 + pixels[i + 2] * 29) >> 8;
      let v = gray;
      if (mode === 'binary') v = gray > threshold ? 255 : 0;
      if (mode === 'negative') v = 255 - gray;
      pixels[i] = pixels[i + 1] = pixels[i + 2] = v;
    }
    ctx.putImageData(image, 0, 0);
  }

  async function detectLoop(now) {
    if (!running || !detector) return;
    frame = requestAnimationFrame(detectLoop);
    if (busy || now - lastAttempt < 180 || video.readyState < 2) return;
    lastAttempt = now;
    busy = true;
    const activeGeneration = generation;
    try {
      // Primero la imagen original; filtros únicamente si no se detecta nada.
      const original = await detector.detect(video);
      if (!running || generation !== activeGeneration) return;
      if (original.length && original[0].rawValue) return save(original[0].rawValue);
      if (prepareCanvas()) {
        // Un filtro por ciclo reduce el uso de CPU y evita congelar la cámara.
        const modes = ['gray', 'binary', 'negative'];
        const mode = modes[Math.floor(now / 900) % modes.length];
        filtered(mode);
        const found = await detector.detect(canvas);
        if (!running || generation !== activeGeneration) return;
        if (found.length && found[0].rawValue) return save(found[0].rawValue);
      }
    } catch (_) { /* Un fotograma fallido no detiene la lectura. */ }
    finally { busy = false; }
  }

  async function configureCamera() {
    track = stream?.getVideoTracks()[0] || null;
    if (!track) return;
    const capabilities = track.getCapabilities?.() || {};
    const advanced = [];
    if (Array.isArray(capabilities.focusMode) && capabilities.focusMode.includes('continuous')) {
      advanced.push({ focusMode: 'continuous' });
    }
    if (advanced.length) {
      try { await track.applyConstraints({ advanced }); } catch (_) {}
    }
    if (capabilities.torch) torchButton.hidden = false;
    if (capabilities.zoom && Number.isFinite(capabilities.zoom.min) && Number.isFinite(capabilities.zoom.max)) {
      zoom = capabilities.zoom;
      zoomInput.min = String(zoom.min);
      zoomInput.max = String(zoom.max);
      zoomInput.step = String(zoom.step || 0.1);
      zoomInput.value = String(track.getSettings?.().zoom || zoom.min);
      zoomLabel.hidden = false;
    }
  }

  torchButton.addEventListener('click', async () => {
    if (!track || !running) return;
    try {
      const next = !torchOn;
      await track.applyConstraints({ advanced: [{ torch: next }] });
      torchOn = next;
      torchButton.textContent = next ? 'Apagar linterna' : 'Linterna';
    } catch (_) { say('La linterna no está disponible en esta cámara.'); }
  });
  zoomInput.addEventListener('input', async () => {
    if (!track || !running) return;
    try { await track.applyConstraints({ advanced: [{ zoom: Number(zoomInput.value) }] }); }
    catch (_) { /* No todas las cámaras permiten cambiar el zoom. */ }
  });

  function loadFallback() {
    if (window.Html5Qrcode) return Promise.resolve();
    if (pendingScript) return pendingScript;
    pendingScript = new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.src = 'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js';
      script.onload = resolve;
      script.onerror = () => reject(new Error('No se pudo cargar el lector alternativo. Revisa Internet.'));
      document.head.appendChild(script);
    }).catch(error => { pendingScript = null; throw error; });
    return pendingScript;
  }

  async function open() {
    if (running) return;
    modal.hidden = false;
    video.hidden = false;
    videoWrapper.hidden = false;
    fallback.hidden = true;
    running = true;
    const activeGeneration = ++generation;
    if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
      say('Para usar la cámara abre FarmaVidaHC mediante HTTPS o http://localhost. Una IP local con HTTP normalmente no funciona.');
      return;
    }
    try {
      say('Solicitando permiso de cámara…');
      let formats = [];
      if ('BarcodeDetector' in window) {
        try {
          const supported = await BarcodeDetector.getSupportedFormats();
          formats = ['qr_code', 'data_matrix', 'ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'itf']
            .filter(format => supported.includes(format));
        } catch (_) {}
      }
      if (activeGeneration !== generation) return;
      if (formats.length) {
        detector = new BarcodeDetector({ formats });
        const obtained = await navigator.mediaDevices.getUserMedia({
          audio: false,
          video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 }, height: { ideal: 1080 } }
        });
        if (activeGeneration !== generation) { obtained.getTracks().forEach(t => t.stop()); return; }
        stream = obtained;
        video.srcObject = stream;
        await video.play();
        if (activeGeneration !== generation) return;
        await configureCamera();
        say('Coloca el código dentro del recuadro y mantén la cámara estable. Los filtros se aplican automáticamente.');
        frame = requestAnimationFrame(detectLoop);
      } else {
        say('Cargando lector compatible…');
        await loadFallback();
        if (activeGeneration !== generation) return;
        videoWrapper.hidden = true;
        fallback.hidden = false;
        const reader = new Html5Qrcode('fv-scan-fallback', { verbose: false });
        scanner = reader;
        await reader.start({ facingMode: 'environment' }, { fps: 12, qrbox: (w, h) => ({ width: Math.round(w * 0.85), height: Math.round(h * 0.65) }) }, save, () => {});
        if (activeGeneration !== generation) {
          try { await reader.stop(); await reader.clear(); } catch (_) {}
          return;
        }
        say('Apunta al código QR o de barras. Este navegador utiliza el lector alternativo.');
      }
    } catch (error) {
      if (activeGeneration !== generation) return;
      const reason = error.name === 'NotAllowedError'
        ? 'Permiso de cámara denegado. Revisa los permisos del sitio.'
        : error.name === 'NotFoundError'
          ? 'No se encontró una cámara disponible.'
          : `No se pudo iniciar la cámara: ${error.message || error}`;
      say(reason);
      if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
    }
  }

  document.querySelectorAll('[data-fv-scan-open]').forEach(button => button.addEventListener('click', open));
  document.querySelectorAll('[data-fv-scan-close]').forEach(button => button.addEventListener('click', close));
  modal.addEventListener('click', event => { if (event.target === modal) close(); });
  document.addEventListener('keydown', event => { if (event.key === 'Escape' && !modal.hidden) close(); });
  window.addEventListener('pagehide', close);
})();
