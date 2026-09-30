<?php
namespace App\Services;
use App\Models\{Inventory, StockTransaction};
use Illuminate\Support\Facades\DB;

/** The ONLY place inventory.quantity may change. Every change writes a ledger row. */
class StockService {
    public function move(int $productId, string $type, int $delta, ?string $reference = null, ?string $reason = null, ?int $userId = null): StockTransaction {
        return DB::transaction(function () use ($productId, $type, $delta, $reference, $reason, $userId) {
            $inv = Inventory::where('product_id', $productId)->lockForUpdate()->firstOrFail();
            $inv->quantity += $delta;   // negative allowed: admin may override stock warning
            $inv->save();
            return StockTransaction::create(['type' => $type, 'product_id' => $productId, 'quantity' => $delta,
                'reference' => $reference, 'reason' => $reason, 'performed_by' => $userId ?? auth()->id()]);
        });
    }
}
