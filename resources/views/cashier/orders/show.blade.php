<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl">Order Details</h2></x-slot>
<div class="max-w-4xl mx-auto p-6 space-y-4">@include('cashier.orders._summary')
<a class="inline-block bg-black text-white px-4 py-2" href="{{ route('cashier.sales.create',$order) }}">Process Payment</a>
<p class="text-sm text-gray-600">Read-only. Wrong amounts? Send it back to the Admin.</p></div></x-app-layout>
