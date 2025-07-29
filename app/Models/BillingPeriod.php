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

  public static function getName(int $period): string
  {
    return match ($period) {
      self::BILLING_PERIODS['Daily']    => 'Daily',
      self::BILLING_PERIODS['Weekly']   => 'Weekly',
      self::BILLING_PERIODS['Monthly']  => 'Monthly',
      self::BILLING_PERIODS['Annually'] => 'Annually',
      default => 'Unknown Period',
    };
  }
}
