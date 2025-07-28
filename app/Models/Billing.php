<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Billing extends Model
{
  use SoftDeletes;
  protected $table = 'billings'; 
  protected $guarded = ['id'];
  
  public function billingMaster()
  {
    return $this->belongsTo(BillingMaster::class, 'billing_master_id');
  }

  public function billingStatus()
  {
    return $this->belongsTo(BillingStatus::class, 'billing_status_id');
  }

  public function paymentAccount()
  {
    return $this->belongsTo(PaymentAccount::class, 'payment_account_id');
  }
}
