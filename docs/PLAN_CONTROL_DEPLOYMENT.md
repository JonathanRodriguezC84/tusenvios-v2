# Plan Control - despliegue staging / produccion

## 1. Auditoria de superficie

### Rutas nuevas o ampliadas

- `/inventory`
- `/inventory/export`
- `/inventory/template`
- `/inventory/import`
- `/inventory/bulk`
- `/inventory/movements`
- `/inventory/movements/export`
- `/inventory/reports/sales`
- `/inventory/reports/sales/export`
- `/inventory/reports/rotation`
- `/inventory/reports/rotation/export`
- `/inventory/reports/categories`
- `/inventory/reports/categories/export`
- `/inventory/{inventoryProduct}`
- `/inventory/{inventoryProduct}/movement`

### Migraciones relacionadas

- `2026_05_27_090000_create_inventory_products_table.php`
- `2026_05_27_091000_create_inventory_movements_table.php`
- `2026_05_27_092000_add_shipment_id_to_inventory_movements.php`
- `2026_05_27_093000_add_inventory_snapshot_to_shipments.php`
- `2026_05_27_094000_backfill_shipment_inventory_snapshots.php`

### Archivos principales

- `app/Models/User.php`
- `app/Models/Shipment.php`
- `app/Models/InventoryProduct.php`
- `app/Models/InventoryMovement.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/ShipmentController.php`
- `app/Http/Controllers/InventoryProductController.php`
- `app/Http/Controllers/InventoryReportController.php`
- `resources/views/dashboard.blade.php`
- `resources/views/layouts/navigation.blade.php`
- `resources/views/inventory/*`
- `resources/views/shipments/create.blade.php`
- `resources/views/shipments/index.blade.php`
- `resources/views/shipments/show.blade.php`
- `resources/views/shipments/edit.blade.php`

## 2. Orden de despliegue

1. Activar modo mantenimiento si el trafico lo amerita.
2. Tomar respaldo completo de base de datos.
3. Tomar respaldo de archivos actuales del servidor.
4. Subir archivos del proyecto.
5. Ejecutar migraciones.
6. Limpiar caches:
   - `php artisan optimize:clear`
   - `php artisan view:clear`
   - `php artisan route:clear`
   - `php artisan config:clear`
7. Validar rutas de Inventario y Crear guia.
8. Desactivar modo mantenimiento.

## 3. Pruebas obligatorias en staging

- Usuario Emprende no ve Inventario y conserva Productos rapidos.
- Usuario Control/Business ve Inventario, reportes y alertas.
- Crear producto de inventario con stock inicial.
- Crear guia con producto de inventario y validar descuento de stock.
- Intentar crear guia con cantidad mayor al stock y validar bloqueo.
- Pausar producto e intentar usarlo en guia.
- Cancelar guia creada con inventario y validar reposicion.
- Intentar cancelar/reponer dos veces y validar que no duplique stock.
- Exportar Inventario, Movimientos, Ventas, Rotacion y Categorias.
- Importar CSV valido.
- Importar CSV sin columna Producto y validar error claro.
- Revisar Dashboard Control: alertas, reposicion, venta potencial y utilidad.

## 4. Rollback

Si falla antes de migraciones:

- Restaurar archivos anteriores.
- Limpiar caches.

Si falla despues de migraciones, pero antes de uso real:

- Restaurar base de datos desde respaldo.
- Restaurar archivos anteriores.
- Limpiar caches.

Si falla despues de uso real:

- No ejecutar rollback destructivo sin revisar datos nuevos.
- Pausar acceso visual a Inventario quitando el item de menu o bloqueando `canUseInventory()`.
- Mantener tablas nuevas para preservar movimientos ya generados.
- Corregir en caliente o preparar parche.

## 5. Riesgos a controlar

- La columna `shipments.inventory_snapshot` modifica una tabla central.
- Las cancelaciones de guias con inventario reponen stock.
- Las acciones masivas pueden cambiar estado/categoria de muchos productos.
- El CSV puede actualizar productos existentes por SKU o nombre/categoria.
- Produccion debe tener planes `control` y `business` configurados correctamente.
