# Estado técnico

Fecha de revisión: 25 de agosto de 2026.

## Verificado en esta refactorización

- Rutas y páginas organizadas por módulos funcionales.
- Lógica transaccional de venta, compra y caja separada en servicios.
- Generación de factura PDF extraída del componente visual.
- Exportadores agrupados dentro del módulo Reports.
- Controladores vacíos, vistas duplicadas y dependencias frontend sin uso retirados.
- Vistas Blade compilables y assets Vite generados para producción.
- Auditoría npm sin vulnerabilidades conocidas.
- Suite automatizada con 29 pruebas y 115 aserciones al momento de esta revisión.

## Riesgo PHP pendiente

`composer audit` detecta avisos en el conjunto de dependencias bloqueado. Algunos afectan directamente a Laravel 11, Livewire/Volt, Dompdf y PHPSpreadsheet. Composer no permite instalar las correcciones parciales porque todas las versiones disponibles dentro de Laravel 11 siguen afectadas por al menos un aviso actual.

No se desactivó el bloqueo ni se agregaron excepciones para ocultar el resultado.

Antes de publicar la aplicación en Internet debe ejecutarse una migración de seguridad independiente:

1. Elegir Laravel 12 o 13 según la versión que ya tenga correcciones para los avisos vigentes.
2. Subir Livewire y Volt a versiones corregidas.
3. Evaluar Maatwebsite Excel 4 y una versión corregida de PHPSpreadsheet.
4. Actualizar Dompdf y Guzzle.
5. Ejecutar pruebas de ventas, compras, caja, exportaciones y carga de archivos.
6. Exigir que `composer audit` no reporte vulnerabilidades críticas o altas antes del despliegue público.

Mientras se realiza esa migración, el entorno debe mantenerse local o detrás de acceso controlado.

## Cobertura que todavía conviene añadir

Las pruebas actuales cubren autenticación, perfil y conexión arquitectónica. Las reglas transaccionales ya están implementadas, pero necesitan pruebas específicas para:

- venta exitosa, stock insuficiente y pago parcial;
- anulación y reversión de Kardex/caja;
- compra al contado y a crédito;
- apertura y cierre de caja;
- exportación PDF/Excel;
- permisos por cada rol.

Estas pruebas son la siguiente prioridad antes de una migración mayor de dependencias.
