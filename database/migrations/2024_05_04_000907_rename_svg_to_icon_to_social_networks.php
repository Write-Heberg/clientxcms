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
        Schema::table('theme_socialnetworks', function (Blueprint $table) {
            $table->renameColumn('svg', 'icon');
            $table->addColumn('boolean', 'hidden', ['default' => false]);
        });
        DB::table('theme_socialnetworks')->truncate();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('theme_socialnetworks', function (Blueprint $table) {
            $table->renameColumn('icon', 'svg');
            $table->dropColumn('hidden');
        });
    }
};
