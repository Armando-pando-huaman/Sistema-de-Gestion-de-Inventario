<?php
// Contenido del manual mostrado dentro del sistema. Cada enlace respeta el rol.
return [
 ['id'=>'inicio','titulo'=>'1. Primeros pasos y rutina diaria','rol'=>'Todos','texto'=>[
  'El sistema organiza el inventario de la botica, sus ventas, lotes, movimientos y caja. Los importes se expresan en soles (S/) y las fechas usan la hora de Lima.',
  'Cada trabajador utiliza su propia cuenta y su propia caja. Las opciones disponibles dependen de su rol: administrador o vendedor.'
 ],'pasos'=>['Inicia sesión con tu correo y contraseña.','Revisa el Dashboard para conocer las ventas, las existencias bajas y los próximos vencimientos.','Abre Mi caja e ingresa el efectivo inicial antes de vender.','Registra ventas durante el turno. El administrador registra y revisa entradas, salidas y correcciones de inventario.','Al finalizar, cuenta el efectivo físico y cierra tu caja. Revisa la diferencia calculada y cierra sesión.'], 'enlace'=>['Dashboard','index','Ir al Dashboard']],
 ['id'=>'acceso','titulo'=>'2. Acceso, registro y límites de intentos','rol'=>'Todos','texto'=>[
  'Para entrar se necesita una cuenta activa. Después de 5 intentos fallidos para un mismo correo dentro de los últimos 15 minutos, se bloquean temporalmente nuevos intentos. La pantalla indica la espera restante; cerrar el navegador o cambiar de dispositivo no borra los fallos.',
  'Los fallos caducan al cumplir 15 minutos. Intentar durante el bloqueo no alarga la espera. Un inicio de sesión correcto antes de llegar al límite limpia los fallos anteriores. El conteo considera el correo sin distinguir mayúsculas y minúsculas.',
  'El registro inicial permite crear la botica y su primera cuenta administradora cuando todavía no existen usuarios. Después, los nuevos registros se incorporan como vendedores y dependen de que el administrador los habilite desde Mi botica.',
  'No existe recuperación automática de contraseña. Si un trabajador pierde el acceso, debe contactar al administrador para que actualice su contraseña desde Personal. Usa Cerrar sesión al terminar de trabajar.'
 ]],
 ['id'=>'dashboard','titulo'=>'3. Dashboard y avisos','rol'=>'Todos','texto'=>[
  'El Dashboard presenta indicadores de ventas y productos, avisos de stock bajo y lotes próximos a vencer, además de accesos a las operaciones habituales. Los avisos ayudan a revisar el inventario; no reemplazan el conteo físico.',
  'Las secciones administrativas solo están disponibles para administradores. Usa los accesos y las opciones del menú para abrir cada módulo.'
 ],'enlace'=>['Dashboard','index','Abrir Dashboard']],
 ['id'=>'productos','titulo'=>'4. Productos, precios y existencias iniciales','rol'=>'Consulta: todos. Altas, cambios y bajas: administrador','texto'=>[
  'En Productos puedes buscar y consultar las fichas del catálogo. El administrador crea productos, modifica su información, define precios y stock mínimo, y los desactiva conservando el historial.',
  'El código de barras identifica el producto. El lector permite escanear códigos QR o de barras con la cámara cuando el navegador lo admite y tiene permiso. También puedes escribir el código manualmente. Revisa los datos completados antes de guardar; un código ya existente no crea otro producto.',
  'Puedes crear solamente la ficha con stock cero o ingresar existencias iniciales. El stock inicial es la cantidad que ya tienes; el stock mínimo es el umbral de aviso y no añade unidades.',
  'Si registras existencias iniciales, indica su lote y vencimiento reales, y el costo por unidad cuando lo conozcas. El proveedor es opcional; puedes elegir uno existente o crear uno desde el formulario. Si asocias un proveedor, el ingreso queda vinculado a su documento o referencia interna.',
  'En productos fraccionables, configura las unidades por blíster y por caja y los precios de cada presentación. El inventario se controla en unidades mínimas: por ejemplo, una caja de 10 unidades equivale a 10 unidades de stock. Revisa la conversión que muestra el formulario.',
  'Para cambiar las cantidades después del alta utiliza Movimientos. Para corregir vencimientos, abre Editar: cuando hay varios lotes, cada uno conserva su propia fecha. Ver lotes muestra los lotes del producto seleccionado.'
 ],'enlace'=>['Producto','index','Abrir Productos']],
 ['id'=>'categorias','titulo'=>'5. Categorías','rol'=>'Consulta: todos. Administración: administrador','texto'=>[
  'Las categorías agrupan productos y permiten interpretar los reportes por grupo. El administrador puede crearlas y cambiar su nombre o eliminarlas mediante las opciones de Categorías. Revisa la categoría asignada en cada ficha de producto.'
 ],'enlace'=>['Categoria','index','Abrir Categorías']],
 ['id'=>'movimientos','titulo'=>'6. Entradas, salidas y seguimiento del inventario','rol'=>'Consulta: todos. Registro: administrador','texto'=>[
  'Movimientos es la pantalla para registrar entradas y salidas de existencias después de crear el producto. Selecciona el producto, el tipo, la cantidad y el motivo. Las entradas solicitan la información del lote y su vencimiento.',
  'Una entrada aumenta el stock y una salida lo reduce. Las ventas también generan movimientos automáticamente: no registres otra salida por la misma venta.',
  'El historial puede filtrarse por clase, producto y fechas. Desde Productos, Registrar movimiento abre este módulo con el producto seleccionado. El historial de entradas con documentos se consulta en Entradas de productos.'
 ],'enlace'=>['Movimiento','index','Abrir Movimientos']],
 ['id'=>'lotes','titulo'=>'7. Lotes, vencimientos y documentos de entrada','rol'=>'Administrador','texto'=>[
  'Un producto puede tener varios lotes con cantidades, costos y vencimientos diferentes. En Lotes puedes buscar por producto o código y revisar sus existencias.',
  'Las ventas consumen primero el lote vigente que vence antes. Los lotes vencidos no se utilizan para vender; por eso el stock físico puede ser mayor que la cantidad disponible para venta.',
  'Cuando un lote indica datos pendientes, utiliza Verificar lote e introduce el código, vencimiento y costo documentados. No asignes costos supuestos a existencias antiguas.',
  'Entradas de productos muestra el historial de documentos vinculados al proveedor. Los nuevos artículos se crean desde Productos y las entradas y salidas posteriores se registran desde Movimientos.',
  'En Reportes de Productos e Inventario se muestran todos los lotes con stock: vencidos, próximos a vencer, vigentes y sin fecha. Puedes ordenarlos por vencimiento o por ingreso reciente; la compra aparece si existe un documento asociado.'
 ],'enlace'=>['Gestion','lotes','Abrir Lotes']],
 ['id'=>'caja','titulo'=>'8. Mi caja, ingresos, retiros y gastos','rol'=>'Cada usuario opera su caja; gastos del negocio: administrador','texto'=>[
  'Debes abrir tu caja antes de vender, indicando el efectivo inicial. Un usuario no puede tener dos cajas abiertas al mismo tiempo.',
  'La caja registra las ventas y reembolsos según su medio de pago. El efectivo esperado considera el efectivo; los medios digitales se presentan por separado.',
  'En Ingreso o egreso de dinero puedes registrar dinero adicional o un retiro, con su importe, motivo y medio de pago. Un egreso o retiro no es automáticamente un gasto.',
  'El administrador puede elegir Gasto del negocio para registrar alquiler, servicios, sueldos u otros gastos. El historial se consulta en Gastos del negocio. Las compras de mercadería forman parte del inventario y no deben volver a anotarse como gasto por el mismo concepto.',
  'Para cerrar, ingresa el efectivo realmente contado. El sistema calcula la diferencia respecto del esperado y conserva el historial. Los vendedores consultan su propio historial de cajas; el administrador tiene una vista más amplia.'
 ],'enlace'=>['Gestion','caja','Abrir Mi caja']],
 ['id'=>'ventas','titulo'=>'9. Registrar ventas y entregar el ticket','rol'=>'Todos','texto'=>[
  'En Ventas agrega productos al carrito, elige su presentación y cantidad, y revisa el total. En los fraccionables se descuentan las unidades mínimas correspondientes a cada presentación.',
  'Completa los datos del cliente que correspondan, el teléfono si compartirás el ticket, el medio de pago y el tipo de comprobante. Confirma solo después de revisar los productos y cantidades.',
  'Al guardar, el sistema comprueba las existencias disponibles, registra la venta y sus salidas de inventario y la vincula a la caja abierta. Si aparece un error, revisa el historial antes de volver a intentarlo.',
  'El detalle muestra los productos, cantidades, precios, estado y medio de pago. Permite imprimir el ticket y compartirlo descargando una imagen PNG y abriendo WhatsApp del cliente según las opciones de la pantalla.',
  'La opción de boleta electrónica permanece pendiente de integración con SUNAT. El documento generado actualmente es una constancia interna; seleccionar esa opción no envía ni valida un comprobante electrónico.'
 ],'enlace'=>['Venta','index','Abrir Ventas']],
 ['id'=>'devoluciones','titulo'=>'10. Historial, devoluciones y anulaciones','rol'=>'Historial: todos. Reembolsos y anulaciones: administrador','texto'=>[
  'El Historial permite buscar ventas y abrir su detalle sin eliminar los registros originales.',
  'Para una devolución parcial, el administrador abre el detalle de la venta, indica las unidades mínimas devueltas por producto, el motivo y el medio de reembolso. Necesita una caja abierta para registrar el reembolso.',
  'La opción Reintegrar productos utilizables al stock devuelve las existencias. Desmárcala cuando estén dañados o no deban revenderse. No se pueden devolver más unidades que las pendientes.',
  'Anular y reembolsar lo pendiente devuelve el saldo de la venta. Las devoluciones anteriores se respetan para evitar reembolsos duplicados. La venta original y su estado se conservan para consulta y auditoría.'
 ],'enlace'=>['Venta','historial','Abrir Historial']],
 ['id'=>'reportes','titulo'=>'11. Reportes y descarga para análisis','rol'=>'Administrador','texto'=>[
  'Reportes del negocio permite analizar períodos de 7, 15, 30, 60, 90 o 365 días. El resumen compara las ventas diarias con el período anterior e incluye movimientos, ingresos por categoría y productos destacados.',
  'Ventas presenta la evolución, distribución de unidades vendidas por producto, horas con más ventas, meses, categorías y vendedores. La tabla mensual abarca los últimos 12 meses, independientemente del período elegido.',
  'Productos muestra los 20 más vendidos en el período, productos de baja rotación histórica y los lotes con existencias. Inventario muestra las cantidades y valores actuales, stock bajo, agotados y lotes.',
  'Los reportes distinguen ventas brutas, reembolsos, ventas netas, costo y gastos. Si faltan costos históricos, el resultado financiero queda pendiente; un valor vacío no equivale a una ganancia de cero. Algunas comparativas reflejan ventas brutas y las distribuciones de productos descuentan devoluciones, tal como indican sus columnas.',
  'Para descargar: aplica primero el período y el orden deseados, elige el conjunto de datos en Datos para analizar fuera del sistema y pulsa Descargar CSV. Cada archivo contiene una tabla para facilitar filtros, sumas y gráficos en otra aplicación.',
  'Los CSV usan UTF-8, separador punto y coma y punto decimal. Puedes abrirlos en Excel o LibreOffice; si se muestran en una sola columna, impórtalos como CSV y selecciona punto y coma. Los importes se exportan sin el símbolo S/ y sus encabezados indican PEN.',
  'La descarga respeta el período aplicado y el orden de los lotes. Los datos de inventario son actuales y la baja rotación es histórica; no se recortan al período de ventas. Un reporte sin registros se descarga con sus encabezados. Las pantallas que ofrecen Imprimir permiten también guardar como PDF desde el navegador.'
 ],'enlace'=>['Reporte','index','Abrir Reportes'],'admin'=>true],
 ['id'=>'personal','titulo'=>'12. Personal y permisos','rol'=>'Administrador','texto'=>[
  'Personal permite crear vendedores, editar los datos y contraseñas de los trabajadores, gestionar su rol y desactivar cuentas. La creación de vendedores requiere que el registro esté habilitado en Mi botica.',
  'El administrador administra productos, categorías, movimientos, lotes, gastos, reportes, personal y configuración del negocio, además de vender y operar su caja. El vendedor consulta productos, categorías y movimientos, vende, consulta el historial y opera su propia caja.',
  'Solo el administrador puede realizar devoluciones y anulaciones. La aplicación comprueba los permisos también al abrir las rutas directamente. No permite dejar el sistema sin al menos un administrador activo.',
  'Desactivar a un trabajador conserva su historial y revoca su acceso. Evita compartir cuentas: cada operación debe poder identificarse con su responsable.'
 ],'enlace'=>['Usuario','index','Abrir Personal'],'admin'=>true],
 ['id'=>'auditoria','titulo'=>'13. Auditoría e historial de cambios','rol'=>'Administrador','texto'=>[
  'Auditoría registra el responsable, la fecha, la acción y el registro afectado en las operaciones que generan trazabilidad. Ver cambio permite consultar el detalle disponible.',
  'Utiliza este módulo para revisar correcciones, movimientos y cambios administrativos junto con los historiales de ventas y caja. Los registros históricos se conservan al desactivar productos o trabajadores.'
 ],'enlace'=>['Gestion','auditoria','Abrir Auditoría'],'admin'=>true],
 ['id'=>'perfil','titulo'=>'14. Mi perfil, Mi botica y apariencia','rol'=>'Perfil y apariencia: todos. Mi botica: administrador','texto'=>[
  'Desde el menú del avatar puedes abrir Mi perfil y actualizar tu nombre y fotografía. Mi botica permite al administrador cambiar el nombre comercial y el logo y habilitar o cerrar el registro de nuevos usuarios.',
  'El nombre y logo del negocio se utilizan en la interfaz y los tickets. Revisa la configuración antes de entregar comprobantes internos.',
  'Los controles superiores permiten elegir la paleta, la posición vertical u horizontal del menú y el modo Día o Noche. Estas preferencias se guardan en el navegador. En móviles el menú funciona como desplegable.',
  'Los iconos animados se muestran cuando terminan de cargar. Si una animación falla se utiliza su icono de respaldo; la preferencia del dispositivo para reducir movimiento también se respeta.'
 ],'enlace'=>['Cuenta','perfil','Abrir Mi perfil']],
 ['id'=>'problemas','titulo'=>'15. Preguntas frecuentes','rol'=>'Todos','texto'=>[
  '¿Por qué no puedo vender? Comprueba que tienes una caja abierta, que el producto está activo y que existen unidades suficientes en lotes vigentes. Los lotes vencidos no están disponibles para venta.',
  '¿Por qué tengo stock pero aparece un aviso? El stock mínimo marca el umbral de reposición. También puede existir stock físico vencido que ya no sea vendible.',
  '¿Por qué no aparece una ganancia? Puede faltar el costo documentado de lotes o detalles históricos. El administrador debe revisar los datos pendientes; el sistema no inventa esos costos.',
  '¿Por qué no puedo crear un trabajador? El registro puede estar deshabilitado en Mi botica o la cuenta actual puede no ser administradora.',
  '¿Por qué se venció un formulario? Recarga la página y vuelve a intentarlo. Si estabas guardando una venta, comprueba primero si aparece en el historial.',
  '¿Qué hago si se pierde la conexión con la base de datos? Reintenta cuando el servicio esté disponible y comunica el problema al administrador. Antes de repetir una operación revisa su historial.',
  '¿La documentación cambia los permisos? No. El manual explica ambos roles; los enlaces y las operaciones administrativas siguen sujetos a las comprobaciones de acceso.'
 ]]
];
