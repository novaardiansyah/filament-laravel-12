<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduledFileDeletionResource\Pages;
use App\Models\ScheduledFileDeletion;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;


class ScheduledFileDeletionResource extends Resource
{
  protected static ?string $model = ScheduledFileDeletion::class;
  protected static ?string $navigationIcon = 'heroicon-o-folder-minus';
  protected static ?string $navigationGroup = 'Berkas';
  protected static ?int $navigationSort = 10;
  protected static ?string $modelLabel = 'Kelola Berkas';
  protected static ?string $pluralModelLabel = 'Kelola Berkas';

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
        Tables\Columns\TextColumn::make('user.name')
          ->label('Pengguna')
          ->searchable()
          ->sortable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('file_name')
          ->label('Berkas')
          ->tooltip(fn(ScheduledFileDeletion $record): string => $record->has_been_deleted ? 'Berkas telah dihapus' : 'Unduh berkas')
          ->url(fn(ScheduledFileDeletion $record): string | null => !$record->has_been_deleted ? $record->download_url : null, fn(ScheduledFileDeletion $record): bool => !$record->has_been_deleted)
          ->searchable()
          ->toggleable(),
        Tables\Columns\TextColumn::make('has_been_deleted')
          ->label('Status')
          ->formatStateUsing(fn(bool $state): string => !$state ? 'Tersedia' : 'Terhapus')
          ->badge()
          ->color(fn(bool $state): string => !$state ? 'success' : 'danger')
          ->toggleable(),
        Tables\Columns\TextColumn::make('scheduled_deletion_time')
          ->label('Terjadwal dihapus')
          ->dateTime('d/m/Y H:i')
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
      ->recordAction(null)
      ->recordUrl(null)
      ->filters([
        //
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\Action::make('delete_file')
            ->label('Hapus')
            ->modalHeading('Hapus Berkas')
            ->color('danger')
            ->icon('heroicon-s-trash')
            ->visible(fn(ScheduledFileDeletion $record): bool => ScheduledFileDeletion::canDeleteFiles() && !$record->has_been_deleted)
            ->action(function(ScheduledFileDeletion $record) {
              $record->deleteFile();
              
              Notification::make()
                ->title('Berkas telah dihapus.')
                ->body('Berkas berhasil dihapus dari sistem.')
                ->icon('heroicon-o-check-circle')
                ->iconColor('success')
                ->send();
            })
            ->requiresConfirmation()
        ]),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\BulkAction::make('delete')
            ->label('Hapus yang dipilih')
            ->color('danger')
            ->icon('heroicon-s-trash')
            ->modalHeading('Hapus Berkas yang dipilih')
            ->action(function (Collection $records) {
              foreach ($records as $record) {
                if ($record->has_been_deleted) continue;
                $record->deleteFile();
              }

              Notification::make()
                ->title('Berkas telah dihapus.')
                ->body('Berkas berhasil dihapus dari sistem.')
                ->icon('heroicon-o-check-circle')
                ->iconColor('success')
                ->send();
            })
            ->visible(fn(): bool => ScheduledFileDeletion::canDeleteFiles())
            ->requiresConfirmation()
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
      'index'  => Pages\ListScheduledFileDeletions::route('/'),
      'create' => Pages\CreateScheduledFileDeletion::route('/create'),
      'edit'   => Pages\EditScheduledFileDeletion::route('/{record}/edit'),
    ];
  }
}
