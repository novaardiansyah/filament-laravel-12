<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingStatus extends Model
{
  protected $table = 'billing_statuses';
  protected $guarded = ['id'];
}
