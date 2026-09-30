<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Product, StockTransaction};
use App\Services\StockService;
use Illuminate\Http\Request;

class StockTransactionController extends Controller {
    public function index(Request $r) {
        $tx = StockTransaction::with('product', 'user')
            ->when($r->type, fn ($q, $v) => $q->where('type', $v))
            ->when($r->product, fn ($q, $v) => $q->where('product_id', $v))
            ->when($r->from, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($r->to, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()->paginate(20)->withQueryString();
        return view('admin.stock.index', ['tx' => $tx, 'products' => Product::orderBy('name')->get()]);
    }
    public function resolve(Request $r, StockTransaction $stockTransaction, StockService $stock) {
        $r->validate(['decision' => 'required|in:approve,reject']);
        abort_unless($stockTransaction->flag === 'discrepancy', 422);
        if ($r->decision === 'approve') $stockTransaction->update(['flag' => 'resolved']);
        else { // reject and return to supplier: take the flagged quantity back out of stock
            $stock->move($stockTransaction->product_id, 'adjustment', -$stockTransaction->quantity, "TX#{$stockTransaction->id}", 'Rejected delivery returned');
            $stockTransaction->update(['flag' => 'rejected']);
        }
        return back()->with('status', 'Discrepancy '.($r->decision === 'approve' ? 'approved' : 'rejected and returned').'.');
    }
}
