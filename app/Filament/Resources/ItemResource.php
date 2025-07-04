<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Item;
use App\Models\ItemType;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemResource extends Resource
{
  protected static ?string $model = Item::class;

  protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
  protected static ?string $navigationGroup = 'Master Data';
  protected static ?int $navigationSort = 10;
  protected static ?string $label = 'Produk & Layanan';

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
        Forms\Components\Section::make('')
        ->description('Informasi Produk & Layanan')
        ->columns(1)
        ->columnSpan(2)
        ->schema([
          Forms\Components\TextInput::make('name')
            ->label('Nama Produk & Layanan')
            ->required()
            ->maxLength(255),
          Forms\Components\TextInput::make('amount')
            ->label('Harga (*satuan)')
            ->required()
            ->numeric()
            ->minValue(0)
            ->live(onBlur: true)
            ->hint(fn (?string $state) => toIndonesianCurrency($state ?? 0)),
          Forms\Components\Select::make('type.id')
            ->label('Jenis')
            ->required()
            ->native(false)
            ->searchable()
            ->preload()
            ->default(1)
            ->relationship('type', 'name'),
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
        Tables\Columns\TextColumn::make('code')
          ->label('ID Barang')
          ->sortable()
          ->searchable()
          ->copyable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('name')
          ->label('Nama Barang')
          ->sortable()
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('type.name')
          ->label('Jenis')
          ->badge()
          ->color(fn ($state) => match ($state) {
            'Produk'  => 'primary',
            'Layanan' => 'info',
            default => 'secondary',
          })
          ->sortable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('amount')
          ->label('Harga (*satuan)')
          ->formatStateUsing(fn($state) => toIndonesianCurrency($state, showCurrency: self::showPaymentCurrency()))
          ->sortable()
          ->toggleable(),
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
      ->defaultSort('updated_at', 'desc')
      ->filters([
        //
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\EditAction::make()
            ->color('primary'),

          Tables\Actions\DeleteAction::make()
            // ->before(function($record, Tables\Actions\DeleteAction $actions): void {
            //   if ($record->payments->isNotEmpty()) {
            //     // ? Jika sudah digunakan transaksi
            //     Notification::make()
            //       ->title('Tidak dapat dihapus')
            //       ->body('Barang ini sudah digunakan dalam transaksi sebelumnya')
            //       ->danger()
            //       ->send();
            //     $actions->halt();
            //   }
            // })
            ->color('danger'),

            Tables\Actions\ForceDeleteAction::make(),
            Tables\Actions\RestoreAction::make(),
        ]),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
          Tables\Actions\ForceDeleteBulkAction::make(),
          Tables\Actions\RestoreBulkAction::make(),
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
      'index' => Pages\ListItems::route('/'),
      'create' => Pages\CreateItem::route('/create'),
      'edit' => Pages\EditItem::route('/{record}/edit'),
    ];
  }
}
