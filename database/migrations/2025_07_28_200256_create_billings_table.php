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
    Schema::create('billings', function (Blueprint $table) {
      $table->id();
      $table->foreignId('billing_master_id')->constrained('billing_masters')->onDelete('cascade');
      $table->foreignId('billing_status_id')->constrained('billing_statuses')->onDelete('cascade');
      $table->foreignId('payment_account_id')->constrained('payment_accounts')->onDelete('cascade');
      $table->timestamp('billing_date')->default(now());
      $table->timestamp('due_date')->nullable()->default(now()->addDays(30));
      $table->softDeletes();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('billings');
  }
};
