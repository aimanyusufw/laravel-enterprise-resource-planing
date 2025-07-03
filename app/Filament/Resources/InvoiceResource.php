<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationGroup = 'Finance & Accounting';

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Select::make('sales_order_id')
                        ->label('Related Sales Order')
                        ->helperText("Select the Sales Order linked to this record. You can search by customer name or Sales Order ID.")
                        ->placeholder("Example: Cust. Customer Name / Order Date: Jan 01, 2001 / Status: Approved / Total: $00,000")
                        ->relationship(
                            name: 'salesOrder',
                            titleAttribute: 'id',
                            modifyQueryUsing: fn(Builder $query) => $query->has('workOrder')
                        )
                        ->searchable()
                        ->preload()
                        ->getOptionLabelFromRecordUsing(function (SalesOrder $record) {
                            $customerName = $record->customer->customer_name ?? 'Customer name empty';
                            $orderDate = Carbon::parse($record->order_date)->format('M d, Y');
                            $orderStatus = ucfirst($record->status);
                            $totalAmount = number_format($record->total_amount, 2, '.', ',');
                            return "Cust. {$customerName} / Order Date: {$orderDate} / Status: {$orderStatus} / Total: $" . $totalAmount;
                        })
                        ->getOptionLabelUsing(function ($value, $state, $record) {
                            if (!$record) {
                                return 'Select Sales Order';
                            }
                            $customerName = $record->customer->customer_name ?? 'Customer name empty';
                            $orderDate = Carbon::parse($record->order_date)->format('M d, Y');
                            $orderStatus = ucfirst($record->status);
                            $totalAmount = number_format($record->total_amount, 2, '.', ',');
                            return "Cust. {$customerName} / Order Date: {$orderDate} / Status: {$orderStatus} / Total: $" . $totalAmount;
                        }),

                    Forms\Components\DatePicker::make('invoice_date')
                        ->label('Invoice Date')
                        ->helperText("Select the date the invoice was issued.")
                        ->suffixIcon('heroicon-o-calendar-days')
                        ->placeholder("mm / dd / YYYY")
                        ->native(false)
                        ->required(),

                    Forms\Components\DatePicker::make('due_date')
                        ->label('Due Date')
                        ->helperText("Select the date the payment is due for this invoice.")
                        ->suffixIcon('heroicon-o-calendar-days')
                        ->placeholder("mm / dd / YYYY")
                        ->native(false),

                    Forms\Components\TextInput::make('total_amount')
                        ->label('Total Amount')
                        ->helperText("Enter the total amount of the invoice.")
                        ->mask(RawJs::make('$money($input)'))
                        ->prefix('$')
                        ->stripCharacters(',')
                        ->required()
                        ->numeric(),

                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->helperText("Select the current status of the invoice.")
                        ->options([
                            "unpaid" => "Unpaid",
                            "paid" => "Paid",
                            "overdue" => "Overdue"
                        ])
                        ->native(false)
                        ->required(),
                ])->columns(["sm" => 1])->columnSpan(2),
                Forms\Components\Section::make("Time Stamps")
                    ->description("details of when data was changed and also created")
                    ->schema([
                        Forms\Components\Placeholder::make("created_at")
                            ->content(fn(?Invoice $record): string => $record ? date_format($record->created_at, "M d, Y") : "-"),
                        Forms\Components\Placeholder::make("updated_at")
                            ->content(fn(?Invoice $record): string => $record ? date_format($record->updated_at, "M d, Y") : "-"),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sales_order_id')
                    ->label("Sales order")
                    ->url(function (Invoice $record): ?string {
                        if (auth()->user()->can('update_sales_order')) {
                            return SalesOrder::getUrl('edit', ['record' => $record->sales_order_id]);
                        }
                        return null;
                    })
                    ->formatStateUsing(function (Invoice $record): ?string {
                        return 'Cst. ' . $record->salesOrder->customer->customer_name . " / Od. " .  date_format($record->salesOrder->order_date, "M d, Y") . ' / Sta. ' . $record->salesOrder->status . ' / Ta. $' . number_format($record->salesOrder->total_amount);
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('invoice_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->prefix("$")
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->placeholder("Invoice not done")
                    ->color(fn(string $state): string => match ($state) {
                        'unpaid' => 'warning',
                        'paid' => 'success',
                        'overdue' => 'danger',
                    })
                    ->searchable(),
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
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateDescription('Create new invoice data here.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create invoice')
                    ->url(InvoiceResource::getUrl('create'))
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
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
