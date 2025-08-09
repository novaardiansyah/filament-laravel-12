<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentAccountResource\Pages;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\Setting;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class PaymentAccountResource extends Resource implements HasShieldPermissions
{
  protected static ?string $model = PaymentAccount::class;

  protected static ?string $navigationIcon = 'heroicon-o-credit-card';
  protected static ?string $navigationGroup = 'Keuangan';
  protected static ?int $navigationSort = 20;
  protected static ?string $label = 'Akun Kas';
  protected static ?string $recordTitleAttribute = 'name';

  public static function getPermissionPrefixes(): array
  {
    return ['view', 'view_any', 'create', 'update', 'restore', 'restore_any', 'replicate', 'reorder', 'delete', 'delete_any', 'force_delete', 'audit'];
  }

  protected static function showPaymentCurrency(): bool
  {
    static $condition;

    if ($condition === null) {
      $condition = Setting::showPaymentCurrency();
    }

    return $condition;
  }

  public static function canAudit(): bool
  {
    static $condition;

    if ($condition === null) {
      $condition = auth()->user() && auth()->user()->can('audit_payment::account');
    }

    return $condition;
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make('Akun Kas')
          ->description('Detail Akun Kas')
          ->columns(1)
          ->columnSpan(2)
          ->schema([
            Forms\Components\TextInput::make('name')
              ->label('Nama Akun')
              ->required()
              ->maxLength(255),
            Forms\Components\FileUpload::make('logo')
              ->label('Logo/Gambar')
              ->directory('images/payment_account')
              ->image()
              ->imageEditor()
              ->enableOpen()
              ->enableDownload(),
          ])
      ])->columns(3);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('index')
          ->rowIndex()
          ->label('#'),
        Tables\Columns\ImageColumn::make('logo')
          ->checkFileExistence(false)
          ->circular(),
        Tables\Columns\TextColumn::make('name')
          ->label('Nama Akun')
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('deposit')
          ->label('Total Saldo')
          ->sortable()
          ->copyable()
          ->copyableState(fn (string $state): string => toIndonesianCurrency((float) $state ?? 0))
          ->formatStateUsing(function (string $state): string {
            return toIndonesianCurrency((float) $state ?? 0, showCurrency: self::showPaymentCurrency());
          }),
        Tables\Columns\TextColumn::make('user.name')
          ->label('Nama Pemilik')
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
      ->filters([
        //
      ])
      ->defaultSort('updated_at', 'desc')
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make()
            ->color('info')
            ->slideOver(),

          Tables\Actions\EditAction::make()
            ->color('primary')
            ->modalWidth(MaxWidth::Medium)
            ->before(function (PaymentAccount $record, array $data): void {
              if ($record->logo && ($data['logo'] !== $record->logo)) {
                // ! Jika gambar berubah, maka hapus gambar lama.
                Storage::disk('public')->exists($record->logo) ? Storage::disk('public')->delete($record->logo) : null;
              }
            }),

          Tables\Actions\Action::make('Audit')
            ->authorize(fn (): bool => self::canAudit())
            ->color('danger')
            ->icon('heroicon-o-scale')
            ->modalWidth(MaxWidth::Medium)
            ->form(self::getAuditFormSchema())
            ->action(fn (PaymentAccount $record, array $data) => self::handleAuditAction($record, $data)),

          Tables\Actions\RestoreAction::make(),
        ])
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\RestoreBulkAction::make(),
        ]),
      ]);
  }

  protected static function getAuditFormSchema(): array
  {
    return [
      Forms\Components\Section::make('')
        ->description('Audit keuangan akun kas')
        ->schema([
          Forms\Components\TextInput::make('deposit')
            ->label('Saldo Awal')
            ->disabled()
            ->default(fn (PaymentAccount $record): string => toIndonesianCurrency($record->deposit ?? 0)),
          Forms\Components\TextInput::make('deposit_to')
            ->label('Saldo Akhir')
            ->numeric()
            ->required()
            ->live(onBlur: true)
            ->hint(fn (?string $state) => toIndonesianCurrency($state ?? 0, showCurrency: self::showPaymentCurrency()))
            ->afterStateUpdated(function (callable $set, PaymentAccount $record, ?string $state) {
              $saldo_awal  = (int) $record->deposit;
              $saldo_akhir = (int) ($state ?? 0);

              $selisih = $saldo_awal - $saldo_akhir;
              $selisih = $selisih > 0 ? -$selisih : abs($selisih);

              $set('difference', toIndonesianCurrency($selisih));
            }),
          Forms\Components\TextInput::make('difference')
            ->label('Selisih Saldo')
            ->disabled()
            ->default(fn() => toIndonesianCurrency(0)),
        ])
    ];
  }

  protected static function handleAuditAction(PaymentAccount $record, array $data): void 
  {
    $deposit = (int) $record->deposit;
    $deposit_to = (int) $data['deposit_to'];

    $isDecrease = $deposit_to < $deposit;
    $selisih = $isDecrease ? $deposit - $deposit_to : $deposit_to - $deposit;

    Payment::create([
      'code'               => getCode('payment'),
      'name'               => "Audit akun kas {$record->name}",
      'type_id'            => $isDecrease ? 1 : 2,
      'user_id'            => auth()->id(),
      'payment_account_id' => $record->id,
      'amount'             => $selisih,
      'income'             => $isDecrease ? null : $selisih,
      'expense'            => $isDecrease ? $selisih : null,
      'has_items'          => false,
      'attachments'        => [],
      'date'               => now()->format('Y-m-d'),
    ]);

    $record->update([
      'deposit' => $deposit_to,
    ]);

    Notification::make()
      ->title('Audit keuangan akun kas')
      ->body('Audit akun kas berhasil diproses.')
      ->success()
      ->send();
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
      'index'  => Pages\ListPaymentAccounts::route('/'),
      'create' => Pages\CreatePaymentAccount::route('/create'),
      'edit'   => Pages\EditPaymentAccount::route('/{record}/edit'),
    ];
  }
}
