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
        Schema::create('pricings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('currency');
            $table->float('onetime', 10)->nullable();
            $table->float('monthly', 10)->nullable();
            $table->float('quarterly', 10)->nullable();
            $table->float('semiannually', 10)->nullable();
            $table->float('annually', 10)->nullable();
            $table->float('biennially', 10)->nullable();
            $table->float('triennially', 10)->nullable();
            $table->float('setup_onetime', 10)->nullable();
            $table->float('setup_monthly', 10)->nullable();
            $table->float('setup_quarterly', 10)->nullable();
            $table->float('setup_semiannually', 10)->nullable();
            $table->float('setup_annually', 10)->nullable();
            $table->float('setup_biennially', 10)->nullable();
            $table->float('setup_triennially', 10)->nullable();
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricings');
    }
};
