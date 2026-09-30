<?php
namespace App\Services;
use App\Models\Sale;
class NumberGenerator {
    /** Sequential per year: INV-2026-000001 / OR-2026-000001. Call inside a DB transaction. */
    public static function next(string $prefix, string $column): string {
        $year = now()->year; $like = "$prefix-$year-%";
        $last = Sale::where($column, 'like', $like)->lockForUpdate()->orderByDesc($column)->value($column);
        $n = $last ? (int) substr($last, -6) + 1 : 1;
        return sprintf('%s-%d-%06d', $prefix, $year, $n);
    }
}
