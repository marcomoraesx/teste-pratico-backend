<?php

use App\Enums\PaymentMethod;
use App\Enums\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->cascadeOnDelete();
            $table->string('payment_method')->default(PaymentMethod::MONEY->value);
            $table->double('total_amount')->default(0.0);
            $table->double('discount_amount')->default(0.0);
            $table->double('net_amount')->default(0.0);
            $table->string('status')->default(Status::PENDING->value);
            $table->timestamps();
        });
        DB::unprepared("
            CREATE TRIGGER trg_refund_product_stock_after_update
            AFTER UPDATE ON sales
            FOR EACH ROW
            BEGIN
                IF (OLD.status <> NEW.status)
                   AND (NEW.status IN ('CANCELED', 'REFUNDED')) THEN
                    UPDATE products p
                    JOIN sale_items si ON si.product_id = p.id
                    SET p.stock = p.stock + si.quantity
                    WHERE si.sale_id = NEW.id;
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
        DB::unprepared("DROP TRIGGER IF EXISTS trg_refund_product_stock_after_update");
    }
};
