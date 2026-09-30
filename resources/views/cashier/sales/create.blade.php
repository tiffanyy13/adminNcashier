<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl">Process Payment</h2></x-slot>
<div class="max-w-4xl mx-auto p-6 space-y-4" x-data="{ mode: '{{ old('payment_mode','cash') }}', due: {{ $order->total }}, recv: 0 }">
@include('cashier.orders._summary')
@if($errors->any())<div class="border-2 border-black p-3 text-sm">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif
<form method="POST" action="{{ route('cashier.sales.store',$order) }}" enctype="multipart/form-data" class="bg-white border border-black p-4 space-y-3">@csrf
  <label class="block text-sm font-semibold">Payment Mode
    <select name="payment_mode" x-model="mode" class="w-full border-black mt-1">
      <option value="cash">Cash</option><option value="gcash">GCash</option><option value="maya">Maya</option>
      <option value="bank_transfer">Bank Transfer</option><option value="check">Check</option><option value="pdc">Post-Dated Check</option></select></label>
  <div x-show="mode==='cash'" class="space-y-1"><label class="text-sm font-semibold">Amount Received</label>
    <input type="number" step="0.01" name="amount_received" x-model.number="recv" class="w-full border-black">
    <div class="text-sm">Change: <b x-text="'₱' + Math.max(0, recv - due).toFixed(2)"></b></div></div>
  <div x-show="['gcash','maya','bank_transfer'].includes(mode)" class="space-y-2">
    <input name="reference_number" placeholder="Reference number" class="w-full border-black" value="{{ old('reference_number') }}">
    <input type="file" name="proof" accept="image/*" class="w-full border border-black p-1"></div>
  <div x-show="['check','pdc'].includes(mode)" class="space-y-2">
    <input name="check_number" placeholder="Check number" class="w-full border-black" value="{{ old('check_number') }}">
    <input name="bank_name" placeholder="Bank name" class="w-full border-black" value="{{ old('bank_name') }}">
    <label x-show="mode==='check'" class="block text-sm">Date on check <input type="date" name="check_date" class="w-full border-black"></label>
    <label x-show="mode==='pdc'" class="block text-sm">Maturity date <input type="date" name="maturity_date" class="w-full border-black"></label></div>
  <button class="bg-black text-white px-5 py-2" onclick="return confirm('Submit payment? This cannot be repeated.')">Submit Payment</button>
</form></div></x-app-layout>
