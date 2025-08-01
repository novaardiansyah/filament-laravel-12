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
    Schema::dropIfExists('payment_summaries');
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::create('payment_summaries', function (Blueprint $table) {
      $table->id();
      $table->string('code')->unique();
      $table->string('period')->nullable();
      $table->double('initial_balance')->default(0);
      $table->double('current_balance')->default(0);
      $table->double('total_income')->default(0);
      $table->double('total_expense')->default(0);
      $table->softDeletes();
      $table->timestamps();
    });
  }
};
