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
    Schema::create('billing_masters', function (Blueprint $table) {
      $table->id();
      $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
      $table->foreignId('billing_period_id')->constrained('billing_periods')->onDelete('cascade');
      $table->decimal('amount', 15, 2);
      $table->boolean('is_active')->default(false);
      $table->softDeletes();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('billing_masters');
  }
};
