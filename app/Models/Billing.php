<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Billing extends Model
{
  use SoftDeletes;
  protected $table = 'billings'; 
  protected $guarded = ['id'];
  
  public function billingStatus(): BelongsTo
  {
    return $this->belongsTo(BillingStatus::class, 'billing_status_id');
  }

  public function paymentAccount(): BelongsTo
  {
    return $this->belongsTo(PaymentAccount::class, 'payment_account_id');
  }

  public function item(): BelongsTo
  {
    return $this->belongsTo(Item::class, 'item_id');
  }

  public function billingPeriod(): BelongsTo
  {
    return $this->belongsTo(BillingPeriod::class, 'billing_period_id');
  }
}
