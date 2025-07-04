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
    Schema::create('payments', function (Blueprint $table) {
      $table->id();
      $table->string('code')->nullable();
      $table->foreignId('type_id')->constrained('payment_types')->cascadeOnDelete();
      $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
      $table->foreignId('payment_account_id')->constrained('payment_accounts')->cascadeOnDelete();
      $table->foreignId('payment_account_to_id')->nullable()->constrained('payment_accounts')->onDelete('cascade');
      $table->text('name')->nullable();
      $table->bigInteger('amount')->default(0);
      $table->bigInteger('income')->nullable();
      $table->bigInteger('expense')->nullable();
      $table->bigInteger('last_balance')->nullable();
      $table->boolean('has_items')->default(false);
      $table->date('date')->nullable();
      $table->text('attachments')->nullable();
      $table->softDeletes();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('payments');
  }
};
