<?php
/*
 * This file is part of the CLIENTXCMS project.
 * This file is the property of the CLIENTXCMS association. Any unauthorized use, reproduction, or download is prohibited.
 * For more information, please consult our support: clientxcms.com/client/support.
 * Year: 2024
 */
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
        Schema::create('proxmox_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->integer('memory');
            $table->integer('disk');
            $table->enum('type', ['lxc', 'qemu']);
            $table->string('node');
            $table->string('storage');
            $table->integer('cores');
            $table->integer('sockets');
            $table->text('templates');
            $table->text('oses');
            $table->unsignedBigInteger('server_id');
            $table->float('rate');
            $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->integer('max_reinstall')->default(0);
            $table->integer('max_backups')->default(0);
            $table->integer('max_snapshots')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proxmox_configs');
    }
};
