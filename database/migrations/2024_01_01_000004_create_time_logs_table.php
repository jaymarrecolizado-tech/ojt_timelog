<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->enum('log_type', ['IN', 'OUT']);
            $table->enum('log_category', ['AM', 'PM']);
            $table->dateTime('timestamp');
            $table->date('date');
            $table->string('qr_token_hash', 64)->nullable();
            $table->uuid('location_id')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('device_info', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->boolean('is_manual')->default(false);
            $table->boolean('is_flagged')->default(false);
            $table->string('flag_reason', 200)->nullable();
            $table->timestamps();
            
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('set null');
            $table->index(['student_id', 'date']);
            $table->index('date');
            $table->index('timestamp');
            $table->unique(['student_id', 'date', 'log_type', 'log_category'], 'unique_log_entry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_logs');
    }
};
