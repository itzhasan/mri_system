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
        Schema::create('reports', function (Blueprint $table) {
             $table->id();
            $table->string('report_number')->unique();
            $table->foreignId('mri_scan_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->text('findings');
            $table->text('impression');
            $table->text('recommendations')->nullable();
            $table->text('comparison_notes')->nullable();
            $table->enum('status', ['draft', 'final', 'amended'])->default('draft');
            $table->datetime('reported_at')->nullable();
            $table->datetime('finalized_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
