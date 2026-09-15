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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_session_id')->constrained()->cascadeOnDelete();
            $table->string('submission_code', 16)->unique();
            $table->string('nama_lengkap', 150);
            $table->string('kelas', 50);
            $table->string('jurusan', 100);
            $table->string('catatan', 1000)->nullable();
            $table->string('status', 20)->default('processing');
            $table->timestamps();

            $table->index('assignment_session_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
