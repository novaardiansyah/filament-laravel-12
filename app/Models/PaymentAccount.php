<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAccount extends Model
{
  protected $guarded = ['id'];

  public const PERMATA_BANK = 1;
  public const DANA = 2;
  public const JAGO_BANK = 3;
  public const TUNAI = 4;
  public const GOPAY = 5;
  public const OVO = 6;
  public const SEA_BANK = 7;

  public const PENGELUARAN = 1;
  public const PEMASUKAN = 2;
  public const TRANSFER = 3;
  public const TARIK_TUNAI = 4;
  
  public function getPaymentAccountNameAttribute(): string
  {
    return $this->name ?? 'Unknown Payment Account';
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}
