<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NoteResource\Pages;
use App\Models\Note;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;

class NoteResource extends Resource
{
  protected static ?string $model = Note::class;

  protected static ?string $navigationIcon = 'heroicon-o-document-text';
  protected static ?string $navigationGroup = 'Produktivitas';
  protected static ?int $navigationSort = 9;
  protected static ?string $modelLabel = 'Catatan';
  protected static ?string $recordTitleAttribute = 'title';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make([
          Forms\Components\TextInput::make('title')
            ->label('Judul')
            ->required()
            ->maxLength(255),
          Forms\Components\RichEditor::make('description')
            ->label('Deskripsi')
            ->maxLength(6000)
            ->disableGrammarly()
            ->disableToolbarButtons([
              'codeBlock',
            ])
            ->fileAttachmentsDirectory('images/note'),
          Forms\Components\Toggle::make('send_notification')
            ->label('Kirim Notifikasi')
            ->live(onBlur: true)
            ->default(false),
          Forms\Components\DateTimePicker::make('notification_at')
            ->label('Waktu Notifikasi')
            ->native(false)
            ->default(now()->addDay())
            ->displayFormat('d/m/Y H:i')
            ->visible(fn(callable $get): bool => $get('send_notification'))
        ])
        ->description('Informasi catatan')
        ->columns(1)
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
          ->label('ID Catatan')
          ->badge()
          ->color('info')
          ->copyable()
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('title')
          ->label('Judul')
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('description')
          ->label('Deskripsi')
          ->searchable()
          ->toggleable()
          ->wrap()
          ->lineClamp(3)
          ->html(),
        Tables\Columns\TextColumn::make('notification_at')
          ->label('Kirim Notifikasi')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->badge()
          ->color(function (string $state) {
            if (now()->greaterThan($state)) return 'danger';
            return 'success';
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
      ->defaultSort('updated_at', 'desc')
      ->recordAction(null)
      ->recordUrl(null)
      ->filters([
        //
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make()
            ->color('info')
            ->modalWidth(MaxWidth::ThreeExtraLarge)
            ->slideOver()
            ->mutateRecordDataUsing(fn (array $data) => self::getEditMutateRecordData($data)),

          Tables\Actions\EditAction::make()
            ->color('primary')
            ->mutateRecordDataUsing(fn (array $data) => self::getEditMutateRecordData($data))
            ->mutateFormDataUsing(fn (array $data, Tables\Actions\EditAction $action, Note $record) => NoteResource::getMutateFormData($data, $action, $record)),

          Tables\Actions\DeleteAction::make(),
        ])
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

  public static function getEditMutateRecordData(array $data): array
  {
    if ($data['notification_at']) {
      $data['send_notification'] = true;
    }

    return $data;
  }

  public static function getMutateFormData(
    array $data, 
    \Filament\Actions\CreateAction | \Filament\Tables\Actions\EditAction $action, 
    ?Note $record = null
  ): array {
    if ($action instanceof \Filament\Actions\CreateAction) {
      $data['code'] = getCode('note');
    }

    if ($action instanceof \Filament\Tables\Actions\EditAction) {
      // ! Do something...
    }

    if (!$data['send_notification']) {
      $data['notification_at'] = null;
    }

    return $data;
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListNotes::route('/'),
      // 'create' => Pages\CreateNote::route('/create'),
      // 'edit' => Pages\EditNote::route('/{record}/edit'),
    ];
  }
}
