@php $u = auth()->user(); @endphp
<nav x-data="{ open: false }" class="bg-black text-white border-b border-gray-800">
  <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-14">
    <div class="flex items-center gap-6 text-sm">
      <span class="font-bold tracking-wide">DPAM</span>
      @role('cashier')
        <a href="{{ route('cashier.dashboard') }}" class="hover:underline">Dashboard</a>
        <a href="{{ route('cashier.orders.index') }}" class="hover:underline">Orders</a>
        <a href="{{ route('cashier.sales.index') }}" class="hover:underline">Sales Records</a>
        <a href="{{ route('cashier.checks.index') }}" class="hover:underline">Check / PDC</a>
      @endrole
      @role('admin')
        <a href="{{ route('admin.dashboard') }}" class="hover:underline">Dashboard</a>
        <a href="{{ route('admin.products.index') }}" class="hover:underline">Products</a>
        <a href="{{ route('admin.inventory.index') }}" class="hover:underline">Inventory</a>
        <a href="{{ route('admin.orders.index') }}" class="hover:underline">Orders</a>
        <a href="{{ route('admin.sales.index') }}" class="hover:underline">Sales</a>
        <a href="{{ route('admin.suppliers.index') }}" class="hover:underline">Suppliers</a>
        <a href="{{ route('admin.purchase-orders.index') }}" class="hover:underline">POs</a>
        <a href="{{ route('admin.stock.index') }}" class="hover:underline">Stock</a>
        <a href="{{ route('admin.reports') }}" class="hover:underline">Reports</a>
        <a href="{{ route('admin.staff.index') }}" class="hover:underline">Staff</a>
      @endrole
    </div>
    <div class="flex items-center gap-4 text-sm">
      <a href="{{ route('profile.edit') }}" class="hover:underline">{{ $u->name }}</a>
      <form method="POST" action="{{ route('logout') }}">@csrf<button class="border border-white px-3 py-1 hover:bg-white hover:text-black">Logout</button></form>
    </div>
  </div>
</nav>
