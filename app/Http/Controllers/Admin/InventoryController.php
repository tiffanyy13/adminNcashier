<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Services\StockService;
use Illuminate\Http\Request;

class InventoryController extends Controller {
    public function index(Request $r) {
        $inv = Inventory::with('product.category')
            ->when($r->q, fn ($q, $v) => $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%$v%")))
            ->paginate(15)->withQueryString();
        return view('admin.inventory.index', ['inventory' => $inv]);
    }
    public function adjust(Request $r, Inventory $inventory, StockService $stock) {
        $data = $r->validate(['new_quantity' => 'required|integer|min:0', 'reason' => 'required|string|max:200']);
        $delta = $data['new_quantity'] - $inventory->quantity;
        if ($delta === 0) return back()->withErrors(['new_quantity' => 'Quantity is unchanged.']);
        $stock->move($inventory->product_id, 'adjustment', $delta, null, $data['reason']);
        return back()->with('status', 'Stock adjusted and logged.');
    }
}
