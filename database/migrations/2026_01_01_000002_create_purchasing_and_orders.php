<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_orders', function (Blueprint $t) {
            $t->id(); $t->string('po_number')->unique(); $t->foreignId('supplier_id')->constrained();
            $t->enum('status', ['draft','sent','received','closed'])->default('draft');
            $t->foreignId('requested_by')->constrained('users');
            $t->foreignId('authorized_by')->nullable()->constrained('users'); $t->timestamp('authorized_at')->nullable();
            $t->date('order_date'); $t->timestamps();
        });
        Schema::create('purchase_order_items', function (Blueprint $t) {
            $t->id(); $t->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained(); $t->unsignedInteger('quantity');
            $t->unsignedInteger('received_quantity')->default(0); $t->decimal('unit_price', 12, 2); $t->timestamps();
        });
        Schema::create('orders', function (Blueprint $t) {
            $t->id(); $t->string('order_number')->unique();
            $t->string('customer_name'); $t->string('customer_contact')->nullable();
            $t->string('customer_company')->nullable(); $t->text('delivery_address')->nullable();
            $t->enum('status', ['quotation','pending_approval','confirmed','paid','released'])->default('quotation');
            $t->enum('discount_type', ['none','percentage','fixed','special_price'])->default('none');
            $t->decimal('discount_value', 12, 2)->default(0);
            $t->foreignId('created_by')->constrained('users');
            $t->foreignId('approved_by')->nullable()->constrained('users'); $t->timestamp('approved_at')->nullable();
            $t->timestamps();
        });
        Schema::create('order_items', function (Blueprint $t) {
            $t->id(); $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained(); $t->unsignedInteger('quantity');
            $t->decimal('quoted_price', 12, 2); $t->timestamps();
        });
    }
    public function down(): void { foreach (['order_items','orders','purchase_order_items','purchase_orders'] as $x) Schema::dropIfExists($x); }
};
