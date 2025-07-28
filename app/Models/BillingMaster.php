<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingMaster extends Model
{
  use SoftDeletes;

  protected $table = 'billing_masters';
  protected $guarded = ['id'];

  protected $casts = [
    'is_active' => 'boolean',
  ];

  public function item(): BelongsTo
  {
    return $this->belongsTo(Item::class, 'item_id');
  }

  public function billingPeriod(): BelongsTo
  {
    return $this->belongsTo(BillingPeriod::class, 'billing_period_id');
  }
}
