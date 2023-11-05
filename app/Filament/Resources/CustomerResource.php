<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Filament\Resources\QuoteResource\Pages\CreateQuote;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\PipelineStage;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('Employee Information'))
                    ->schema([
                        Forms\Components\Select::make('employee_id')
                            ->label(__('Employee name'))
                            ->options(User::where('role_id', Role::where('name', 'Employee')->first()->id)->pluck('name', 'id'))
                    ])
                    ->hidden(!auth()->user()->isAdmin()),
                Forms\Components\Section::make(__('Customer Details'))
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label(__('First name'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('last_name')
                            ->label(__('Last name'))
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone_number')
                            ->label(__('Phone number'))
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label(__('Description'))
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns(),
                Forms\Components\Section::make(__('Lead Details'))
                    ->schema([
                        Forms\Components\Select::make('lead_source_id')
                            ->label(__('Lead source'))
                            ->relationship('leadSource', 'name'),
                        Forms\Components\Select::make('tags')
                            ->label(__('Tags'))
                            ->relationship('tags', 'name')
                            ->multiple(),
                        Forms\Components\Select::make('pipeline_stage_id')
                            ->label(__('Pipeline stage'))
                            ->relationship('pipelineStage', 'name', function ($query) {
                                $query->orderBy('position', 'asc');
                            })
                            ->default(PipelineStage::where('is_default', true)->first()?->id)
                    ])
                    ->columns(3),
                Forms\Components\Section::make(__('Documents'))
                    ->visibleOn('edit')
                    ->schema([
                        Forms\Components\Repeater::make('documents')
                            ->relationship('documents')
                            ->hiddenLabel()
                            ->reorderable(false)
                            ->addActionLabel(__('Add Document'))
                            ->schema([
                                Forms\Components\FileUpload::make('file_path')
                                    ->label(__('File path'))
                                    ->required(),
                                Forms\Components\Textarea::make('comments')
                                    ->label(__('Comments')),
                            ])
                            ->columns()
                    ]),
                Forms\Components\Section::make(__('Additional fields'))
                    ->schema([
                        Forms\Components\Repeater::make('fields')
                            ->hiddenLabel()
                            ->relationship('customFields')
                            ->schema([
                                Forms\Components\Select::make('custom_field_id')
                                    ->label(__('Field Type'))
                                    ->options(CustomField::pluck('name', 'id')->toArray())
                                    ->disableOptionWhen(function ($value, $state, Get $get) {
                                        return collect($get('../*.custom_field_id'))
                                            ->reject(fn($id) => $id === $state)
                                            ->filter()
                                            ->contains($value);
                                    })
                                    ->required()
                                    ->searchable()
                                    ->live(),
                                Forms\Components\TextInput::make('value')
                                    ->label(__('Value'))
                                    ->required()
                            ])
                            ->addActionLabel(__('Add another Field'))
                            ->columns(),
                    ]),
            ]);
    }

    public static function getModelLabel(): string
    {
        return __('customer');
    }

    public static function getNavigationLabel(): string
    {
        return __('Customers');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
            'view' => Pages\ViewCustomer::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infoList(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make(__('Personal Information'))
                    ->schema([
                        TextEntry::make('first_name')
                            ->label(__('First name')),
                        TextEntry::make('last_name')
                            ->label(__('Last name')),
                    ])
                    ->columns(),
                Section::make(__('Contact Information'))
                    ->schema([
                        TextEntry::make('email')
                            ->label(__('Email')),
                        TextEntry::make('phone_number')
                            ->label(__('Phone number')),
                    ])
                    ->columns(),
                Section::make(__('Additional Details'))
                    ->schema([
                        TextEntry::make('description')
                            ->label(__('Description')),
                    ]),
                Section::make(__('Lead and Stage Information'))
                    ->schema([
                        TextEntry::make('leadSource.name')
                            ->label(__('Lead source')),
                        TextEntry::make('pipelineStage.name')
                            ->label(__('Pipeline stage')),
                    ])
                    ->columns(),
                Section::make(__('Additional fields'))
                    ->hidden(fn($record) => $record->customFields->isEmpty())
                    ->schema(
                        fn($record) => $record->customFields->map(function ($customField) {
                            return TextEntry::make($customField->customField->name)
                                ->label($customField->customField->name)
                                ->default($customField->value);
                        })->toArray()
                    )
                    ->columns(),
                Section::make(__('Documents'))
                    ->hidden(fn($record) => $record->documents->isEmpty())
                    ->schema([
                        RepeatableEntry::make('documents')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('file_path')
                                    ->label(__('Document'))
                                    ->formatStateUsing(fn() => __('Download Document'))
                                    ->url(fn($record) => Storage::url($record->file_path), true)
                                    ->badge()
                                    ->color(Color::Blue),
                                TextEntry::make('comments'),
                            ])
                            ->columns()
                    ]),
                Section::make(__('Pipeline Stage History and Notes'))
                    ->schema([
                        ViewEntry::make('pipelineStageLogs')
                            ->label('')
                            ->view('infolists.components.pipeline-stage-history-list')
                    ])
                    ->collapsible(),
                Tabs::make(__('Tasks'))
                    ->tabs([
                        Tabs\Tab::make('Completed')
                            ->label(__('Completed'))
                            ->badge(fn($record) => $record->completedTasks->count())
                            ->schema([
                                RepeatableEntry::make('completedTasks')
                                    ->hiddenLabel()
                                    ->schema([
                                        TextEntry::make('description')
                                            ->label(__('Description'))
                                            ->html()
                                            ->columnSpanFull(),
                                        TextEntry::make('employee.name')
                                            ->label(__('Employee name'))
                                            ->hidden(fn($state) => is_null($state)),
                                        TextEntry::make('due_date')
                                            ->label(__('Due date'))
                                            ->hidden(fn($state) => is_null($state))
                                            ->date(),
                                    ])
                                    ->columns()
                            ]),
                        Tabs\Tab::make('Incomplete')
                            ->label(__('Incomplete'))
                            ->badge(fn($record) => $record->incompleteTasks->count())
                            ->schema([
                                RepeatableEntry::make('incompleteTasks')
                                    ->hiddenLabel()
                                    ->schema([
                                        TextEntry::make('description')
                                            ->label(__('Description'))
                                            ->html()
                                            ->columnSpanFull(),
                                        TextEntry::make('employee.name')
                                            ->label(__('Employee name'))
                                            ->hidden(fn($state) => is_null($state)),
                                        TextEntry::make('due_date')
                                            ->label(__('Due date'))
                                            ->hidden(fn($state) => is_null($state))
                                            ->date(),
                                        TextEntry::make('is_completed')
                                            ->label(__('Is completed'))
                                            ->formatStateUsing(function ($state) {
                                                return $state ? __('Yes') : __('No');
                                            })
                                            ->suffixAction(
                                                Action::make('complete')
                                                    ->label(__('Complete'))
                                                    ->button()
                                                    ->requiresConfirmation()
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
                                            ),
                                    ])
                                    ->columns(3)
                            ])
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->with('tags');
            })
            ->columns([
                Tables\Columns\TextColumn::make('employee.name')
                    ->label(__('Employee name'))
                    ->hidden(!auth()->user()->isAdmin()),
                Tables\Columns\TextColumn::make('first_name')
                    ->label(__('Customer name'))
                    ->formatStateUsing(function ($record) {
                        $tagsList = view('customer.tagsList', ['tags' => $record->tags])->render();

                        return $record->first_name . ' ' . $record->last_name . ' ' . $tagsList;
                    })
                    ->html()
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label(__('Phone number'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('leadSource.name')
                    ->label(__('Lead source')),
                Tables\Columns\TextColumn::make('pipelineStage.name')
                    ->label(__('Pipeline stage')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->hidden(fn($record) => $record->trashed()),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\Action::make('Move to Stage')
                        ->label(__('Move to Stage'))
                        ->hidden(fn($record) => $record->trashed())
                        ->icon('heroicon-m-pencil-square')
                        ->form([
                            Forms\Components\Select::make('pipeline_stage_id')
                                ->label(__('Status'))
                                ->options(PipelineStage::pluck('name', 'id')->toArray())
                                ->default(function (Customer $record) {
                                    $currentPosition = $record->pipelineStage->position;
                                    return PipelineStage::where('position', '>', $currentPosition)->first()?->id;
                                }),
                            Forms\Components\Textarea::make('notes')
                                ->label(__('Notes'))
                        ])
                        ->action(function (Customer $customer, array $data): void {
                            $customer->pipeline_stage_id = $data['pipeline_stage_id'];
                            $customer->save();

                            $customer->pipelineStageLogs()->create([
                                'pipeline_stage_id' => $data['pipeline_stage_id'],
                                'notes' => $data['notes'],
                                'user_id' => auth()->id()
                            ]);

                            Notification::make()
                                ->title(__('Customer Pipeline Updated'))
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('Add Task')
                        ->label(__('Add Task'))
                        ->icon('heroicon-s-clipboard-document')
                        ->form([
                            Forms\Components\RichEditor::make('description')
                                ->label(__('Description'))
                                ->required(),
                            Forms\Components\Select::make('user_id')
                                ->label(__('Employee name'))
                                ->preload()
                                ->searchable()
                                ->relationship('employee', 'name'),
                            Forms\Components\DatePicker::make('due_date')
                                ->label(__('Due date'))
                                ->native(false),

                        ])
                        ->action(function (Customer $customer, array $data) {
                            $customer->tasks()->create($data);

                            Notification::make()
                                ->title(__('Task created successfully'))
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\Action::make('Create Quote')
                        ->label(__('Create Quote'))
                        ->icon('heroicon-m-book-open')
                        ->url(function ($record) {
                            return CreateQuote::getUrl(['customer_id' => $record->id]);
                        })
                ])
            ])
            ->recordUrl(function ($record) {
                if ($record->trashed()) {
                    return null;
                }

                return Pages\ViewCustomer::getUrl([$record->id]);
            })
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
