<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Billing extends Model
{
  use SoftDeletes;
  protected $table = 'billings'; 
  protected $guarded = ['id'];

  protected $casts = [
    'billing_date' => 'date',
  ];
  
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

  public function payment(): BelongsTo
  {
    return $this->belongsTo(Payment::class, 'payment_id');
  }

  public function afterSuccessPaid(array $data = []): void
  {
    $data['billing_status_id'] = $data['billing_status_id'] ?? BillingStatus::PAID;
    $this->update($data);

    if ($data['billing_status_id'] === BillingStatus::PAID) {
      $periodDays = $this->billingPeriod->days ?? 7;
  
      $newRecord = $this->replicate();
      $newRecord->billing_status_id = BillingStatus::PENDING;
      $newRecord->billing_date = Carbon::parse($this->billing_date)->addDays($periodDays);
      $newRecord->code = getCode('billing');
      $newRecord->payment_id = null;
      $newRecord->save();
    }
  }
}
