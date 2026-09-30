<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model {
    protected $guarded = []; protected $casts = ['approved_at' => 'datetime', 'discount_value' => 'decimal:2'];
    public function items() { return $this->hasMany(OrderItem::class); }
    public function sale() { return $this->hasOne(Sale::class); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function getSubtotalAttribute(): float { return (float) $this->items->sum(fn ($i) => $i->quantity * $i->quoted_price); }
    public function getDiscountAmountAttribute(): float {
        $s = $this->subtotal;
        return match ($this->discount_type) {
            'percentage' => round($s * $this->discount_value / 100, 2),
            'fixed' => min($s, (float) $this->discount_value),
            'special_price' => max(0, $s - (float) $this->discount_value), // value = agreed total
            default => 0.0,
        };
    }
    public function getTotalAttribute(): float { return round($this->subtotal - $this->discount_amount, 2); }
    public function isLocked(): bool { return in_array($this->status, ['confirmed','paid','released']); }
}
