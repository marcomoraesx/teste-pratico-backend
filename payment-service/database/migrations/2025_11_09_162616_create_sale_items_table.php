<?php

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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->double('unity_price')->default(0.0);
            $table->double('total_amount')->default(0.0);
            $table->timestamps();
        });
        DB::unprepared("
            CREATE TRIGGER trg_debit_product_stock_after_insert
            AFTER INSERT ON sale_items
            FOR EACH ROW
            BEGIN
                UPDATE products
                SET stock = stock - NEW.quantity
                WHERE id = NEW.product_id;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        DB::unprepared("DROP TRIGGER IF EXISTS trg_debit_product_stock_after_insert");
    }
};
