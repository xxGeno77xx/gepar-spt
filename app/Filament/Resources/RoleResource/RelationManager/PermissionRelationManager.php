<?php

namespace App\Filament\Resources\RoleResource\RelationManager;

use App\Support\Database\PermissionsClass;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\BelongsToManyRelationManager;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\PermissionRegistrar;

class PermissionRelationManager extends BelongsToManyRelationManager
{
    protected static string $relationship = 'permissions';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label(strval(__('filament-authentication::filament-authentication.field.name'))),
                TextInput::make('guard_name')
                    ->label(strval(__('filament-authentication::filament-authentication.field.guard_name')))
                    ->default(config('auth.defaults.guard')),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(strval(__('filament-authentication::filament-authentication.field.name')))
                    ->searchable(),
                // ->getStateUsing( function (Permission $record){
                //     $traductions = array(
                //         "engines" => "engins",
                //         "create" => "ajouter",
                //         "read" => "voir",
                //         "update" => "modifier",
                //         "delete" => "supprimer",
                //     );
                //     return strtr( str_replace("_", ": ", $record['name']) , $traductions);
                //  }),
                TextColumn::make('guard_name')
                    ->label(strval(__('filament-authentication::filament-authentication.field.guard_name'))),

            ])
            ->filters([
                //
            ]);
    }

    public function afterAttach(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function afterDetach(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function getResourceTable(): Table
    {
        $table = Table::make();

        if (auth()->user()->hasPermissionTo(PermissionsClass::Roles_update()->value)) {
            $table->actions([
                $this->getViewAction(),
                // $this->getEditAction(),
                $this->getDetachAction(),
                // $this->getDeleteAction(),
            ]);
        }

        // $table->bulkActions(array_merge(
        //     ($this->canDeleteAny() ? [$this->getDeleteBulkAction()] : []),
        //     ($this->canDetachAny() ? [$this->getDetachBulkAction()] : []),
        // ));

        $table->headerActions(array_merge(
            // ($this->canCreate() ? [$this->getCreateAction()] : []),
            ($this->canAttach() ? [$this->getAttachAction()] : []),
        ));

        return $this->table($table);
    }

    protected static function getAttachFormRecordSelect(): Select
    {
        return Select::make('recordId')
            ->label(__('filament-support::actions/attach.single.modal.fields.record_id.label'))
            ->required()
            ->multiple()
            ->searchable()
            ->getSearchResultsUsing(static function (Select $component, BelongsToManyRelationManager $livewire, string $search): array {
                /** @var BelongsToMany $relationship */
                $relationship = $livewire->getRelationship();

                $titleColumnName = static::getRecordTitleAttribute();

                /** @var Builder $relationshipQuery */
                $relationshipQuery = $relationship->getRelated()->query()->orderBy($titleColumnName);

                $search = strtolower($search);

                /** @var Connection $databaseConnection */
                $databaseConnection = $relationshipQuery->getConnection();

                $searchOperator = match ($databaseConnection->getDriverName()) {
                    'pgsql' => 'ilike',
                    default => 'like',
                };

                $searchColumns = $component->getSearchColumns() ?? [$titleColumnName];
                $isFirst = true;

                $relationshipQuery->where(function (Builder $query) use ($isFirst, $search, $searchColumns, $searchOperator): Builder {
                    foreach ($searchColumns as $searchColumnName) {
                        $whereClause = $isFirst ? 'where' : 'orWhere';

                        $query->{$whereClause}(
                            $searchColumnName,
                            $searchOperator,
                            "%{$search}%",
                        );

                        $isFirst = false;
                    }

                    return $query;
                });

                $relatedKeyName = $relationship->getRelatedKeyName();

                return $relationshipQuery
                    ->when(
                        ! $livewire->allowsDuplicates(),
                        static fn (Builder $query): Builder => $query->whereDoesntHave(
                            $livewire->getInverseRelationshipName(),
                            static function (Builder $query) use ($livewire): Builder {
                                return $query->where($livewire->getOwnerRecord()->getQualifiedKeyName(), $livewire->getOwnerRecord()->getKey());
                            },
                        ),
                    )
                    //customized query to exclude  user_update and user_delete   #here
                    // ->whereNot('name', PermissionsClass::Users_update()->value)
                    // ->whereNot('name', PermissionsClass::Users_delete()->value)
                    ->get()
                    ->mapWithKeys(static fn (Model $record): array => [$record->{$relatedKeyName} => static::getRecordTitle($record)])
                    ->toArray();
            })
            ->getOptionLabelUsing(static fn (RelationManager $livewire, $value): ?string => static::getRecordTitle($livewire->getRelationship()->getRelated()->query()->find($value)))
            ->options(function (BelongsToManyRelationManager $livewire): array {
                if (! static::$shouldPreloadAttachFormRecordSelectOptions) {
                    return [];
                }

                /** @var BelongsToMany $relationship */
                $relationship = $livewire->getRelationship();

                $titleColumnName = static::getRecordTitleAttribute();

                $relatedKeyName = $relationship->getRelatedKeyName();

                return $relationship
                    ->getRelated()
                    ->query()

                    ->orderBy($titleColumnName)
                    ->when(
                        ! $livewire->allowsDuplicates(),
                        static fn (Builder $query): Builder => $query->whereDoesntHave(
                            $livewire->getInverseRelationshipName(),
                            static function (Builder $query) use ($livewire): Builder {
                                return $query->where($livewire->getOwnerRecord()->getQualifiedKeyName(), $livewire->getOwnerRecord()->getKey());
                            },
                        ),
                    )
                    ->get()
                    ->mapWithKeys(static fn (Model $record): array => [$record->{$relatedKeyName} => static::getRecordTitle($record)])
                    ->toArray();
            })

            ->disableLabel();
    }
}
