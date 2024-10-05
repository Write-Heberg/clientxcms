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
        Schema::table('theme_footerlinks', function (Blueprint $table) {
            $table->rename('theme_menu_links');
            $table->string('type')->change();
        });
        DB::statement("ALTER TABLE theme_menu_links MODIFY COLUMN type ENUM('list', 'bottom', 'front') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_links', function (Blueprint $table) {
            $table->rename('theme_footerlinks');
        });
    }
};
