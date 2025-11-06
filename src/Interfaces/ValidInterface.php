<?php

namespace Proho\Domain\Interfaces;

interface ValidInterface
{
    public static function rules(): array;
    public static function messages(): array;
    public function validate(array $data, ?int $id = null): void;
    public function validateForCreate(array $data, ?int $id = null): void;
    public function validateForUpdate(array $data, int $id): void;
    public function validateForDelete(array $data, ?int $id = null): void;
}
