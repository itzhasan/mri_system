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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('mri_scan_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('report_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['scan_uploaded', 'scan_assigned', 'report_ready', 'urgent_scan']);
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
