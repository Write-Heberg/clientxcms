
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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('firstname');
            $table->string('lastname');
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('address');
            $table->string('address2')->nullable();
            $table->string('city');
            $table->string('country');
            $table->string('region');
            $table->string('zipcode');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->unsignedFloat('balance')->default(0);
            $table->timestamp('last_login')->nullable();
            $table->ipAddress('last_ip')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->uuid('confirmation_token')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->string('totp_secret')->nullable();
            $table->boolean('dark_mode')->default(false);
            $table->text('notes')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
