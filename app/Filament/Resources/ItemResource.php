<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Jobs\ItemResource\MakePdfJob;
use App\Models\Item;
use App\Models\Setting;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

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
              ->hint(fn(?string $state) => toIndonesianCurrency($state ?? 0)),
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
          ->label('Kode SKU')
          ->sortable()
          ->searchable()
          ->copyable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('name')
          ->label('Nama')
          ->sortable()
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('type.name')
          ->label('Jenis')
          ->badge()
          ->color(fn($state) => match ($state) {
            'Produk' => 'primary',
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
        Tables\Filters\Filter::make('date')
          ->form([
            Forms\Components\DatePicker::make('from_created_at')
              ->label('Dari Tanggal')
              ->displayFormat('d M Y')
              ->native(false),
            Forms\Components\DatePicker::make('end_created_at')
              ->label('Sampai Tanggal')
              ->displayFormat('d M Y')
              ->native(false),
          ])
          ->indicateUsing(function (array $data): ?array {
            $indicators = [];

            if ($data['from_created_at'] ?? null) {
              $indicators[] = Indicator::make('Dari Tanggal ' . Carbon::parse($data['from_created_at'])->translatedFormat('d M Y'))
                ->removeField('from_created_at');
            }

            if ($data['end_created_at'] ?? null) {
              $indicators[] = Indicator::make('Sampai Tanggal ' . Carbon::parse($data['end_created_at'])->translatedFormat('d M Y'))
                ->removeField('end_created_at');
            }

            return $indicators;
          })
          ->query(function (Builder $query, array $data): Builder {
            return $query
              ->when(
                $data['from_created_at'],
                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
              )
              ->when(
                $data['end_created_at'],
                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
              );
          })
          ->columns(2)
      ], layout: FiltersLayout::Modal)
      ->filtersFormColumns(2)
      ->filtersFormSchema(fn(array $filters): array => [
        Forms\Components\Section::make('')
          ->description('Filter data berdasarkan kriteria berikut:')
          ->schema([
            $filters['date']
          ])
          ->columns(1)
      ])
      ->headerActions([
        ExportAction::make()->exports([
          ExcelExport::make('table')->fromTable()
            ->except(['index'])
            ->withChunkSize(200)
            ->queue(),
        ]),

        Tables\Actions\Action::make('print_pdf')
          ->label('Cetak PDF')
          ->color('primary')
          ->icon('heroicon-o-printer')
          ->action(function (): void {
            MakePdfJob::dispatch(user: auth()->user());

            Notification::make()
              ->title('Cetak PDF dalam antrian')
              ->body('Cetak PDF telah masuk antrian. Anda akan diberitahu ketika file siap diunduh.')
              ->success()
              ->send();
          })
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\EditAction::make()
            ->color('primary')
            ->hidden(fn($livewire) => $livewire->activeTab == 'Deleted'),

          Tables\Actions\DeleteAction::make()
            ->color('danger'),

          Tables\Actions\ForceDeleteAction::make()
            ->before(function ($record, Tables\Actions\DeleteAction $actions): void {
              if ($record->payments->isNotEmpty()) {
                Notification::make()
                  ->title('Tidak dapat dihapus')
                  ->body(self::$label . ' ini sudah digunakan dalam transaksi keuangan.')
                  ->danger()
                  ->send();
                $actions->cancel();
              }
            }),

          Tables\Actions\RestoreAction::make(),
        ]),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make()
            ->deselectRecordsAfterCompletion()
            ->hidden(fn($livewire) => $livewire->activeTab == 'Deleted'),

          Tables\Actions\ForceDeleteBulkAction::make()
            ->deselectRecordsAfterCompletion()
            ->hidden(fn($livewire) => $livewire->activeTab !== 'Deleted')
            ->action(function (Collection $records) {
              $hasPayments = false;

              foreach ($records as $record) {
                if ($record->payments->isNotEmpty()) {
                  $hasPayments = true;
                  continue;
                }

                $record->forceDelete();
              }

              if ($hasPayments) {
                Notification::make()
                  ->title('Tidak dapat dihapus')
                  ->body('Beberapa ' . self::$label . ' ini sudah digunakan dalam transaksi keuangan.')
                  ->warning()
                  ->send();
              }
            }),

          Tables\Actions\RestoreBulkAction::make()
            ->deselectRecordsAfterCompletion()
            ->hidden(fn($livewire) => $livewire->activeTab !== 'Deleted'),
        ])
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
