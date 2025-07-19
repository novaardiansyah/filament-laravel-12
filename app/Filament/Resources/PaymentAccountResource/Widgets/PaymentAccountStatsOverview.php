<?php

namespace App\Filament\Resources\PaymentAccountResource\Widgets;

use App\Models\Payment;
use App\Models\Setting;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentAccountStatsOverview extends BaseWidget
{
  protected function getColumns(): int
  {
    return 3;
  }

  protected static function showPaymentCurrency(): bool
  {
    static $condition;

    if ($condition === null) {
      $condition = Setting::showPaymentCurrency();
    }

    return $condition;
  }

  protected function getStats(): array
  {
    $paymentsModel = new Payment();
    $overview      = $paymentsModel->overviewReport();

    $payments    = $overview['payments'];
    $month_str   = $overview['month_str'];
    $total_saldo = $overview['total_saldo'];

    $scheduled_expense = $payments->scheduled_expense ?? 0;
    $scheduled_income = $payments->scheduled_income ?? 0;

    $totalAfterScheduledExpense = $total_saldo + $scheduled_income - $scheduled_expense;
    
    return [
      Stat::make('Pemasukan (' . $month_str . ')', toIndonesianCurrency($payments->all_income, showCurrency: self::showPaymentCurrency()))
        ->description(toIndonesianCurrency($payments->daily_income) . ' hari ini')
        ->descriptionIcon('heroicon-m-arrow-trending-up')
        ->descriptionColor('success'),
      Stat::make('Pengeluaran (' . $month_str . ')', toIndonesianCurrency($payments->all_expense, showCurrency: self::showPaymentCurrency()))
        ->description(toIndonesianCurrency($payments->daily_expense) . ' hari ini')
        ->descriptionIcon('heroicon-m-arrow-trending-down')
        ->descriptionColor('danger'),
      Stat::make('Total Saldo Tersisa (' . $month_str . ')', toIndonesianCurrency($total_saldo, showCurrency: self::showPaymentCurrency()))
        ->description(toIndonesianCurrency($totalAfterScheduledExpense, showCurrency: self::showPaymentCurrency()) . ' sisa terjadwal')
        ->descriptionIcon('heroicon-m-credit-card')
        ->descriptionColor('primary'),
    ];
  }
}
