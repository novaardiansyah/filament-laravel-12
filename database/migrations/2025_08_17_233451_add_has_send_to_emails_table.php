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
    Schema::table('emails', function (Blueprint $table) {
      $table->boolean('has_send')->default(false)->after('recipient');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('emails', function (Blueprint $table) {
      $table->dropColumn('has_send');
    });
  }
};
