<?php

declare(strict_types=1);

namespace App\Services\DataTable\Column;

use Illuminate\Validation\Rules\Enum;

class EnumColumn
{
    /**
     * Create a new EnumColumn instance.
     *
     * @param Enum $enum
     */
    public function __construct(
        public Enum $enum
    ) {
    }
}
