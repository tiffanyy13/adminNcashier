<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl">Confirmed Orders</h2></x-slot>
<div class="max-w-7xl mx-auto p-6"><div class="bg-white border border-black">
<table class="w-full text-sm"><thead class="bg-black text-white"><tr><th class="p-2 text-left">Order #</th><th class="p-2 text-left">Customer</th><th class="p-2 text-right">Total</th><th></th></tr></thead>
@foreach($orders as $o)<tr class="border-t"><td class="p-2">{{ $o->order_number }}</td><td class="p-2">{{ $o->customer_name }}</td><td class="p-2 text-right">₱{{ number_format($o->total,2) }}</td>
<td class="p-2 text-right"><a class="underline" href="{{ route('cashier.orders.show',$o) }}">View</a> · <a class="underline" href="{{ route('cashier.sales.create',$o) }}">Pay</a></td></tr>@endforeach</table></div>
<div class="mt-4">{{ $orders->links() }}</div></div></x-app-layout>
