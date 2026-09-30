<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $warehouse = DB::table('roles')->where('name', 'warehouse')->first();
        $secretary = DB::table('roles')->where('name', 'secretary')->first();

        if ($warehouse && ! $secretary) {
            DB::table('roles')->where('id', $warehouse->id)->update([
                'name' => 'secretary',
                'updated_at' => now(),
            ]);

            return;
        }

        if (! $secretary) {
            DB::table('roles')->insert([
                'name' => 'secretary',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $secretary = DB::table('roles')->where('name', 'secretary')->first();
        $warehouse = DB::table('roles')->where('name', 'warehouse')->first();

        if ($secretary && ! $warehouse) {
            DB::table('roles')->where('id', $secretary->id)->update([
                'name' => 'warehouse',
                'updated_at' => now(),
            ]);
        }
    }
};
