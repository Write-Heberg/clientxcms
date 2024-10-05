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
            $table->float('weekly')->nullable();
            $table->float('setup_weekly')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricings', function (Blueprint $table) {
            $table->dropColumn('weekly');
            $table->dropColumn('setup_weekly');
        });
    }
};
