<html><body style="font-family:DejaVu Sans;font-size:12px">
<h2 style="margin:0">DPAM Industrial Supplies and Services Inc.</h2><p>INVOICE {{ $sale->invoice_number }} &nbsp;|&nbsp; OFFICIAL RECEIPT {{ $sale->official_receipt_number }}<br>Date: {{ $sale->created_at->format('M d, Y h:i A') }}</p>
<p>Customer: {{ $sale->order->customer_name }}<br>{{ $sale->order->delivery_address }}</p>
<table width="100%" border="1" cellspacing="0" cellpadding="4"><tr><th align="left">Item</th><th>Qty</th><th align="right">Price</th><th align="right">Amount</th></tr>
@foreach($sale->items as $i)<tr><td>{{ $i->product->full_name }}</td><td align="center">{{ $i->quantity }}</td><td align="right">{{ number_format($i->unit_price,2) }}</td><td align="right">{{ number_format($i->line_total,2) }}</td></tr>@endforeach</table>
<p align="right">Discount: {{ number_format($sale->discount_applied,2) }}<br><b>TOTAL: PHP {{ number_format($sale->total,2) }}</b><br>Paid via {{ strtoupper($sale->payment_mode) }}: {{ number_format($sale->amount_paid,2) }}</p>
<p>Received by: {{ $sale->cashier->name }}</p></body></html>
