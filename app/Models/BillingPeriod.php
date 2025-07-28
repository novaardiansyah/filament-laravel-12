<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingPeriod extends Model
{
  protected $table = 'billing_periods';
  protected $guarded = ['id'];
  public const BILLING_PERIODS = [
    'Daily'    => 1,
    'Weekly'   => 2,
    'Monthly'  => 3,
    'Annually' => 4,
  ];
}
