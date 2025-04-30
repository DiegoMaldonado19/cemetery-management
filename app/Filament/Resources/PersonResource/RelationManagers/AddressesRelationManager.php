<?php

namespace App\Filament\Resources\PersonResource\RelationManagers;

use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $recordTitleAttribute = 'address_line';

    protected static ?string $title = 'Direcciones';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('department_id')
                    ->label('Departamento')
                    ->options(Department::pluck('name', 'id'))
                    ->required(),
                Forms\Components\TextInput::make('address_line')
                    ->label('Dirección')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('reference')
                    ->label('Referencia')
                    ->maxLength(65535),
                Forms\Components\Toggle::make('is_primary')
                    ->label('Dirección Principal')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('address_line')
                    ->label('Dirección')
                    ->searchable(),
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Departamento')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Principal')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_primary')
                    ->label('Dirección Principal')
                    ->query(fn(Builder $query): Builder => $query->where('is_primary', true))
                    ->toggle(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (RelationManager $livewire, array $data): mixed {
                        // Si la nueva dirección es principal, actualizar las demás para que no lo sean
                        if (isset($data['is_primary']) && $data['is_primary']) {
                            $livewire->getRelationship()
                                ->where('is_primary', true)
                                ->update(['is_primary' => false]);
                        }

                        // Añadir el CUI de la persona
                        $data['cui'] = $livewire->getOwnerRecord()->cui;

                        return $livewire->getRelationship()->create($data);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function (RelationManager $livewire, $record, array $data): mixed {
                        // Si la dirección editada se marca como principal, actualizar las demás
                        if (isset($data['is_primary']) && $data['is_primary'] && !$record->is_primary) {
                            $livewire->getRelationship()
                                ->where('is_primary', true)
                                ->update(['is_primary' => false]);
                        }

                        $record->update($data);

                        return $record;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
