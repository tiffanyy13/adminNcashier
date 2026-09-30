<x-app-layout>
<x-slot name="header"><h2 class="font-semibold text-xl">Cashier Dashboard</h2></x-slot>
<div class="max-w-7xl mx-auto p-6 space-y-6">
  @if(session('status'))<div class="border border-black p-3">{{ session('status') }}</div>@endif
  <div class="grid grid-cols-3 gap-4">
    <div class="border-2 border-black p-4 bg-white"><div class="text-sm">Orders Awaiting Payment</div><div class="text-3xl font-bold">{{ $ready->count() }}</div></div>
    <div class="border-2 border-black p-4 bg-white"><div class="text-sm">Today's Collections</div><div class="text-3xl font-bold">₱{{ number_format($collections,2) }}</div></div>
    <div class="border-2 border-black p-4 bg-white"><div class="text-sm">Checks/PDCs Maturing This Week</div><div class="text-3xl font-bold">{{ $weekCount }} · ₱{{ number_format($weekTotal,2) }}</div></div>
  </div>
  <div class="bg-white border border-black"><h3 class="p-3 font-semibold border-b border-black">Confirmed Orders Ready for Payment</h3>
    <table class="w-full text-sm"><thead class="bg-black text-white"><tr><th class="p-2 text-left">Customer</th><th class="p-2 text-right">Total</th><th class="p-2">Confirmed</th><th></th></tr></thead>
    @forelse($ready as $o)<tr class="border-t"><td class="p-2">{{ $o->customer_name }}</td><td class="p-2 text-right">₱{{ number_format($o->total,2) }}</td><td class="p-2 text-center">{{ $o->approved_at?->format('M d, Y') }}</td>
      <td class="p-2 text-right"><a class="bg-black text-white px-3 py-1" href="{{ route('cashier.sales.create',$o) }}">Process Payment</a></td></tr>
    @empty<tr><td colspan="4" class="p-4 text-center text-gray-500">No orders awaiting payment.</td></tr>@endforelse</table></div>
  <div class="bg-white border border-black"><h3 class="p-3 font-semibold border-b border-black">Checks / PDCs Nearing Maturity</h3>
    <table class="w-full text-sm"><thead class="bg-black text-white"><tr><th class="p-2 text-left">Customer</th><th class="p-2">Check #</th><th class="p-2">Maturity</th><th class="p-2 text-right">Amount</th></tr></thead>
    @forelse($pending as $s)<tr class="border-t"><td class="p-2">{{ $s->order->customer_name }}</td><td class="p-2 text-center">{{ $s->check_number }}</td><td class="p-2 text-center">{{ $s->check_maturity_date?->format('M d, Y') }}</td><td class="p-2 text-right">₱{{ number_format($s->total,2) }}</td></tr>
    @empty<tr><td colspan="4" class="p-4 text-center text-gray-500">None.</td></tr>@endforelse</table></div>
</div></x-app-layout>
