<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Filament\Resources\QuoteResource\RelationManagers;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quote;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

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
                    ->default(request()->has('customer_id') ? request()->get('customer_id') : null)
                    ->required(),
                Section::make()
                    ->columns(1)
                    ->schema([
                        Forms\Components\Repeater::make('quoteProducts')
                            ->label(__('Quote products'))
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label(__('Product name'))
                                    ->relationship('product', 'name')
                                    ->disableOptionWhen(function ($value, $state, Get $get) {
                                        return collect($get('../*.product_id'))
                                            ->reject(fn($id) => $id == $state)
                                            ->filter()
                                            ->contains($value);
                                    })
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, Set $set, $livewire) {
                                        $set('price', Product::find($get('product_id'))->price);
                                        self::updateTotals($get, $livewire);
                                    })
                                    ->required(),
                                Forms\Components\TextInput::make('price')
                                    ->label(__('Price'))
                                    ->required()
                                    ->numeric()
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, $livewire) {
                                        self::updateTotals($get, $livewire);
                                    })
                                    ->prefix('$'),
                                Forms\Components\TextInput::make('quantity')
                                    ->label(__('Quantity'))
                                    ->integer()
                                    ->default(1)
                                    ->required()
                                    ->live()
                            ])
                            ->live()
                            ->afterStateUpdated(function (Get $get, $livewire) {
                                self::updateTotals($get, $livewire);
                            })
                            ->afterStateHydrated(function (Get $get, $livewire) {
                                self::updateTotals($get, $livewire);
                            })
                            ->deleteAction(
                                fn(Action $action) => $action->after(fn(Get $get, $livewire) => self::updateTotals($get, $livewire)),
                            )
                            ->reorderable(false)
                            ->columns(3)
                    ]),
                Section::make()
                    ->columns(1)
                    ->maxWidth('1/2')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label(__('Subtotal'))
                            ->numeric()
                            ->readOnly()
                            ->prefix('$')
                            ->afterStateUpdated(function (Get $get, $livewire) {
                                self::updateTotals($get, $livewire);
                            }),
                        Forms\Components\TextInput::make('taxes')
                            ->label(__('Taxes'))
                            ->suffix('%')
                            ->required()
                            ->numeric()
                            ->default(20)
                            ->live(true)
                            ->afterStateUpdated(function (Get $get, $livewire) {
                                self::updateTotals($get, $livewire);
                            }),
                        Forms\Components\TextInput::make('total')
                            ->label(__('Total'))
                            ->numeric()
                            ->readOnly()
                            ->prefix('$')
                    ])
            ]);
    }

    public static function getModelLabel(): string
    {
        return __('quote');
    }

    public static function getNavigationLabel(): string
    {
        return __('Quotes');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuotes::route('/'),
            'create' => Pages\CreateQuote::route('/create'),
            'view' => Pages\ViewQuote::route('/{record}'),
            'edit' => Pages\EditQuote::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                ViewEntry::make('invoice')
                    ->columnSpanFull()
                    ->viewData([
                        'record' => $infolist->record
                    ])
                    ->view('infolists.components.quote-invoice-view')
            ]);
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
                Tables\Columns\TextColumn::make('taxes')
                    ->label(__('Taxes'))
                    ->numeric()
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label(__('Subtotal'))
                    ->numeric()
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label(__('Total'))
                    ->numeric()
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(function ($record) {
                return Pages\ViewQuote::getUrl([$record]);
            });
    }

    public static function updateTotals(Get $get, $livewire): void
    {
        // Retrieve the state path of the form. Most likely it's `data` but it could be something else.
        $statePath = $livewire->getFormStatePath();

        $products = data_get($livewire, $statePath . '.quoteProducts');
        if (collect($products)->isEmpty()) {
            return;
        }
        $selectedProducts = collect($products)->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));

        $prices = collect($products)->pluck('price', 'product_id');

        $subtotal = $selectedProducts->reduce(function ($subtotal, $product) use ($prices) {
            return $subtotal + ($prices[$product['product_id']] * $product['quantity']);
        }, 0);

        data_set($livewire, $statePath . '.subtotal', number_format($subtotal, 2, '.', ''));
        data_set($livewire, $statePath . '.total', number_format($subtotal + ($subtotal * (data_get($livewire, $statePath . '.taxes') / 100)), 2, '.', ''));
    }
}
