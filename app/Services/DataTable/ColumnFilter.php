<?php

declare(strict_types=1);

namespace App\Services\DataTable;

use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ColumnFilter
{
    private DataTable $dataTable;

    private Builder $builder;

    /**
     * Create a new ColumnFilter instance.
     *
     * @param DataTable $dataTable
     */
    public function __construct(DataTable $dataTable)
    {
        $this->dataTable = $dataTable;
    }

    /**
     * Apply a column filter on a specific column.
     *
     * @param  Builder $builder
     * @param  string  $columnKey   the column key to filter
     * @param  mixed   $filterValue the filter value
     * @param  bool    $useOrWhere  indicates if the filter should use 'orWhere' or 'andWhere' (default: false)
     * @return self
     */
    public function apply(Builder $builder, string $columnKey, $filterValue, bool $useOrWhere = false): self
    {
        $column = $this->dataTable->getColumnByKey($columnKey);

        if (! $column) {
            throw new InvalidArgumentException(__('Invalid column name'));
        }

        $enumColumns = $this->dataTable->getEnumColumns();
        $dateColumns = $this->dataTable->getDateColumns();
        $priceColumns = $this->dataTable->getPriceColumns();
        $table = $builder->getModel()->getTable();

        $operator = $column->exactMatch ? '=' : 'LIKE';
        $value = $column->exactMatch ? $filterValue : "%{$filterValue}%";

        if (! empty($dateColumns[$columnKey])) { // Check if there are dates to convert
            $dateColumn = $dateColumns[$columnKey];

            $clientTimezone = new DateTimeZone(request(null)->input('filter.timeZone'));
            $serverTimezone = new DateTimeZone(date_default_timezone_get());

            $dateTimeHelper = new DateTimeHelper($dateColumn, $clientTimezone, $serverTimezone, $filterValue);

            $convertedDate = $dateTimeHelper->convert()->convertedDate;

            if (! $convertedDate) {
                return $this;
            }

            $value = $column->exactMatch ? $convertedDate : "%{$convertedDate}%";

            if ($column->relation) {
                $relationColumn = $column->relation->relationColumn;

                $builder->{$useOrWhere ? 'orWhereHasRelation' : 'whereHasRelation'}($column->relation->relationString, function ($query) use ($table, $value, $dateTimeHelper, $relationColumn, $operator) {
                    $query->whereRaw("DATE_FORMAT(`{$table}`.{$relationColumn}, '{$dateTimeHelper->sqlFormat}' ) {$operator} ?", [$value]);
                });
            } else {
                $builder->{$useOrWhere ? 'orWhere' : 'where'}(function ($query) use ($table, $columnKey, $value, $dateTimeHelper) {
                    $query->whereRaw("DATE_FORMAT(`{$table}`.{$columnKey}, '{$dateTimeHelper->sqlFormat}' ) LIKE ?", [$value]);
                });
            }
        } elseif (! empty($enumColumns[$columnKey])) {  // If attached enumColumns with the same key
            $enumFormattedValue = str_replace(' ', '_', $filterValue);
            $filteredEnumCases = $enumColumns[$columnKey]::getCasesByName($enumFormattedValue, true);

            $matchedEnumIds = (new Collection($filteredEnumCases))->pluck('value');

            if ($column->relation) {
                $builder = $useOrWhere ? $builder->orWhereIn($column->relation->relationString, $matchedEnumIds)
                    : $builder->whereIn($column->relation->relationString, $matchedEnumIds);
            } else {
                $mainTableName = $builder->getModel()->getTable();
                $builder = $useOrWhere ? $builder->orWhereIn("{$mainTableName}.{$columnKey}", $matchedEnumIds)
                    : $builder->whereIn($columnKey, $matchedEnumIds);
            }
        } elseif ($priceColumns->keys()->contains($columnKey)) {  // If the column is a price column
            $priceFormattedValue = preg_replace('/[^0-9%]/', '', $value);

            if ($priceFormattedValue !== '%%') {
                $builder->{$useOrWhere ? 'orWhere' : 'where'}($columnKey, $operator, $priceFormattedValue);
            }
        } elseif ($column->relation) {   // If NOT enum but has relation
            $builder = $useOrWhere ? $builder->orWhereRelation($column->relation->relationString, $column->relation->relationColumn, $operator, $value)
            : $builder->orWhereRelation($column->relation->relationString, $column['relationColumn'], $operator, $value);
        } else {  // If NOT enum AND DESNT HAVE relation
            $builder = $useOrWhere ? $builder->orWhere($builder->getModel()->getTable() . ".{$columnKey}", $operator, $value)
            : $builder->where($builder->getModel()->getTable() . ".{$columnKey}", $operator, $value);
        }

        $this->setBuilder($builder);

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
}
