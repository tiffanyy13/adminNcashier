<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Product extends Model {
    protected $guarded = []; protected $casts = ['unit_price' => 'decimal:2'];
    public function category() { return $this->belongsTo(Category::class); }
    public function inventory() { return $this->hasOne(Inventory::class); }
    public function scopeActive($q) { return $q->where('status', 'active'); }
    public function getFullNameAttribute() { return "{$this->name} ({$this->package_size})"; }
}
