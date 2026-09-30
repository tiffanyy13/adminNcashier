<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;

class SupplierController extends Controller {
    public function index() { return view('admin.suppliers.index', ['suppliers' => Supplier::orderBy('name')->paginate(15)]); }
    public function create() { return view('admin.suppliers.form', ['supplier' => new Supplier]); }
    public function store(SupplierRequest $r) { Supplier::create($r->validated()); return redirect()->route('admin.suppliers.index')->with('status', 'Supplier added.'); }
    public function edit(Supplier $supplier) { return view('admin.suppliers.form', compact('supplier')); }
    public function update(SupplierRequest $r, Supplier $supplier) { $supplier->update($r->validated()); return redirect()->route('admin.suppliers.index')->with('status', 'Supplier updated.'); }
    public function destroy(Supplier $supplier) {
        if ($supplier->purchaseOrders()->exists()) return back()->withErrors(['supplier' => 'Supplier has purchase orders and cannot be deleted.']);
        $supplier->delete(); return back()->with('status', 'Supplier deleted.');
    }
}
