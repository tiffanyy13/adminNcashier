<?php
namespace App\Http\Controllers\Cashier;
use App\Http\Controllers\Controller;
use App\Models\{AuditLog, Sale};

class CheckController extends Controller {
    public function index() {
        return view('cashier.checks.index', ['sales' => Sale::with('order')->where('status', 'check_pending_clearance')->orderBy('check_maturity_date')->get()]);
    }
    public function clear(Sale $sale) {
        abort_unless($sale->status === 'check_pending_clearance', 422);
        $sale->update(['status' => 'cleared']);
        AuditLog::record($sale, 'check_cleared', ['status' => 'check_pending_clearance'], ['status' => 'cleared']);
        return back()->with('status', "Check {$sale->check_number} marked as cleared.");
    }
}
