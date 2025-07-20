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
    Schema::create('user_logs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
      $table->string('email')->nullable();
      $table->string('ip_address')->nullable();
      $table->string('country')->nullable();
      $table->string('city')->nullable();
      $table->string('region')->nullable();
      $table->string('postal')->nullable();
      $table->string('geolocation')->nullable();
      $table->string('timezone')->nullable();
      $table->string('user_agent')->nullable();
      $table->string('referer')->nullable();
      $table->softDeletes();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('user_logs');
  }
};
