<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl"> {{ $po->po_number }} — {{ $po->status }}</h2></x-slot><div class="max-w-4xl mx-auto p-6 space-y-3">@include("partials.flash")
<div class="bg-white border border-black p-4 text-sm">Supplier: {{ $po->supplier->name }} · Requested by {{ $po->requester->name }} · Authorized: {{ $po->authorizer ? $po->authorizer->name.' ('.$po->authorized_at->format('M d Y').')' : 'not yet' }}</div>
@if($po->status==='sent')<form method="POST" action="{{ route('admin.purchase-orders.receive',$po) }}">@csrf @endif
<div class="bg-white border border-black"><table class="w-full text-sm"><thead class="bg-black text-white"><tr><th class="p-2 text-left">Product</th><th class="p-2">Ordered</th><th class="p-2">Received</th><th class="p-2 text-right">Unit price</th></tr></thead>
@foreach($po->items as $i)<tr class="border-t"><td class="p-2">{{ $i->product->full_name }}</td><td class="p-2 text-center">{{ $i->quantity }}</td>
<td class="p-2 text-center">@if($po->status==='sent')<input type="number" min="0" name="received[{{ $i->id }}]" value="{{ $i->quantity }}" class="w-20 border-black">@else{{ $i->received_quantity }}@endif</td><td class="p-2 text-right">₱{{ number_format($i->unit_price,2) }}</td></tr>@endforeach</table></div>
@if($po->status==='sent')<button class="bg-black text-white px-4 py-2 mt-2">Record Delivery (stock-in)</button></form>@endif
<div class="flex gap-3">@if($po->status==='draft')<a class="underline" href="{{ route('admin.purchase-orders.edit',$po) }}">Edit</a>
@unless($po->authorized_by)<form method="POST" action="{{ route('admin.purchase-orders.authorize',$po) }}">@csrf<button class="bg-black text-white px-4 py-2">Authorize</button></form>@endunless
@if($po->authorized_by)<form method="POST" action="{{ route('admin.purchase-orders.advance',$po) }}">@csrf<button class="border-2 border-black px-4 py-2">Mark as Sent</button></form>@endif
<form method="POST" action="{{ route('admin.purchase-orders.destroy',$po) }}" onsubmit="return confirm('Delete draft?')">@csrf @method('DELETE')<button class="underline">Delete</button></form>@endif
@if($po->status==='received')<form method="POST" action="{{ route('admin.purchase-orders.advance',$po) }}">@csrf<button class="bg-black text-white px-4 py-2">Close PO</button></form>@endif</div></div></x-app-layout>
