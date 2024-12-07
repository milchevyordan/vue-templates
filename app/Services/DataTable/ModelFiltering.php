<?php

declare(strict_types=1);

namespace App\Services\DataTable;

class ModelFiltering
{
    private bool $showTrashed = false;

    private bool $onlyTrashed = false;

    public function showTrashed(bool $show = true): self
    {
        $this->showTrashed = $show;

        return $this;
    }

    public function onlyTrashed(bool $only = true): self
    {
        $this->onlyTrashed = $only;

        return $this;
    }

    public function apply($query)
    {
        if ($this->onlyTrashed) {
            return $query->onlyTrashed();
        }

        if ($this->showTrashed) {
            return $query->withTrashed();
        }

        return $query;
    }
}
