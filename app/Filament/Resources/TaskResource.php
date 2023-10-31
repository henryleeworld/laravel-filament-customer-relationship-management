<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Customer;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->label(__('Customer name'))
                    ->searchable()
                    ->relationship('customer')
                    ->getOptionLabelFromRecordUsing(fn(Customer $record) => $record->first_name . ' ' . $record->last_name)
                    ->searchable(['first_name', 'last_name'])
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label(__('Employee name'))
                    ->preload()
                    ->searchable()
                    ->relationship('employee', 'name'),
                Forms\Components\RichEditor::make('description')
                    ->label(__('Description'))
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\DatePicker::make('due_date')
                    ->label(__('Due date')),
                Forms\Components\Toggle::make('is_completed')
                    ->label(__('Is completed'))
                    ->required(),
            ]);
    }

    public static function getModelLabel(): string
    {
        return __('task');
    }

    public static function getNavigationLabel(): string
    {
        return __('Tasks');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
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
                Tables\Columns\TextColumn::make('customer.first_name')
                    ->label(__('Customer name'))
                    ->formatStateUsing(function ($record) {
                        return $record->customer->first_name . ' ' . $record->customer->last_name;
                    })
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                Tables\Columns\TextColumn::make('employee.name')
                    ->label(__('Employee name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label(__('Description'))
                    ->html(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label(__('Due date'))
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_completed')
                    ->label(__('Is completed'))
                    ->boolean(),
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
                Tables\Actions\Action::make('Complete')
                    ->label(__('Complete'))
                    ->hidden(fn(Task $record) => $record->is_completed)
                    ->icon('heroicon-m-check-badge')
                    ->modalHeading(__('Mark task as completed?'))
                    ->modalDescription(__('Are you sure you want to mark this task as completed?'))
                    ->action(function (Task $record) {
                        $record->is_completed = true;
                        $record->save();

                        Notification::make()
                            ->title(__('Task marked as completed'))
                            ->success()
                            ->send();
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort(function ($query) {
                return $query->orderBy('due_date', 'asc')
                    ->orderBy('id', 'desc');
            });
    }
}
