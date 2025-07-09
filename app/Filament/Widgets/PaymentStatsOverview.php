<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentStatsOverview extends BaseWidget
{
  protected function getColumns(): int
  {
    return 3;
  }

  protected function getStats(): array
  {
    $paymentsModel = new Payment();
    $overview      = $paymentsModel->overviewReport();

    $payments    = $overview['payments'];
    $month_str   = $overview['month_str'];
    $total_saldo = $overview['total_saldo'];
    $thisWeek    = $overview['thisWeek'];

    return [
      Stat::make('Pemasukan (' . $month_str . ')', toIndonesianCurrency($payments->all_income))
        ->description(toIndonesianCurrency($payments->daily_income) . ' hari ini')
        ->descriptionIcon('heroicon-m-arrow-trending-up')
        ->descriptionColor('success'),
      Stat::make('Pengeluaran (' . $month_str . ')', toIndonesianCurrency($payments->all_expense))
        ->description(toIndonesianCurrency($payments->daily_expense) . ' hari ini')
        ->descriptionIcon('heroicon-m-arrow-trending-down')
        ->descriptionColor('danger'),
      Stat::make('Pengeluaran Harian', toIndonesianCurrency($payments->avg_daily_expense))
        ->description('Rata-rata pengeluaran harian')
        ->descriptionIcon('heroicon-m-arrow-trending-down')
        ->descriptionColor('warning'),
      Stat::make('Pengeluaran Mingguan', toIndonesianCurrency($payments->avg_weekly_expense ))
        ->description('Rata-rata pengeluaran mingguan')
        ->descriptionIcon('heroicon-m-arrow-trending-down')
        ->descriptionColor('info'),
      Stat::make('Pengeluaran Minggu Ini', toIndonesianCurrency($payments->weekly_expense))
        ->description("Pengeluaran $thisWeek")
        ->descriptionIcon('heroicon-m-arrow-trending-down')
        ->descriptionColor('success'),
      Stat::make('Total Saldo Tersisa', toIndonesianCurrency($total_saldo))
        ->description('Total saldo tersisa pada akun kas')
        ->descriptionIcon('heroicon-m-credit-card')
        ->descriptionColor('primary'),
    ];
  }
}
