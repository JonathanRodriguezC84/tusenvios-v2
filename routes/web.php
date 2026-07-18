<?php

use App\Http\Controllers\FrequentRecipientController;
use App\Http\Controllers\BrandSettingController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DailyTaskController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\InventoryProductController;
use App\Http\Controllers\InventoryReportController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuickProductController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShippingRateController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/deploy/{key}', DeployController::class);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/track', [TrackingController::class, 'index'])->name('tracking.index');
Route::post('/track', [TrackingController::class, 'search'])->name('tracking.search');
Route::get('/track/{guideNumber}', [TrackingController::class, 'show'])->name('tracking.show');

Route::get('/billing/blocked', fn () => view('billing.blocked'))
    ->middleware('auth')
    ->name('billing.blocked');

Route::get('/billing/checkout', [BillingController::class, 'checkout'])
    ->middleware('auth')
    ->name('billing.checkout');
Route::post('/billing/payment-link', [BillingController::class, 'createPaymentLink'])
    ->middleware('auth')
    ->name('billing.payment-link');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', 'active.user'])
    ->name('dashboard');

Route::middleware(['auth', 'active.user'])->group(function () {
    Route::get('/daily-tasks', DailyTaskController::class)->name('daily-tasks.index');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/clients', [AdminDashboardController::class, 'clients'])->name('clients');
        Route::get('/clients/{tenant}', [AdminDashboardController::class, 'showClient'])->name('clients.show');
        Route::post('/clients/{tenant}/token', [AdminDashboardController::class, 'generateToken'])->name('clients.token');
        Route::post('/clients/{tenant}/webhook', [AdminDashboardController::class, 'updateWebhook'])->name('clients.webhook');
        Route::post('/clients/{tenant}/wallet', [AdminDashboardController::class, 'walletTransaction'])->name('clients.wallet');
        Route::get('/clients/create/new', [AdminDashboardController::class, 'createClient'])->name('clients.create');
        Route::post('/clients', [AdminDashboardController::class, 'storeClient'])->name('clients.store');
        Route::patch('/clients/{tenant}/status', [AdminDashboardController::class, 'updateClientStatus'])->name('clients.status');
        Route::get('/subscriptions', [AdminDashboardController::class, 'subscriptions'])->name('subscriptions');
        Route::patch('/subscriptions/{subscription}', [AdminDashboardController::class, 'updateSubscription'])->name('subscriptions.update');
        Route::post('/subscriptions/{subscription}/manual-payment', [AdminDashboardController::class, 'registerManualPayment'])->name('subscriptions.manual-payment');
        Route::post('/subscriptions/{subscription}/payment-link', [AdminDashboardController::class, 'createPaymentLink'])->name('payment-links.create');
        Route::post('/payments/{payment}/sync', [AdminDashboardController::class, 'syncPayment'])->name('payments.sync');
        Route::get('/plans', [AdminDashboardController::class, 'plans'])->name('plans');
        Route::post('/plans', [AdminDashboardController::class, 'storePlan'])->name('plans.store');
        Route::patch('/plans/{plan}', [AdminDashboardController::class, 'updatePlan'])->name('plans.update');
        Route::delete('/plans/{plan}', [AdminDashboardController::class, 'destroyPlan'])->name('plans.destroy');
        Route::get('/activity', [AdminDashboardController::class, 'activity'])->name('activity');
        Route::get('/activity/export', [AdminDashboardController::class, 'exportActivity'])->name('activity.export');
        Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
        Route::patch('/users/{user}/role', [AdminDashboardController::class, 'updateUserRole'])->name('users.role');
        Route::patch('/users/{user}/status', [AdminDashboardController::class, 'updateUserStatus'])->name('users.status');
        Route::get('/users/impersonate/{user}', [AdminDashboardController::class, 'impersonate'])->name('users.impersonate');
        Route::post('/subscriptions/bulk', [AdminDashboardController::class, 'bulkAction'])->name('subscriptions.bulk');
        Route::get('/settings', [AdminDashboardController::class, 'systemSettings'])->name('settings');
        Route::patch('/settings', [AdminDashboardController::class, 'updateSystemSettings'])->name('settings.update');
        Route::get('/carriers', [AdminDashboardController::class, 'carriers'])->name('carriers');
        Route::get('/api-docs', [AdminDashboardController::class, 'apiDocs'])->name('api-docs');
        Route::get('/whatsapp', [AdminDashboardController::class, 'whatsapp'])->name('whatsapp');
        Route::get('/profile', [AdminDashboardController::class, 'profile'])->name('profile');
        Route::patch('/password', [AdminDashboardController::class, 'updatePassword'])->name('password');
    });

    Route::get('/configuration', [BrandSettingController::class, 'settings'])->name('store-settings.edit');
    Route::patch('/configuration', [BrandSettingController::class, 'updateSettings'])->name('store-settings.update');

    Route::get('/my-brand', [BrandSettingController::class, 'edit'])->name('brand-settings.edit');
    Route::get('/my-brand/label-preview', [BrandSettingController::class, 'preview'])->name('brand-settings.preview');
    Route::patch('/my-brand', [BrandSettingController::class, 'update'])->name('brand-settings.update');

    Route::get('/quick-products', [QuickProductController::class, 'index'])->name('quick-products.index');
    Route::post('/quick-products', [QuickProductController::class, 'store'])->name('quick-products.store');
    Route::patch('/quick-products/{quickProduct}', [QuickProductController::class, 'update'])->name('quick-products.update');
    Route::delete('/quick-products/{quickProduct}', [QuickProductController::class, 'destroy'])->name('quick-products.destroy');

    Route::get('/inventory/create', [InventoryProductController::class, 'create'])->name('inventory.create');
    Route::get('/inventory', [InventoryProductController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/movements', [InventoryProductController::class, 'movements'])->name('inventory.movements');
    Route::get('/inventory/export', [InventoryProductController::class, 'export'])->name('inventory.export');
    Route::get('/inventory/export/pdf', [InventoryProductController::class, 'exportPdf'])->name('inventory.export.pdf');
    Route::get('/inventory/template', [InventoryProductController::class, 'template'])->name('inventory.template');
    Route::get('/inventory/movements/export', [InventoryProductController::class, 'exportMovements'])->name('inventory.movements.export');
    Route::get('/inventory/movements/export/pdf', [InventoryProductController::class, 'exportMovementsPdf'])->name('inventory.movements.export.pdf');
    Route::get('/inventory/reports/sales', [InventoryReportController::class, 'sales'])->name('inventory.reports.sales');
    Route::get('/inventory/reports/sales/export', [InventoryReportController::class, 'exportSales'])->name('inventory.reports.sales.export');
    Route::get('/inventory/reports/sales/export/pdf', [InventoryReportController::class, 'exportSalesPdf'])->name('inventory.reports.sales.export.pdf');
    Route::get('/inventory/reports/rotation', [InventoryReportController::class, 'rotation'])->name('inventory.reports.rotation');
    Route::get('/inventory/reports/rotation/export', [InventoryReportController::class, 'exportRotation'])->name('inventory.reports.rotation.export');
    Route::get('/inventory/reports/rotation/export/pdf', [InventoryReportController::class, 'exportRotationPdf'])->name('inventory.reports.rotation.export.pdf');
    Route::get('/inventory/reports/categories', [InventoryReportController::class, 'categories'])->name('inventory.reports.categories');
    Route::get('/inventory/reports/categories/export', [InventoryReportController::class, 'exportCategories'])->name('inventory.reports.categories.export');
    Route::get('/inventory/reports/categories/export/pdf', [InventoryReportController::class, 'exportCategoriesPdf'])->name('inventory.reports.categories.export.pdf');
    Route::post('/inventory', [InventoryProductController::class, 'store'])->name('inventory.store');
    Route::post('/inventory/import', [InventoryProductController::class, 'import'])->name('inventory.import');
    Route::post('/inventory/bulk', [InventoryProductController::class, 'bulk'])->name('inventory.bulk');
    Route::patch('/inventory/{inventoryProduct}', [InventoryProductController::class, 'update'])->name('inventory.update');
    Route::post('/inventory/{inventoryProduct}/movement', [InventoryProductController::class, 'movement'])->name('inventory.movement');
    Route::delete('/inventory/{inventoryProduct}', [InventoryProductController::class, 'destroy'])->name('inventory.destroy');

    Route::get('/shipments', [ShipmentController::class, 'index'])->name('shipments.index');
    Route::get('/shipments/export', [ShipmentController::class, 'export'])->name('shipments.export');
    Route::get('/shipments/export/pdf', [ShipmentController::class, 'exportPdf'])->name('shipments.export.pdf');
    Route::get('/shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
    Route::post('/shipments', [ShipmentController::class, 'store'])->name('shipments.store');
    Route::get('/shipments/bulk-print', fn () => redirect()
        ->route('shipments.index', ['status' => 'created'])
        ->with('status', 'Selecciona las guias que quieres imprimir.'))->name('shipments.bulk-print.fallback');
    Route::match(['post', 'patch'], '/shipments/bulk-print', [ShipmentController::class, 'bulkPrint'])->name('shipments.bulk-print');
    Route::patch('/shipments/bulk-status', [ShipmentController::class, 'bulkUpdateStatus'])->name('shipments.bulk-status');
    Route::get('/shipments/{shipment}/print', [ShipmentController::class, 'print'])->name('shipments.print');
    Route::get('/shipments/{shipment}/print/pdf', [ShipmentController::class, 'printPdf'])->name('shipments.print.pdf');
    Route::patch('/shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])->name('shipments.update-status');
    Route::patch('/shipments/{shipment}/cancel', [ShipmentController::class, 'cancel'])->name('shipments.cancel');
    Route::get('/shipments/{shipment}/edit', [ShipmentController::class, 'edit'])->name('shipments.edit');
    Route::patch('/shipments/{shipment}', [ShipmentController::class, 'update'])->name('shipments.update');
    Route::get('/shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::any('/scan/{any?}', fn () => view('scan.index'))->where('any', '.*')->name('scan.index');
    Route::any('/my-route/{any?}', fn () => redirect()->route('dashboard'))->where('any', '.*')->name('courier-route.index');
    Route::any('/reports/{any?}', fn () => redirect()->route('dashboard'))->where('any', '.*')->name('reports.collections');
    Route::any('/affiliated-companies/{any?}', fn () => redirect()->route('dashboard'))->where('any', '.*')->name('affiliated-companies.index');
    Route::any('/sender-profiles/{any?}', fn () => redirect()->route('dashboard'))->where('any', '.*')->name('sender-profiles.index');
    Route::any('/delivery-zones/{any?}', fn () => redirect()->route('dashboard'))->where('any', '.*')->name('delivery-zones.index');
    Route::any('/users/{any?}', fn () => redirect()->route('dashboard'))->where('any', '.*')->name('users.index');
    Route::any('/tenants/{any?}', fn () => redirect()->route('dashboard'))->where('any', '.*')->name('tenants.index');
    Route::any('/subscriptions/{any?}', fn () => redirect()->route('dashboard'))->where('any', '.*')->name('subscriptions.index');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/export', [AuditLogController::class, 'export'])->name('audit-logs.export');
    Route::any('/backups/{any?}', fn () => redirect()->route('dashboard'))->where('any', '.*')->name('backups.index');
    Route::any('/settings/{any?}', fn () => view('settings.edit'))->where('any', '.*')->name('settings.edit');

    Route::get('/recipients', [FrequentRecipientController::class, 'index'])->name('recipients.index');
    Route::get('/recipients/search', [FrequentRecipientController::class, 'search'])->name('recipients.search');
    Route::post('/recipients', [FrequentRecipientController::class, 'store'])->name('recipients.store');
    Route::delete('/recipients/{frequentRecipient}', [FrequentRecipientController::class, 'destroy'])->name('recipients.destroy');

    Route::get('/shipping-rates', [ShippingRateController::class, 'index'])->name('shipping-rates.index');
    Route::post('/shipping-rates/calculate', [ShippingRateController::class, 'calculate'])->name('shipping-rates.calculate');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::patch('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/api/departments', [LocationController::class, 'departments'])->name('api.departments');
    Route::get('/api/cities', [LocationController::class, 'cities'])->name('api.cities');
});

require __DIR__.'/auth.php';
