<x-app-layout><x-slot name="header"><h2 class="font-semibold text-xl"> {{ $user->exists?'Edit':'New' }} Staff Account</h2></x-slot><div class="max-w-xl mx-auto p-6">@include("partials.flash")
<form method="POST" action="{{ $user->exists ? route('admin.staff.update',$user) : route('admin.staff.store') }}" class="bg-white border border-black p-4 space-y-3">@csrf @if($user->exists) @method('PUT') @endif
<input name="name" placeholder="Name" value="{{ old('name',$user->name) }}" class="w-full border-black"><input name="email" type="email" placeholder="Email" value="{{ old('email',$user->email) }}" class="w-full border-black">
<input name="contact" placeholder="Contact" value="{{ old('contact',$user->contact) }}" class="w-full border-black">
<select name="role" class="w-full border-black">@foreach(['cashier','secretary'] as $r)<option value="{{ $r }}" @selected(old('role',$user->getRoleNames()->first())==$r)>{{ $r }}</option>@endforeach</select>
<input name="password" type="password" placeholder="{{ $user->exists?'New password (leave blank to keep)':'Password' }}" class="w-full border-black">
<label class="text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$user->is_active))> Active</label><div><button class="bg-black text-white px-5 py-2">Save</button></div></form></div></x-app-layout>
