<?php

namespace App\Filament\Resources\PaymentResource\RelationManagers;

use App\Models\Item;
use App\Models\PaymentSummary;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ItemsRelationManager extends RelationManager
{
  protected static string $relationship = 'items';
  protected static ?string $modelLabel = 'Barang';
  protected static ?string $pluralModelLabel = 'Barang';
  protected static ?string $title = 'Kelola Barang';

  public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
  {
    return $ownerRecord->has_items;
  }
  
  protected static function showPaymentCurrency(): bool
  {
    static $condition;

    if ($condition === null) {
      $condition = Setting::showPaymentCurrency();
    }

    return $condition;
  }
  
  public function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make('')
          ->description('Informasi Barang')
          ->columns(2)
          ->schema([
            Forms\Components\TextInput::make('name')
              ->label('Nama Barang')
              ->required()
              ->maxLength(255),
            Forms\Components\TextInput::make('amount')
              ->label('Harga')
              ->required()
              ->numeric()
              ->minValue(0)
              ->live(onBlur: true)
              ->afterStateUpdated(function($state, $set, $get): void {
                $get('quantity') && $set('total', $state * $get('quantity'));
              })
              ->hintIcon('heroicon-m-question-mark-circle', tooltip: fn (?string $state) => toIndonesianCurrency($state ?? 0)),
            Forms\Components\TextInput::make('quantity')
              ->label('Kuantitas')
              ->required()
              ->numeric()
              ->default(1)
              ->minValue(0)
              ->live(onBlur: true)
              ->afterStateUpdated(function($state, $set, $get): void {
                $get('amount') && $set('total', $state * $get('amount'));
              })
              ->hintIcon('heroicon-m-question-mark-circle', tooltip: fn (?string $state) => number_format($state ?? 0, 0, ',', '.')),
            Forms\Components\TextInput::make('total')
              ->label('Total Harga')
              ->numeric()
              ->minValue(0)
              ->live(onBlur: true)
              ->readOnly()
              ->hintIcon('heroicon-m-question-mark-circle', tooltip: fn (?string $state) => toIndonesianCurrency($state ?? 0)),
          ]),
      ]);
  }

  public function table(Table $table): Table
  {
    return $table
      ->recordTitleAttribute('name')
      ->columns([
        Tables\Columns\TextColumn::make('index')
          ->rowIndex()
          ->label('#'),
        Tables\Columns\TextColumn::make('code')
          ->label('ID Barang')
          ->sortable()
          ->searchable()
          ->copyable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('pivot.item_code')
          ->label('ID Transaksi')
          ->sortable()
          ->searchable()
          ->copyable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('name')
          ->label('Nama Barang')
          ->sortable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('price')
          ->label('Harga')
          ->formatStateUsing(fn($state) => toIndonesianCurrency($state, showCurrency: self::showPaymentCurrency()))
          ->sortable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('quantity')
          ->label('Kuantitas')
          ->formatStateUsing(fn($state) => number_format($state ?? 0, 0, ',', '.'))
          ->sortable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('total')
          ->label('Total')
          ->formatStateUsing(fn($state) => toIndonesianCurrency($state))
          ->sortable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('pivot.updated_at')
          ->label('Diubah pada')
          ->dateTime('d M Y H:i')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        //
      ])
      ->defaultSort('pivot_updated_at', 'desc')
      ->headerActions([
        Tables\Actions\CreateAction::make()
          ->modalWidth(MaxWidth::ThreeExtraLarge)
          ->mutateFormDataUsing(function (array $data): array {
            $data['code'] = getCode(3);
            $data['item_code'] = getCode(4);
            $data['price'] = $data['amount'];
            return $data;
          })
          ->after(function(array $data, ?Item $record, RelationManager $livewire, Tables\Actions\CreateAction $action): void {
            $owner = $livewire->getOwnerRecord();

             // * Hitung total pengeluaran baru
            $expense = $owner->amount + (int) $data['total'];

             // * Hitung perubahan deposit
            $adjustedDeposit = $owner->payment_account->deposit + $owner->amount - $expense;

            $is_scheduled = $owner->is_scheduled ?? false;

            if (!$is_scheduled) {
              // * Update deposit akun kas
              $owner->payment_account->update(['deposit' => $adjustedDeposit]);
            }

            // * Tambah ke catatan otomatis
            $note = trim(($owner->name ?? '') . ', ' . "{$record->name} (x{$data['quantity']})", ', ');
            
            // * Update pengeluaran dan jumlah pada owner
            $owner->update(['expense' => $expense, 'amount' => $expense, 'name' => $note]);

            // * Update Summary
            $period      = now()->translatedFormat('mY');
            $syncSummary = PaymentSummary::setSync($period);

            if (!$syncSummary) return;

            $owner->update([
              'last_balance' => $syncSummary['current_balance'] ?? 0,
            ]);

            // * refresh parent form
            $action->getLivewire()->dispatch('refreshForm');
          }),

        Tables\Actions\AttachAction::make()
          ->modalWidth(MaxWidth::ThreeExtraLarge)
          ->recordSelectSearchColumns(['name', 'code'])
          ->recordSelect(
            function (Forms\Components\Select $select) { 
              return $select->placeholder('Pilih Barang')
                ->hintIcon('heroicon-m-question-mark-circle', tooltip: 'Pencarian dengan menggunakan {nama} atau {ID Barang} pada master.')
                ->live(onBlur: true)
                ->afterStateUpdated(function($state, $set, $get): void {
                  $item = Item::find($state ?? 0);

                  if ($item) {
                    $set('amount', $item->amount);
                    $get('quantity') && $set('total', $item->amount * $get('quantity'));
                  }
                });
            }
          )
          ->form(fn ($action): array => [
            Forms\Components\Section::make('')
              ->description('Informasi Barang')
              ->columns(2)
              ->schema([
                $action->getRecordSelect(),
                Forms\Components\TextInput::make('amount')
                  ->label('Harga')
                  ->required()
                  ->numeric()
                  ->minValue(0)
                  ->live(onBlur: true)
                  ->afterStateUpdated(function($state, $set, $get): void {
                    $get('quantity') && $set('total', $state * $get('quantity'));
                  })
                  ->hintIcon('heroicon-m-question-mark-circle', tooltip: fn (?string $state) => toIndonesianCurrency($state ?? 0)),
                Forms\Components\TextInput::make('quantity')
                  ->label('Kuantitas')
                  ->required()
                  ->numeric()
                  ->default(1)
                  ->minValue(0)
                  ->live(onBlur: true)
                  ->afterStateUpdated(function($state, $set, $get): void {
                    $get('amount') && $set('total', $state * $get('amount'));
                  })
                  ->hintIcon('heroicon-m-question-mark-circle', tooltip: fn (?string $state) => number_format($state ?? 0, 0, ',', '.')),
                Forms\Components\TextInput::make('total')
                  ->label('Total Harga')
                  ->required()
                  ->numeric()
                  ->minValue(0)
                  ->live(onBlur: true)
                  ->readOnly()
                  ->hintIcon('heroicon-m-question-mark-circle', tooltip: fn (?string $state) => toIndonesianCurrency($state ?? 0)),
              ])
          ])
          ->mutateFormDataUsing(function (array $data): array {
            $data['price'] = $data['amount'];
            $data['item_code'] = getCode(4);
            return $data;
          })
          ->after(function (array $data, ?Item $record, RelationManager $livewire, Tables\Actions\AttachAction $action) {
            $owner = $livewire->getOwnerRecord();

            // * Update harga barang
            $record->update(['amount' => $data['amount']]);

            // * Hitung total pengeluaran baru
            $expense = $owner->amount + (int) $data['total'];

            // * Hitung perubahan deposit
            $adjustedDeposit = $owner->payment_account->deposit + $owner->amount - $expense;

            $is_scheduled = $owner->is_scheduled ?? false;

            if (!$is_scheduled) {
              // * Update deposit akun kas
              $owner->payment_account->update(['deposit' => $adjustedDeposit]);
            }

            // * Tambah ke catatan otomatis
            $note = trim(($owner->name ?? '') . ', ' . "{$record->name} (x{$data['quantity']})", ', ');

            // * Update pengeluaran dan jumlah pada owner
            $owner->update(['expense' => $expense, 'amount' => $expense, 'name' => $note]);
            
            // * Update Summary
            $period      = now()->translatedFormat('mY');
            $syncSummary = PaymentSummary::setSync($period);

            if (!$syncSummary) return;

            $owner->update([
              'last_balance' => $syncSummary['current_balance'] ?? 0,
            ]);

            // * refresh parent form
            $action->getLivewire()->dispatch('refreshForm');
          }),
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\EditAction::make()
            ->color('primary')
            ->mutateFormDataUsing(function ($data): array {
              $data['price'] = $data['amount'];
              return $data;
            })
            ->before(function (array $data, Model $record, RelationManager $livewire) {
              $pivot_total = $record->pivot->total;

              $owner = $livewire->getOwnerRecord();
              $adjustedPivot = intval($pivot_total - (int) $data['total']);

              // * Hitung perubahan deposit dan expense (-+)
              $adjustedDeposit = $owner->payment_account->deposit + $adjustedPivot;
              $expense = $owner->amount + (-$adjustedPivot);

              $is_scheduled = $owner->is_scheduled ?? false;

              if (!$is_scheduled) {
                // * Update deposit akun kas
                $owner->payment_account->update(['deposit' => $adjustedDeposit]);
              }
              
              // * Tambah ke catatan otomatis
              $itemName = "{$record->name} (x{$record->quantity})";
              $note = implode(', ', array_diff(explode(', ', $owner->name ?? ''), [$itemName]));
              $note = trim($note) . ', ' . "{$data['name']} (x{$data['quantity']})";

              // * Update pengeluaran dan jumlah pada owner
              $owner->update(['expense' => $expense, 'amount' => $expense, 'name' => $note]);
            })
            ->after(function(Tables\Actions\EditAction $action) {
              // * refresh parent form
              $action->getLivewire()->dispatch('refreshForm');
            }),

          Tables\Actions\DetachAction::make()
            ->color('danger')
            ->before(function (?Item $record, RelationManager $livewire, Tables\Actions\DetachAction $action): void {
              $owner = $livewire->getOwnerRecord();
              
              // * Hitung pengeluaran baru setelah pengurangan
              $expense = $owner->amount - $record->pivot_total;

              // * Hitung perubahan deposit
              $adjustedDeposit = $owner->payment_account->deposit + $owner->amount - $expense;

              $is_scheduled = $owner->is_scheduled ?? false;

              if (!$is_scheduled) {
                // * Update deposit akun kas
                $owner->payment_account->update(['deposit' => $adjustedDeposit]);
              }

              $itemName = $record->name . ' (x' . $record->quantity . ')';
              $note = trim(implode(', ', array_diff(explode(', ', $owner->name ?? ''), [$itemName])));

              // * Update pengeluaran dan jumlah pada owner
              $owner->update(['expense' => $expense, 'amount' => $expense, 'name' => $note]);

              // * Update Summary
              $period      = now()->translatedFormat('mY');
              $syncSummary = PaymentSummary::setSync($period);

              if (!$syncSummary) return;

              $owner->update([
                'last_balance' => $syncSummary['current_balance'] ?? 0,
              ]);

              // * refresh parent form
              $action->getLivewire()->dispatch('refreshForm');
            }),
        ])
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\BulkAction::make('Lepaskan lampiran yang dipilih')
            ->color('danger')
            ->icon('heroicon-m-x-mark')
            ->requiresConfirmation()
            ->action(function ($records, RelationManager $livewire) {
              $owner = $livewire->getOwnerRecord();
        
              $totalExpense = $records->sum(function ($record) {
                return $record->pivot_total;
              });

               // * Hitung pengeluaran baru setelah pengurangan
              $expense = $owner->amount - $totalExpense;

              // * Hitung perubahan deposit
              $adjustedDeposit = $owner->payment_account->deposit + $owner->amount - $expense;

              $is_scheduled = $owner->is_scheduled ?? false;

              if (!$is_scheduled) {
                // * Update deposit akun kas
                $owner->payment_account->update(['deposit' => $adjustedDeposit]);
              }

              // * Update pengeluaran dan jumlah pada owner
              $owner->update(['expense' => $expense, 'amount' => $expense]);

              // * Update Summary
              $period      = now()->translatedFormat('mY');
              $syncSummary = PaymentSummary::setSync($period);

              if (!$syncSummary) return;

              $owner->update([
                'last_balance' => $syncSummary['current_balance'] ?? 0,
              ]);

              // * Lakukan detach pada setiap record
              foreach ($records as $record) {
                $livewire->getRelationship()->detach($record);
              }

              Notification::make()
                ->title('Lampiran terpilih berhasil dilepaskan')
                ->success()
                ->send();
            }),
        ]),
      ]);
  }
}
