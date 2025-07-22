<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentSummaryResource\Pages;
use App\Models\PaymentSummary;
use App\Models\Setting;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class PaymentSummaryResource extends Resource
{
  protected static ?string $model = PaymentSummary::class;

  protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
  protected static ?string $navigationGroup = 'Keuangan';
  protected static ?int $navigationSort = 30;
  protected static ?string $pluralModelLabel = 'Ringkasan';
  protected static ?string $modelLabel = 'Ringkasan';

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
        Forms\Components\Section::make()
          ->columns(1)
          ->columnSpan(1)
          ->description('Pilih periode summary')
          ->schema([
            Forms\Components\Select::make('month')
              ->label('Bulan')
              ->options(getOptionMonths())
              ->native(false)
              ->required()
              ->live(onBlur: true)
              ->afterStateUpdated(fn (callable $get, callable $set) => PaymentSummaryResource::setSummary($get, $set)),
            Forms\Components\TextInput::make('year')
              ->label('Tahun')
              ->default(now()->translatedFormat('Y'))
              ->required()
              ->numeric()
              ->length(4)
              ->live(onBlur: true)
              ->afterStateUpdated(fn (callable $get, callable $set) => PaymentSummaryResource::setSummary($get, $set)),
          ]),

        Forms\Components\Section::make()
          ->columns(2)
          ->columnSpan(2)
          ->description('Detail Summary')
          ->schema([
            Forms\Components\TextInput::make('initial_balance')
              ->label('Saldo Awal')
              ->required()
              ->numeric()
              ->maxLength(11)
              ->minValue(1000)
              ->live(onBlur: true)
              ->hint(fn (?string $state) => toIndonesianCurrency($state ?? 0))
              ->afterStateUpdated(function (int $state, callable $get, callable $set): void {
                $total_expense   = $get('total_expense');
                $current_balance = $state - $total_expense;
                $set('current_balance', $current_balance);
              }),
            Forms\Components\TextInput::make('total_income')
              ->label('Total Pemasukan')
              ->readOnlyOn('create')
              ->live(onBlur: true)
              ->hint(fn (?string $state) => toIndonesianCurrency($state ?? 0)),
            Forms\Components\TextInput::make('total_expense')
              ->label('Total Pengeluaran')
              ->readOnlyOn('create')
              ->live(onBlur: true)
              ->hint(fn (?string $state) => toIndonesianCurrency($state ?? 0)),
            Forms\Components\TextInput::make('current_balance')
              ->label('Saldo Tersisa')
              ->readOnlyOn('create')
              ->live(onBlur: true)
              ->hint(fn (?string $state) => toIndonesianCurrency($state ?? 0)),
          ])
      ])
      ->columns(3);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('index')
          ->rowIndex()
          ->label('#'),
        Tables\Columns\TextColumn::make('code')
          ->label('ID Summary')
          ->searchable()
          ->copyable(),
        Tables\Columns\TextColumn::make('period')
          ->label('Periode')
          ->toggleable()
          ->formatStateUsing(function (string $state): string {
            $month = now()->month((int) substr($state, 0, 2))->translatedFormat('F');
            $year  = substr($state, 2, 4);
            return "{$month}-{$year}";
          }),
        Tables\Columns\TextColumn::make('initial_balance')
          ->label('Saldo Awal')
          ->sortable()
          ->toggleable()
          ->formatStateUsing(fn (string $state) => toIndonesianCurrency($state, showCurrency: self::showPaymentCurrency())),
        Tables\Columns\TextColumn::make('total_income')
          ->label('Total Pemasukan')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true)
          ->formatStateUsing(fn (string $state) => toIndonesianCurrency($state, showCurrency: self::showPaymentCurrency())),
        Tables\Columns\TextColumn::make('total_expense')
          ->label('Total Pengeluaran')
          ->sortable()
          ->toggleable()
          ->formatStateUsing(fn (string $state) => toIndonesianCurrency($state, showCurrency: self::showPaymentCurrency())),
        Tables\Columns\TextColumn::make('current_balance')
          ->label('Saldo Tersisa')
          ->toggleable()
          ->sortable()
          ->formatStateUsing(fn (string $state) => toIndonesianCurrency($state, showCurrency: self::showPaymentCurrency())),
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
      ->recordAction(null)
      ->recordUrl(null)
      ->defaultSort('updated_at', 'desc')
      ->filters([])
      ->headerActions([
        ExportAction::make()->exports([
          ExcelExport::make('table')->fromTable()
            ->except(['index'])
            ->withChunkSize(200)
            ->queue(),
        ]),
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\EditAction::make()
            ->color('primary'),

          Tables\Actions\DeleteAction::make()
            ->color('danger'),

          Tables\Actions\ForceDeleteAction::make(),

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
            ->hidden(fn($livewire) => $livewire->activeTab !== 'Deleted'),

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
      'index' => Pages\ListPaymentSummaries::route('/'),
      'create' => Pages\CreatePaymentSummary::route('/create'),
      'edit' => Pages\EditPaymentSummary::route('/{record}/edit'),
    ];
  }

  public static function setSummary(callable $get, callable $set): void {
    $month = $get('month');
    $year  = $get('year');
    
    if (!$month || !$year) return;

    if ((int) $month < 9) $month = "0{$month}";
    $sync = PaymentSummary::getSync($month, $year);

    $set('initial_balance', $sync['initial_balance'] ?? 0);
    $set('total_income', $sync['total_income'] ?? 0);
    $set('total_expense', $sync['total_expense'] ?? 0);
    $set('current_balance', $sync['current_balance'] ?? 0);
  }
}
