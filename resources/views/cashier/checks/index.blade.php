<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl">Check / PDC Tracker</h2></x-slot>
<div class="max-w-7xl mx-auto p-6">@if(session('status'))<div class="border border-black p-3 mb-3">{{ session('status') }}</div>@endif
<div class="bg-white border border-black"><table class="w-full text-sm"><thead class="bg-black text-white"><tr><th class="p-2 text-left">Customer</th><th class="p-2">Check #</th><th class="p-2">Bank</th><th class="p-2">Maturity</th><th class="p-2 text-right">Amount</th><th></th></tr></thead>
@forelse($sales as $s)@php $soon = $s->check_maturity_date && $s->check_maturity_date->lte(now()->addDays(7)); @endphp
<tr class="border-t {{ $soon ? 'bg-gray-200 font-semibold' : '' }}"><td class="p-2">{{ $s->order->customer_name }}</td><td class="p-2 text-center">{{ $s->check_number }}</td><td class="p-2 text-center">{{ $s->bank_name }}</td>
<td class="p-2 text-center">{{ $s->check_maturity_date?->format('M d, Y') }} @if($soon)<span class="bg-black text-white px-1 text-xs">≤7 DAYS</span>@endif</td><td class="p-2 text-right">₱{{ number_format($s->total,2) }}</td>
<td class="p-2"><form method="POST" action="{{ route('cashier.checks.clear',$s) }}" onsubmit="return confirm('Confirm this check was encashed?')">@csrf<button class="bg-black text-white px-3 py-1">Mark as cleared</button></form></td></tr>
@empty<tr><td colspan="6" class="p-4 text-center text-gray-500">No checks pending clearance.</td></tr>@endforelse</table></div></div></x-app-layout>
