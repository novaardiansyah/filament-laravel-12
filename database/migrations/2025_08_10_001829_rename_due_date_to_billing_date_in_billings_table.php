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
      //  $table->renameColumn('due_date', 'billing_date');
      $table->timestamp('billing_date')->nullable()->after('due_date');
      $table->dropColumn('due_date');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('billings', function (Blueprint $table) {
      $table->timestamp('due_date')->nullable()->after('billing_date');
      $table->removeColumn('billing_date');
    });
  }
};
