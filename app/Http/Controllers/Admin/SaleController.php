<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaleUpdateRequest;
use App\Models\{AuditLog, Sale};
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller {
    public function index(Request $r) {
        $sales = Sale::with('order', 'cashier')
            ->when($r->q, fn ($q, $v) => $q->where('official_receipt_number', 'like', "%$v%")->orWhereHas('order', fn ($o) => $o->where('customer_name', 'like', "%$v%")))
            ->latest()->paginate(15)->withQueryString();
        return view('admin.sales.index', compact('sales'));
    }
    public function show(Sale $sale) {
        $logs = AuditLog::where('auditable_type', Sale::class)->where('auditable_id', $sale->id)->latest()->get();
        return view('admin.sales.show', ['sale' => $sale->load('order', 'items.product', 'cashier'), 'logs' => $logs]);
    }
    public function edit(Sale $sale) { abort_if($sale->status === 'voided', 403); return view('admin.sales.edit', compact('sale')); }
    public function update(SaleUpdateRequest $r, Sale $sale) {
        abort_if($sale->status === 'voided', 403);
        $new = $r->safe()->only(['payment_mode', 'amount_paid', 'reference_number', 'check_maturity_date']);
        $old = collect($new)->map(fn ($v, $k) => $sale->$k instanceof \DateTimeInterface ? $sale->$k->format('Y-m-d') : (string) $sale->$k)->all();
        DB::transaction(function () use ($sale, $new, $old, $r) {
            $sale->update($new);
            AuditLog::record($sale, 'sale_corrected', $old, $new + ['reason' => $r->reason]);
        });
        return redirect()->route('admin.sales.show', $sale)->with('status', 'Sale corrected and logged.');
    }
    public function destroy(Request $r, Sale $sale, StockService $stock) { // void, never hard delete
        abort_if($sale->status === 'voided', 403);
        $r->validate(['reason' => 'required|string|max:255']);
        DB::transaction(function () use ($sale, $stock, $r) {
            foreach ($sale->items as $i) $stock->move($i->product_id, 'adjustment', $i->quantity, $sale->invoice_number, 'Void: '.$r->reason);
            $sale->update(['status' => 'voided']);
            AuditLog::record($sale, 'sale_voided', ['status' => $sale->getOriginal('status')], ['status' => 'voided', 'reason' => $r->reason]);
        });
        return redirect()->route('admin.sales.index')->with('status', 'Sale voided and stock restored.');
    }
}
