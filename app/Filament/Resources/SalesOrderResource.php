<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesOrderResource\Pages;
use App\Filament\Resources\SalesOrderResource\RelationManagers;
use App\Models\Customer;
use App\Models\SalesOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Select::make('customer_id')
                        ->label("Customer")
                        ->helperText("Select the customer who made the purchase.")
                        ->searchable()
                        ->relationship("customer", "customer_name")
                        ->placeholder("Search or select a customer")
                        ->required()
                        ->columnSpan(1),
                    Forms\Components\DatePicker::make('order_date')
                        ->label("Order Date")
                        ->helperText("Select the date when the order was placed.")
                        ->required()
                        ->placeholder("Pick a date")
                        ->columnSpan(1),
                    Forms\Components\Select::make('status')
                        ->label("Order Status")
                        ->helperText("Set the current status of this order. (e.g., Pending, Paid, Shipped)")
                        ->options([
                            "pending" => "Pending",
                            "approved" => "Approved",
                            "shipped" => "Shipped",
                            "completed" => "Completed",
                        ])
                        ->required()
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('total_amount')
                        ->label("Total Amount")
                        ->helperText("The total amount of the order in USD.")
                        ->mask(RawJs::make('$money($input)'))
                        ->prefix('$')
                        ->stripCharacters(',')
                        ->placeholder("e.g. 120.00")
                        ->numeric()
                        ->columnSpan(1),
                ])->columns(["sm" => 1])->columnSpan(2),
                Forms\Components\Section::make("Time Stamps")
                    ->description("details of when data was changed and also created")
                    ->schema([
                        Forms\Components\Placeholder::make("created_at")
                            ->content(fn(?SalesOrder $record): string => $record ? date_format($record->created_at, "M d, Y") : "-"),
                        Forms\Components\Placeholder::make("updated_at")
                            ->content(fn(?SalesOrder $record): string => $record ? date_format($record->updated_at, "M d, Y") : "-"),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.customer_name')
                    ->numeric()
                    ->label('Customer')
                    ->url(function (SalesOrder $record): ?string {
                        if (auth()->user()->can('update_customer')) {
                            return CustomerResource::getUrl('edit', ['record' => $record->customer_id]);
                        }
                        return null;
                    })
                    ->openUrlInNewTab()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'gray',
                        'submitted' => 'warning',
                        'shipped' => 'info',
                        'approved' => 'success',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.username')
                    ->url(function (SalesOrder $record): ?string {
                        if ($record->user_id !== auth()->id() && auth()->user()->can('update_user')) {
                            return UserResource::getUrl('edit', ['record' => $record->customer_id]);
                        }
                        return null;
                    })
                    ->openUrlInNewTab()
                    ->formatStateUsing(function (string $state, SalesOrder $record): string {
                        $user = $record->user;
                        return $user->id == auth()->id() ? "You" : $user->username;
                    })
                    ->sortable(),
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
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->emptyStateDescription('Create new sales order data here.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create sales order')
                    ->url(SalesOrderResource::getUrl('create'))
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
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
            'index' => Pages\ListSalesOrders::route('/'),
            'create' => Pages\CreateSalesOrder::route('/create'),
            'edit' => Pages\EditSalesOrder::route('/{record}/edit'),
        ];
    }
}
