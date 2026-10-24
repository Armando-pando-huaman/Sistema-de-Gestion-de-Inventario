# Registro y datos de la botica

Esta instalacion gestiona una sola botica y conserva sus usuarios, productos y ventas actuales.

## Instalacion y actualizaciones

1. Ejecutar `C:/xampp/php/php.exe config/migrar-identidad.php`. La migracion agrega la tabla `botica` y la columna nullable `usuarios.foto`; puede repetirse.
2. Habilitar `extension=gd` en el `php.ini` del servidor y reiniciar Apache para permitir subidas optimizadas. Se requieren GD con soporte WebP y fileinfo/EXIF para las imagenes.
3. Dar permiso de escritura al proceso PHP sobre `public/uploads/identidad`. Incluir esa carpeta y la base de datos en los respaldos.

Si ya existen usuarios, un administrador abre **Mi botica**, completa el nombre y decide si habilita el registro. No se habilita el registro publico mientras la botica este sin configurar.

Si no existen usuarios ni botica, **Crear mi cuenta** permite indicar los datos personales y crear la unica botica. Ese fundador obtiene el rol `admin`. Las altas posteriores, incluidas las de Personal, siempre crean `vendedor`. Los administradores existentes conservan su rol; la edicion de roles mantiene sus reglas anteriores.

El interruptor **Habilitar registro de vendedores** controla tanto el registro publico como el alta desde Personal. El servidor vuelve a comprobarlo dentro de la transaccion, independientemente del formulario. No afecta al inicio de sesion de usuarios existentes. El registro abierto permite que cualquier persona con acceso al sitio cree una cuenta de vendedor.

## Imagenes

Se aceptan JPG, PNG y WebP de hasta 5 MB, 16 megapixeles y 8000 px por lado. PHP valida y recodifica la imagen; el original no se conserva. Fotos: hasta 256 px. Logos: hasta 512 px con transparencia. Se usa WebP con calidad 78 y un limite final de 180 KB, reduciendo calidad si es necesario. Las imagenes reciben nombres aleatorios; al reemplazarlas se elimina la anterior. No se aceptan SVG ni archivos ejecutables.

