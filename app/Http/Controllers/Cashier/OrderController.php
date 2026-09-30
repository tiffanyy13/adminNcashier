<?php
namespace App\Http\Controllers\Cashier;
use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller {
    public function index() {
        return view('cashier.orders.index', ['orders' => Order::with('items')->where('status', 'confirmed')->latest('approved_at')->paginate(15)]);
    }
    public function show(Order $order) {
        abort_unless($order->status === 'confirmed', 404);   // cashier sees confirmed orders only
        return view('cashier.orders.show', ['order' => $order->load('items.product')]);
    }
}
