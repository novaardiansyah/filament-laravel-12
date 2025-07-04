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
      ['name' => 'Payment Code', 'prefix' => 'TR-', ...$default],
      ['name' => 'Complaint Code', 'prefix' => 'KP-', ...$default],
      ['name' => 'Item SKU', 'prefix' => 'SKU-', ...$default],
      ['name' => 'Payment Item Code', 'prefix' => 'PI-', ...$default],
      ['name' => 'Note Code', 'prefix' => 'NT-', ...$default],
      ['name' => 'Email Template Code', 'prefix' => 'ET-', ...$default],
    ];

    Generate::insert($result);
  }
}
