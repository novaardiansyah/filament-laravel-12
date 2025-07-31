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
      $table->foreignId('billing_period_id')
        ->nullable()
        ->after('billing_status_id')
        ->constrained('billing_periods')
        ->nullOnDelete();
      \Log::info('Added billing_period_id foreign key to billings table');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('billings', function (Blueprint $table) {
      $table->dropForeign(['billing_period_id']);
      $table->dropColumn('billing_period_id');
      \Log::info('Dropped billing_period_id from billings table');
    });
  }
};
