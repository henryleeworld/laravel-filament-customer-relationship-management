<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadSourceResource\Pages;
use App\Filament\Resources\LeadSourceResource\RelationManagers;
use App\Models\LeadSource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeadSourceResource extends Resource
{
    protected static ?string $model = LeadSource::class;

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('Name'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function getModelLabel(): string
    {
        return __('lead source');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Lead Sources');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeadSources::route('/'),
            'create' => Pages\CreateLeadSource::route('/create'),
            'edit' => Pages\EditLeadSource::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function ($data, $record) {
                        if ($record->customers()->count() > 0) {
                            Notification::make()
                                ->danger()
                                ->title(__('Lead Source is in use'))
                                ->body(__('Lead Source is in use by customers.'))
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->success()
                            ->title(__('Lead Source deleted'))
                            ->body(__('Lead Source has been deleted.'))
                            ->send();

                        $record->delete();
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
