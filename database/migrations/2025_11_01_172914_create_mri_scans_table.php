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
        Schema::create('mri_scans', function (Blueprint $table) {
             $table->id();
            $table->string('scan_number')->unique();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('mri_technician_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referring_doctor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_doctor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('body_part', ['brain', 'spine', 'chest', 'abdomen', 'pelvis', 'shoulder', 'knee', 'hip', 'ankle', 'other']);
            $table->string('scan_type');
            $table->text('clinical_indication');
            $table->datetime('scan_date');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'reported', 'cancelled'])->default('pending');
            $table->text('technician_notes')->nullable();
            $table->integer('number_of_images')->default(0);
            $table->enum('priority', ['routine', 'urgent', 'stat'])->default('routine');
            $table->boolean('contrast_used')->default(false);
            $table->string('contrast_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mri_scans');
    }
};
