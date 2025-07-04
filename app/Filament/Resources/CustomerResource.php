<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Parfaitementweb\FilamentCountryField\Forms\Components\Country;
use Parfaitementweb\FilamentCountryField\Tables\Columns\CountryColumn;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('customer_name')
                        ->label("Name")
                        ->required()
                        ->placeholder("Jhon Doe")
                        ->columnSpanFull()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('contact_person')
                        ->placeholder("eg Email, Phone, Position ")
                        ->columnSpanFull()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->placeholder("customer@example.com")
                        ->email()
                        ->maxLength(255),
                    PhoneInput::make('phone')
                        ->validateFor(
                            lenient: true, // default: false
                        )
                        ->displayNumberFormat(PhoneInputNumberType::INTERNATIONAL)
                        ->inputNumberFormat(PhoneInputNumberType::INTERNATIONAL)
                        ->placeholder("9213870123"),
                    Forms\Components\Textarea::make('address')
                        ->placeholder("PT. Maju Jaya, 123 Jalan Merdeka, Kebayoran Baru, Jakarta, 12190, Indonesia")
                        ->rows(4)
                        ->columnSpanFull()
                        ->maxLength(255),
                    Country::make('country')
                        ->searchable(),
                    Forms\Components\TextInput::make('city')
                        ->placeholder("Jakarta")
                        ->maxLength(255),
                ])->columns(["sm" => 2])->columnSpan(2),
                Forms\Components\Section::make("Time Stamps")
                    ->description("details of when data was changed and also created")
                    ->schema([
                        Forms\Components\Placeholder::make("created_at")
                            ->content(fn(?Customer $record): string => $record ? date_format($record->created_at, "M d, Y") : "-"),
                        Forms\Components\Placeholder::make("updated_at")
                            ->content(fn(?Customer $record): string => $record ? date_format($record->updated_at, "M d, Y") : "-"),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->label("Name")
                    ->searchable(),
                Tables\Columns\TextColumn::make('contact_person')
                    ->placeholder("Contact Person is empty")
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->placeholder("Email is empty")
                    ->searchable(),
                PhoneColumn::make('phone')
                    ->displayFormat(PhoneInputNumberType::INTERNATIONAL),
                CountryColumn::make('country')
                    ->placeholder("-")
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
            ->emptyStateIcon('heroicon-o-identification')
            ->emptyStateDescription('Create new customer data here.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create supplier')
                    ->url(CustomerResource::getUrl('create'))
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
