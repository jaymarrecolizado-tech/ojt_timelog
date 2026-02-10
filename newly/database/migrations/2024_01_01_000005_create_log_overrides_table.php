<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_overrides', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('time_log_id')->nullable();
            $table->uuid('student_id');
            $table->uuid('admin_id');
            $table->enum('action', ['CREATE', 'UPDATE', 'DELETE']);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('reason');
            $table->timestamps();
            
            $table->foreign('time_log_id')->references('id')->on('time_logs')->onDelete('set null');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('student_id');
            $table->index('admin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_overrides');
    }
};
