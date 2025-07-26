<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserLogResource\Pages;
use App\Models\UserLog;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Dotswan\MapPicker\Fields\Map;

class UserLogResource extends Resource
{
  protected static ?string $model = UserLog::class;

  protected static ?string $navigationIcon = 'heroicon-o-user-circle';
  protected static ?string $navigationGroup = 'Audit Logs';
  protected static ?int $navigationSort = 30;
  protected static ?string $modelLabel = 'User Log';
  protected static ?string $pluralModelLabel = 'User Log';
  protected static ?string $recordTitleAttribute = 'email';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make()
          ->columns(3)
          ->schema([
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
            Forms\Components\TextInput::make('geolocation')
              ->label('Geolokasi')
              ->afterStateHydrated(function ($state, Forms\Set $set): void {
                $geolocation = $state ?? null;
                if ($geolocation) {
                  $geolocationParts = explode(',', $geolocation);
                  $set('location', [
                    'lat' => $geolocationParts[0] ?? null,
                    'lng' => $geolocationParts[1] ?? null,
                  ]);
                }
              }),
            Forms\Components\Textarea::make('user_agent')
              ->label('Perangkat')
              ->rows(3)
              ->columnSpanFull(),
          ]),

        Map::make('location')
          ->label('Peta Lokasi Pengguna')
          ->defaultLocation(latitude: -6.2886, longitude: 106.7179)
          ->draggable(false)
          ->clickable(false)
          ->zoom(10)
          ->minZoom(0)
          ->maxZoom(28)
          ->tilesUrl("https://tile.openstreetmap.de/{z}/{x}/{y}.png")
          ->detectRetina(true)
          ->extraStyles([
            'border-radius: 12px',
            'min-height: 400px',
          ])
          ->columnSpanFull(),
        
        Forms\Components\Section::make()
          ->columns(3)
          ->schema([
            Forms\Components\DateTimePicker::make('created_at')
              ->label('Dibuat pada')
              ->displayFormat('d/m/Y H:i'),
            Forms\Components\DateTimePicker::make('updated_at')
              ->label('Diubah pada')
              ->displayFormat('d/m/Y H:i'),
          ]),
      ])->columns(3);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('index')
          ->rowIndex()
          ->label('#'),
        Tables\Columns\TextColumn::make('ip_address')
          ->label('Alamat IP')
          ->limit(15)
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('email')
          ->label('Email')
          ->searchable()
          ->toggleable()
          ->formatStateUsing(fn(string $state): string => textLower($state)),
        Tables\Columns\TextColumn::make('user_agent')
          ->label('Perangkat')
          ->searchable()
          ->toggleable()
          ->wrap()
          ->lineClamp(2),
        Tables\Columns\TextColumn::make('geolocation')
          ->label('Geolokasi')
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true)
          ->formatStateUsing(fn(string $state): string => str_replace(',', ', ', $state)),
        Tables\Columns\TextColumn::make('city')
          ->label('Lokasi')
          ->searchable()
          ->toggleable()
          ->formatStateUsing(fn (UserLog $record): string => self::getFullAddress($record)),
        Tables\Columns\TextColumn::make('timezone')
          ->label('Zona Waktu')
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('created_at')
          ->label('Dibuat pada')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->label('Diubah pada')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->toggleable(),
      ])
      // ->recordAction(null)
      ->recordUrl(null)
      ->defaultSort('updated_at', 'desc')
      ->filters([
        //
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make()
            ->modalWidth(MaxWidth::ThreeExtraLarge)
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

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListUserLogs::route('/'),
      // 'create' => Pages\CreateUserLog::route('/create'),
      // 'edit' => Pages\EditUserLog::route('/{record}/edit'),
    ];
  }

  public static function getFullAddress(UserLog $record): string
  {
    $country = $record->country ?? '';
    $region  = $record->region ?? '';
    $city    = $record->city ?? '';
    $postal  = $record->postal ?? '';

    if (!$city) return '';
    
    return trim("{$city}, {$region}, {$country} ({$postal})");
  }
}
