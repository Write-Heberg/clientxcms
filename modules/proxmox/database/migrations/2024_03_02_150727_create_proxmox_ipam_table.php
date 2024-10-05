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
        Schema::create('proxmox_ipam', function (Blueprint $table) {
            $table->id();
            $table->string('ip');
            $table->string('gateway');
            $table->string('netmask');
            $table->string('bridge')->nullable();
            $table->integer('mtu')->default(1500)->nullable();
            $table->string('mac')->nullable();
            $table->string('ipv6')->nullable();
            $table->string('ipv6_gateway')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedBigInteger('service_id')->nullable();
            $table->foreign('service_id')->references('id')->on('services')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->enum('status', ['used', 'unavailable', 'available'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proxmox_ipam');
    }
};
