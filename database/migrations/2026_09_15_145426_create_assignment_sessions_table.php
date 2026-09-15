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
        Schema::create('assignment_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_code', 32)->unique();
            $table->string('title', 150);
            $table->string('subject', 100);
            $table->text('description')->nullable();
            $table->date('assignment_date');
            $table->string('status', 20)->default('active');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_sessions');
    }
};
