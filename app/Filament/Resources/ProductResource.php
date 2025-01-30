<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\ProductVariantsRelationManager;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 2;

    protected static bool $canCreateAnother = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('General')
                    ->description('Fill general product information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->afterStateUpdated(function (Set $set, $state) {
                                $set('slug', Product::generateUniqueSlug($state));
                            })
                            ->live(onBlur: true)
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->readOnly()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('category_id')
                            ->relationship(name: 'category', titleAttribute: 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                            ]),
                    ])->columns(1),
                ]),
                Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Price & Stock')
                    ->schema([
                            Forms\Components\TextInput::make('price')
                                ->required()
                                ->numeric()
                                ->prefix('Rp'),
                            Forms\Components\TextInput::make('stock')
                                ->required()
                                ->numeric()
                        ])->columns(2),
                    Forms\Components\Group::make()
                    ->schema([
                            Forms\Components\Section::make('Settings')
                            ->schema([
                                Forms\Components\Toggle::make('is_new')
                                    ->label('New')
                                    ->inline(false)
                                    ->required(),
                                Forms\Components\Toggle::make('is_featured')
                                    ->label('Featured')
                                    ->inline(false)
                                    ->required(),
                                Forms\Components\Toggle::make('is_enabled')
                                    ->label('Status')
                                    ->inline(false)
                                    ->required(),
                            ])->columns(3)
                        ]),
                        Forms\Components\Group::make()
                    ->schema([
                        
                    ]),
                ]),
                 // Variant Selection Form
                // Forms\Components\Group::make()
                // ->schema([
                //     Forms\Components\Section::make('Product Variants')
                //         ->description('Add variants based on attributes')
                //         ->schema([
                //             Forms\Components\Repeater::make('attribute') // Repeater for variants
                //                 ->schema([
                //                     Select::make('attribute_id') // Kolom untuk memilih Attribute
                //                         ->label('Attribute') 
                //                         ->options(Attribute::all()->pluck('name', 'id')) // Menampilkan nama atribut dari tabel Attributes
                //                         ->searchable()
                //                         ->reactive() // Untuk memperbarui kolom berikutnya berdasarkan pilihan ini
                //                         ->required()
                //                         ->afterStateUpdated(function (Set $set, $state) {
                //                             // Setelah memilih attribute_id, kita set nilai attribute_value_id menjadi null
                //                             // dan update opsi untuk attribute_value_id
                //                             $set('attribute_value_id', null); // Reset value yang dipilih
                //                             $set('attribute_values', AttributeValue::where('attribute_id', $state)->pluck('value', 'id')); // Ambil nilai berdasarkan attribute_id
                //                         }),
                //                         // Select untuk memilih Attribute Value, ini akan berubah berdasarkan attribute_id
                //                         Select::make('attribute_value_id') // Kolom untuk memilih Attribute Value
                //                             ->label('Attribute Value') 
                //                             ->options(function (callable $get) {
                //                                 $attributeId = $get('attribute_id'); // Ambil attribute_id yang dipilih
                //                                 return AttributeValue::where('attribute_id', $attributeId)
                //                                     ->pluck('value', 'id'); // Ambil nilai dari AttributeValue yang sesuai dengan attribute_id
                //                             })
                //                             ->searchable()
                //                             ->required(), // Pilihan ini juga required
                //                     TextInput::make('stock')
                //                         ->numeric()
                //                         ->default(0)
                //                         ->required(),

                //                     TextInput::make('image_path')
                //                         ->label('Variant Image')
                //                         ->nullable(),
                                    
                //                     Toggle::make('is_enabled')
                //                         ->label('Variant Status')
                //                         ->default(true),
                //                 ])
                //                 ->columns(1),
                                
                //         ]),
                // ]),
                Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Description')
                    ->schema([
                        Forms\Components\MarkdownEditor::make('description')
                    ])  
                ]), 
                Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Image')
                    ->schema([
                        FileUpload::make('attachments')
                        ->multiple()
                    ])  
                ]),
               
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProductVariantsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            // 'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
