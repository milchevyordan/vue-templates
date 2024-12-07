<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Class MultiSelectService.
 *
 * This class provides data for the Selects /Multiselect lib/ in the app.
 */
class MultiSelectService
{
    /**
     * Builder instance for querying the data.
     *
     * @var Builder
     */
    public Builder $model;

    /**
     * $idColumnName variable - the column name for the select value - Default 'id'.
     *
     * @var string
     */
    public string $idColumnName = 'id';

    /**
     *  $textColumnName variable - the column name for the select text - Default 'name'.
     *
     * @var string
     */
    public string $textColumnName = 'name';

    /**
     * Create a new MultiSelect instance.
     *
     * @param mixed $modelClass the model class or builder instance for querying the data
     */
    public function __construct(mixed $modelClass)
    {
        $this->model = $modelClass instanceof Builder ? $modelClass : (new $modelClass());
    }

    /**
     * Get $idColumnName variable - the column name for the select value - Default 'id'.
     *
     * @return string
     */
    public function getIdColumnName(): string
    {
        return $this->idColumnName;
    }

    /**
     * Set $idColumnName variable - the column name for the select value - Default 'id'.
     *
     * @param  string $idColumnName $idColumnName variable - the column name for the select value - Default 'id'
     * @return self
     */
    public function setIdColumnName(string $idColumnName): self
    {
        $this->idColumnName = $idColumnName;

        return $this;
    }

    /**
     * Get $textColumnName variable - the column name for the select text - Default 'name'.
     *
     * @return string
     */
    public function getTextColumnName(): string
    {
        return $this->textColumnName;
    }

    /**
     * Set $textColumnName variable - the column name for the select text - Default 'name'.
     *
     * @param  string $textColumnName $textColumnName variable - the column name for the select text - Default 'name'
     * @return self
     */
    public function setTextColumnName(string $textColumnName): self
    {
        $this->textColumnName = $textColumnName;

        return $this;
    }

    /**
     * Returns collection data that suits for the Multi select library.
     *
     * @return Collection
     */
    public function dataForSelect(): Collection
    {
        return $this->model->get([$this->idColumnName, $this->textColumnName])->pluck($this->idColumnName, $this->textColumnName);
    }
}
