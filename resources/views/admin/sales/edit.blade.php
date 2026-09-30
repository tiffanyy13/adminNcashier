<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl"> Correct Sale</h2></x-slot><div class="max-w-xl mx-auto p-6">@include("partials.flash")
<form method="POST" action="{{ route('admin.sales.update',$sale) }}" class="bg-white border border-black p-4 space-y-3">@csrf @method('PUT')
<select name="payment_mode" class="w-full border-black">@foreach(['cash','gcash','maya','bank_transfer','check','pdc'] as $m)<option @selected(old('payment_mode',$sale->payment_mode)==$m)>{{ $m }}</option>@endforeach</select>
<input name="amount_paid" type="number" step="0.01" value="{{ old('amount_paid',$sale->amount_paid) }}" class="w-full border-black">
<input name="reference_number" placeholder="Reference #" value="{{ old('reference_number',$sale->reference_number) }}" class="w-full border-black">
<input name="check_maturity_date" type="date" value="{{ old('check_maturity_date',$sale->check_maturity_date?->format('Y-m-d')) }}" class="w-full border-black">
<input name="reason" required placeholder="Reason for correction (required)" class="w-full border-black"><button class="bg-black text-white px-5 py-2">Save & Log</button></form></div></x-app-layout>
