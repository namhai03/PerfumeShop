<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('voucher_id');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type');
            $table->unsignedInteger('file_size');
            $table->timestamps();

            $table->foreign('voucher_id')->references('id')->on('cash_vouchers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_attachments');
    }
};
