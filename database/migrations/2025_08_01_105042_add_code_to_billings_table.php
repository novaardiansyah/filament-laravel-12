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
    Schema::table('billings', function (Blueprint $table) {
      $table->string('code')->unique()->nullable()->after('id');
      \Log::info('Adding code column to billings table');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('billings', function (Blueprint $table) {
      $table->dropColumn('code');
      \Log::info('Dropping code column from billings table');
    });
  }
};
