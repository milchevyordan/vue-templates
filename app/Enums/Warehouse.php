<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\Enum;

enum Warehouse: int
{
    use Enum;

    case Varna = 1;
    case France = 2;
    case Netherlands = 3;
}
