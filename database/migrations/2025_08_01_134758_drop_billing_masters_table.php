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
    Schema::dropIfExists('billing_masters');
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::create('billing_masters', function (Blueprint $table) {
      $table->id();
      $table->string('code')->unique();
      $table->foreignId('item_id')->constrained('items')->nullOnDelete();
      $table->foreignId('billing_period_id')->constrained('billing_periods')->nullOnDelete();
      $table->double('amount')->default(0);
      $table->boolean('is_active')->default(true);
      $table->softDeletes();
      $table->timestamps();
    });
  }
};
