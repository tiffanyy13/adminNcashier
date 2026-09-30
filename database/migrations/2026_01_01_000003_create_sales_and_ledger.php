<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('sales', function (Blueprint $t) {
            $t->id(); $t->foreignId('order_id')->unique()->constrained(); // unique => no double payment
            $t->foreignId('processed_by')->constrained('users');
            $t->enum('payment_mode', ['cash','gcash','maya','bank_transfer','check','pdc']);
            $t->decimal('total', 12, 2); $t->decimal('amount_paid', 12, 2); $t->decimal('discount_applied', 12, 2)->default(0);
            $t->string('invoice_number')->unique(); $t->string('official_receipt_number')->unique();
            $t->enum('status', ['pending','paid','check_pending_clearance','cleared','voided'])->default('paid');
            $t->string('reference_number')->nullable(); $t->string('proof_path')->nullable();
            $t->string('check_number')->nullable(); $t->string('bank_name')->nullable();
            $t->date('check_date')->nullable(); $t->date('check_maturity_date')->nullable(); $t->timestamps();
        });
        Schema::create('sale_items', function (Blueprint $t) {
            $t->id(); $t->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained(); $t->unsignedInteger('quantity');
            $t->decimal('unit_price', 12, 2); $t->decimal('line_total', 12, 2); $t->timestamps();
        });
        Schema::create('stock_transactions', function (Blueprint $t) {
            $t->id(); $t->enum('type', ['stock_in','stock_out','release','adjustment']);
            $t->foreignId('product_id')->constrained(); $t->integer('quantity'); // signed delta
            $t->string('reference')->nullable(); $t->string('reason')->nullable();
            $t->enum('flag', ['none','discrepancy','resolved','rejected'])->default('none');
            $t->foreignId('performed_by')->constrained('users'); $t->timestamps();
        });
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->constrained(); $t->string('auditable_type'); $t->unsignedBigInteger('auditable_id');
            $t->string('action'); $t->json('old_values')->nullable(); $t->json('new_values')->nullable(); $t->timestamps();
        });
    }
    public function down(): void { foreach (['audit_logs','stock_transactions','sale_items','sales'] as $x) Schema::dropIfExists($x); }
};
