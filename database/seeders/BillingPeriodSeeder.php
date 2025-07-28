<?php

namespace Database\Seeders;

use App\Models\BillingPeriod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BillingPeriodSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $now = now();
    $default = ['created_at' => $now, 'updated_at' => $now];

    $result = [
      ['name' => 'Daily', 'days' => 1, ...$default],
      ['name' => 'Weekly', 'days' => 7, ...$default],
      ['name' => 'Monthly', 'days' => 30, ...$default],
      ['name' => 'Annually', 'days' => 365, ...$default],
    ];

    BillingPeriod::insert($result);
  }
}
