<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StockTransactionController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Cashier\CheckController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));
require __DIR__.'/auth.php';

// Role-aware landing: sends each user to their own dashboard
Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    $u = auth()->user();

    return redirect()->route($u->hasRole('admin') ? 'admin.dashboard' : ($u->hasRole('cashier') ? 'cashier.dashboard' : 'profile.edit'));
})->name('dashboard');

// ---------------- ADMIN (route-level 403 for everyone else) ----------------
Route::middleware(['auth', EnsureUserIsActive::class, 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', ProductController::class)->except(['destroy', 'show']);
    Route::patch('products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory/{inventory}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
    Route::resource('orders', OrderController::class);
    Route::post('orders/{order}/approve', [OrderController::class, 'approve'])->name('orders.approve');
    Route::post('orders/{order}/reject', [OrderController::class, 'reject'])->name('orders.reject');
    Route::resource('sales', SaleController::class)->only(['index', 'show', 'edit', 'update', 'destroy']);
    Route::resource('suppliers', SupplierController::class)->except('show');
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::post('purchase-orders/{purchase_order}/authorize', [PurchaseOrderController::class, 'authorizePo'])->name('purchase-orders.authorize');
    Route::post('purchase-orders/{purchase_order}/advance', [PurchaseOrderController::class, 'advance'])->name('purchase-orders.advance');
    Route::post('purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    Route::get('stock-transactions', [StockTransactionController::class, 'index'])->name('stock.index');
    Route::post('stock-transactions/{stockTransaction}/resolve', [StockTransactionController::class, 'resolve'])->name('stock.resolve');
    Route::get('reports/{type?}', [ReportController::class, 'show'])->name('reports');
    Route::resource('staff', StaffController::class)->except('show');
});

// ---------------- CASHIER ----------------
Route::middleware(['auth', EnsureUserIsActive::class, 'role:cashier'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('dashboard', [App\Http\Controllers\Cashier\DashboardController::class, 'index'])->name('dashboard');
    Route::get('orders', [App\Http\Controllers\Cashier\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [App\Http\Controllers\Cashier\OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/pay', [App\Http\Controllers\Cashier\SaleController::class, 'create'])->name('sales.create');
    Route::post('orders/{order}/pay', [App\Http\Controllers\Cashier\SaleController::class, 'store'])->name('sales.store');
    Route::get('sales', [App\Http\Controllers\Cashier\SaleController::class, 'index'])->name('sales.index');
    Route::get('sales/{sale}', [App\Http\Controllers\Cashier\SaleController::class, 'show'])->name('sales.show');
    Route::get('sales/{sale}/pdf', [App\Http\Controllers\Cashier\SaleController::class, 'pdf'])->name('sales.pdf');
    Route::get('checks', [CheckController::class, 'index'])->name('checks.index');
    Route::post('checks/{sale}/clear', [CheckController::class, 'clear'])->name('checks.clear');
});

// Profile (Breeze) - all roles
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
