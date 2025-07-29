<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillingMasterResource\Pages;
use App\Models\BillingMaster;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;

class BillingMasterResource extends Resource
{
  protected static ?string $model = BillingMaster::class;

  protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
  protected static ?string $navigationGroup = 'Tagihan';
  protected static ?int $navigationSort = 20;
  protected static ?string $label = 'Master Tagihan';
  protected static ?string $recordTitleAttribute = 'item.name';

  protected static function showPaymentCurrency(): bool
  {
    static $condition;

    if ($condition === null) {
      $condition = Setting::showPaymentCurrency();
    }

    return $condition;
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make([
          Forms\Components\Select::make('item_id')
            ->label('Produk & Layanan')
            ->relationship('item', 'name')
            ->native(false)
            ->preload(false)
            ->searchable()
            ->required()
            ->live(onBlur: true)
            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
              if ($state) {
                $item = \App\Models\Item::find($state);
                $set('amount', $item->amount ?? 0);
              } else {
                $set('amount', 0);
              }
            }),
          Forms\Components\Select::make('billing_period_id')
            ->label('Periode Tagihan')
            ->relationship('billingPeriod', 'name')
            ->native(false)
            ->preload(true)
            ->searchable()
            ->required()
            ->default(3),
          Forms\Components\TextInput::make('amount')
            ->label('Jumlah Tagihan')
            ->numeric()
            ->minValue(0)
            ->live(onBlur: true)
            ->required()
            ->hint(fn(?string $state) => toIndonesianCurrency($state ?? 0, 2)),
          Forms\Components\Toggle::make('is_active')
            ->label('Status Aktif')
            ->default(true)
            ->inline(false)
            ->required(),
        ])
        ->description('Informasi Tagihan')
        ->columns(2)
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('index')
          ->rowIndex()
          ->label('#'),
        Tables\Columns\TextColumn::make('code')
          ->label('ID Tagihan')
          ->searchable()
          ->toggleable()
          ->copyable(),
        Tables\Columns\TextColumn::make('item.name')
          ->label('Produk & Layanan')
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('billingPeriod.name')
          ->label('Periode Tagihan')
          ->toggleable(),
        Tables\Columns\TextColumn::make('amount')
          ->label('Jumlah Tagihan')
          ->sortable()
          ->toggleable()
          ->formatStateUsing(fn($state) => toIndonesianCurrency($state ?? 0, 2, showCurrency: self::showPaymentCurrency())),
        Tables\Columns\TextColumn::make('is_active')
          ->label('Status')
          ->badge()
          ->color(fn($state) => $state ? 'success' : 'danger')
          ->sortable()
          ->toggleable()
          ->formatStateUsing(fn($state) => $state ? 'Aktif' : 'Tidak Aktif'),
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
      ->filters([
        //
      ])
      ->defaultSort('updated_at', 'desc')
      ->recordAction(null)
      ->recordUrl(null)
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\EditAction::make()
            ->color('primary')
            ->modalWidth(MaxWidth::ThreeExtraLarge),

          Tables\Actions\DeleteAction::make()
            ->color('danger'),
        ]),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
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
      'index' => Pages\ListBillingMasters::route('/'),
      // 'create' => Pages\CreateBillingMaster::route('/create'),
      // 'edit' => Pages\EditBillingMaster::route('/{record}/edit'),
    ];
  }
}
