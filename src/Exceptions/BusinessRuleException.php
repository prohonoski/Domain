<?php

namespace Proho\Domain\Exceptions;

class BusinessRuleException extends \Exception
{
    public function __construct(public array $errors)
    {
        $mensagem = collect($this->errors)
            ->flatMap(
                fn($msgs, $key) => collect($msgs)->map(
                    fn($msg) => "• $msg ($key)",
                ),
            )
            ->implode("\n");

        parent::__construct("Regras de negócio violadas. $mensagem");
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorsMessages(): string
    {
        $mensagem = collect($this->errors)
            ->flatMap(
                fn($msgs, $key) => collect($msgs)->map(
                    fn($msg) => "• $msg ($key)",
                ),
            )
            ->implode("\n");

        return $mensagem;
    }
}
