<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeResource\Pages;
use App\Filament\Resources\AttributeResource\RelationManagers\AttributeValuesRelationManager;
use App\Models\Attribute;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-2-stack';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Menampilkan kolom nama atribut
                Tables\Columns\TextColumn::make('name')
                    ->label('Attribute Name')
                    ->searchable(),

                // Menampilkan nilai atribut dari relasi `attributeValues`
                Tables\Columns\TextColumn::make('values')
                    ->label('Attribute Values')
                    ->getStateUsing(fn ($record) => $record->attributeValues->pluck('value')->join(', ')) // Mengambil nilai atribut menggunakan relasi attributeValues
                    ->badge()
                    ->color('warning')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Attribute')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Attribute Name'),
                    ])->columns(1),
                
                Section::make('Values')
                    ->schema([
                        TextEntry::make('values')
                            ->label('Attribute Values')
                            ->getStateUsing(fn ($record) => $record->attributeValues->pluck('value')->join(', ')),
                    ])->columns(1),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AttributeValuesRelationManager::class, // Gunakan relasi ke AttributeValues
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttributes::route('/'),
            'create' => Pages\CreateAttribute::route('/create'),
            'edit' => Pages\EditAttribute::route('/{record}/edit'),
        ];
    }
}
