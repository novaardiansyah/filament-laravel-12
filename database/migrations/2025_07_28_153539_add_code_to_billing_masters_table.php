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
    Schema::table('billing_masters', function (Blueprint $table) {
      $table->string('code')
        ->nullable()
        ->after('id')
        ->comment('Kode unik untuk tagihan, digunakan untuk identifikasi dan referensi');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('billing_masters', function (Blueprint $table) {
      $table->dropColumn('code');
    });
  }
};
