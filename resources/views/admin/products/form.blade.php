<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl">{{ $product->exists ? 'Edit' : 'New' }} Product</h2></x-slot>
<div class="max-w-2xl mx-auto p-6">@if($errors->any())<div class="border-2 border-black p-3 mb-3 text-sm">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif
<form method="POST" action="{{ $product->exists ? route('admin.products.update',$product) : route('admin.products.store') }}" class="bg-white border border-black p-4 space-y-3">@csrf @if($product->exists) @method('PUT') @endif
<select name="category_id" class="w-full border-black">@foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id',$product->category_id)==$c->id)>{{ $c->name }}</option>@endforeach</select>
<input name="name" placeholder="Name" value="{{ old('name',$product->name) }}" class="w-full border-black">
<input name="package_size" placeholder="Package size" value="{{ old('package_size',$product->package_size) }}" class="w-full border-black">
<input name="unit_of_measure" placeholder="Unit of measure" value="{{ old('unit_of_measure',$product->unit_of_measure) }}" class="w-full border-black">
<input name="unit_price" type="number" step="0.01" placeholder="Unit price" value="{{ old('unit_price',$product->unit_price) }}" class="w-full border-black">
<input name="minimum_stock" type="number" placeholder="Minimum stock level" value="{{ old('minimum_stock',$product->inventory->minimum_stock ?? 0) }}" class="w-full border-black">
<select name="status" class="w-full border-black"><option value="active" @selected(old('status',$product->status)=='active')>active</option><option value="inactive" @selected(old('status',$product->status)=='inactive')>inactive</option></select>
<button class="bg-black text-white px-5 py-2">Save</button></form></div></x-app-layout>
