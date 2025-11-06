<?php

namespace Proho\Domain;

use Illuminate\Support\MessageBag;

abstract class BaseValidator
{
    protected bool $failed = false;
    protected array $successData = [];
    protected array $failedData = [];
    public MessageBag $errorsGeneral;
    public MessageBag $errorsValidation;

    public function __construct()
    {
        $this->errorsGeneral = new MessageBag();
        $this->errorsValidation = new MessageBag();
    }

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
}
