<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class SettingResource extends Resource 
{
  protected static ?string $model = Setting::class;

  protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
  protected static ?string $navigationGroup = 'Pengaturan';
  protected static ?int $navigationSort = 10;
  protected static ?string $modelLabel = 'Pengaturan Aplikasi';
  protected static ?string $pluralModelLabel = 'Aplikasi';
  protected static ?string $recordTitleAttribute = 'name';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make()
          ->description('Informasi akun pengguna')
          ->collapsible()
          ->columns(1)
          ->columnSpan(2)
          ->schema([
            Forms\Components\TextInput::make('name')
              ->label('Nama pengaturan')
              ->required()
              ->live(debounce: 1000)
              ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get) {
                $name = $get('name');
                if ($name) {
                  $key = strtolower(str_replace(' ', '_', $name));
                  $set('key', $key);
                }
              }),

            Forms\Components\TextInput::make('key')
              ->label('Alias')
              ->required()
              ->maxLength(255)
              ->unique(ignoreRecord: true)
              ->regex('/^[a-zA-Z0-9_]+$/')
              ->readOnly()
              ->helperText('Hanya boleh menggunakan huruf, angka, dan garis bawah. Contoh: site_name, max_upload_size'),

            Forms\Components\Textarea::make('value')
              ->label('Nilai')
              ->required()
              ->rows(4)
              ->visible(fn (Forms\Get $get) => !$get('has_options'))
              ->maxLength(255),

            Forms\Components\Select::make('value_option')
              ->label('Pilihan nilai')
              ->required()
              ->visible(fn (Forms\Get $get) => $get('has_options'))
              ->native(false)
              ->searchable()
              ->options(function (Forms\Get $get) {
                $options = $get('options') ?? [];
                return collect($options)->mapWithKeys(function ($option) {
                  return [$option => $option];
                });
              })
          ]),

        Forms\Components\Section::make()
          ->description('Informasi tambahan')
          ->collapsible()
          ->columnSpan(2)
          ->schema([
            Forms\Components\Toggle::make('has_options')
              ->label('Punya opsi nilai')
              ->live(debounce: 1000),

            Forms\Components\TagsInput::make('options')
              ->label('Opsi nilai')
              ->placeholder('Masukkan opsi nilai, pisahkan dengan koma')
              ->separator(',')
              ->visible(fn (Forms\Get $get) => $get('has_options'))
              ->live(debounce: 1000)
              ->helperText('Gunakan ini jika pengaturan ini memiliki beberapa opsi nilai. Contoh: "Cyan, Neutral, Zinc, etc."'),

            Forms\Components\Textarea::make('description')
              ->label('Keterangan')
              ->maxLength(1000)
              ->rows(4)
              ->placeholder('Masukkan keterangan pengaturan ini'),
          ])
      ])
      ->columns(4);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('index')
          ->rowIndex()
          ->label('#'),
        Tables\Columns\TextColumn::make('name')
          ->label('Nama Pengaturan')
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('key')
          ->label('Alias')
          ->searchable()
          ->badge()
          ->sortable()
          ->toggleable()
          ->copyable(),
        Tables\Columns\TextColumn::make('value')
          ->label('Nilai')
          ->badge()
          ->searchable()
          ->sortable(),
        Tables\Columns\TextColumn::make('description')
          ->label('Keterangan')
          ->limit(50)
          ->searchable()
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
      ->filters([
        //
      ])
      ->headerActions([
        ExportAction::make()->exports([
          ExcelExport::make('table')->fromTable()
            ->except(['index'])
            ->withChunkSize(200)
            ->queue(),
        ])
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make()
            ->color('info')
            ->slideOver(),
          
          Tables\Actions\EditAction::make()
            ->color('primary'),

          Tables\Actions\DeleteAction::make(),
          Tables\Actions\ForceDeleteAction::make(),
          Tables\Actions\RestoreAction::make(),
        ]),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
          Tables\Actions\ForceDeleteBulkAction::make(),
          Tables\Actions\RestoreBulkAction::make(),
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
      'index' => Pages\ListSettings::route('/'),
      'create' => Pages\CreateSetting::route('/create'),
      'edit' => Pages\EditSetting::route('/{record}/edit'),
    ];
  }
}
