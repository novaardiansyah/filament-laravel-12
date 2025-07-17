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
    Schema::create('scheduled_file_deletions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete();
      $table->string('file_name');
      $table->string('file_path')->nullable(); 
      $table->string('download_url')->nullable();
      $table->boolean('has_been_deleted')->default(false);
      $table->timestamp('scheduled_deletion_time')->nullable();
      $table->string('reason')->nullable();
      $table->softDeletes();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('scheduled_file_deletions');
  }
};
