<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentSummary extends Model
{
  use SoftDeletes;
  protected $guarded = ['id'];

  public static function getSync(string $month, string $year): array
  {
    $month = intval($month);
    $year  = intval($year);

    if (!$month || !$year) return [];
    
    $current_balance = PaymentAccount::sum('deposit');
    $expense         = Payment::where('type_id', 1)->whereMonth('date', $month)->whereYear('date', $year)->sum('expense');
    $income          = Payment::where('type_id', 2)->whereMonth('date', $month)->whereYear('date', $year)->sum('income');
    $intial_balance  = $current_balance + $expense - $income;

    return [
      'initial_balance' => (int) $intial_balance,
      'total_income'    => (int) $income,
      'total_expense'   => (int) $expense,
      'current_balance' => (int) $current_balance,
    ];
  }

  public static function setSync(string $period): array
  {
    $record = self::where('period', $period)->first();

    $month = substr($period, 0, 2);
    $year  = (int) substr($period, 2, 4);
    $sync  = self::getSync($month, $year);
    
    if (!$sync) return $sync;
    if (!$record) return $sync;

    $record->update($sync);
    $record->refresh();

    return $record->toArray();
  }
}
