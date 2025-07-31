<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillingResource\Pages;
use App\Filament\Resources\BillingResource\RelationManagers;
use App\Models\Billing;
use App\Models\BillingMaster;
use App\Models\BillingPeriod;
use App\Models\BillingStatus;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\Setting;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
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
  protected static ?string $recordTitleAttribute = 'billingMaster.item.name';

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
            ->required(),
          
          Forms\Components\Select::make('payment_account_id')
            ->label('Akun Pembayaran')
            ->relationship('paymentAccount', 'name')
            ->native(false)
            ->searchable()
            ->preload()
            ->default(PaymentAccount::PERMATA_BANK)
            ->required(),

          Forms\Components\TextInput::make('billing_period_id')
            ->label('Periode Tagihan')
            ->default('Monthly')
            ->readOnly(),

          Forms\Components\DatePicker::make('due_date')
            ->label('Tanggal Tagihan')
            ->default(now())
            ->native(false)
            ->displayFormat('d/m/Y')
            ->required(),

          Forms\Components\Select::make('billing_status_id')
            ->label('Status Tagihan')
            ->relationship('billingStatus', 'name')
            ->native(false)
            ->searchable()
            ->required()
            ->default(BillingStatus::PENDING)
            ->preload(),
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
        Tables\Columns\TextColumn::make('due_date')
          ->label('Tanggal Tagihan')
          ->dateTime('d M Y')
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
            ->modalWidth(MaxWidth::FourExtraLarge)
            ->color('primary')
            ->mutateRecordDataUsing(function (?Billing $record, array $data): array {
              $billingPeriod = BillingPeriod::getName($record->billingMaster->billing_period_id) ?? 'Monthly';
              $data['billing_period_id'] = $billingPeriod;
              return $data;
            }),

          Tables\Actions\Action::make('already_paid')
            ->label('Sudah Dibayar')
            ->icon('heroicon-o-check-circle')
            ->color('info')
            ->visible(fn (Billing $record) => $record->billing_status_id != BillingStatus::PAID)
            ->modalWidth(MaxWidth::Medium)
            ->form(self::getAlreadyPaidForm())
            ->fillForm(fn (Billing $record, array $data) => self::getAlreadyPaidFillForm($record))
            ->action(fn (Billing $record, Tables\Actions\Action $action, array $data) => self::getAlreadyPaidAction($record, $action, $data)),

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

  public static function getAlreadyPaidForm(): array
  {
    return [
      Forms\Components\Section::make('')
        ->description('Informasi pembayaran')
        ->schema([
          Forms\Components\Toggle::make('has_charge')
            ->label('Tanpa Tagihan?'),
          Forms\Components\Select::make('payment_account_id')
            ->label('Akun Kas')
            ->relationship('paymentAccount', titleAttribute: 'name')
            ->native(false)
            ->required()
            ->live()
            ->default(function (?Billing $record) {
              return $record->payment_account_id;
            })
            ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
              $set('payment_account_to_id', null);

              if (!$state) return $set('payment_account_deposit', 'Rp0');

              $paymentAccount = PaymentAccount::find($state);

              $set('payment_account_deposit', toIndonesianCurrency($paymentAccount->deposit));
            }),
          Forms\Components\TextInput::make('payment_account_deposit')
            ->label('Saldo Akun Kas')
            ->disabled()
            ->default('Rp0'),
          Forms\Components\TextInput::make('amount')
            ->label('Nominal')
            ->readOnly()
            ->hint(fn (string $state) => toIndonesianCurrency($state ?? 0, 2)),
        ])
      ];
  }

  public static function getAlreadyPaidAction(Billing $record, Tables\Actions\Action $action, array $data): void
  {
    $item_name = $record->billingMaster->item->name;

    $data = array_merge($data, [
      'has_items'   => false,
      'date'        => now()->translatedFormat('Y-m-d'),
      'name'        => $item_name . ' (' . $record->billingMaster->code . ')',
      'attachments' => [],
      'type_id'     => PaymentAccount::PENGELUARAN,
    ]);

    $payment = new Payment();
    $mutate  = $payment::mutateDataPayment($data);
    $data    = $mutate['data'];

    if ($mutate['status'] == false) {
      Notification::make()
        ->danger()
        ->title('Proses gagal!')
        ->body($mutate['message'])
        ->send();

      $action->halt();
    }

    $payment->create($data);

    $record->billing_status_id = BillingStatus::PAID;
    $record->payment_account_id = $data['payment_account_id'];
    $record->save();

    // ! Duplikat $record menjadi data baru dengan due_date yang baru
    $newRecord = $record->replicate();

    $periodDays = $record->billingMaster->billingPeriod->days ?? 7;
    $newRecord->due_date = Carbon::parse($record->due_date)->addDays($periodDays);
    $newRecord->billing_status_id = BillingStatus::PENDING;
    $newRecord->save();

    Notification::make()
      ->success()
      ->title('Pembayaran Berhasil')
      ->body("Tagihan {$item_name} telah dibayar.")
      ->send();
  }

  public static function getAlreadyPaidFillForm(Billing $record): array
  {
    return [
      'amount'                  => $record->billingMaster->amount,
      'payment_account_deposit' => toIndonesianCurrency($record->paymentAccount->deposit),
    ];
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
