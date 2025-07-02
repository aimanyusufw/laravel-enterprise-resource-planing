<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkOrderResource\Pages;
use App\Filament\Resources\WorkOrderResource\RelationManagers;
use App\Models\SalesOrder;
use App\Models\WorkOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;

class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;

    protected static ?string $navigationGroup = 'Production & Manufacturing';

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('sales_order_id')
                            ->label('Related Sales Order')
                            ->helperText("Select the Sales Order this work order is linked to. You can search by customer name or Sales Order ID.")
                            ->placeholder("Cst. Customer Name / Od. Jan 01, 2001 / Sta. Approved / Ta. $00,000 ")
                            ->relationship(
                                name: 'salesOrder',
                                titleAttribute: 'id',
                                modifyQueryUsing: fn(Builder $query) => $query->whereDoesntHave('workOrder')
                            )
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(function (SalesOrder $record) {
                                $customerName = $record->customer->customer_name ?? 'Customer name is empty';
                                $orderDate = Carbon::parse($record->order_date)->format('M d, Y');
                                $orderStatus = ucfirst($record->status);
                                $totalAmount = number_format($record->total_amount, 2, ',', '.');
                                return "Cst. {$customerName} / Od. {$orderDate} / Sta. {$orderStatus} / Ta. $" . $totalAmount;
                            })
                            ->getOptionLabelUsing(function ($value, $state, $record) {
                                if (!$record) {
                                    return 'Select Sales Order';
                                }
                                $customerName = $record->customer->customer_name ?? 'Customer name is empty';
                                $orderDate = Carbon::parse($record->order_date)->format('M d, Y');
                                $orderStatus = ucfirst($record->order_status);
                                $totalAmount = number_format($record->total_amount, 2, ',', '.');
                                return "Cst. {$customerName} / Od. {$orderDate} / Sta. {$orderStatus} / Ta. $" . $totalAmount;
                            }),

                        Forms\Components\Select::make('product_id')
                            ->label("Associated Product")
                            ->helperText("Choose the specific product this work order will produce. You can search by product name.")
                            ->searchable()
                            ->relationship('product', "product_name")
                            ->required(),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Production Quantity')
                            ->placeholder("000")
                            ->helperText('Enter the total quantity of products to be manufactured or processed.')
                            ->required()
                            ->numeric(),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Planned Start Date')
                            ->helperText('Set the estimated date when work on this order will begin.')
                            ->placeholder("dd / mm / yyyy")
                            ->suffixIcon('heroicon-m-calendar-date-range')
                            ->native(false),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Planned End Date')
                            ->helperText('Set the estimated date when work on this order is expected to be completed.')
                            ->suffixIcon('heroicon-m-calendar-date-range')
                            ->placeholder("dd / mm / yyyy")
                            ->native(false),

                        Forms\Components\Select::make('status')
                            ->label('Work Order Status')
                            ->native(false)
                            ->helperText("Define the current progress status of this work order (e.g., Planned, In Progress, Completed, Canceled).")
                            ->options([
                                "planned" => "Planned",
                                "in progress" => "In Progress",
                                "completed" => "Completed",
                                "canceled" => "Canceled",
                            ])
                            ->required(),
                    ])
                    ->columns(["sm" => 1])
                    ->columnSpan(2),
                Forms\Components\Section::make("Time Stamps")
                    ->description("details of when data was changed and also created")
                    ->schema([
                        Forms\Components\Placeholder::make("created_at")
                            ->content(fn(?WorkOrder $record): string => $record ? date_format($record->created_at, "M d, Y") : "-"),
                        Forms\Components\Placeholder::make("updated_at")
                            ->content(fn(?WorkOrder $record): string => $record ? date_format($record->updated_at, "M d, Y") : "-"),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sales_order_id')
                    ->label("Sales order")
                    ->url(function (WorkOrder $record): ?string {
                        if (auth()->user()->can('update_sales_order')) {
                            return SalesOrder::getUrl('edit', ['record' => $record->sales_order_id]);
                        }
                        return null;
                    })
                    ->formatStateUsing(function (WorkOrder $record): ?string {
                        return 'Cst. ' . $record->salesOrder->customer->customer_name . " / Od. " .  date_format($record->salesOrder->order_date, "M d, Y") . ' / Sta. ' . $record->salesOrder->status . ' / Ta. $' . number_format($record->salesOrder->total_amount);
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.product_name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'planned' => 'warning',
                        'in progress' => 'info',
                        'completed' => 'success',
                        'canceled' => 'danger',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_id')
                    ->label("Responsible")
                    ->url(function (WorkOrder $record): ?string {
                        if ($record->user_id !== auth()->id() && auth()->user()->can('update_user')) {
                            return UserResource::getUrl('edit', ['record' => $record->user_id]);
                        }
                        return null;
                    })
                    ->formatStateUsing(function (string $state, WorkOrder $record): string {
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
            ->emptyStateIcon('heroicon-o-wrench-screwdriver')
            ->emptyStateDescription('Create new work order data here.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create work order')
                    ->url(WorkOrderResource::getUrl('create'))
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
            'index' => Pages\ListWorkOrders::route('/'),
            'create' => Pages\CreateWorkOrder::route('/create'),
            'edit' => Pages\EditWorkOrder::route('/{record}/edit'),
        ];
    }
}
