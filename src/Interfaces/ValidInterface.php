<?php

namespace Proho\Domain\Interfaces;

interface ValidInterface
{
    public static function rules(): array;
    public static function messages(): array;
    public static function validate(array $data, ?int $id = null): void;
    public static function validateForCreate(
        array $data,
        ?int $id = null,
    ): void;
    public static function validateForUpdate(
        array $data,
        ?int $id = null,
    ): void;
    public static function validateForDelete(
        array $data,
        ?int $id = null,
    ): void;
}
