<?php

namespace Database\Seeders;

use App\Models\EmailLogStatus;
use Illuminate\Database\Seeder;

class EmailLogStatusSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $now = now();
    $default = ['created_at' => $now, 'updated_at' => $now];

    $result = [
      ['name' => 'draft', 'description' => 'Email is saved as a draft', ...$default],
      ['name' => 'pending', 'description' => 'Email is pending to be sent', ...$default],
      ['name' => 'success', 'description' => 'Email has been sent successfully', ...$default],
      ['name' => 'failed', 'description' => 'Email sending failed', ...$default],
    ];

    EmailLogStatus::insert($result);
  }
}
