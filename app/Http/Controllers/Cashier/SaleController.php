<?php
namespace App\Http\Controllers\Cashier;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\{Order, Sale};
use App\Services\{NumberGenerator, StockService};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller {
    public function index(Request $r) {
        $q = Sale::with('order')->where('processed_by', auth()->id())
            ->when($r->receipt, fn ($q, $v) => $q->where('official_receipt_number', 'like', "%$v%"))
            ->when($r->customer, fn ($q, $v) => $q->whereHas('order', fn ($o) => $o->where('customer_name', 'like', "%$v%")))
            ->when($r->from, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($r->to, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($r->mode, fn ($q, $v) => $q->where('payment_mode', $v))
            ->when($r->status, fn ($q, $v) => $q->where('status', $v));
        return view('cashier.sales.index', ['sales' => $q->latest()->paginate(15)->withQueryString()]);
    }

    public function create(Order $order) {
        abort_unless($order->status === 'confirmed' && !$order->sale, 404);
        return view('cashier.sales.create', ['order' => $order->load('items.product')]);
    }

    public function store(StorePaymentRequest $req, Order $order, StockService $stock) {
        $sale = DB::transaction(function () use ($req, $order, $stock) {
            $order = Order::with('items')->lockForUpdate()->findOrFail($order->id);
            abort_unless($order->status === 'confirmed' && !$order->sale()->exists(), 409, 'This order was already paid or is not confirmed.');
            $total = $order->total; $mode = $req->payment_mode; $isCheck = in_array($mode, ['check','pdc']);
            $paid = $mode === 'cash' ? (float) $req->amount_received : $total;
            if ($mode === 'cash' && $paid < $total) abort(422, 'Amount received is less than the total due.');

            $sale = Sale::create([
                'order_id' => $order->id, 'processed_by' => auth()->id(), 'payment_mode' => $mode,
                'total' => $total, 'amount_paid' => $paid, 'discount_applied' => $order->discount_amount,
                'invoice_number' => NumberGenerator::next('INV', 'invoice_number'),
                'official_receipt_number' => NumberGenerator::next('OR', 'official_receipt_number'),
                'status' => $isCheck ? 'check_pending_clearance' : 'paid',
                'reference_number' => $req->reference_number,
                'proof_path' => $req->hasFile('proof') ? $req->file('proof')->store('proofs', 'public') : null,
                'check_number' => $req->check_number, 'bank_name' => $req->bank_name,
                'check_date' => $req->check_date ?? $req->maturity_date,
                'check_maturity_date' => $mode === 'pdc' ? $req->maturity_date : ($mode === 'check' ? $req->check_date : null),
            ]);
            foreach ($order->items as $i) {
                $sale->items()->create(['product_id' => $i->product_id, 'quantity' => $i->quantity,
                    'unit_price' => $i->quoted_price, 'line_total' => $i->quantity * $i->quoted_price]);
                $stock->move($i->product_id, 'stock_out', -$i->quantity, $sale->invoice_number, 'Sale '.$order->order_number);
            }
            $order->update(['status' => 'paid']);
            return $sale;
        });
        return redirect()->route('cashier.sales.show', $sale)->with('status', 'Payment recorded. Invoice and OR generated.');
    }

    public function show(Sale $sale) { return view('cashier.sales.show', ['sale' => $sale->load('order', 'items.product', 'cashier')]); }

    public function pdf(Sale $sale) {
        $sale->load('order', 'items.product', 'cashier');
        return Pdf::loadView('cashier.sales.pdf', compact('sale'))->stream($sale->official_receipt_number.'.pdf');
    }
}
