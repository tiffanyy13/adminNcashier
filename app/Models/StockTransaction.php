<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StockTransaction extends Model {
    protected $guarded = [];
    public function product() { return $this->belongsTo(Product::class); }
    public function user() { return $this->belongsTo(User::class, 'performed_by'); }
}
