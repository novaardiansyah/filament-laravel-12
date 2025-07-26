<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use App\Filament\Resources\ActivityLogResource\RelationManagers;
use App\Models\ActivityLog;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Models\Activity as ActivityModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Filament\Tables\Filters;
use Filament\Facades\Filament;

class ActivityLogResource extends Resource
{
  protected static ?string $model = ActivityLog::class;

  public static function getCluster(): ?string
  {
    return config('filament-logger.resources.cluster');
  }

  public static function getLabel(): string
  {
    return __('filament-logger::filament-logger.resource.label.log');
  }

  public static function getPluralLabel(): string
  {
    return __('filament-logger::filament-logger.resource.label.logs');
  }

  public static function getNavigationGroup(): ?string
  {
    return __(config('filament-logger.resources.navigation_group', 'Settings'));
  }

  public static function getNavigationLabel(): string
  {
    return __('filament-logger::filament-logger.nav.log.label');
  }

  public static function getNavigationIcon(): string
  {
    return __('filament-logger::filament-logger.nav.log.icon');
  }

  public static function isScopedToTenant(): bool
  {
    return config('filament-logger.scoped_to_tenant', true);
  }

  public static function getNavigationSort(): ?int
  {
    return config('filament-logger.navigation_sort', null);
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make([
          Forms\Components\TextInput::make('causer_id')
            ->afterStateHydrated(function ($component, ?Model $record) {
              /** @phpstan-ignore-next-line */
              return $component->state($record->causer?->name);
            })
            ->label(__('filament-logger::filament-logger.resource.label.user')),

          Forms\Components\TextInput::make('subject_type')
            ->afterStateHydrated(function ($component, ?Model $record, $state) {
              /** @var Activity&ActivityModel $record */
              return $state ? $component->state(Str::of($state)->afterLast('\\')->headline() . ' # ' . $record->subject_id) : '-';
            })
            ->label(__('filament-logger::filament-logger.resource.label.subject')),

          Forms\Components\TextInput::make('log_name')
            ->afterStateHydrated(function (?Model $record): string {
              /** @var Activity&ActivityModel $record */
              return $record->log_name ? ucwords($record->log_name) : '-';
            })
            ->label(__('filament-logger::filament-logger.resource.label.type')),

          Forms\Components\TextInput::make('event')
            ->afterStateHydrated(function (?Model $record): string {
              /** @phpstan-ignore-next-line */
              return $record?->event ? ucwords($record?->event) : '-';
            })
            ->label(__('filament-logger::filament-logger.resource.label.event')),
          
          Forms\Components\TextInput::make('created_at')
            ->label(__('filament-logger::filament-logger.resource.label.logged_at'))
            // ->displayFormat(config('filament-logger.datetime_format', 'd/m/Y H:i:s')),
            ->formatStateUsing(function ($state) {
              return $state ? carbonTranslatedFormat($state, config('filament-logger.datetime_format', 'd/m/Y H:i:s')) : '-';
            }),

          Forms\Components\Textarea::make('description')
            ->label(__('filament-logger::filament-logger.resource.label.description'))
            ->rows(2)
            ->columnSpan('full'),
        ])
        ->description('Informasi log aktivitas')
        ->columns(3)
        ->columnSpan('4')
        ->collapsible(),

        Forms\Components\Section::make()
          ->schema(function (?Model $record) {
            /** @var Activity&ActivityModel $record */
            $properties = $record->properties->except(['attributes', 'old']);

            $schema = [];

            if ($properties->count()) {
              $schema[] = Forms\Components\KeyValue::make('properties')
                ->label(__('filament-logger::filament-logger.resource.label.properties'))
                ->columnSpan('full');
            }

            if ($old = $record->properties->get('old')) {
              $schema[] = Forms\Components\KeyValue::make('old')
                ->afterStateHydrated(fn(Forms\Components\KeyValue $component) => $component->state($old))
                ->label(__('filament-logger::filament-logger.resource.label.old'));
            }

            if ($attributes = $record->properties->get('attributes')) {
              $schema[] = Forms\Components\KeyValue::make('attributes')
                ->afterStateHydrated(fn(Forms\Components\KeyValue $component) => $component->state($attributes))
                ->label(__('filament-logger::filament-logger.resource.label.new'));
            }

            return $schema;
          })
          ->description('Informasi perubahan properti')
          ->visible(fn($record) => $record->properties?->count() > 0)
          ->collapsible(),
        
        Forms\Components\Section::make([
          Forms\Components\TextInput::make('email')
            ->label('Email'),
          Forms\Components\TextInput::make('ip_address')
            ->label('Alamat IP'),
          Forms\Components\TextInput::make('timezone')
            ->label('Zona Waktu'),
          Forms\Components\TextInput::make('country')
            ->label('Negara'),
          Forms\Components\TextInput::make('city')
            ->label('Kota/Kabupaten'),
          Forms\Components\TextInput::make('region')
            ->label('Wilayah/Provinsi'),
          Forms\Components\TextInput::make('postal')
            ->label('Kode Pos'),
          Forms\Components\TextInput::make(name: 'geolocation')
            ->label('Geolokasi')
            ->afterStateHydrated(function ($state, Forms\Set $set): void {
              $state = $state ? explode(',', $state) : null;
              if ($state) {
                $set('location', [
                  'lat' => $state[0] ?? null,
                  'lng' => $state[1] ?? null,
                ]);
              }
            }),
          Forms\Components\Textarea::make('user_agent')
            ->label('Perangkat')
            ->rows(3)
            ->columnSpanFull(),
          Map::make('location')
            ->visible(fn($get) => $get('geolocation'))
            ->label('Peta Lokasi Pengguna')
            ->defaultLocation(latitude: -6.2886, longitude: 106.7179)
            ->draggable(true)
            ->clickable(true)
            ->zoom(15)
            ->minZoom(0)
            ->maxZoom(28)
            ->tilesUrl("https://tile.openstreetmap.de/{z}/{x}/{y}.png")
            ->detectRetina(true)
            ->extraStyles([
              'border-radius: 12px',
              'min-height: 400px',
            ])
            ->columnSpanFull(),
        ])
        ->columns(3)
        ->description('Infomasi Perangkat')
      ])
      ->columns(4);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('index')
          ->rowIndex()
          ->label('#'),
        Tables\Columns\TextColumn::make('id')
          ->label('ID Aktivitas')
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('log_name')
          ->badge()
          ->colors(static::getLogNameColors())
          ->label(__('filament-logger::filament-logger.resource.label.type'))
          ->formatStateUsing(fn($state) => ucwords($state))
          ->sortable(),
        Tables\Columns\TextColumn::make('event')
          ->label(__('filament-logger::filament-logger.resource.label.event'))
          ->toggleable()
          ->sortable(),
        Tables\Columns\TextColumn::make('description')
          ->label(__('filament-logger::filament-logger.resource.label.description'))
          ->toggleable()
          ->wrap()
          ->limit(80),
        Tables\Columns\TextColumn::make('subject_type')
          ->label(__('filament-logger::filament-logger.resource.label.subject'))
          ->formatStateUsing(function ($state, Model $record) {
            /** @var Activity&ActivityModel $record */
            if (!$state) {
              return '-';
            }
            return Str::of($state)->afterLast('\\')->headline() . ' # ' . $record->subject_id;
          })
          ->toggleable(isToggledHiddenByDefault: true),

        Tables\Columns\TextColumn::make('causer.name')
          ->label(__('filament-logger::filament-logger.resource.label.user'))
          ->toggleable(isToggledHiddenByDefault: true),

        Tables\Columns\TextColumn::make('created_at')
          ->label(__('filament-logger::filament-logger.resource.label.logged_at'))
          ->dateTime(config('filament-logger.datetime_format', 'd/m/Y H:i:s'), config('app.timezone'))
          ->sortable(),
      ])
      // ->recordAction(null)
      ->recordUrl(null)
      ->defaultSort('created_at', 'desc')
      ->filters([
        Filters\Filter::make('id')
          ->form([
            Forms\Components\TextInput::make('id')
              ->label('ID Aktivitas')
              ->numeric(),
          ])
          ->query(function (Builder $query, array $data): Builder {
            return $query->when($data['id'], fn(Builder $query, $id) => $query->where('id', $id));
          })
          ->indicateUsing(function (array $data): ?string {
            if (!$data['id']) return null;
            return 'ID Aktivitas: ' . $data['id'];
          }),

        Filters\SelectFilter::make('log_name')
          ->label(__('filament-logger::filament-logger.resource.label.type'))
          ->options(static::getLogNameList()),

        Filters\SelectFilter::make('subject_type')
          ->label(__('filament-logger::filament-logger.resource.label.subject_type'))
          ->options(static::getSubjectTypeList()),

        Filters\Filter::make('properties->old')
          ->indicateUsing(function (array $data): ?string {
            if (!$data['old']) {
              return null;
            }

            return __('filament-logger::filament-logger.resource.label.old_attributes') . $data['old'];
          })
          ->form([
            Forms\Components\TextInput::make('old')
              ->label(__('filament-logger::filament-logger.resource.label.old'))
              ->hint(__('filament-logger::filament-logger.resource.label.properties_hint')),
          ])
          ->query(function (Builder $query, array $data): Builder {
            if (!$data['old']) {
              return $query;
            }

            return $query->where('properties->old', 'like', "%{$data['old']}%");
          }),

        Filters\Filter::make('properties->attributes')
          ->indicateUsing(function (array $data): ?string {
            if (!$data['new']) {
              return null;
            }

            return __('filament-logger::filament-logger.resource.label.new_attributes') . $data['new'];
          })
          ->form([
            Forms\Components\TextInput::make('new')
              ->label(__('filament-logger::filament-logger.resource.label.new'))
              ->hint(__('filament-logger::filament-logger.resource.label.properties_hint')),
          ])
          ->query(function (Builder $query, array $data): Builder {
            if (!$data['new']) {
              return $query;
            }

            return $query->where('properties->attributes', 'like', "%{$data['new']}%");
          }),

        Filters\Filter::make('created_at')
          ->form([
            Forms\Components\DatePicker::make('logged_at')
              ->label(__('filament-logger::filament-logger.resource.label.logged_at'))
              ->displayFormat(config('filament-logger.date_format', 'd/m/Y')),
          ])
          ->query(function (Builder $query, array $data): Builder {
            return $query
              ->when(
                $data['logged_at'],
                fn(Builder $query, $date): Builder => $query->whereDate('created_at', $date),
              );
          }),
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make()
            ->modalWidth(MaxWidth::FiveExtraLarge)
            ->slideOver()
            ->color('primary'),
        ]),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          // Tables\Actions\DeleteBulkAction::make(),
        ]),
      ]);
  }

  public static function getRelations(): array
  {
    return [
      //
    ];
  }

  protected static function getLogNameColors(): array
  {
    $customs = [];

    foreach (config('filament-logger.custom') ?? [] as $custom) {
      if (filled($custom['color'] ?? null)) {
        $customs[$custom['color']] = $custom['log_name'];
      }
    }

    $result = array_merge(
      (config('filament-logger.resources.enabled') && config('filament-logger.resources.color')) ? [
        config('filament-logger.resources.color') => config('filament-logger.resources.log_name'),
      ] : [],
      (config('filament-logger.models.enabled') && config('filament-logger.models.color')) ? [
        config('filament-logger.models.color') => config('filament-logger.models.log_name'),
      ] : [],
      (config('filament-logger.access.enabled') && config('filament-logger.access.color')) ? [
        config('filament-logger.access.color') => config('filament-logger.access.log_name'),
      ] : [],
      (config('filament-logger.notifications.enabled') && config('filament-logger.notifications.color')) ? [
        config('filament-logger.notifications.color') => config('filament-logger.notifications.log_name'),
      ] : [],
      $customs,
    );

    return $result;
  }

  protected static function getLogNameList(): array
  {
    $customs = [];

    foreach (config('filament-logger.custom') ?? [] as $custom) {
      $customs[$custom['log_name']] = $custom['log_name'];
    }

    $result = array_merge(
      config('filament-logger.resources.enabled') ? [
        config('filament-logger.resources.log_name') => config('filament-logger.resources.log_name'),
      ] : [],
      config('filament-logger.models.enabled') ? [
        config('filament-logger.models.log_name') => config('filament-logger.models.log_name'),
      ] : [],
      config('filament-logger.access.enabled')
      ? [config('filament-logger.access.log_name') => config('filament-logger.access.log_name')]
      : [],
      config('filament-logger.notifications.enabled') ? [
        config('filament-logger.notifications.log_name') => config('filament-logger.notifications.log_name'),
      ] : [],
      $customs,
    );

    return $result;
  }

  protected static function getSubjectTypeList(): array
  {
    if (config('filament-logger.resources.enabled', true)) {
      $subjects = [];
      $exceptResources = [...config('filament-logger.resources.exclude'), config('filament-logger.activity_resource')];
      $removedExcludedResources = collect(Filament::getResources())->filter(function ($resource) use ($exceptResources) {
        return !in_array($resource, $exceptResources);
      });
      foreach ($removedExcludedResources as $resource) {
        $model = $resource::getModel();
        $subjects[$model] = Str::of(class_basename($model))->headline();
      }
      return $subjects;
    }
    return [];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListActivityLogs::route('/'),
      // 'create' => Pages\CreateActivityLog::route('/create'),
      // 'edit' => Pages\EditActivityLog::route('/{record}/edit'),
    ];
  }
}
