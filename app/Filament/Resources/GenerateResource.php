<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GenerateResource\Pages;
use App\Models\Generate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GenerateResource extends Resource
{
  protected static ?string $model = Generate::class;

  protected static ?string $navigationIcon = 'heroicon-o-identification';
  protected static ?string $navigationGroup = 'Pengaturan';
  protected static ?int $navigationSort = 11;
  protected static ?string $modelLabel = 'ID Generator';
  protected static ?string $pluralModelLabel = 'ID Generator';
  protected static ?string $recordTitleAttribute = 'name';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make([
          // ! Nama ID, Alias, Prefix, Separator, Antrian, Next ID
          Forms\Components\TextInput::make('name')
            ->label('Nama ID')
            ->required()
            ->maxLength(255),
          Forms\Components\TextInput::make('alias')
            ->label('Alias')
            ->required()
            ->maxLength(255),

          Forms\Components\Group::make([
            Forms\Components\TextInput::make('prefix')
              ->label('Prefix')
              ->required()
              ->maxLength(5)
              ->suffix('-')
              ->live(onBlur: true)
              ->afterStateUpdated(fn (callable $set, callable $get) => static::getReviewID($set, $get)),
            Forms\Components\TextInput::make('separator')
              ->label('Separator')
              ->readOnly()
              ->default(now()->format('ymd'))
              ->maxLength(6),
            Forms\Components\TextInput::make('queue')
              ->label('Antrian')
              ->required()
              ->numeric()
              ->minValue(1)
              ->default(1)
              ->maxValue(999999)
              ->live(onBlur: true)
              ->afterStateUpdated(fn (callable $set, callable $get) => static::getReviewID($set, $get)),
            Forms\Components\TextInput::make('next_id')
              ->label('Next ID')
              ->disabled()
              ->placeholder('Akan diisi otomatis'),
          ])
          ->columns(4)
          ->columnSpanFull(),
        ])
        ->description('Informasi ID Generator')
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
        Tables\Columns\TextColumn::make('name')
          ->label('Nama ID')
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('alias')
          ->label('Alias')
          ->copyable()
          ->badge()
          ->color('info')
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('prefix')
          ->label('Prefix')
          ->copyable()
          ->badge()
          ->color('info')
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('separator')
          ->label('Separator')
          ->copyable()
          ->badge()
          ->color('info')
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('queue')
          ->label('Antrian')
          ->copyable()
          ->badge()
          ->color('info')
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('id')
          ->label('Next ID')
          ->copyable()
          ->badge()
          ->color('primary')
          ->toggleable()
          ->formatStateUsing(function (Generate $record) {
            return $record->getNextId();
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
            ->color('primary')
            ->hidden(fn($livewire) => $livewire->activeTab == 'Deleted')
            ->mutateRecordDataUsing(function (array $data, Generate $record) {
              $data['next_id'] = $record->getNextId();
              return $data;
            })
            ->mutateFormDataUsing(function (array $data, Generate $record, Tables\Actions\EditAction $action): array {
              if ((int) $data['queue'] < $record->queue) {
                Notification::make()
                  ->title('Gagal mengubah antrian')
                  ->body('Nilai antrian tidak boleh lebih kecil dari nilai saat ini (' . $record->queue . ').')
                  ->danger()
                  ->send();

                $action->halt();
              }

              return $data;
            }),

          Tables\Actions\DeleteAction::make(),

          Tables\Actions\RestoreAction::make()
            ->color('info')
        ]),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
          Tables\Actions\RestoreBulkAction::make()
            ->color('info')
        ]),
      ]);
  }

  public static function getRelations(): array
  {
    return [
      //
    ];
  }

  public static function getReviewID(callable $set, callable $get): void
  {
    $prefix = $get('prefix');
    $separator = $get('separator');
    $queue = $get('queue');

    if (!$prefix || !$separator || !$queue) return;

    $res = $prefix . '-' . substr($separator, 0, 4) . str_pad($queue, 4, '0', STR_PAD_LEFT) . substr($separator, 4, 2);

    $set('next_id', $res);
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListGenerates::route('/'),
      // 'create' => Pages\CreateGenerate::route('/create'),
      // 'edit' => Pages\EditGenerate::route('/{record}/edit'),
    ];
  }
}
