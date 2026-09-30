<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AuditLog extends Model {
    protected $guarded = []; protected $casts = ['old_values' => 'array', 'new_values' => 'array'];
    public static function record(Model $m, string $action, array $old = [], array $new = []): self {
        return static::create(['user_id' => auth()->id(), 'auditable_type' => $m::class, 'auditable_id' => $m->getKey(),
            'action' => $action, 'old_values' => $old, 'new_values' => $new]);
    }
}
