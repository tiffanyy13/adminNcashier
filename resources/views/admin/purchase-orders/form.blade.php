<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl"> {{ $po->exists?'Edit '.$po->po_number:'New Purchase Order' }}</h2></x-slot>
@php
    $rows = $po->exists
        ? $po->items->map(fn ($i) => [
            'product_id' => $i->product_id,
            'quantity' => $i->quantity,
            'unit_price' => $i->unit_price,
        ])->values()
        : [['product_id' => '', 'quantity' => 1, 'unit_price' => 0]];
@endphp
<div class="max-w-4xl mx-auto p-6" x-data="{ products: @js($products), rows: @js($rows),
 pick(r){ const p=this.products.find(x=>x.id==r.product_id); if(p) r.unit_price=p.price; } }">@include("partials.flash")
<form method="POST" action="{{ $po->exists ? route('admin.purchase-orders.update',$po) : route('admin.purchase-orders.store') }}" class="bg-white border border-black p-4 space-y-3">@csrf @if($po->exists) @method('PUT') @endif
<div class="grid grid-cols-2 gap-2"><select name="supplier_id" class="border-black">@foreach($suppliers as $s)<option value="{{ $s->id }}" @selected(old('supplier_id',$po->supplier_id)==$s->id)>{{ $s->name }}</option>@endforeach</select>
<input type="date" name="order_date" value="{{ old('order_date',$po->order_date?->format('Y-m-d')) }}" class="border-black"></div>
<template x-for="(r,i) in rows" :key="i"><div class="flex gap-2 text-sm"><select :name="`items[${i}][product_id]`" x-model="r.product_id" @change="pick(r)" class="border-black flex-1"><option value="">Product</option><template x-for="p in products" :key="p.id"><option :value="p.id" x-text="p.name"></option></template></select>
<input type="number" min="1" :name="`items[${i}][quantity]`" x-model.number="r.quantity" class="w-24 border-black"><input type="number" step="0.01" :name="`items[${i}][unit_price]`" x-model.number="r.unit_price" class="w-28 border-black">
<button type="button" x-show="rows.length>1" @click="rows.splice(i,1)" class="border border-black px-2">✕</button></div></template>
<button type="button" @click="rows.push({product_id:'',quantity:1,unit_price:0})" class="border border-black px-3 py-1 text-sm">+ Add item</button><div><button class="bg-black text-white px-5 py-2">Save Draft</button></div></form></div></x-app-layout>