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
      $table->foreignId('item_id')
            ->nullable()
            ->after('id')
            ->comment('Foreign key to items table')
            ->constrained('items')
            ->nullOnDelete();
      \Log::info('Added item_id foreign key to billings table');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('billings', function (Blueprint $table) {
      $table->dropForeign(['item_id']);
      $table->dropColumn('item_id');
      \Log::info('Dropped item_id foreign key from billings table');
    });
  }
};
