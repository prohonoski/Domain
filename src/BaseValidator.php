<?php

namespace Proho\Domain;

use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Proho\Domain\Exceptions\BusinessRuleException;

abstract class BaseValidator
{
    protected bool $failed = false;
    protected array $successData = [];
    protected array $failedData = [];
    public MessageBag $errorsGeneral;
    public MessageBag $errorsValidation;
    protected array $activeRules = [];
    protected array $activeMessages = [];

    public function __construct()
    {
        $this->errorsGeneral = new MessageBag();
        $this->errorsValidation = new MessageBag();
    }

    abstract public static function rules(): array;
    abstract public static function messages(): array;

    /**
     * Verifica se houve falha
     */
    public function failed(): bool
    {
        return $this->failed;
    }

    /**
     * Verifica se foi bem-sucedido
     */
    public function succeeded(): bool
    {
        return !$this->failed;
    }

    /**
     * Retorna o MessageBag com todos os erros
     */
    public function errors(): MessageBag
    {
        $mb = new MessageBag();
        $mb->merge($this->errorsValidation->toArray());
        $mb->merge($this->errorsGeneral->toArray());
        return $mb;
    }

    /**
     * Retorna todos os erros como array
     */
    public function getErrors(): array
    {
        return [
            $this->errorsValidation->toArray(),
            $this->errorsGeneral->toArray(),
        ];
    }

    /**
     * Retorna apenas as mensagens de erro
     */
    public function getErrorMessages(): array
    {
        return [$this->errorsValidation->all(), $this->errorsGeneral->all()];
    }

    /**
     * Reseta o estado do serviço
     */
    public function reset(): self
    {
        $this->errorsGeneral = new MessageBag();
        $this->errorsValidation = new MessageBag();
        $this->failed = false;
        return $this;
    }

    /**
     * Retorna mensagem formatada de todos os erros
     */
    public function getFormattedErrors(string $separator = "\n"): string
    {
        $errors = array_merge(
            $this->errorsGeneral->all(),
            $this->errorsValidation->all(),
        );

        return collect($errors)
            ->map(fn($msg, $index) => "• $msg")
            ->implode($separator);
    }
    public function validateForCreate(array $data, ?int $id = null): void
    {
        $this->activeRules = static::rules();
        $this->activeMessages = static::messages();

        $this->validate($data, $id);
    }
    public function validateForUpdate(array $data, int $id): void
    {
        $this->activeRules = static::rules();
        $this->activeMessages = static::messages();

        $this->validate($data, $id);
    }
    public function validateForDelete(array $data, ?int $id = null): void
    {
        $this->activeRules = static::rules();
        $this->activeMessages = static::messages();

        $this->validate($data, $id);
    }

    public function validate(array $data, ?int $id = null): void
    {
        // $rules = static::rules();
        // $messages = static::messages();

        $rules = $this->activeRules ?: static::rules();
        $messages = $this->activeMessages ?: static::messages();

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails() || $this->errorsValidation->any()) {
            $allErrors = $validator->errors()->merge($this->errorsValidation);

            // Você pode lançar aqui também, se quiser
            throw ValidationException::withMessages($allErrors->toArray());
        }

        if (
            $this->errorsGeneral->any() ||
            $validator->fails() ||
            $this->errorsValidation->any()
        ) {
            $this->failed = true;
            $this->errorsValidation->merge($validator->errors());

            throw new BusinessRuleException($this->errors()->toArray());
        }
    }
}
