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
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedFloat('fees')->default(0);
            $table->string('invoice_number')->nullable()->unique();
        });
        $invoices = \App\Models\Core\Invoice::all();
        $invoice_months = [];
        foreach ($invoices as $invoice) {
            $key = $invoice->created_at->format('Y-m');
            if (!isset($invoice_months[$key])) {
                $invoice_months[$key] = 1;
            } else {
                $invoice_months[$key]++;
            }
            $invoice->fees = 0;
            $invoice->invoice_number = "CTX-" . $key . "-" . str_pad($invoice_months[$key], 4, '0', STR_PAD_LEFT);
            $invoice->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('fees');
            $table->dropColumn('invoice_number');
        });
    }
};
