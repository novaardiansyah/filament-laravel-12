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
    Schema::create('email_logs', function (Blueprint $table) {
      $table->id();
      $table->foreignId('status_id')->constrained('email_log_statuses')->cascadeOnDelete();
      $table->string('name')->nullable();
      $table->string('email');
      $table->string('subject')->nullable();
      $table->text('message')->nullable();
      $table->text('response')->nullable();
      $table->softDeletes();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('email_logs');
  }
};
