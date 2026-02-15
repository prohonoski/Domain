<?php

namespace Proho\Domain\Services\ActivityLog\Validators;
use Proho\Domain\BaseValidator;
use Proho\Domain\Interfaces\ValidInterface;

class ActivityLogValidator extends BaseValidator implements ValidInterface
{
    public static function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'subject_type' => ['nullable', 'string', 'max:255'],
            'subject_id' => ['nullable', 'integer'],
            'causer_type' => ['nullable', 'string', 'max:255'],
            'causer_id' => ['nullable', 'integer'],
            'properties' => ['nullable', 'json'],
            'event' => ['nullable', 'string', 'max:255'],
            'batch_uuid' => ['nullable', 'uuid'],
        ];
    }

    public static function messages(): array
    {
        return [
            'description.required' => 'A descrição é obrigatória.',
            'description.max' => 'A descrição não pode ter mais de 255 caracteres.',
            'subject_type.max' => 'O tipo de subject não pode ter mais de 255 caracteres.',
            'subject_id.integer' => 'O ID do subject deve ser um número inteiro.',
            'causer_type.max' => 'O tipo de causer não pode ter mais de 255 caracteres.',
            'causer_id.integer' => 'O ID do causer deve ser um número inteiro.',
            'properties.json' => 'As propriedades devem estar em formato JSON válido.',
            'event.max' => 'O evento não pode ter mais de 255 caracteres.',
            'batch_uuid.uuid' => 'O batch UUID deve ser um UUID válido.',
        ];
    }
}
