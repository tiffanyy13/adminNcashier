<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\{Category, Inventory, Product};
use Illuminate\Http\Request;

class ProductController extends Controller {
    public function index(Request $r) {
        $products = Product::with('category', 'inventory')
            ->when($r->q, fn ($q, $v) => $q->where('name', 'like', "%$v%"))
            ->when($r->category, fn ($q, $v) => $q->where('category_id', $v))
            ->when($r->status, fn ($q, $v) => $q->where('status', $v))
            ->orderBy('name')->paginate(15)->withQueryString();
        return view('admin.products.index', ['products' => $products, 'categories' => Category::all()]);
    }
    public function create() { return view('admin.products.form', ['product' => new Product(['status' => 'active']), 'categories' => Category::all()]); }
    public function store(ProductRequest $r) {
        $p = Product::create($r->safe()->except('minimum_stock'));
        Inventory::create(['product_id' => $p->id, 'quantity' => 0, 'minimum_stock' => $r->minimum_stock]);
        return redirect()->route('admin.products.index')->with('status', 'Product created.');
    }
    public function edit(Product $product) { return view('admin.products.form', ['product' => $product->load('inventory'), 'categories' => Category::all()]); }
    public function update(ProductRequest $r, Product $product) {
        $product->update($r->safe()->except('minimum_stock'));
        $product->inventory()->updateOrCreate([], ['minimum_stock' => $r->minimum_stock]);
        return redirect()->route('admin.products.index')->with('status', 'Product updated.');
    }
    public function toggle(Product $product) { // deactivate instead of delete
        $product->update(['status' => $product->status === 'active' ? 'inactive' : 'active']);
        return back()->with('status', "{$product->name} is now {$product->status}.");
    }
}
