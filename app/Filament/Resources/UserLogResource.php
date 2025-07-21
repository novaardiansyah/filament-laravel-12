<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserLogResource\Pages;
use App\Models\UserLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists;

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
        //
      ]);
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

  public static function infolist(Infolist $infolist): Infolist
  {
    return $infolist
      ->schema([
        Infolists\Components\Section::make()
          ->columns(3)
          ->schema([
            Infolists\Components\TextEntry::make('email')
              ->label('Email')
              ->formatStateUsing(fn(string $state): string => textLower($state))
              ->copyable(),
            Infolists\Components\TextEntry::make('ip_address')
              ->label('Alamat IP')
              ->limit(15)
              ->copyable(),
            Infolists\Components\TextEntry::make('city')
              ->label('Lokasi')
              ->formatStateUsing(fn (UserLog $record): string => self::getFullAddress($record))
              ->copyable()
              ->copyableState(fn (UserLog $record): string => self::getFullAddress($record)),
            Infolists\Components\TextEntry::make('timezone')
              ->label('Zona Waktu')
              ->copyable(),
            Infolists\Components\TextEntry::make('geolocation')
              ->label('Geolokasi')
              ->formatStateUsing(fn(string $state): string => str_replace(',', ', ', $state))
              ->copyable()
              ->copyableState(fn(string $state): string => str_replace(',', ', ', $state)),
            Infolists\Components\TextEntry::make('user_agent')
              ->label('Perangkat')
              ->columnSpanFull()
              ->copyable(),
          ]),

        Infolists\Components\Section::make()
          ->columns(3)
          ->schema([
            Infolists\Components\TextEntry::make('created_at')
              ->label('Dibuat pada')
              ->dateTime('d M Y H:i'),
            Infolists\Components\TextEntry::make('updated_at')
              ->label('Diubah pada')
              ->dateTime('d M Y H:i'),
          ]),
      ]);
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
