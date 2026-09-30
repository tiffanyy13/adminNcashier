<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\{Category, Inventory, Product, PurchaseOrder, Sale};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportController extends Controller {
    public function show(Request $r, string $type = 'inventory') {
        abort_unless(in_array($type, ['inventory', 'sales', 'reorder', 'delivery']), 404);
        [$title, $head, $rows] = $this->build($type, $r);
        if ($r->format === 'pdf') return Pdf::loadView('admin.reports.pdf', compact('title', 'head', 'rows'))->download("$type-report.pdf");
        if ($r->format === 'csv') return response()->streamDownload(function () use ($head, $rows) {
            $o = fopen('php://output', 'w'); fputcsv($o, $head); foreach ($rows as $x) fputcsv($o, $x); fclose($o);
        }, "$type-report.csv"); // opens directly in Excel
        return view('admin.reports.show', compact('type', 'title', 'head', 'rows') + ['categories' => Category::all(), 'products' => Product::orderBy('name')->get()]);
    }
    private function build(string $type, Request $r): array {
        $money = fn ($n) => number_format($n, 2);
        if (in_array($type, ['inventory', 'reorder'])) {
            $inv = Inventory::with('product.category')
                ->whereHas('product', fn ($p) => $p->when($r->category, fn ($q, $v) => $q->where('category_id', $v))->when($r->product, fn ($q, $v) => $q->where('id', $v)))
                ->when($type === 'reorder', fn ($q) => $q->whereColumn('quantity', '<=', 'minimum_stock'))->get();
            $rows = $inv->map(fn ($i) => [$i->product->full_name, $i->product->category->name, $i->quantity, $i->minimum_stock, $i->stock_status, $money($i->quantity * $i->product->unit_price)])->all();
            if ($type === 'inventory') $rows[] = ['TOTAL VALUATION', '', '', '', '', $money($inv->sum(fn ($i) => $i->quantity * $i->product->unit_price))];
            return [$type === 'inventory' ? 'Inventory Report' : 'Reorder Alert Report', ['Product', 'Category', 'Qty', 'Min', 'Status', 'Valuation (PHP)'], $rows];
        }
        if ($type === 'delivery') {
            $pos = PurchaseOrder::with('supplier', 'items.product')->when($r->from, fn ($q, $v) => $q->whereDate('order_date', '>=', $v))->when($r->to, fn ($q, $v) => $q->whereDate('order_date', '<=', $v))->latest('order_date')->get();
            return ['Delivery Log (Purchase Orders)', ['PO #', 'Supplier', 'Date', 'Status', 'Items', 'Total (PHP)'],
                $pos->map(fn ($p) => [$p->po_number, $p->supplier->name, $p->order_date->format('Y-m-d'), $p->status, $p->items->map(fn ($i) => $i->product->name.' x'.$i->received_quantity.'/'.$i->quantity)->join('; '), $money($p->items->sum(fn ($i) => $i->quantity * $i->unit_price))])->all()];
        }
        $group = $r->group ?? 'day';
        $sales = Sale::with('order', 'items.product')->where('status', '!=', 'voided')
            ->when($r->from, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))->when($r->to, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($r->product, fn ($q, $v) => $q->whereHas('items', fn ($i) => $i->where('product_id', $v)))
            ->when($r->category, fn ($q, $v) => $q->whereHas('items.product', fn ($p) => $p->where('category_id', $v)))->get();
        if ($group === 'product') {
            $rows = $sales->flatMap->items->groupBy(fn ($i) => $i->product->full_name)->map(fn ($g, $k) => [$k, $g->sum('quantity'), $money($g->sum('line_total'))])->values()->all();
            return ['Sales Report by Product', ['Product', 'Qty Sold', 'Amount (PHP)'], $rows];
        }
        $key = fn ($s) => match ($group) { 'month' => $s->created_at->format('Y-m'), 'customer' => $s->order->customer_name, default => $s->created_at->format('Y-m-d') };
        $rows = $sales->groupBy($key)->map(fn ($g, $k) => [$k, $g->count(), $money($g->sum('total'))])->values()->all();
        $rows[] = ['GRAND TOTAL', $sales->count(), $money($sales->sum('total'))];
        return ['Sales Report by '.ucfirst($group), [ucfirst($group), 'Transactions', 'Total (PHP)'], $rows];
    }
}
