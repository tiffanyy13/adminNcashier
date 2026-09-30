<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl">{{ $order->exists ? 'Edit '.$order->order_number : 'New Order / Quotation' }}</h2></x-slot>
@php
    $rows = $order->exists
        ? $order->items->map(fn ($i) => [
            'product_id' => $i->product_id,
            'quantity' => $i->quantity,
            'quoted_price' => $i->quoted_price,
        ])->values()
        : [['product_id' => '', 'quantity' => 1, 'quoted_price' => 0]];
@endphp
<div class="max-w-4xl mx-auto p-6" x-data="{ products: @js($products), rows: @js($rows),
  pick(r){ const p=this.products.find(x=>x.id==r.product_id); if(p) r.quoted_price=p.price; },
  stock(r){ const p=this.products.find(x=>x.id==r.product_id); return p?p.stock:null; },
  total(){ return this.rows.reduce((s,r)=>s+r.quantity*r.quoted_price,0); } }">
@if($errors->any())<div class="border-2 border-black p-3 mb-3 text-sm">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif
<form method="POST" action="{{ $order->exists ? route('admin.orders.update',$order) : route('admin.orders.store') }}" class="bg-white border border-black p-4 space-y-3">@csrf @if($order->exists) @method('PUT') @endif
<div class="grid grid-cols-2 gap-2"><input name="customer_name" placeholder="Customer name" value="{{ old('customer_name',$order->customer_name) }}" class="border-black">
<input name="customer_contact" placeholder="Contact" value="{{ old('customer_contact',$order->customer_contact) }}" class="border-black">
<input name="customer_company" placeholder="Company" value="{{ old('customer_company',$order->customer_company) }}" class="border-black">
<input name="delivery_address" placeholder="Delivery address" value="{{ old('delivery_address',$order->delivery_address) }}" class="border-black"></div>
<template x-for="(r,i) in rows" :key="i"><div class="flex gap-2 items-start text-sm">
  <select :name="`items[${i}][product_id]`" x-model="r.product_id" @change="pick(r)" class="border-black flex-1"><option value="">Select product</option><template x-for="p in products" :key="p.id"><option :value="p.id" x-text="p.name"></option></template></select>
  <input type="number" min="1" :name="`items[${i}][quantity]`" x-model.number="r.quantity" class="w-20 border-black">
  <input type="number" step="0.01" :name="`items[${i}][quoted_price]`" x-model.number="r.quoted_price" class="w-28 border-black">
  <div class="w-40 text-xs"><span x-show="stock(r)!==null">Stock: <b x-text="stock(r)"></b></span><div x-show="stock(r)!==null && r.quantity>stock(r)" class="font-bold">⚠ Exceeds stock (reorder will be needed)</div></div>
  <button type="button" @click="rows.splice(i,1)" x-show="rows.length>1" class="border border-black px-2">✕</button></div></template>
<button type="button" @click="rows.push({product_id:'',quantity:1,quoted_price:0})" class="border border-black px-3 py-1 text-sm">+ Add item</button>
<div class="text-right font-bold">Subtotal: ₱<span x-text="total().toFixed(2)"></span></div>
<div class="flex gap-2"><button name="action" value="quotation" class="border-2 border-black px-4 py-2">Save as Quotation</button><button name="action" value="pending_approval" class="bg-black text-white px-4 py-2">Submit for Approval</button></div>
</form></div></x-app-layout>
