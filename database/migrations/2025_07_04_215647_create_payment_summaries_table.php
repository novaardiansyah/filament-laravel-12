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
    Schema::create('payment_summaries', function (Blueprint $table) {
      $table->id();
      $table->string('code')->unique();
      $table->string('period')->unique();
      $table->bigInteger('initial_balance')->default(0);
      $table->bigInteger('current_balance')->default(0);
      $table->bigInteger('total_income')->default(0);
      $table->bigInteger('total_expense')->default(0);
      $table->softDeletes();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('payment_summaries');
  }
};
