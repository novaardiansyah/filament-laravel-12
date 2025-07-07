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

  public static function mutateDataPayment(array $data): array
  {
    $data['user_id'] = auth()->id();

    $has_charge         = boolval($data['has_charge'] ?? 0);
    $type_id            = intval($data['type_id'] ?? 2);
    $amount             = intval($data['amount'] ?? 0);
    $payment_account    = PaymentAccount::find($data['payment_account_id']);
    $payment_account_to = PaymentAccount::find($data['payment_account_to_id'] ?? -1);

    if ($type_id == 2) {
      // ? Pemasukan
      $payment_account->deposit += $amount;
      $data['income'] = $amount;
    } else {
      if (!$has_charge && $payment_account->deposit < $amount) {
        return ['status' => false, 'message' => 'Saldo akun kas tidak mencukupi.', 'data' => $data];
      }

      if ($type_id == 1) {
        // ? Pengeluaran
        $payment_account->deposit -= $amount;
        $data['expense'] = $amount;
      } else if ($type_id == 3 || $type_id == 4) {
        // ? Transfer / Tarik Tunai
        if (!$payment_account_to) return ['status' => false, 'message' => 'Akun tujuan tidak valid.', 'data' => $data];
        
        $payment_account->deposit -= $amount;
        $payment_account_to->deposit += $amount;
      } else {
        // ! NO ACTION
        return ['status' => false, 'message' => 'Tipe transaksi tidak valid.', 'data' => $data];
      }
    }
    
    if (!$has_charge) {
      if ($payment_account->isDirty('deposit')) {
        $payment_account->save();
      }
  
      if ($payment_account_to && $payment_account_to->isDirty('deposit')) {
        $payment_account_to->save();
      }
    }

    $data['code'] = getCode(1);

    return ['status' => true, 'message' => 'Data berhasil di mutasi.', 'data' => $data];
  }
}
