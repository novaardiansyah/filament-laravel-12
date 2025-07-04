<?php

namespace Database\Seeders;

use App\Models\PaymentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentTypeSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $now = now();
    $default = ['created_at' => $now, 'updated_at' => $now];

    $result = [
      ['name' => 'Pengeluaran', ...$default],
      ['name' => 'Pemasukan', ...$default],
      ['name' => 'Transfer', ...$default],
      ['name' => 'Tarik Tunai', ...$default],
    ];

    PaymentType::insert($result);
  }
}
