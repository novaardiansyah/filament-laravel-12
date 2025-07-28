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
      ['name' => 'Daily', ...$default],
      ['name' => 'Weekly', ...$default],
      ['name' => 'Monthly', ...$default],
      ['name' => 'Annually', ...$default],
    ];

    BillingPeriod::insert($result);
  }
}
