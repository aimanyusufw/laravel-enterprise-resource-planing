<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Invoice;
use App\Models\Payment;
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
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationGroup = 'Finance & Accounting';

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\Select::make('invoice_id')
                        ->label('Related Invoice')
                        ->helperText("Select the Sales Order this work order is linked to. You can search by customer name or Invoice ID.")
                        ->placeholder("Example: Cust. ABC / Date: Jan 01, 2001 / Status: Approved / SO Total: $00,000")
                        ->relationship(
                            name: 'invoice',
                            titleAttribute: 'id',
                            modifyQueryUsing: fn(Builder $query) => $query->where("status", "!=", "paid")
                        )
                        ->searchable()
                        ->preload()
                        ->getOptionLabelFromRecordUsing(function (Invoice $record) {
                            $customerName = $record->salesOrder->customer->customer_name ?? 'Customer name empty';
                            $orderDate = Carbon::parse($record->salesOrder->order_date)->format('M d, Y');
                            $orderStatus = ucfirst($record->salesOrder->status);
                            $totalAmount = number_format($record->salesOrder->total_amount, 2, '.', ',');
                            $invoiceAmount = number_format($record->total_amount, 2, '.', ',');
                            return "Cust. {$customerName} / Date: {$orderDate} / Status: {$orderStatus} / SO Total: $" . $totalAmount . " / Invoice Total: $" . $invoiceAmount;
                        })
                        ->getOptionLabelUsing(function ($value, $state, $record) {
                            if (!$record) {
                                return 'Select Invoice';
                            }
                            $customerName = $record->salesOrder->customer->customer_name ?? 'Customer name empty';
                            $orderDate = Carbon::parse($record->salesOrder->order_date)->format('M d, Y');
                            $orderStatus = ucfirst($record->salesOrder->status);
                            $totalAmount = number_format($record->salesOrder->total_amount, 2, '.', ',');
                            $invoiceAmount = number_format($record->total_amount, 2, '.', ',');
                            return "Cust. {$customerName} / Date: {$orderDate} / Status: {$orderStatus} / SO Total: $" . $totalAmount . " / Invoice Total: $" . $invoiceAmount;
                        }),

                    Forms\Components\DatePicker::make('payment_date')
                        ->label('Payment Date')
                        ->helperText("Select the date the payment was received.")
                        ->native(false)
                        ->placeholder("mm / dd / yyyy")
                        ->suffixIcon("heroicon-o-calendar-days")
                        ->required(),

                    Forms\Components\TextInput::make('amount')
                        ->label('Payment Amount')
                        ->helperText("Enter the total amount of payment received.")
                        ->mask(RawJs::make('$money($input)'))
                        ->prefix('$')
                        ->stripCharacters(',')
                        ->required()
                        ->numeric(),

                    Forms\Components\TextInput::make('payment_method')
                        ->label('Payment Method')
                        ->helperText("Specify the payment method used (e.g., Bank Transfer, Cash, Credit Card).")
                        ->maxLength(255),
                ])->columns(["sm" => 1])->columnSpan(2),
                Forms\Components\Section::make("Time Stamps")
                    ->description("details of when data was changed and also created")
                    ->schema([
                        Forms\Components\Placeholder::make("created_at")
                            ->content(fn(?Payment $record): string => $record ? date_format($record->created_at, "M d, Y") : "-"),
                        Forms\Components\Placeholder::make("updated_at")
                            ->content(fn(?Payment $record): string => $record ? date_format($record->updated_at, "M d, Y") : "-"),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_id')
                    ->label("Sales order")
                    ->url(function (Payment $record): ?string {
                        if (auth()->user()->can('update_invoice')) {
                            return InvoiceResource::getUrl('edit', ['record' => $record->invoice_id]);
                        }
                        return null;
                    })
                    ->formatStateUsing(function (Payment $record): ?string {
                        return 'Cst. ' . $record->invoice->salesOrder->customer->customer_name . " / Od. " .  date_format($record->invoice->salesOrder->order_date, "M d, Y") . ' / Sta. ' . $record->invoice->salesOrder->status . ' / Ta. $' . number_format($record->invoice->salesOrder->total_amount);
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->prefix("$")
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.username')
                    ->label("Responsible")
                    ->url(function (Payment $record): ?string {
                        if ($record->user_id !== auth()->id() && auth()->user()->can('update_user')) {
                            return UserResource::getUrl('edit', ['record' => $record->customer_id]);
                        }
                        return null;
                    })
                    ->openUrlInNewTab()
                    ->formatStateUsing(function (string $state, Payment $record): string {
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
            ->emptyStateIcon('heroicon-o-credit-card')
            ->emptyStateDescription('Create new payment data here.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create payment')
                    ->url(PaymentResource::getUrl('create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                ActivityLogTimelineTableAction::make('Activities')
                    ->hidden(!auth()->user()->can("view_activitylog"))
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
