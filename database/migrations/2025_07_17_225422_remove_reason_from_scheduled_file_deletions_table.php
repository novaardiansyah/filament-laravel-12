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
    Schema::table('scheduled_file_deletions', function (Blueprint $table) {
      $table->dropColumn('reason');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('scheduled_file_deletions', function (Blueprint $table) {
      $table->string('reason')->nullable();
    });
  }
};
