<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Inventory, Order, PurchaseOrder, Sale};

class DashboardController extends Controller {
    public function index() {
        $sales = Sale::where('status', '!=', 'voided');
        $low = Inventory::with('product')->whereColumn('quantity', '<=', 'minimum_stock')->orderBy('quantity')->get();
        return view('admin.dashboard', [
            'orders' => Order::with('items')->where('status', 'pending_approval')->latest()->get(),
            'pos' => PurchaseOrder::with('supplier', 'items.product')->where('status', 'draft')->whereNull('authorized_by')->get(),
            'low' => $low, 'lowCount' => $low->count(),
            'today' => (clone $sales)->whereDate('created_at', today())->sum('total'),
            'month' => (clone $sales)->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->sum('total'),
        ]);
    }
}
