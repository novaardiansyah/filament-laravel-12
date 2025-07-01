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
    Schema::table('settings', function (Blueprint $table) {
      $table->boolean('has_options')->default(false)->after('value');
      $table->text('options')->nullable()->after('has_options');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('settings', function (Blueprint $table) {
      $table->dropColumn('has_options');
      $table->dropColumn('options');
    });
  }
};
