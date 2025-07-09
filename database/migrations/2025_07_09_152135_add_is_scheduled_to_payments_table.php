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
    Schema::table('payments', function (Blueprint $table) {
      $table->boolean('is_scheduled')->default(false)->after('date')->comment('Indicates if the payment is scheduled for a future date');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('payments', function (Blueprint $table) {
      $table->dropColumn('is_scheduled');
    });
  }
};
