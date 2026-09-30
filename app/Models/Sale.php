<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Sale extends Model {
    protected $guarded = []; protected $casts = ['check_maturity_date' => 'date', 'check_date' => 'date'];
    public function order() { return $this->belongsTo(Order::class); }
    public function items() { return $this->hasMany(SaleItem::class); }
    public function cashier() { return $this->belongsTo(User::class, 'processed_by'); }
}
