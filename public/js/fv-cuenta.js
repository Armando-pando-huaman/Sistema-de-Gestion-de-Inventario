(() => {
    document.querySelectorAll('[data-image-preview]').forEach(input => {
        let previewUrl;
        input.addEventListener('change', () => {
            input.setCustomValidity('');
            const file = input.files[0];
            if (!file) { document.getElementById(input.dataset.imagePreview).hidden = true; return; }
            if (file.size > 5 * 1024 * 1024 || !['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                input.value = ''; input.setCustomValidity('Selecciona JPG, PNG o WebP de hasta 5 MB.'); input.reportValidity(); return;
            }
            input.setCustomValidity('');
            if (previewUrl) URL.revokeObjectURL(previewUrl);
            previewUrl = URL.createObjectURL(file);
            const preview = document.getElementById(input.dataset.imagePreview);
            preview.src = previewUrl; preview.hidden = false;
        });
    });
    const form = document.getElementById('registro-form');
    if (!form) return;
    const persona = document.getElementById('registro-persona');
    const botica = document.getElementById('registro-botica');
    const siguiente = document.getElementById('registro-siguiente');
    const atras = document.getElementById('registro-atras');
    botica.hidden = true; siguiente.hidden = false; atras.hidden = false;
    siguiente.addEventListener('click', () => {
        const password = document.getElementById('password');
        const confirmacion = document.getElementById('confirmacion');
        confirmacion.setCustomValidity(password.value === confirmacion.value ? '' : 'Las contraseñas no coinciden.');
        for (const input of persona.querySelectorAll('input')) if (!input.reportValidity()) return;
        persona.hidden = true; botica.hidden = false;
        botica.querySelector('input:not([type=hidden]), select, button[type=submit]')?.focus();
    });
    atras.addEventListener('click', () => { persona.hidden = false; botica.hidden = true; document.getElementById('nombre').focus(); });
    form.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !persona.hidden && event.target.tagName === 'INPUT') { event.preventDefault(); siguiente.click(); }
    });
})();
