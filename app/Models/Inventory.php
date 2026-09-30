<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Inventory extends Model {
    protected $guarded = [];
    public function product() { return $this->belongsTo(Product::class); }
    public function getStockStatusAttribute() {
        return $this->quantity <= 0 ? 'Out' : ($this->quantity <= $this->minimum_stock ? 'Low' : 'OK');
    }
}
