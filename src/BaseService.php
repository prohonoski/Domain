<?php

namespace Proho\Domain;

use App\Exceptions\DryRunException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use LaravelDoctrine\ORM\Facades\EntityManager;

abstract class BaseService
{
    protected MessageBag $errors;
    protected bool $failed = false;
    protected bool $dryRun = false;
    protected bool $useTransaction = false;
    protected bool $useFlush = false;
    protected array $successData = [];
    protected array $failedData = [];
    protected EntityManager $em;

    public function __construct()
    {
        $this->errors = new MessageBag();
    }

    /**
     * Adiciona um erro ao MessageBag
     */
    protected function addError(
        string $key,
        string $message,
        mixed $context = null,
    ): void {
        $this->failed = true;

        $this->errors->add($key, $message);

        if ($context !== null) {
            $this->failedData[] = [
                "key" => $key,
                "message" => $message,
                "context" => $context,
            ];
        }
    }

    /**
     * Adiciona múltiplos erros de uma vez
     */
    protected function addErrors(array $errors, mixed $context = null): void
    {
        foreach ($errors as $key => $messages) {
            foreach ((array) $messages as $message) {
                $this->addError($key, $message, $context);
            }
        }
    }

    /**
     * Adiciona item ao array de sucesso
     */
    protected function addSuccess(mixed $data): void
    {
        $this->successData[] = $data;
    }

    /**
     * Verifica se houve falha
     */
    public function failed(): bool
    {
        return $this->failed;
    }

    /**
     *
     */
    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    /**
     *
     */
    public function dryRun(?bool $dryRun = true): self
    {
        $this->dryRun = $dryRun;
        return $this;
    }

    // Método abstrato que as classes filhas devem implementar
    abstract protected function handle(): mixed;

    // Métodos fluent para controlar transação
    public function withTransaction(): static
    {
        $this->useTransaction = true;
        return $this;
    }

    public function withoutTransaction(): static
    {
        $this->useTransaction = false;
        return $this;
    }

    // Métodos fluent para flush
    public function withFlush(): static
    {
        $this->useFlush = true;
        return $this;
    }

    public function withoutFlush(): static
    {
        $this->useFlush = false;
        return $this;
    }

    //$em,camarinha, 1970186,diony.lucindo@prf.gov.br
    // Método público que executa com ou sem transação
    public function execute(): self
    {
        if ($this->isDryRun()) {
            try {
                $this->em::getConnection()->transactional(function () {
                    $this->handle();
                    throw new DryRunException([
                        "dryRun" => "Simulação finalizada",
                    ]);
                });
            } catch (DryRunException $e) {
                return $this;
            }
            return $this;
        }

        if ($this->useTransaction) {
            $this->em::getConnection()->transactional(function () {
                return $this->handle();
            });
            Log::debug("executo o handle no trans");

            if ($this->useFlush) {
                Log::debug("executo o flush 1");
                $this->em::flush();
            }

            return $this;
        }

        $this->handle();
        if ($this->useFlush) {
            Log::debug("executo o flush 2");
            $this->em::flush();
        }
        return $this;
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
        return $this->errors;
    }

    /**
     * Retorna todos os erros como array
     */
    public function getErrors(): array
    {
        return $this->errors->toArray();
    }

    /**
     * Retorna apenas as mensagens de erro
     */
    public function getErrorMessages(): array
    {
        return $this->errors->all();
    }

    /**
     * Retorna dados que falharam com contexto
     */
    public function getFailedData(): array
    {
        return $this->failedData;
    }

    /**
     * Retorna dados que tiveram sucesso
     */
    public function getSuccessData(): array
    {
        return $this->successData;
    }

    /**
     * Retorna resumo do processamento
     */
    public function getSummary(): array
    {
        return [
            "success_count" => count($this->successData),
            "failed_count" => count($this->failedData),
            "total" => count($this->successData) + count($this->failedData),
            "has_errors" => $this->failed,
        ];
    }

    /**
     * Reseta o estado do serviço
     */
    public function reset(): self
    {
        $this->errors = new MessageBag();
        $this->failed = false;
        $this->successData = [];
        $this->failedData = [];

        return $this;
    }

    /**
     * Retorna mensagem formatada de todos os erros
     */
    public function getFormattedErrors(string $separator = "\n"): string
    {
        return collect($this->errors->all())
            ->map(fn($msg, $index) => "• $msg")
            ->implode($separator);
    }
}
