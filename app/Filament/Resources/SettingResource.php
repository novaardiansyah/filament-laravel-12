<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class SettingResource extends Resource implements HasShieldPermissions
{
  protected static ?string $model = Setting::class;

  protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
  protected static ?string $navigationGroup = 'Pengaturan';
  protected static ?int $navigationSort = 10;
  protected static ?string $modelLabel = 'Pengaturan Aplikasi';
  protected static ?string $pluralModelLabel = 'Aplikasi';
  protected static ?string $recordTitleAttribute = 'name';

  public static function getPermissionPrefixes(): array
  {
    return [
      'view',
      'view_any',
      'create',
      'update',
      'restore',
      'restore_any',
      'replicate',
      'reorder',
      'delete',
      'delete_any',
      'force_delete',
      'update_restricted',
    ];
  }

  public static function canUpdateRestrictedSetting(): bool
  {
    static $condition;

    if ($condition === null) {
      $condition = auth()->user() && auth()->user()->can('update_restricted_setting');
    }

    return $condition;
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make()
          ->description('Pengaturan tambahan')
          ->collapsible()
          ->columnSpan(2)
          ->schema([
            Forms\Components\TextInput::make('name')
              ->label('Nama pengaturan')
              ->required()
              ->live(onBlur: true)
              ->readOnly(!static::canUpdateRestrictedSetting())
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
              ->readOnly(!static::canUpdateRestrictedSetting())
              ->helperText('Hanya boleh menggunakan huruf, angka, dan garis bawah. Contoh: site_name, max_upload_size'),

            Forms\Components\Toggle::make('has_options')
              ->label('Punya opsi nilai')
              ->live()
              ->visible(static::canUpdateRestrictedSetting()),

            Forms\Components\TagsInput::make('options')
              ->label('Opsi nilai')
              ->placeholder('Masukkan opsi nilai, pisahkan dengan koma')
              ->separator(',')
              ->visible(fn(Forms\Get $get) => $get('has_options'))
              ->live(onBlur: true)
              ->helperText('Tekan Enter untuk menambahkan opsi baru')
              ->visible(static::canUpdateRestrictedSetting()),
            ]),

        Forms\Components\Section::make()
          ->description('Pengaturan umum aplikasi')
          ->collapsible()
          ->columns(1)
          ->columnSpan(2)
          ->schema([
            Forms\Components\Textarea::make('value')
              ->label('Nilai')
              ->required()
              ->rows(3)
              ->visible(fn(Forms\Get $get) => !$get('has_options'))
              ->maxLength(255),

            Forms\Components\Select::make('value_option')
              ->label('Pilihan nilai')
              ->required()
              ->visible(fn(Forms\Get $get) => $get('has_options'))
              ->native(false)
              ->searchable()
              ->options(function (Forms\Get $get) {
                $options = $get('options') ?? [];
                return collect($options)->mapWithKeys(function ($option) {
                  return [$option => $option];
                });
              }),

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
      ->recordAction('edit_value')
      ->recordUrl(null)
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

          Tables\Actions\Action::make('edit_value')
            ->label('Ganti Nilai')
            ->icon('heroicon-o-arrows-right-left')
            ->modalHeading('Ganti nilai pengaturan aplikasi')
            ->modalWidth(MaxWidth::ExtraLarge)
            ->color('primary')
            ->form(self::getFormEditValue())
            ->fillForm(fn (Setting $record) => self::fillFormEditValue($record))
            ->action(fn (Setting $record, array $data) => self::actionFormEditValue($record, $data)),

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

  public static function getFormEditValue(): array 
  {
    return [
      Forms\Components\Section::make()
        ->description('Informasi pengaturan aplikasi')
        ->collapsible()
        ->columnSpan(2)
        ->schema([
          Forms\Components\TextInput::make('name')
            ->label('Nama pengaturan')
            ->disabled(),

          Forms\Components\TextInput::make('options')
            ->label('Opsi nilai')
            ->hidden(),

          Forms\Components\Toggle::make('has_options')
            ->label('Punya opsi nilai')
            ->live()
            ->hidden(),

          Forms\Components\Textarea::make('value')
            ->label('Nilai')
            ->required()
            ->rows(3)
            ->maxLength(255)
            ->visible(fn(Forms\Get $get) => !$get('has_options')),

          Forms\Components\Select::make('value_option')
            ->label('Pilihan nilai')
            ->required()
            ->native(false)
            ->searchable()
            ->options(function (Forms\Get $get) {
              $options = $get('options') ?? [];
              return collect($options)->mapWithKeys(function ($option) {
                return [$option => $option];
              });
            })
            ->visible(fn(Forms\Get $get) => $get('has_options')),
          ])
    ];
  }

  public static function fillFormEditValue(Setting $record): array
  {
    return [
      'name'         => $record->name,
      'value'        => $record->value,
      'has_options'  => $record->has_options,
      'options'      => $record->options ? explode(',', $record->options) : [],
      'value_option' => $record->value,
    ];
  }

  public static function actionFormEditValue(Setting $record, array $data): void
  {
    $value = $data['value'] ?? $data['value_option'];
    $record->update(['value' => $value]);

    Notification::make()
      ->title('Nilai pengaturan berhasil diubah.')
      ->success()
      ->send();
  }
}
