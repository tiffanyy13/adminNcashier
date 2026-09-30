<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PurchaseOrder extends Model {
    protected $guarded = []; protected $casts = ['authorized_at' => 'datetime', 'order_date' => 'date'];
    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function items() { return $this->hasMany(PurchaseOrderItem::class); }
    public function requester() { return $this->belongsTo(User::class, 'requested_by'); }
    public function authorizer() { return $this->belongsTo(User::class, 'authorized_by'); }
}
