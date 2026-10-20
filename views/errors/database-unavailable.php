<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Base de datos no disponible | Inventario</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: #f1f5f9; color: #172033; font-family: system-ui, sans-serif; }
        main { width: 100%; max-width: 560px; padding: 40px 32px; background: white; border-radius: 20px; box-shadow: 0 16px 48px #17203312; text-align: center; }
        .icon { display: inline-grid; place-items: center; width: 64px; height: 64px; border-radius: 50%; background: #fff1e6; color: #b45309; font-size: 36px; font-weight: 700; }
        .status { color: #64748b; font-size: 13px; letter-spacing: .08em; text-transform: uppercase; margin-top: 24px; }
        h1 { font-size: clamp(24px, 5vw, 32px); margin: 12px 0; }
        p { color: #526078; line-height: 1.65; }
        a { display: inline-block; margin: 16px 0; padding: 13px 24px; background: #2563eb; color: white; border-radius: 10px; text-decoration: none; font-weight: 600; }
        a:hover { background: #1d4ed8; }
        a:focus-visible { outline: 3px solid #172033; outline-offset: 4px; }
        .help { font-size: 14px; margin-bottom: 0; }
    </style>
</head>
<body>
    <main>
        <span class="icon" aria-hidden="true">!</span>
        <div class="status">Servicio temporalmente no disponible · 503</div>
        <h1>Base de datos no disponible</h1>
        <p>No podemos conectarnos al servidor de base de datos en este momento. El servicio puede estar detenido o la conexión no estar disponible.</p>
        <p>Espera unos momentos y vuelve a intentarlo.</p>
        <a href="index.php">Volver al inicio</a>
        <p class="help">Si el problema continúa, contacta al administrador para revisar el servicio MySQL y la configuración de conexión.</p>
    </main>
</body>
</html>
