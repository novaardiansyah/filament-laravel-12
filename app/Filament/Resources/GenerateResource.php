<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GenerateResource\Pages;
use App\Filament\Resources\GenerateResource\RelationManagers;
use App\Models\Generate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
        //
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
          ->color('success')
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('queue')
          ->label('Next ID')
          ->copyable()
          ->badge()
          ->color('primary')
          ->toggleable()
          ->formatStateUsing(function (string $state, Generate $record) {
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
            ->color('primary'),

          Tables\Actions\DeleteAction::make()
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

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListGenerates::route('/'),
      'create' => Pages\CreateGenerate::route('/create'),
      'edit' => Pages\EditGenerate::route('/{record}/edit'),
    ];
  }
}
