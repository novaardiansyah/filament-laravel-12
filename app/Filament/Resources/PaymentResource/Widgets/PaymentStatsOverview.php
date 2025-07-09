<?php

namespace App\Filament\Resources\PaymentResource\Widgets;

use App\Models\Payment;
use App\Models\Setting;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentStatsOverview extends BaseWidget
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

    $payments = $overview['payments'];
    $thisWeek = $overview['thisWeek'];

    return [
      Stat::make('Avg. Pengeluaran Mingguan', toIndonesianCurrency($payments->avg_weekly_expense, showCurrency: self::showPaymentCurrency()))
        ->description('Rata-rata pengeluaran mingguan')
        ->descriptionIcon('heroicon-m-arrow-trending-down')
        ->descriptionColor('info'),
      Stat::make('Pengeluaran Minggu Ini', toIndonesianCurrency($payments->weekly_expense, showCurrency: self::showPaymentCurrency()))
        ->description("Pengeluaran $thisWeek")
        ->descriptionIcon('heroicon-m-arrow-trending-down')
        ->descriptionColor('success'),
      Stat::make('Avg. Pengeluaran Harian', toIndonesianCurrency($payments->avg_daily_expense, showCurrency: self::showPaymentCurrency()))
        ->description(toIndonesianCurrency($payments->daily_expense) . ' pengeluaran hari ini')
        ->descriptionIcon('heroicon-m-arrow-trending-down')
        ->descriptionColor('danger'),
    ];
  }
}
