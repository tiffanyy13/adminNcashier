<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl">{{ $order->order_number }} — {{ str_replace('_',' ',$order->status) }}</h2></x-slot>
<div class="max-w-4xl mx-auto p-6 space-y-4">@if(session('status'))<div class="border border-black p-3">{{ session('status') }}</div>@endif
@if($errors->any())<div class="border-2 border-black p-3 text-sm">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif
@include('cashier.orders._summary')
<div class="text-sm">Customer: {{ $order->customer_name }} · {{ $order->customer_contact }} · {{ $order->delivery_address }}@if($order->approver)<br>Approved by {{ $order->approver->name }} on {{ $order->approved_at->format('M d, Y h:i A') }}@endif</div>
@if(in_array($order->status,['quotation','pending_approval']))
<form method="POST" action="{{ route('admin.orders.approve',$order) }}" class="bg-white border border-black p-4 flex gap-2 items-end text-sm">@csrf
<label>Discount<select name="discount_type" class="border-black block">@foreach(['none','percentage','fixed','special_price'] as $t)<option @selected($order->discount_type==$t)>{{ $t }}</option>@endforeach</select></label>
<label>Value<input name="discount_value" type="number" step="0.01" value="{{ $order->discount_value }}" class="border-black block"></label>
<span class="text-xs w-48">percentage = %, fixed = ₱ off, special_price = agreed total ₱</span>
<button class="bg-black text-white px-4 py-2">Approve & Confirm</button></form>
@if($order->status=='pending_approval')<form method="POST" action="{{ route('admin.orders.reject',$order) }}">@csrf<button class="border-2 border-black px-4 py-2">Reject (back to quotation)</button></form>@endif
<form method="POST" action="{{ route('admin.orders.destroy',$order) }}" onsubmit="return confirm('Delete this order?')">@csrf @method('DELETE')<button class="underline text-sm">Delete</button></form>@endif
@unless(in_array($order->status,['paid','released']))<a class="underline" href="{{ route('admin.orders.edit',$order) }}">Edit order</a>@endunless
</div></x-app-layout>
