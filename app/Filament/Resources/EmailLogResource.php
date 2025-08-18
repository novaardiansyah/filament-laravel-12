<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailLogResource\Pages;
use App\Models\EmailLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists;

class EmailLogResource extends Resource
{
  protected static ?string $model = EmailLog::class;

  protected static ?string $navigationIcon = 'heroicon-o-envelope';
  protected static ?string $navigationGroup = 'Audit Logs';
  protected static ?int $navigationSort = 20;
  protected static ?string $modelLabel = 'Email Log';
  protected static ?string $pluralModelLabel = 'Email Log';
  protected static ?string $recordTitleAttribute = 'subject';

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
        Tables\Columns\TextColumn::make('email')
          ->label('Email')
          ->searchable()
          ->toggleable()
          ->formatStateUsing(fn(string $state): string => textLower($state)),
        Tables\Columns\TextColumn::make('subject')
          ->label('Subjek')
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('status.name')
          ->label('Status')
          ->toggleable()
          ->badge()
          ->color(fn(string $state): string => match ((string) $state) {
            'draft'   => 'warning',
            'pending' => 'primary',
            'success' => 'success',
            'failed'  => 'danger',
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
      // ->recordAction(null)
      ->recordUrl(null)
      ->defaultSort('updated_at', 'desc')
      ->filters([
        //
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make()
            ->modalWidth(MaxWidth::ThreeExtraLarge)
            ->slideOver()
            ->color('primary'),

          Tables\Actions\Action::make('preview_email')
            ->label('Pratinjau Email')
            ->icon('heroicon-o-document-magnifying-glass')
            ->color('warning')
            ->url(fn (EmailLog $record): string => route('admin.email_logs.preview', $record))
            ->openUrlInNewTab(),
        ]),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          // Tables\Actions\DeleteBulkAction::make(),
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
      'index'  => Pages\ListEmailLogs::route('/'),
      // 'create' => Pages\CreateEmailLog::route('/create'),
      // 'edit'   => Pages\EditEmailLog::route('/{record}/edit'),
    ];
  }

  public static function infolist(Infolist $infolist): Infolist
  {
    return $infolist
      ->schema([
        Infolists\Components\Section::make()
          ->columns(2)
          ->schema([
            Infolists\Components\TextEntry::make('email')
              ->label('Email')
              ->formatStateUsing(fn(string $state): string => textLower($state)),
            Infolists\Components\TextEntry::make('subject')
              ->label('Subject'),
            Infolists\Components\TextEntry::make('message')
              ->label('Pesan')
              ->columnSpanFull()
              ->html(),
          ]),

        Infolists\Components\Section::make()
          ->columns(3)
          ->schema([
            Infolists\Components\TextEntry::make('status.name')
              ->label('Status')
              ->badge()
              ->color(fn(string $state): string => match ((string) $state) {
                'draft'   => 'warning',
                'pending' => 'primary',
                'success' => 'success',
                'failed'  => 'danger',
              }),
            Infolists\Components\TextEntry::make('created_at')
              ->label('Dibuat pada')
              ->dateTime('d M Y H:i'),
            Infolists\Components\TextEntry::make('updated_at')
              ->label('Diubah pada')
              ->dateTime('d M Y H:i'),
            Infolists\Components\TextEntry::make('status_description')
              ->label('Status Deskripsi')
              ->columnSpanFull()
              ->html(),
          ]),
      ]);
  }
}
