<?php

namespace Proho\Domain\Traits;

use ReflectionEnum;

trait EnumToArray
{
    /**
     * Get the names of all cases in the enum.
     *
     * @return array<int, string>
     */
    public static function names(): array
    {
        return array_column(self::cases(), "name");
    }

    /**
     * Get the values of all cases in the enum.
     * For pure enums, the case name is returned as the value.
     *
     * @return array<int, string|int>
     */
    public static function values(): array
    {
        $reflection = new ReflectionEnum(self::class);
        $property = $reflection->isBacked() ? "value" : "name";

        return array_column(self::cases(), $property);
    }

    /**
     * Get an associative array of the enum cases, where keys are names and values are values.
     * For pure enums, this results in [name => name].
     *
     * @return array<string|int, string>
     */
    public static function toArray(): array
    {
        return array_combine(self::names(), self::values());
    }

    /**
     * Creates an enum instance from a name or a value.
     * For pure enums, it only checks against the case name.
     *
     * @param string|int $nameOrValue
     * @return static
     * @throws \ValueError
     */
    public static function fromNameOrValue(string|int $nameOrValue): static
    {
        $reflection = new ReflectionEnum(self::class);
        $enum = null;

        if ($reflection->isBacked()) {
            // For backed enums, try from value first.
            $enum = static::tryFrom($nameOrValue);
        }

        // If it's a pure enum, or a backed enum where the value didn't match, try matching by name.
        if ($enum === null) {
            if (defined(static::class . "::" . $nameOrValue)) {
                $enum = constant(static::class . "::" . $nameOrValue);
            }
        }

        if ($enum === null) {
            throw new \ValueError(
                '"' .
                    $nameOrValue .
                    '" is not a valid name or value for ' .
                    static::class,
            );
        }

        return $enum;
    }

    /**
     * Get a human-readable label for the enum case.
     * For backed enums, it returns the value.
     * For pure enums, it returns a title-cased version of the name.
     *
     * @return string
     */
    public function getLabel(): string
    {
        $reflection = new \ReflectionEnum(self::class);

        // For backed enums, the value is often the desired human-readable label.
        if ($reflection->isBacked()) {
            return $this->value;
        }

        // For pure enums, format the name to be a good default label.
        // e.g., 'CASE_NAME' becomes 'Case Name'.
        return str_replace("_", " ", ucwords(strtolower($this->name)));
    }

    /**
     * Get an array of all case labels.
     *
     * @return array<int, string>
     */
    public static function labels(): array
    {
        return array_map(fn($case) => $case->getLabel(), self::cases());
    }

    /**
     * Get an associative array of values and labels, ideal for HTML select options.
     *
     * @return array<string|int, string>
     */
    public static function toSelectArray(): array
    {
        return array_combine(self::values(), self::labels());
    }
}
