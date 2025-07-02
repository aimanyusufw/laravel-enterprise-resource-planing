<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuotationResource\Pages;
use App\Filament\Resources\QuotationResource\RelationManagers;
use App\Models\Customer;
use App\Models\Quotation;
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

class QuotationResource extends Resource
{
    protected static ?string $model = Quotation::class;

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

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
                        ->required(),
                    Forms\Components\DatePicker::make('quotation_date')
                        ->helperText("Select the date when the qutation was placed.")
                        ->required(),
                    Forms\Components\DatePicker::make('valid_until')
                        ->helperText("Select the date when the qutation was valid."),
                    Forms\Components\Select::make('status')
                        ->helperText("Set the current status of this qutation. (e.g., Draft, Submitted, Approved)")
                        ->options([
                            "draft" => "Draft",
                            "submitted" => "Submitted",
                            "approved" => "Approved",
                            "rejected" => "Rejected",
                        ])
                        ->required(),
                    Forms\Components\TextInput::make('total_amount')
                        ->label("Total Amount")
                        ->helperText("The total amount of the qutation in USD.")
                        ->mask(RawJs::make('$money($input)'))
                        ->prefix('$')
                        ->stripCharacters(',')
                        ->placeholder("e.g. 120.00")
                        ->numeric(),
                ])->columns(["sm" => 1])->columnSpan(2),
                Forms\Components\Section::make("Time Stamps")
                    ->description("details of when data was changed and also created")
                    ->schema([
                        Forms\Components\Placeholder::make("created_at")
                            ->content(fn(?Quotation $record): string => $record ? date_format($record->created_at, "M d, Y") : "-"),
                        Forms\Components\Placeholder::make("updated_at")
                            ->content(fn(?Quotation $record): string => $record ? date_format($record->updated_at, "M d, Y") : "-"),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.customer_name')
                    ->url(function (Quotation $record): ?string {
                        if (auth()->user()->can('update_customer')) {
                            return CustomerResource::getUrl('edit', ['record' => $record->customer_id]);
                        }
                        return null;
                    })
                    ->openUrlInNewTab()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quotation_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->date()
                    ->placeholder("Valid untill is empty")
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->prefix("$")
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.username')
                    ->url(function (Quotation $record): ?string {
                        if ($record->user_id !== auth()->id() && auth()->user()->can('update_user')) {
                            return UserResource::getUrl('edit', ['record' => $record->customer_id]);
                        }
                        return null;
                    })
                    ->formatStateUsing(function (string $state, Quotation $record): string {
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
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateDescription('Create new quotation data here.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create quotation')
                    ->url(QuotationResource::getUrl('create'))
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
            'index' => Pages\ListQuotations::route('/'),
            'create' => Pages\CreateQuotation::route('/create'),
            'edit' => Pages\EditQuotation::route('/{record}/edit'),
        ];
    }
}
