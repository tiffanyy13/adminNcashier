<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\{ApproveOrderRequest, OrderRequest};
use App\Models\{Order, Product};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller {
    public function index(Request $r) {
        $orders = Order::with('items')
            ->when($r->status, fn ($q, $v) => $q->where('status', $v))
            ->when($r->q, fn ($q, $v) => $q->where('customer_name', 'like', "%$v%"))
            ->latest()->paginate(15)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }
    private function products() {
        return Product::active()->with('inventory')->orderBy('name')->get()->map(fn ($p) => [
            'id' => $p->id, 'name' => $p->full_name, 'price' => (float) $p->unit_price, 'stock' => $p->inventory->quantity ?? 0]);
    }
    public function create() { return view('admin.orders.form', ['order' => new Order, 'products' => $this->products()]); }
    public function store(OrderRequest $r) {
        $order = DB::transaction(function () use ($r) {
            $o = Order::create($r->safe()->only(['customer_name','customer_contact','customer_company','delivery_address']) + [
                'order_number' => sprintf('ORD-%d-%04d', now()->year, Order::max('id') + 1),
                'status' => $r->action, 'created_by' => auth()->id()]);
            $o->items()->createMany($r->items);
            return $o;
        });
        return redirect()->route('admin.orders.show', $order)->with('status', 'Order saved.');
    }
    public function show(Order $order) { return view('admin.orders.show', ['order' => $order->load('items.product', 'approver', 'sale')]); }
    public function edit(Order $order) {
        abort_if(in_array($order->status, ['paid', 'released']), 403, 'Paid/released orders can no longer be edited.');
        return view('admin.orders.form', ['order' => $order->load('items'), 'products' => $this->products()]);
    }
    public function update(OrderRequest $r, Order $order) {
        abort_if(in_array($order->status, ['paid', 'released']), 403);
        DB::transaction(function () use ($r, $order) {
            $order->update($r->safe()->only(['customer_name','customer_contact','customer_company','delivery_address']));
            $order->items()->delete(); $order->items()->createMany($r->items);
        });
        return redirect()->route('admin.orders.show', $order)->with('status', 'Order updated.');
    }
    public function destroy(Order $order) {
        abort_unless(in_array($order->status, ['quotation', 'pending_approval']), 422, 'Only unapproved orders can be deleted.');
        $order->delete();
        return redirect()->route('admin.orders.index')->with('status', 'Order deleted.');
    }
    public function approve(ApproveOrderRequest $r, Order $order) {
        abort_unless(in_array($order->status, ['quotation', 'pending_approval']), 422, 'Order is not awaiting approval.');
        $order->update([
            'discount_type' => $r->discount_type ?? $order->discount_type,
            'discount_value' => $r->discount_value ?? $order->discount_value,
            'status' => 'confirmed', 'approved_by' => auth()->id(), 'approved_at' => now()]);
        return redirect()->route('admin.orders.show', $order)->with('status', 'Order confirmed. Cashier can now process payment.');
    }
    public function reject(Order $order) {
        abort_unless($order->status === 'pending_approval', 422);
        $order->update(['status' => 'quotation']);
        return back()->with('status', 'Order returned to quotation.');
    }
}
