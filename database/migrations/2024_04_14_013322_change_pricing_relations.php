<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pricings', function (Blueprint $table) {
            $table->renameColumn('product_id', 'related_id');
            $table->string('related_type')->after('product_id')->default('product');
            $table->dropForeign(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricings', function (Blueprint $table) {
            $table->renameColumn('related_id', 'product_id');
            $table->dropColumn('related_type');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
};
