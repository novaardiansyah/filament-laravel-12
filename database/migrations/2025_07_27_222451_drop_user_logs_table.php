<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::dropIfExists('user_logs');
    
    // Optionally, you can log the migration action
    \Log::info('User logs table has been dropped successfully.');
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::create('user_logs', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id');
      $table->string('ip_address')->nullable();
      $table->timestamps();

      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });

    // Optionally, you can log the migration rollback action
    \Log::info('User logs table has been recreated successfully.');
  }
};
