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
      $table->double('amount')
        ->default(0)
        ->after('payment_account_id')
        ->comment('Jumlah tagihan dalam satuan mata uang yang ditentukan');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('billings', function (Blueprint $table) {
      $table->dropColumn('amount');
    });
  }
};
