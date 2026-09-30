<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $t) { $t->string('contact')->nullable(); $t->boolean('is_active')->default(true); });
        Schema::create('categories', function (Blueprint $t) { $t->id(); $t->string('name')->unique(); $t->timestamps(); });
        Schema::create('products', function (Blueprint $t) {
            $t->id(); $t->foreignId('category_id')->constrained();
            $t->string('name'); $t->string('unit_of_measure'); $t->string('package_size');
            $t->decimal('unit_price', 12, 2); $t->enum('status', ['active','inactive'])->default('active'); $t->timestamps();
            $t->unique(['category_id','name','package_size']);
        });
        Schema::create('inventories', function (Blueprint $t) {
            $t->id(); $t->foreignId('product_id')->unique()->constrained();
            $t->integer('quantity')->default(0); $t->unsignedInteger('minimum_stock')->default(0); $t->timestamps();
        });
        Schema::create('suppliers', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('contact_person')->nullable(); $t->string('phone')->nullable();
            $t->string('email')->nullable(); $t->string('categories_supplied')->nullable(); $t->text('notes')->nullable(); $t->timestamps();
        });
    }
    public function down(): void {
        foreach (['suppliers','inventories','products','categories'] as $x) Schema::dropIfExists($x);
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['contact','is_active']));
    }
};
