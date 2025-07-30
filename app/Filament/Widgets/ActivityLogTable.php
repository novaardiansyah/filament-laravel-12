<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ActivityLogTable extends BaseWidget
{
  protected static ?string $heading = 'Log Aktivitas Terakhir';
  protected int|string|array $columnSpan = 'full';

  protected array $customColors = [];

  public function __construct()
  {
    $this->customColors = collect(config('filament-logger.custom', []))
      ->mapWithKeys(fn ($item) => [$item['log_name'] => $item['color']])
      ->toArray();
  }

  public static function canView(): bool
  {
    return auth()->user()?->can('widget_ActivityLogTable');
  }

  public function table(Table $table): Table
  {
    return $table
      ->query(
        ActivityLog::query()
          ->limit(10)
      )
      ->columns([
        Tables\Columns\TextColumn::make('index')
          ->rowIndex()
          ->label('#'),
        Tables\Columns\TextColumn::make('log_name')
          ->label('Jenis')
          ->badge()
          ->color(fn(string $state): string => match ($state) {
            'Resource'     => config('filament-logger.resources.color'),
            'Access'       => config('filament-logger.access.color'),
            'Notification' => config('filament-logger.notifications.color'),
            'Model'        => config('filament-logger.models.color'),
            default        => $this->customColors[$state] ?? 'primary',
          })
          ->sortable(),
        Tables\Columns\TextColumn::make('event')
          ->label('Kegiatan')
          ->sortable(),
        Tables\Columns\TextColumn::make('description')
          ->label('Deskripsi')
          ->wrap()
          ->limit(80)
          ->sortable()
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('created_at')
          ->label('Tercatat pada')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->toggleable()
      ])
      ->defaultSort('created_at', 'desc')
      ->recordAction(null)
      ->recordUrl(function ($record) {
        if (auth()->user()?->can('view_activity')) {
          return url("/admin/activity-logs?tableAction=view&tableActionRecord={$record->id}");
        }
        return null;
      })
      ->paginated(false);
  }
}
