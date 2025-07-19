<?php

namespace App\Models;

use App\Observers\PaymentObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([PaymentObserver::class])]
class Payment extends Model
{
  use SoftDeletes;
  protected $guarded = ['id'];

  protected $casts = [
    'attachments'  => 'array',
    'has_items'    => 'boolean',
    'is_scheduled' => 'boolean',
  ];

  public function payment_account(): BelongsTo
  {
    return $this->belongsTo(PaymentAccount::class, 'payment_account_id');
  }

  public function payment_account_to(): BelongsTo
  {
    return $this->belongsTo(PaymentAccount::class, 'payment_account_to_id');
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
    $is_scheduled       = boolval($data['is_scheduled'] ?? 0);
    $type_id            = intval($data['type_id'] ?? 2);
    $amount             = intval($data['amount'] ?? 0);
    $payment_account    = PaymentAccount::find($data['payment_account_id']);
    $payment_account_to = PaymentAccount::find($data['payment_account_to_id'] ?? -1);

    if ($is_scheduled) $has_charge = true;

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

  public function overviewReport(): array
  {
    // Mengambil tanggal awal dan akhir bulan ini
    $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
    $endDate   = Carbon::now()->format('Y-m-d');
    $endMonth  = Carbon::now()->endOfMonth()->format('Y-m-d');

    // Mengambil tanggal hari ini
    $today = Carbon::now()->format('Y-m-d');

    // Mengambil tanggal awal minggu ini (Senin)
    $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
    // Mengambil tanggal akhir minggu ini (Minggu)
    $endOfWeek = Carbon::now()->endOfWeek()->format('Y-m-d');

    // Jika awal minggu ini berada di bulan sebelumnya, setel ke awal bulan ini
    if (Carbon::parse($startOfWeek)->month != Carbon::now()->month) {
      $startOfWeek = $startDate;
    }

    // Jika akhir minggu ini berada di bulan berikutnya, setel ke akhir bulan ini
    if (Carbon::parse($endOfWeek)->month != Carbon::now()->month) {
      $endOfWeek = $endDate;
    }
    
    // Mengambil bulan dan tahun saat ini dalam format terjemahan
    $month_str = Carbon::now()->translatedFormat('F Y');

    // Menghitung jumlah hari yang telah berlalu dalam bulan ini sampai hari ini
    $daysElapsed = Carbon::now()->day;

    // Menghitung jumlah hari dalam bulan ini
    $daysInMonth = Carbon::now()->daysInMonth;

    // Menghitung jumlah minggu dalam bulan ini
    $weeksInMonth = intval(ceil($daysInMonth / 7));

    $payments = Payment::selectRaw('
      SUM(CASE WHEN type_id = 1 AND date BETWEEN ? AND ? THEN expense ELSE 0 END) AS all_expense,
      SUM(CASE WHEN type_id = 2 AND date BETWEEN ? AND ? THEN income ELSE 0 END) AS all_income,
      SUM(CASE WHEN type_id = 1 AND date = ? THEN expense ELSE 0 END) AS daily_expense,
      SUM(CASE WHEN type_id = 2 AND date = ? THEN income ELSE 0 END) AS daily_income,
      SUM(CASE WHEN type_id = 1 AND date BETWEEN ? AND ? THEN expense ELSE 0 END) / ? AS avg_daily_expense,
      SUM(CASE WHEN type_id = 1 AND date BETWEEN ? AND ? THEN expense ELSE 0 END) / ? AS avg_weekly_expense,
      SUM(CASE WHEN type_id = 1 AND date BETWEEN ? AND ? THEN expense ELSE 0 END) AS weekly_expense,
      SUM(CASE WHEN type_id = 1 AND is_scheduled = 1 AND date BETWEEN ? AND ? THEN expense ELSE 0 END) AS scheduled_expense,
      SUM(CASE WHEN type_id = 2 AND is_scheduled = 1 AND date BETWEEN ? AND ? THEN income ELSE 0 END) AS scheduled_income
    ', [
      $startDate, $endDate, // All expense range
      $startDate, $endDate, // All income range
      $today,               // Daily expense
      $today,               // Daily income
      $startDate, $endDate, $daysElapsed,  // Avg daily expense
      $startDate, $endDate, $weeksInMonth, // Avg weekly expense
      $startOfWeek, $endOfWeek, // Weekly expense
      $startDate, $endMonth, // Scheduled expense
      $startDate, $endMonth // Scheduled income
    ])->first();
    
    $total_saldo = PaymentAccount::sum('deposit');

    $thisWeek = Carbon::parse($startOfWeek)->translatedFormat('d') . '-' .  Carbon::parse($endOfWeek)->translatedFormat('d M Y');

    return [
      'month_str'   => $month_str,
      'thisWeek'    => $thisWeek,
      'payments'    => $payments,
      'total_saldo' => $total_saldo,
    ];
  }
}
