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
    Schema::table('generates', function (Blueprint $table) {
      $table->string('alias')->nullable()->after('name')->comment('Alias for the generate record');
      \Log::info('Added alias column to generates table');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('generates', function (Blueprint $table) {
      $table->dropColumn('alias');
      \Log::info('Dropped alias column from generates table');
    });
  }
};
