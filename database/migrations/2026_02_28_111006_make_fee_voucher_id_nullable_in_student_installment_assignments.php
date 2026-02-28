<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_installment_assignments', function (Blueprint $table) {
            $table->integer('fee_voucher_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_installment_assignments', function (Blueprint $table) {
            $table->integer('fee_voucher_id')->nullable(false)->change();
        });
    }
};
