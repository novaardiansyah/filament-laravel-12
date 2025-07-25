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
    Schema::create('contact_messages', function (Blueprint $table) {
      $table->id();
      $table->string('name')->nullable();
      $table->string('email')->nullable();
      $table->string('subject')->nullable();
      $table->text('message')->nullable();
      $table->boolean('is_read')->default(false);
      $table->string('ip_address')->nullable();
      $table->string('user_agent')->nullable();
      $table->string('path')->nullable();
      $table->string('url')->nullable();
      $table->string('full_url')->nullable();
      $table->softDeletes();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('contact_messages');
  }
};
