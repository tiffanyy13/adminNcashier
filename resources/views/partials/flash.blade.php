@if(session('status'))<div class="border border-black p-3 mb-3">{{ session('status') }}</div>@endif
@if($errors->any())<div class="border-2 border-black p-3 mb-3 text-sm">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>@endif
