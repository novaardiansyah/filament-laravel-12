<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShortUrlResource\Pages;
use App\Models\ShortUrl;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;

class ShortUrlResource extends Resource
{
  protected static ?string $model = ShortUrl::class;

  protected static ?string $navigationIcon = 'heroicon-o-link';
  protected static ?string $navigationGroup = 'Produktivitas';
  protected static ?int $navigationSort = 20;
  protected static ?string $modelLabel = 'Short URLs';
  protected static ?string $pluralModelLabel = 'Short URLs';
  protected static ?string $recordTitleAttribute = 'tiny_url';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make('')
          ->description('Informasi Short URL')
          ->columns(1)
          ->schema([
            Forms\Components\Textarea::make('long_url')
              ->label('URL Asli')
              ->placeholder('https://example.com')
              ->regex('/^(https?:\/\/)([^\s]+)$/')
              ->validationMessages([
                'regex' => 'URL harus dimulai dengan http:// atau https:// dan tidak boleh mengandung spasi.',
              ])
              ->required()
              ->rows(2)
              ->maxLength(255),
            Forms\Components\Toggle::make('is_active')
              ->label('Status Aktif')
              ->inline(false)
              ->helperText('Aktifkan untuk membuat Short URL ini tersedia dan dapat diakses.')
              ->default(true),
          ]),
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
          ->label('Kode')
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('long_url')
          ->copyable()
          ->label('URL Asli')
          ->searchable()
          ->toggleable(isToggledHiddenByDefault: true)
          ->wrap()
          ->limit(40),
        Tables\Columns\TextColumn::make('short_url')
          ->label('Short URL')
          ->searchable()
          ->toggleable()
          ->wrap()
          ->limit(40)
          ->copyable()
          ->copyableState(fn (string $state, ShortUrl $record): string => $record->tiny_url ?? $state)
          ->formatStateUsing(fn (string $state, ShortUrl $record): string => $record->tiny_url ?? $state),
        Tables\Columns\TextColumn::make('is_active')
          ->label('Status')
          ->badge()
          ->color(fn (bool $state): string => $state ? 'success' : 'danger')
          ->sortable()
          ->toggleable()
          ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Tidak Aktif'),
        Tables\Columns\TextColumn::make('clicks')
          ->label('Total Klik')
          ->sortable()
          ->badge()
          ->color('info')
          ->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', '.')),
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
      ->recordAction('view')
      ->recordUrl(null)
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make()
            ->modalHeading('Detail Short URL')
            ->color('info')
            ->icon('heroicon-o-eye')
            ->modalWidth(MaxWidth::TwoExtraLarge)
            ->slideOver(),

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

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListShortUrls::route('/'),
      // 'create' => Pages\CreateShortUrl::route('/create'),
      // 'edit' => Pages\EditShortUrl::route('/{record}/edit'),
    ];
  }

  public static function infolist(Infolist $infolist): Infolist
  {
    return $infolist
      ->schema([
        Infolists\Components\Section::make([
            Infolists\Components\TextEntry::make('long_url')
              ->label('URL Asli')
              ->badge()
              ->color('info')
              ->copyable(),

            Infolists\Components\TextEntry::make('short_url')
              ->label('Short URL')
              ->badge()
              ->color('info')
              ->copyable()
              ->copyableState(fn (string $state, ShortUrl $record): string => $record->tiny_url ?? $state)
              ->formatStateUsing(fn (string $state, ShortUrl $record): string => $record->tiny_url ?? $state),
          ])
          ->columns(2),

        Infolists\Components\Section::make([
          Infolists\Components\TextEntry::make('code')
              ->label('Kode'),
            
          Infolists\Components\TextEntry::make('is_active')
            ->label('Status')
            ->badge()
            ->color(fn (bool $state): string => $state ? 'success' : 'danger')
            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Tidak Aktif'),

          Infolists\Components\TextEntry::make('clicks')
            ->label('Total Klik')
            ->badge()
            ->color('info')
            ->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', '.')),
        ])
        ->columns(3),

        Infolists\Components\Section::make([
            Infolists\Components\TextEntry::make('created_at')
              ->label('Dibuat pada')
              ->dateTime('d M Y H:i'),

            Infolists\Components\TextEntry::make('updated_at')
              ->label('Diubah pada')
              ->dateTime('d M Y H:i'),
          ])
          ->columns(3),
      ]);
  }
}
