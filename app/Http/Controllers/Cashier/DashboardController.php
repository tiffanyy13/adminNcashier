<?php
namespace App\Http\Controllers\Cashier;
use App\Http\Controllers\Controller;
use App\Models\{Order, Sale};

class DashboardController extends Controller {
    public function index() {
        $ready = Order::with('items')->where('status', 'confirmed')->latest('approved_at')->get();
        $pending = Sale::with('order')->where('status', 'check_pending_clearance')->orderBy('check_maturity_date')->get();
        $week = $pending->filter(fn ($s) => $s->check_maturity_date && $s->check_maturity_date->lte(now()->addDays(7)->endOfDay()));
        return view('cashier.dashboard', [
            'ready' => $ready, 'pending' => $pending->take(10),
            'collections' => Sale::whereDate('created_at', today())->where('status', '!=', 'voided')->sum('amount_paid'),
            'weekCount' => $week->count(), 'weekTotal' => $week->sum('total'),
        ]);
    }
}
