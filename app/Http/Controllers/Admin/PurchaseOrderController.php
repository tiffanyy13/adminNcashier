<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOrderRequest;
use App\Models\{Product, PurchaseOrder, Supplier};
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller {
    public function index() { return view('admin.purchase-orders.index', ['pos' => PurchaseOrder::with('supplier')->latest()->paginate(15)]); }
    private function formData(PurchaseOrder $po) {
        return ['po' => $po, 'suppliers' => Supplier::orderBy('name')->get(),
                'products' => Product::active()->orderBy('name')->get()->map(fn ($p) => ['id' => $p->id, 'name' => $p->full_name, 'price' => (float) $p->unit_price])];
    }
    public function create() { return view('admin.purchase-orders.form', $this->formData(new PurchaseOrder(['order_date' => today()]))); }
    public function store(PurchaseOrderRequest $r) {
        $po = DB::transaction(function () use ($r) {
            $po = PurchaseOrder::create(['po_number' => sprintf('PO-%d-%04d', now()->year, PurchaseOrder::max('id') + 1),
                'supplier_id' => $r->supplier_id, 'order_date' => $r->order_date, 'requested_by' => auth()->id(), 'status' => 'draft']);
            $po->items()->createMany($r->items); return $po;
        });
        return redirect()->route('admin.purchase-orders.show', $po)->with('status', 'Draft PO created. Authorize it to send.');
    }
    public function show(PurchaseOrder $purchase_order) { return view('admin.purchase-orders.show', ['po' => $purchase_order->load('supplier', 'items.product', 'requester', 'authorizer')]); }
    public function edit(PurchaseOrder $purchase_order) {
        abort_unless($purchase_order->status === 'draft', 403, 'Only drafts can be edited.');
        return view('admin.purchase-orders.form', $this->formData($purchase_order->load('items')));
    }
    public function update(PurchaseOrderRequest $r, PurchaseOrder $purchase_order) {
        abort_unless($purchase_order->status === 'draft', 403);
        DB::transaction(function () use ($r, $purchase_order) {
            $purchase_order->update($r->safe()->only(['supplier_id', 'order_date']));
            $purchase_order->items()->delete(); $purchase_order->items()->createMany($r->items);
        });
        return redirect()->route('admin.purchase-orders.show', $purchase_order)->with('status', 'PO updated.');
    }
    public function destroy(PurchaseOrder $purchase_order) {
        abort_unless($purchase_order->status === 'draft', 403); $purchase_order->delete();
        return redirect()->route('admin.purchase-orders.index')->with('status', 'Draft deleted.');
    }
    public function authorizePo(PurchaseOrder $purchase_order) { // distinct approval checkpoint
        abort_unless($purchase_order->status === 'draft' && !$purchase_order->authorized_by, 422);
        $purchase_order->update(['authorized_by' => auth()->id(), 'authorized_at' => now()]);
        return back()->with('status', 'Purchase order authorized.');
    }
    public function advance(PurchaseOrder $purchase_order) {
        if ($purchase_order->status === 'draft') {
            abort_unless($purchase_order->authorized_by, 422, 'Authorize the PO before sending.'); $purchase_order->update(['status' => 'sent']);
        } elseif ($purchase_order->status === 'received') $purchase_order->update(['status' => 'closed']);
        else abort(422);
        return back()->with('status', 'PO status updated.');
    }
    public function receive(Request $r, PurchaseOrder $purchase_order, StockService $stock) {
        abort_unless($purchase_order->status === 'sent', 422, 'Only sent POs can be received.');
        $r->validate(['received' => 'required|array', 'received.*' => 'required|integer|min:0']);
        DB::transaction(function () use ($r, $purchase_order, $stock) {
            foreach ($purchase_order->items as $item) {
                $qty = (int) ($r->received[$item->id] ?? 0);
                $item->update(['received_quantity' => $qty]);
                if ($qty > 0) {
                    $tx = $stock->move($item->product_id, 'stock_in', $qty, $purchase_order->po_number, 'PO received');
                    if ($qty < $item->quantity) $tx->update(['flag' => 'discrepancy', 'reason' => "Short delivery: {$qty} of {$item->quantity}"]);
                }
            }
            $purchase_order->update(['status' => 'received']);
        });
        return back()->with('status', 'Delivery recorded; stock updated. Short deliveries were flagged in the stock ledger.');
    }
}
