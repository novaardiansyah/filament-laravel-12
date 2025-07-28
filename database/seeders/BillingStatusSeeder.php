<?php

namespace Database\Seeders;

use App\Models\BillingStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BillingStatusSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $now = now();
    $default = ['created_at' => $now, 'updated_at' => $now];

    $result = [
      ['name' => 'Pending', ...$default],
      ['name' => 'Paid', ...$default],
      ['name' => 'Failed', ...$default],
    ];

    BillingStatus::insert($result);
  }
}
