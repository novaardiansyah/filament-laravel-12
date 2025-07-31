<?php

namespace Database\Seeders;

use App\Models\Generate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GenerateSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $now = now();
    $default = ['created_at' => $now, 'updated_at' => $now];

    $result = [
      ['name' => 'Payment Code', 'alias' => 'payment', 'prefix' => 'TR-', ...$default],
      ['name' => 'Complaint Code', 'alias' => 'complaint', 'prefix' => 'KP-', ...$default],
      ['name' => 'Item SKU', 'alias' => 'item', 'prefix' => 'SKU-', ...$default],
      ['name' => 'Payment Item Code', 'alias' => 'payment_item', 'prefix' => 'PI-', ...$default],
      ['name' => 'Note Code', 'alias' => 'note', 'prefix' => 'NT-', ...$default],
      ['name' => 'Email Template Code', 'alias' => 'email_template', 'prefix' => 'ET-', ...$default],
      ['name' => 'Budgets', 'alias' => 'budget', 'prefix' => 'B-', ...$default],
      ['name' => 'Budget Accounts', 'alias' => 'budget_account', 'prefix' => 'BA-', ...$default],
      ['name' => 'Backup Files', 'alias' => 'backup_file', 'prefix' => 'BAK-', ...$default],
      ['name' => 'Payment Summaries', 'alias' => 'payment_summary', 'prefix' => 'PS-', ...$default],
      ['name' => 'Billing Masters ID', 'alias' => 'billing_master', 'prefix' => 'BM-', ...$default],
      ['name' => 'Billing ID', 'alias' => 'billing', 'prefix' => 'BI-', ...$default],
    ];

    Generate::insert($result);
  }
}
