<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsOverview extends BaseWidget
{
  public static function canView(): bool
  {
    return auth()->user()?->can('widget_UserStatsOverview');
  }

  protected function getStats(): array
  {
    $user = new User();
    $activeUsers = $user::whereNull('deleted_at')->count();
    $inactiveUsers = $user::onlyTrashed()->count();

    return [
      Stat::make('Pengguna Aktif', $activeUsers)
        ->description('Total pengguna aktif')
        ->descriptionIcon('heroicon-s-users')
        ->color('info'),
      Stat::make('Pengguna Nonaktif', $inactiveUsers)
        ->description('Total pengguna nonaktif')
        ->descriptionIcon('heroicon-s-user-minus')
        ->color('danger'),
      Stat::make('Total Pengguna', $activeUsers + $inactiveUsers)
        ->description('Jumlah keseluruhan pengguna')
        ->descriptionIcon('heroicon-s-users')
        ->color('primary'),
    ];
  }
}
