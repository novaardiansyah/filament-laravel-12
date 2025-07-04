<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
  use SoftDeletes;
  protected $guarded = ['id'];

  protected $casts = [
    'attachments' => 'array',
    'has_items'   => 'boolean'
  ];

  public function payment_account(): BelongsTo
  {
    return $this->belongsTo(PaymentAccount::class, 'payment_account_id');
  }

  public function payment_account_to(): BelongsTo
  {
    return $this->belongsTo(PaymentAccount::class, 'payment_account_id');
  }

  public function payment_type(): BelongsTo
  {
    return $this->belongsTo(PaymentType::class, 'type_id');
  }

  public function items(): BelongsToMany
  {
    return $this->belongsToMany(Item::class, 'payment_item')->withPivot(['item_code', 'quantity', 'price', 'total'])->withTimestamps();
  }
}
