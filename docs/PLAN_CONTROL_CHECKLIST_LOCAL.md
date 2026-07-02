# Plan Control - Checklist local

## Listo en local

- Inventario disponible solo para planes Control/Business mediante `canUseInventory()`.
- Plan Emprende conserva Productos rapidos y no ve Inventario.
- Productos con SKU, categoria, costo, precio, stock, minimo, estado y movimientos.
- Crear guia descuenta stock y bloquea productos pausados o sin unidades suficientes.
- Cancelar guia repone inventario y evita reposicion duplicada.
- Detalle de guia muestra productos descontados, venta, costo y utilidad.
- Mis guias marca las guias que movieron inventario.
- Dashboard muestra resumen Control, alertas y tareas sugeridas.
- Reportes de ventas, rotacion, categorias y Kardex exportan CSV.
- Importacion CSV valida encabezados y reporta errores claros.

## Validado

- Permiso Emprende sin inventario.
- Producto pausado bloqueado en Crear guia.
- Stock insuficiente bloqueado en Crear guia.
- Stock exacto descuenta correctamente.
- Cancelacion/reintento de reposicion no duplica unidades.
- CSV sin columna Producto muestra error claro.
- Pantallas principales renderizan: Dashboard, Inventario, Crear guia, Mis guias y Detalle.

## Pendiente antes de produccion

- Probar con una copia reciente de datos reales.
- Revisar migraciones en staging antes de ejecutarlas en produccion.
- Confirmar planes activos de clientes Control/Business.
- Hacer respaldo de base de datos antes del despliegue.
- Validar permisos con usuarios reales: tenant admin, afiliado, bodega y Emprende.
- Hacer prueba manual completa en staging: crear producto, crear guia, cancelar guia, exportar reportes.
