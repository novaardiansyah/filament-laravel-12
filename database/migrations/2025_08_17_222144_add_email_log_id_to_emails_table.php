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
    Schema::table('emails', function (Blueprint $table) {
      $table->foreignId('email_log_id')
        ->after('id')
        ->nullable()
        ->constrained('email_logs');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('emails', function (Blueprint $table) {
      $table->dropForeign(['email_log_id']);
      $table->dropColumn('email_log_id');
    });
  }
};
