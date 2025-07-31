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
      $table->dropForeign(['billing_master_id']);
      $table->dropColumn('billing_master_id');
      \Log::info('Dropped billing_master_id from billings table');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('billings', function (Blueprint $table) {
      $table->foreignId('billing_master_id')
            ->nullable()
            ->after('item_id')
            ->constrained('billing_masters')
            ->nullOnDelete();
      \Log::info('Added billing_master_id back to billings table');
    });
  }
};
