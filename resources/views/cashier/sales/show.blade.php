<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl">Invoice / Official Receipt</h2></x-slot>
<div class="max-w-3xl mx-auto p-6 space-y-3">
@if(session('status'))<div class="border border-black p-3">{{ session('status') }}</div>@endif
<div class="bg-white border border-black p-5 text-sm space-y-1">
<div>Invoice: <b>{{ $sale->invoice_number }}</b> · OR: <b>{{ $sale->official_receipt_number }}</b></div>
<div>Customer: {{ $sale->order->customer_name }} · Mode: {{ $sale->payment_mode }} · Status: {{ str_replace('_',' ',$sale->status) }}</div>
@if($sale->check_number)<div>Check #{{ $sale->check_number }} ({{ $sale->bank_name }}) matures {{ $sale->check_maturity_date?->format('M d, Y') }}</div>@endif
<div>Total ₱{{ number_format($sale->total,2) }} · Paid ₱{{ number_format($sale->amount_paid,2) }} · Change ₱{{ number_format(max(0,$sale->amount_paid-$sale->total),2) }}</div></div>
<a class="inline-block bg-black text-white px-4 py-2" target="_blank" href="{{ route('cashier.sales.pdf',$sale) }}">Print / Download PDF</a></div></x-app-layout>
