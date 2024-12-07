<?php

declare(strict_types=1);

namespace App\Services\DataTable\Traits;

trait Enum
{
    /**
     * Return enum's options count.
     *
     * @return int
     */
    public static function count(): int
    {
        return count(self::cases());
    }

    /**
     * Return array of enum's names.
     *
     * @return array
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * Return array of enum's values.
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Return enum's case by given value.
     *
     * @param            $value
     * @return null|Enum
     */
    public static function getCaseByValue($value): null|static
    {
        foreach (self::cases() as $case) {
            if ($value === $case->value) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Return enum's case by given name.
     *
     * @param  string      $name
     * @param  bool        $partialMatch
     * @return null|static
     */
    public static function getCaseByName(string $name, bool $partialMatch = false): null|static
    {
        foreach (self::cases() as $case) {
            if ($partialMatch && str_contains($case->name, $name)) {
                return $case;
            }
            if (! $partialMatch && $name === $case->name) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Return enum cases by given name.
     *
     * @param  string $searchName
     * @param  bool   $partialMatch
     * @return array
     */
    public static function getCasesByName(string $searchName, bool $partialMatch = false): array
    {
        $cases = [];

        $caseName = strtolower($searchName);

        foreach (self::cases() as $case) {
            $enumCaseName = strtolower($case->name);

            if ($partialMatch && str_contains($enumCaseName, $caseName)) {
                $cases[] = $case;
            } elseif (! $partialMatch && $caseName === $enumCaseName) {
                $cases[] = $case;
            }
        }

        return $cases;
    }

    /**
     * Convert php enums to ts enums.
     *
     * @return string
     */
    public static function toTS(): string
    {
        $class = str_replace('App\\Enums\\', '', static::class);
        $ts = "export enum {$class} {\n";

        foreach (self::cases() as $case) {
            $value = 'string' == gettype($case->value) ? "'{$case->value}'" : $case->value;
            $ts .= "    {$case->name} = {$value},\n";
        }

        $ts .= '}';

        return $ts;
    }
}
