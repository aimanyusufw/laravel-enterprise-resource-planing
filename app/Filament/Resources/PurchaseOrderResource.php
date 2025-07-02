<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationGroup = 'Supply Chain Management';

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Select::make('supplier_id')
                        ->helperText("Select the supplier who made the supply.")
                        ->searchable()
                        ->relationship("supplier", "supplier_name")
                        ->required(),
                    Forms\Components\DatePicker::make('order_date')
                        ->helperText("Select the date when the order was placed.")
                        ->required(),
                    Forms\Components\DatePicker::make('delivery_date')
                        ->helperText("Select the date when the order was delivered."),
                    Forms\Components\Select::make('status')
                        ->helperText("Set the current status of this qutation. (e.g., Draft, Received, Approved)")
                        ->options([
                            "draft" => "Draft",
                            "pending approval" => "Pending Approval",
                            "approved" => "Approved",
                            "received" => "Received",
                            "closed" => "Closed",
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('total_amount')
                        ->helperText("The total amount of the qutation in USD.")
                        ->mask(RawJs::make('$money($input)'))
                        ->prefix('$')
                        ->stripCharacters(',')
                        ->placeholder("e.g. 120.00")
                        ->prefix("$")
                        ->numeric(),
                ])->columns(["sm" => 1])->columnSpan(2),
                Forms\Components\Section::make("Time Stamps")
                    ->description("details of when data was changed and also created")
                    ->schema([
                        Forms\Components\Placeholder::make("created_at")
                            ->content(fn(?PurchaseOrder $record): string => $record ? date_format($record->created_at, "M d, Y") : "-"),
                        Forms\Components\Placeholder::make("updated_at")
                            ->content(fn(?PurchaseOrder $record): string => $record ? date_format($record->updated_at, "M d, Y") : "-"),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supplier.supplier_name')
                    ->url(function (PurchaseOrder $record): ?string {
                        if (auth()->user()->can('update_supplier')) {
                            return SupplierResource::getUrl('edit', ['record' => $record->supplier_id]);
                        }
                        return null;
                    })
                    ->openUrlInNewTab()
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending approval' => 'warning',
                        'approved' => 'info',
                        'received' => 'success',
                        'closed' => 'danger',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->url(function (PurchaseOrder $record): ?string {
                        if ($record->user_id !== auth()->id() && auth()->user()->can('update_user')) {
                            return UserResource::getUrl('edit', ['record' => $record->customer_id]);
                        }
                        return null;
                    })
                    ->formatStateUsing(function (string $state, PurchaseOrder $record): string {
                        $user = $record->user;
                        return $user->id == auth()->id() ? "You" : $user->username;
                    })
                    ->openUrlInNewTab()
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
            ->emptyStateIcon('heroicon-o-currency-dollar')
            ->emptyStateDescription('Create new purchase order data here.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create purchase order')
                    ->url(PurchaseOrderResource::getUrl('create'))
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
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
