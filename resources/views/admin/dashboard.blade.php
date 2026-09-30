<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl">Admin Dashboard</h2></x-slot>
<div class="max-w-7xl mx-auto p-6 space-y-6">@if(session('status'))<div class="border border-black p-3">{{ session('status') }}</div>@endif
<div class="grid grid-cols-5 gap-3 text-sm">
@foreach([['Pending Order Approvals',$orders->count()],['PO Authorizations',$pos->count()],['Low / Out of Stock',$lowCount],["Today's Sales",'₱'.number_format($today,2)],['This Month','₱'.number_format($month,2)]] as [$l,$v])
<div class="border-2 border-black bg-white p-3"><div>{{ $l }}</div><div class="text-2xl font-bold">{{ $v }}</div></div>@endforeach</div>
<div class="bg-white border border-black"><h3 class="p-3 font-semibold border-b border-black">Orders Awaiting Approval</h3><table class="w-full text-sm">
@forelse($orders as $o)<tr class="border-t"><td class="p-2"><a class="underline" href="{{ route('admin.orders.show',$o) }}">{{ $o->customer_name }}</a></td><td class="p-2 text-right">₱{{ number_format($o->total,2) }}</td><td class="p-2">{{ $o->discount_type=='none'?'No discount':$o->discount_type.' '.$o->discount_value }}</td>
<td class="p-2 flex gap-2 justify-end"><form method="POST" action="{{ route('admin.orders.approve',$o) }}">@csrf<button class="bg-black text-white px-3 py-1">Approve</button></form>
<form method="POST" action="{{ route('admin.orders.reject',$o) }}">@csrf<button class="border border-black px-3 py-1">Reject</button></form></td></tr>
@empty<tr><td class="p-4 text-center text-gray-500">Nothing pending.</td></tr>@endforelse</table></div>
<div class="bg-white border border-black"><h3 class="p-3 font-semibold border-b border-black">Purchase Orders Awaiting Authorization</h3><table class="w-full text-sm">
@forelse($pos as $p)<tr class="border-t"><td class="p-2">{{ $p->po_number }}</td><td class="p-2">{{ $p->supplier->name }}</td><td class="p-2">{{ $p->items->map(fn($i)=>$i->product->name.' ×'.$i->quantity)->join(', ') }}</td>
<td class="p-2 text-right"><form method="POST" action="{{ route('admin.purchase-orders.authorize',$p) }}">@csrf<button class="bg-black text-white px-3 py-1">Authorize</button></form></td></tr>
@empty<tr><td class="p-4 text-center text-gray-500">Nothing pending.</td></tr>@endforelse</table></div>
<div class="bg-white border border-black"><h3 class="p-3 font-semibold border-b border-black">Low / Out-of-Stock</h3><table class="w-full text-sm">
@forelse($low as $i)<tr class="border-t"><td class="p-2">{{ $i->product->full_name }}</td><td class="p-2 text-center">{{ $i->quantity }} / min {{ $i->minimum_stock }}</td><td class="p-2 text-right font-bold">{{ $i->stock_status }}</td></tr>
@empty<tr><td class="p-4 text-center text-gray-500">All stocked.</td></tr>@endforelse</table></div></div></x-app-layout>
