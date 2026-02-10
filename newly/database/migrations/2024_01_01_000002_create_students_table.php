<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->string('student_id_no')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->string('department');
            $table->string('program');
            $table->string('company')->nullable();
            $table->string('company_address')->nullable();
            $table->string('supervisor_name')->nullable();
            $table->date('ojt_start')->nullable();
            $table->date('ojt_end')->nullable();
            $table->decimal('required_hours', 7, 2)->default(500.00);
            $table->string('contact_no')->nullable();
            $table->enum('status', ['pending', 'active', 'completed', 'inactive'])->default('active');
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
            $table->index('status');
            $table->index('department');
            $table->index('student_id_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
