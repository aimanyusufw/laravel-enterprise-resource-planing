<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use function PHPSTORM_META\map;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Production & Manufacturing';

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('product_name')
                        ->label('Product Name')
                        ->required()
                        ->placeholder('e.g. Cotton T-Shirt')
                        ->helperText('Enter the full name of the product.')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->label('Product Description')
                        ->rows(4)
                        ->placeholder('e.g. High-quality cotton t-shirt suitable for all seasons.')
                        ->helperText('Provide details such as material, usage, or size information.')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('standard_cost')
                        ->label('Standard Cost')
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->prefix('$')
                        ->placeholder('e.g. 5.00')
                        ->helperText('Base cost to produce or acquire the product.')
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('selling_price')
                        ->label('Selling Price')
                        ->helperText('Selling price for the end customer.')
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters(',')
                        ->prefix('$')
                        ->placeholder("e.g. 120.00")
                        ->numeric()
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('stock_on_hand')
                        ->label('Stock on Hand')
                        ->required()
                        ->numeric()
                        ->placeholder('e.g. 150')
                        ->helperText('Number of items currently in stock.')
                        ->columnSpan(1),
                    Forms\Components\Select::make('unit_of_measure')
                        ->label('Unit of Measure')
                        ->required()
                        ->searchable()
                        ->placeholder('Select a unit')
                        ->helperText('Choose the appropriate unit to measure the product.')
                        ->options([
                            "unit" => "Unit",
                            "Kg" => "Kilogram",
                            "L" => "Liter",
                            "M" => "Meter",
                            "box" => "Box",
                        ])
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('min_stock_level')
                        ->label('Minimum Stock Level')
                        ->required()
                        ->numeric()
                        ->placeholder('e.g. 20')
                        ->helperText('Set the minimum level before restocking is needed.')
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('max_stock_level')
                        ->label('Maximum Stock Level')
                        ->required()
                        ->numeric()
                        ->placeholder('e.g. 500')
                        ->helperText('Maximum quantity allowed in inventory.')
                        ->columnSpan(1),
                ])->columns(["sm" => 2])->columnSpan(2),
                Forms\Components\Section::make("Time Stamps")
                    ->description("details of when data was changed and also created")
                    ->schema([
                        Forms\Components\Placeholder::make("created_at")
                            ->content(fn(?Product $record): string => $record ? date_format($record->created_at, "M d, Y") : "-"),
                        Forms\Components\Placeholder::make("updated_at")
                            ->content(fn(?Product $record): string => $record ? date_format($record->updated_at, "M d, Y") : "-"),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('standard_cost')
                    ->numeric()
                    ->placeholder("Standard Cost is empty")
                    ->prefix("$ ")
                    ->sortable(),
                Tables\Columns\TextColumn::make('selling_price')
                    ->numeric()
                    ->placeholder("Selling Price is empty")
                    ->prefix("$ ")
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_of_measure')
                    ->label("Profit margin")
                    ->formatStateUsing(function (string $state, Product $record): string {
                        $salePrice = $record->selling_price ?? 0;
                        $cost = $record->standard_cost ?? 0;
                        $profit = $salePrice - $cost;
                        $percent = floor((($salePrice - $cost) / $salePrice) * 100);
                        return  "{$percent}% / " . "$" . "{$profit}";
                    })
                    ->searchable(false),
                Tables\Columns\TextColumn::make('stock_on_hand')
                    ->formatStateUsing(function (string $state, Product $record): string {
                        $stockOnHand = $record->stock_on_hand ?? 0;
                        $mou = $record->unit_of_measure ?? '';
                        return "{$stockOnHand} {$mou}";
                    })
                    ->sortable(['stock_on_hand', 'unit_of_measure'])
                    ->searchable(['stock_on_hand', 'unit_of_measure']),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->emptyStateIcon('heroicon-o-cube')
            ->emptyStateDescription('Create new product data here.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create product')
                    ->url(ProductResource::getUrl('create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
