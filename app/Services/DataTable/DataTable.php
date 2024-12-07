<?php

declare(strict_types=1);

namespace App\Services\DataTable;

use App\Services\DataTable\Column\Column;
use App\Services\DataTable\Column\ColumnRelation;
use App\Services\DataTable\Column\DateColumn;
use App\Services\DataTable\Column\EnumColumn;
use App\Services\DataTable\Column\PriceColumn;
use App\Traits\Enum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class DataTable
{
    private string $enumNamespace = 'App\\Enums\\';

    /**
     * @var Paginator
     */
    public Paginator $paginator;

    /**
     * The array that holds the table ordering information for the dataТable.
     *
     * @var ?RawOrdering
     */
    public ?RawOrdering $rawOrdering = null;

    /**
     * @var Collection<Column>
     */
    public Collection $columns;

    /**
     * The retrieved data.
     *
     * @var Collection<Model>
     */
    public Collection $data;

    /**
     * @var Builder
     */
    private Builder $builder;

    /**
     * @var ColumnFilter
     */
    private ColumnFilter $columnFilter;

    /**
     * @var Collection<EnumColumn>
     */
    private Collection $enumColumns;

    /**
     * @var Collection<PriceColumn>
     */
    private Collection $priceColumns;

    /**
     * @var Collection<DateColumn>
     */
    private Collection $dateColumns;

    /**
     * @var Collection<TableRelation>
     */
    private Collection $relations;

    /**
     * The array that holds the table ordering information for the dataТable.
     *
     * @var Ordering
     */
    private Ordering $ordering;

    /**
     * @var SoftRestorer
     */
    private SoftRestorer $softRestorer;

    /**
     * Create a new DataTable instance.
     *
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->relations = new Collection();
        $this->columns = new Collection();
        $this->dateColumns = new Collection();
        $this->enumColumns = new Collection();
        $this->priceColumns = new Collection();
        $this->columnFilter = new ColumnFilter($this);
        $this->ordering = new Ordering();

        $this->setBuilder($builder);
    }

    /**
     * Apply filtering and ordering logic based on the request parameters.
     *
     * @param  int       $paginate
     * @param  ?callable $callbackBeforePaginate - callback before the function order and paginate -> Gets the model as param
     * @return self
     */
    public function run(int $paginate = 10, ?callable $callbackBeforePaginate = null): self
    {
        $globalFilterText = request(null)->input('filter.global');
        $paginate = request(null)->input('perPage') ?? $paginate;

        $this->initRelations();

        $this->applyModelFiltering();

        $this->softRestoreRecord();

        $this->applyOrderByColumns();

        if ($globalFilterText) {
            $this->applyGlobalFilter($globalFilterText);
        }

        $this->applyCallbackBeforePaginate($callbackBeforePaginate);

        $paginator = new Paginator(
            $this->getBuilder()
                ->paginate($paginate)
                ->withQueryString()
        );
        $this->setPaginatior($paginator);

        $data = new Collection($this->getPaginator()->items());

        $this->setData($data);

        return $this;
    }

    private function initRelations(): void
    {
        $builder = $this->getBuilder();

        foreach ($this->getRelations() as $relation) {
            $builder->with(
                [$relation->relationsString => function ($query) use ($relation) {
                    $relationSelectColumns = $relation?->columnsToSelect ?? null;

                    if ($relationSelectColumns) {
                        $query->select($relationSelectColumns);
                    }
                }]
            );
        }

        $this->setBuilder($builder);
    }

    /**
     * Perform an advanced search on the model.
     *
     * @param  callable $callback A callback function to customize the search query.
     *                            The callback receives the query as its argument and can modify it.
     * @return self     the current instance of the class
     */
    public function advancedSearch(callable $callback): self
    {
        $builder = $this->getBuilder();
        $callback($builder);

        return $this;
    }

    /**
     * Apply the model filtering.
     *
     * @return void
     */
    private function applyModelFiltering()
    {
        $filterTrashed = request(null)->input('filter.trashed');

        $modelFiltering = new ModelFiltering();

        $builder = $this->getBuilder();

        if ('true' == $filterTrashed) {
            $builder = $modelFiltering->onlyTrashed()->apply($builder);
        }

        $this->setBuilder($builder);
    }

    /**
     * Remove deleted_at from the record.
     *
     * @return void
     */
    private function softRestoreRecord(): void
    {
        $restoreId = request(null)->input('restore_id');

        if (! $restoreId) {
            return;
        }

        $model = $this
            ->getBuilder()
            ->getModel()
            ->withTrashed()
            ->findOrFail($restoreId);

        $softRestorer = new SoftRestorer($model);
        $softRestorer->restore();
    }

    /**
     * Apply a column filter on a specific column.
     *
     * @param  Builder $queryBuilder
     * @param  string  $columnKey    the column key to filter
     * @param  mixed   $filterValue  the filter value
     * @param  bool    $useOrWhere   indicates if the filter should use 'orWhere' or 'andWhere' (default: false)
     * @return self
     */
    private function applyColumnFilter(Builder $queryBuilder, string $columnKey, $filterValue, bool $useOrWhere = false): self
    {
        $columnFilter = $this->columnFilter->apply($queryBuilder, $columnKey, $filterValue, $useOrWhere);

        $this->setBuilder($columnFilter->getBuilder());

        return $this;
    }

    /**
     * Apply callback before pagination.
     *
     * @param  ?callable $callbackBeforePaginate
     * @return void
     */
    protected function applyCallbackBeforePaginate($callbackBeforePaginate): void
    {
        $currentBuilder = $this->getBuilder();

        if ($callbackBeforePaginate) {
            $newBuilder = $currentBuilder->where(function ($query) use ($callbackBeforePaginate) {
                $callbackBeforePaginate($query);
            });

            $this->setBuilder($newBuilder);
        }
    }

    /**
     * Order the columns by set value.
     *
     * @return self
     */
    public function applyOrderByColumns(): self
    {
        $ordering = $this->getOrdering();
        $rawOrdering = $this->getRawOrdering();
        $builder = $this->getBuilder();

        $mainModelColumnsToSelect = $builder->getQuery()->getColumns();
        $mainTableName = $builder->getModel()->getTable();

        // Select necessary columns from the main model
        if (! empty($mainModelColumnsToSelect)) {
            $builder->getQuery()->columns = [];
            foreach ($mainModelColumnsToSelect as $column) {
                $builder->addSelect("{$mainTableName}.{$column}");
            }
        } else {
            $builder->select("{$mainTableName}.*");
        }

        // Apply ordering
        if ($rawOrdering) {
            $builder->orderByRaw($rawOrdering->getString());
        }

        if (! $ordering->hasRelations) {
            $builder->orderBy($ordering->columnName, $ordering->direction);
        } else {
            $relations = explode('.', $ordering->relationsString);
            $this->applyRelationOrdering($builder, $relations, $mainTableName, $ordering);
        }

        $this->setBuilder($builder);

        return $this;
    }

    /**
     * Applying the ordering by relations.
     *
     * @param  Builder       $builder
     * @param  array<string> $relations
     * @param  string        $prevTable
     * @param  Ordering      $ordering
     * @return void
     */
    protected function applyRelationOrdering(Builder $builder, array $relations, string $prevTable, Ordering $ordering): void
    {
        $parentModel = $builder->getModel();
        foreach ($relations as $relation) {
            $relationInstance = $parentModel->{$relation}();
            $relatedModel = $relationInstance->getRelated();
            $relatedTable = $relatedModel->getTable();

            if ($relationInstance instanceof \Illuminate\Database\Eloquent\Relations\MorphOne) {
                $morphType = $relationInstance->getMorphType();
                $morphId = $relationInstance->getForeignKeyName();
                $builder->leftJoin("{$relatedTable} AS {$relation}", function ($join) use ($prevTable, $relation, $morphType, $morphId, $parentModel) {
                    $join->on("{$relation}.{$morphId}", '=', "{$prevTable}.id")
                        ->where("{$relation}.{$morphType}", '=', $parentModel->getMorphClass());
                });
            } else {
                $foreignKey = $relationInstance->getForeignKeyName();
                $ownerKeyName = $relationInstance->getOwnerKeyName();
                $builder->leftJoin("{$relatedTable} AS {$relation}", "{$relation}.{$ownerKeyName}", '=', "{$prevTable}.{$foreignKey}");
            }

            $prevTable = $relation;
            $parentModel = $relatedModel;
        }
        $columnName = "{$prevTable}.{$ordering->columnName}";
        $builder->orderBy($columnName, $ordering->direction);
    }

    /**
     * Apply a global search filter on all searchable columns.
     * Usage with joins: Needs to write the joined table and the column with dot.
     * Example usage: Model with relations: Vehicle::with(['model:id,name', 'model.make']); (DataTable object)->setColumn('make.name', 'Make', true).
     *
     * @param  mixed $searchText the search text
     * @return self
     */
    private function applyGlobalFilter(string $searchText): self
    {
        $newBuilder = $this->getBuilder();
        $searchableColumns = $this->getAllSearchableColumns()->keys()->toArray();

        $newBuilder->where(function ($query) use ($searchText, $searchableColumns) {
            foreach ($searchableColumns as $columnKey) {
                $column = $this->getColumnByKey($columnKey);

                //    Search in the relation tables
                if ($column->relation) {
                    $query->orWhereHas($column->relation->relationString, function ($q) use ($column, $searchText) {
                        $q->where($column->relation->relationColumn, 'LIKE', '%' . $searchText . '%');
                    });
                } else {
                    // Search in the main table
                    $this->applyColumnFilter($query, $columnKey, $searchText, true);
                }
            }
        });

        $this->setBuilder($newBuilder);

        return $this;
    }

    /**
     * Get the value of builder.
     *
     * @return Builder
     */
    public function getBuilder(): Builder
    {
        return $this->builder;
    }

    /**
     * Set the value of builder.
     *
     * @param  Builder $builder
     * @return self
     */
    public function setBuilder(Builder $builder): self
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * Get the value of paginator.
     *
     * @return Paginator
     */
    public function getPaginator(): Paginator
    {
        return $this->paginator;
    }

    /**
     * Set the value of paginator.
     *
     * @param  Paginator $paginator
     * @return self
     */
    public function setPaginatior(Paginator $paginator): self
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * Get the value of relations.
     *
     * @return Collection<TableRelation>
     */
    public function getRelations(): Collection
    {
        return $this->relations;
    }

    /**
     * Set the value of relations.
     *
     * @param  string $relationString
     * @param  ?array $columnsToSelect
     * @return self
     */
    public function setRelation(string $relationString, ?array $columnsToSelect = null): self
    {
        $relation = new TableRelation($relationString);

        if (! empty($columnsToSelect)) {
            $relation->setColumnsToSelect($columnsToSelect);
        }

        $this->relations->push($relation);

        return $this;
    }

    /**
     * Get all searchable columns from the columns array.
     *
     * @return Collection<Column> the searchable columns array
     */
    private function getAllSearchableColumns(): Collection
    {
        return $this->getColumns()->filter(fn ($column) => $column->searchable);
    }

    /**
     * Get column by key.
     *
     * @param  string      $key
     * @return null|Column
     */
    public function getColumnByKey(string $key): null|Column
    {
        return $this->getColumns()->get($key);
    }

    /**
     * Get the collection of all columns.
     *
     * @return Collection<Column>
     */
    public function getColumns(): Collection
    {
        return $this->columns;
    }

    /**
     * Set a column with its properties.
     *
     * @param  ?string     $relationString
     * @param  null|string $label          the column label
     * @param  bool        $searchable     indicates if the column is searchable
     * @param  bool        $orderable      indicates if the column is orderable
     * @param  bool        $exactMatch     indicates if the search should be an exact match
     * @return self
     */
    public function setColumn(?string $relationString = null, ?string $label = null, bool $searchable = false, bool $orderable = false, bool $exactMatch = false): self
    {
        $column = new Column(
            label: $label,
            searchable: $searchable,
            orderable: $orderable,
            exactMatch: $exactMatch
        );

        $relationsArray = explode('.', $relationString);

        if (count($relationsArray) > 1) {
            $relation = new ColumnRelation($relationsArray);

            $column->setRelation($relation);
        }

        $this->columns->put($relationString, $column);

        return $this;
    }

    /**
     * Get the data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set data.
     *
     * @param  mixed $data
     * @return self
     */
    private function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the value of enumColumns.
     *
     * @return Collection<EnumColumn>
     */
    public function getEnumColumns(): Collection
    {
        return $this->enumColumns;
    }

    /**
     * Set the value of enumColumns.
     *
     * @param  string $key
     * @param  string $enumClassName
     * @return self
     */
    public function setEnumColumn(string $key, string $enumClassName): self
    {
        $this->validateEnumClass($enumClassName);

        $this->enumColumns->put($key, $enumClassName);

        return $this;
    }

    /**
     * Get the value of enums.
     *
     * @return Collection<PriceColumn>
     */
    public function getPriceColumns(): Collection
    {
        return $this->priceColumns;
    }

    /**
     * Set the value of enums.
     *
     * @param  string $key
     * @return self
     */
    public function setPriceColumn(string $key): self
    {
        $this->priceColumns->put($key, new PriceColumn());

        return $this;
    }

    /**
     * Get the value of dateColumns.
     *
     * @return Collection<DateColumn>
     */
    public function getDateColumns(): Collection
    {
        return $this->dateColumns;
    }

    /**
     * Set the value of dateColumns.
     *
     * @param  string  $key
     * @param  string  $format
     * @param  ?string $dateDelimiter
     * @param  ?string $timeDelimiter
     * @return self
     */
    public function setDateColumn(string $key, string $format, ?string $dateDelimiter = '.', ?string $timeDelimiter = ':'): self
    {
        $this->dateColumns->put($key, new DateColumn($format, $dateDelimiter, $timeDelimiter));

        return $this;
    }

    private function validateEnumClass(string $enumClassName): void
    {
        if (! str_starts_with($enumClassName, $this->enumNamespace) || ! in_array(Enum::class, class_uses($enumClassName), true)) {
            throw new InvalidArgumentException("{$enumClassName} is not in the App\\Enums namespace.");
        }
    }

    /**
     * Get the value of ordering.
     *
     * @return Ordering
     */
    public function getOrdering(): Ordering
    {
        return $this->ordering;
    }

    /**
     * Set the value of ordering.
     *
     * @param  Ordering $ordering
     * @return self
     */
    public function setOrdering(Ordering $ordering): self
    {
        $this->ordering = $ordering;

        return $this;
    }

    /**
     * Get the value of rawOrdering.
     *
     * @return ?RawOrdering
     */
    public function getRawOrdering(): ?RawOrdering
    {
        return $this->rawOrdering;
    }

    /**
     * Set the value of rawOrdering.
     *
     * @param  ?RawOrdering $rawOrdering
     * @return self
     */
    public function setRawOrdering(?RawOrdering $rawOrdering): self
    {
        $this->rawOrdering = $rawOrdering;

        return $this;
    }
}
