<div class="bg-white border border-black p-4">
  <div class="font-semibold">{{ $order->order_number }} — {{ $order->customer_name }} {{ $order->customer_company ? '('.$order->customer_company.')' : '' }}</div>
  <table class="w-full text-sm mt-3"><thead class="border-b border-black"><tr><th class="text-left py-1">Item</th><th>Qty</th><th class="text-right">Price</th><th class="text-right">Amount</th></tr></thead>
  @foreach($order->items as $i)<tr><td class="py-1">{{ $i->product->full_name }}</td><td class="text-center">{{ $i->quantity }}</td><td class="text-right">₱{{ number_format($i->quoted_price,2) }}</td><td class="text-right">₱{{ number_format($i->quantity*$i->quoted_price,2) }}</td></tr>@endforeach</table>
  <div class="text-right mt-3 text-sm">Subtotal: ₱{{ number_format($order->subtotal,2) }}<br>Discount ({{ $order->discount_type }}): −₱{{ number_format($order->discount_amount,2) }}<br>
  <span class="text-xl font-bold">Total: ₱{{ number_format($order->total,2) }}</span></div>
</div>
