<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Filament\Resources\PaymentResource\RelationManagers\ItemsRelationManager;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\PaymentType;
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
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class PaymentResource extends Resource
{
  protected static ?string $model = Payment::class;

  protected static ?string $navigationIcon = 'heroicon-o-banknotes';
  protected static ?string $navigationGroup = 'Keuangan';
  protected static ?int $navigationSort = 20;
  protected static ?string $label = 'Keuangan';

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
        Forms\Components\Group::make([
          Forms\Components\Section::make('Keuangan')
            ->description('Detail pencatatan saldo keuangan.')
            ->columns(2)
            ->schema([
              Forms\Components\Toggle::make('has_items')
                ->label('Punya Barang?')
                ->disabledOn('edit')
                ->live(onBlur: true)
                ->afterStateUpdated(function (Forms\Set $set, string $state): void {
                  if ($state) {
                    $set('amount', 0);
                    $set('type_id', 1);
                    $set('has_charge', false);
                  }
                }),
              Forms\Components\Toggle::make('has_charge')
                ->label('Tanpa Tagihan?')
                ->disabled(function (callable $get, callable $set, string $operation) {
                  if ($operation === 'edit')
                    return true;
                  return $get('has_items');
                }),
              Forms\Components\TextInput::make('amount')
                ->label('Nominal')
                ->required()
                ->disabled(fn(Forms\Get $get) => $get('has_items'))
                ->numeric()
                ->live(onBlur: true)
                ->hintIcon('heroicon-m-question-mark-circle', tooltip: fn(?string $state) => toIndonesianCurrency($state ?? 0)),
              Forms\Components\DatePicker::make('date')
                ->label('Tanggal')
                ->required()
                ->default(Carbon::now())
                ->displayFormat('d M Y')
                ->closeOnDateSelection()
                ->native(false),
              Forms\Components\Textarea::make('name')
                ->label('Catatan')
                ->nullable()
                ->columnSpanFull()
                ->required(fn(Forms\Get $get) => !$get('has_items'))
                ->rows(3),
              Forms\Components\FileUpload::make('attachments')
                ->label('Bukti Transaksi')
                ->directory('img/payment')
                ->image()
                ->imageEditor()
                ->multiple()
                ->columnSpanFull(),
            ]),
        ])
          ->columnSpan(2),

        Forms\Components\Group::make([
          Forms\Components\Section::make('Akun Kas')
            ->collapsible()
            ->schema([
              Forms\Components\TextInput::make('code')
                ->label('ID Transaksi')
                ->placeholder('Auto Generated')
                ->disabled()
                ->visibleOn('edit'),
              Forms\Components\Select::make('type_id')
                ->label('Tipe Transaksi')
                ->options(function (Forms\Get $get): Collection {
                  if ($get('has_items')) return PaymentType::where('id', 1)->pluck('name', 'id');
                  return PaymentType::all()->pluck('name', 'id');
                })
                ->live(onBlur: true)
                ->native(false)
                ->default(1)
                ->required()
                ->disabledOn('edit'),
              Forms\Components\Select::make('payment_account_id')
                ->label('Akun Kas')
                ->relationship('payment_account', titleAttribute: 'name')
                ->native(false)
                ->live(onBlur: true)
                ->required()
                ->disabledOn('edit')
                ->afterStateUpdated(function (Forms\Set $set, ?string $state, string $operation) {
                  $set('payment_account_to_id', null);

                  if (!$state)
                    return $set('payment_account_deposit', 'Rp. 0');

                  $payment_account = PaymentAccount::find($state);

                  if ($operation === 'create') {
                    $set('payment_account_deposit', toIndonesianCurrency($payment_account->deposit));
                  }
                }),
              Forms\Components\Select::make('payment_account_to_id')
                ->label('Akun Kas Tujuan')
                ->options(function ($get) {
                  if (!$get('payment_account_id'))
                    return [];
                  return PaymentAccount::where('id', '!=', $get('payment_account_id'))
                    ->pluck('name', 'id');
                })
                ->native(false)
                ->required(fn($get): bool => ($get('type_id') == 3 || $get('type_id') == 4))
                ->visible(fn($get): bool => ($get('type_id') == 3 || $get('type_id') == 4))
                ->disabled(fn($get, string $operation): bool => !($get('type_id') == 3 || $get('type_id') == 4) || $operation == 'edit'),
              Forms\Components\TextInput::make('payment_account_deposit')
                ->label('Saldo Akun Kas')
                ->disabled()
                ->default('Rp. 0'),
            ]),
        ])
          ->columnSpan(1)
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
          ->label('ID Transaksi')
          ->sortable()
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('amount')
          ->label('Nominal')
          ->sortable()
          ->formatStateUsing(function (string $state, ?Payment $record): string {
            if ($record->type_id == 1)
              $state = $record->expense;
            if ($record->type_id == 2)
              $state = $record->income;
            return toIndonesianCurrency($state, showCurrency: self::showPaymentCurrency());
          })
          ->toggleable(),
        Tables\Columns\TextColumn::make('payment_account.name')
          ->label('Akun Kas')
          ->sortable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('payment_account_to.name')
          ->label('Akun Kas Tujuan')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('payment_type.name')
          ->label('Tipe Transaksi')
          ->badge()
          ->color(fn(string $state): string => match (strtolower($state)) {
            'pemasukan' => 'success',
            'tarik tunai' => 'info',
            'transfer' => 'info',
            'pengeluaran' => 'danger',
          })
          ->toggleable(),
        Tables\Columns\TextColumn::make('date')
          ->label('Tanggal')
          ->date('d M Y')
          ->sortable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('name')
          ->label('Catatan')
          ->searchable()
          ->wrap()
          ->words(100)
          ->toggleable(),
        Tables\Columns\ImageColumn::make('attachments')
          ->checkFileExistence(false)
          ->wrap()
          ->limit(3)
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
      ->defaultSort('date', 'desc')
      ->recordUrl(null)
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
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\EditAction::make()
            ->color('primary'),

          Tables\Actions\DeleteAction::make()
            ->color('danger')
            ->after(function (?Payment $record): void {
              $attachments = $record->attachments;

              // ? Has attachments
              if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                  // ? Doesnt exist
                  if (!Storage::disk('public')->exists($attachment))
                    continue;

                  // ! Delete attachment
                  Storage::disk('public')->delete($attachment);
                }
              }

              // ? pengeluaran
              if ($record->type_id == 1) {
                $record->payment_account->update([
                  'deposit' => $record->payment_account->deposit + $record->amount
                ]);
              } else {
                // ? Pemasukan
                $record->payment_account->update([
                  'deposit' => $record->payment_account->deposit - $record->amount
                ]);
              }
            }),
        ]),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          // Tables\Actions\RestoreBulkAction::make(),
        ]),
      ]);
  }

  public static function getRelations(): array
  {
    return [
      ItemsRelationManager::class
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListPayments::route('/'),
      'create' => Pages\CreatePayment::route('/create'),
      'edit' => Pages\EditPayment::route('/{record}/edit'),
    ];
  }
}
