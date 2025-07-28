<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingStatus extends Model
{
  protected $table = 'billing_statuses';
  protected $guarded = ['id'];

  public const PENDING = 1;
  public const PAID = 2;
  public const FAILED = 3;

  public static function getName(int $status): string
  {
    return match ($status) {
      self::PENDING => 'Pending',
      self::PAID    => 'Paid',
      self::FAILED  => 'Failed',
      default       => 'Unknown Status',
    };
  }
}
