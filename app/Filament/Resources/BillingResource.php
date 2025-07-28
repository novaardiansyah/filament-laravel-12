<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillingResource\Pages;
use App\Filament\Resources\BillingResource\RelationManagers;
use App\Models\Billing;
use App\Models\BillingMaster;
use App\Models\BillingStatus;
use App\Models\PaymentAccount;
use App\Models\Setting;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BillingResource extends Resource
{
  protected static ?string $model = Billing::class;

  protected static ?string $navigationIcon = 'heroicon-o-credit-card';
  protected static ?string $navigationGroup = 'Tagihan';
  protected static ?int $navigationSort = 10;
  protected static ?string $label = 'Tagihan';
  protected static ?string $recordTitleAttribute = 'name';

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
          Forms\Components\Select::make('billing_master_id')
            ->label('Master Tagihan')
            ->options(self::getBillingMasterOptions())
            ->native(false)
            ->searchable()
            ->preload()
            ->live(onBlur: true)
            ->required()
            ->afterStateUpdated(fn (callable $set, callable $get)  => self::setBillingPeriod($set, $get)),
          
          Forms\Components\TextInput::make('billing_period_id')
            ->label('Periode Tagihan')
            ->default('Monthly')
            ->readOnly(),

          Forms\Components\Select::make('payment_account_id')
            ->label('Akun Pembayaran')
            ->relationship('paymentAccount', 'name')
            ->native(false)
            ->searchable()
            ->preload()
            ->default(PaymentAccount::PERMATA_BANK)
            ->required(),

          Forms\Components\Select::make('billing_status_id')
            ->label('Status Tagihan')
            ->relationship('billingStatus', 'name')
            ->native(false)
            ->searchable()
            ->required()
            ->default(BillingStatus::PENDING)
            ->preload(),

          Forms\Components\DatePicker::make('billing_date')
            ->label('Tanggal Tagihan')
            ->default(now())
            ->displayFormat('d/m/Y')
            ->required()
            ->native(false)
            ->live(onBlur: true)
            ->afterStateUpdated(fn (callable $set, callable $get)  => self::setBillingPeriod($set, $get)),

          Forms\Components\DatePicker::make('due_date')
            ->label('Tanggal Jatuh Tempo')
            ->default(now()->addDays(30))
            ->native(false)
            ->displayFormat('d/m/Y')
            ->readOnly(),
        ])
          ->description('Informasi tagihan')
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
        Tables\Columns\TextColumn::make('billingMaster.item.name')
          ->label('Tagihan')
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('billingMaster.code')
          ->label('ID Tagihan')
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true)
          ->copyable(),
        Tables\Columns\TextColumn::make('paymentAccount.name')
          ->label('Akun Pembayaran')
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('billingMaster.amount')
          ->label('Jumlah Tagihan')
          ->sortable()
          ->toggleable()
          ->formatStateUsing(fn ($state) => toIndonesianCurrency($state, 2, showCurrency: self::showPaymentCurrency())),
        Tables\Columns\TextColumn::make('billing_date')
          ->label('Tanggal Tagihan')
          ->dateTime('d/m/Y')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('due_date')
          ->label('Tanggal Jatuh Tempo')
          ->dateTime('d/m/Y')
          ->sortable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('billingStatus.name')
          ->label('Status')
          ->toggleable()
          ->sortable()
          ->badge()
          ->color(fn ($state) => match ($state) {
            BillingStatus::getName(BillingStatus::PENDING) => 'warning',
            BillingStatus::getName(BillingStatus::PAID)    => 'success',
            BillingStatus::getName(BillingStatus::FAILED)  => 'danger',
          }),
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
      ->recordAction(null)
      ->recordUrl(null)
      ->defaultSort('updated_at', 'desc')
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\EditAction::make()
            ->color('primary'),

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

  protected static function getBillingMasterOptions()
  {
    return BillingMaster::with('item')
      ->get()
      ->mapWithKeys(function ($bm) {
        return [$bm->id => "{$bm->item->name} ($bm->code)"];
      });
  }

  protected static function setBillingPeriod(callable $set, callable $get): void
  {
    $billingMasterId = $get('billing_master_id') ?? 0;
    $billingMaster = BillingMaster::with('billingPeriod')->find($billingMasterId);

    $billing_date = $get('billing_date') ?? now();
    $due_date = Carbon::parse($billing_date)->addDays($billingMaster->billingPeriod->days ?? 30);

    $set('billing_period_id', $billingMaster->billingPeriod->name ?? 'Monthly');
    $set('due_date', $due_date->format('Y-m-d'));
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListBillings::route('/'),
      // 'create' => Pages\CreateBilling::route('/create'),
      // 'edit' => Pages\EditBilling::route('/{record}/edit'),
    ];
  }
}
