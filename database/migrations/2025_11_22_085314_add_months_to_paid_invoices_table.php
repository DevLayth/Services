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
        Schema::table('paid_invoices', function (Blueprint $table) {
            $table->integer('months')->default(1)->after('amount_one_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paid_invoices', function (Blueprint $table) {
            $table->dropColumn('months');
        });
    }
};
