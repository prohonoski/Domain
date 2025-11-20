<?php
namespace Proho\Domain;

use Illuminate\Support\MessageBag;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Proho\Domain\BaseService;
use Proho\Domain\Repository;

abstract class BaseOrch
{
    private bool $flush = true;
    protected bool $failed = false;
    protected bool $dryRun = false;
    protected bool $useTransaction = false;
    protected array $successData = [];
    protected array $failedData = [];
    protected EntityManager $em;

    protected Repository $repository;
    protected BaseValidator $validator;
    protected MessageBag $errors;

    public function __construct(
        Repository $repository,
        BaseValidator $validator,
    ) {
        $this->repository = $repository;
        $this->validator = $validator;
        $this->errors = new MessageBag();
    }

    public function withFlush(bool $flush = true): static
    {
        $this->flush = $flush;
        return $this;
    }

    public function shouldFlush(): bool
    {
        return $this->flush;
    }
    /**
     *
     */
    public function isDryRun(): bool
    {
        return $this->dryRun;
    }
    /**
     * Ativa modo dry run
     */
    public function dryRun(bool $dryRun = true): static
    {
        $this->dryRun = $dryRun;
        return $this;
    }

    /**
     * Ativa transação
     */
    public function withTransaction(): static
    {
        $this->useTransaction = true;
        return $this;
    }

    /**
     * Desativa transação
     */
    public function withoutTransaction(): static
    {
        $this->useTransaction = false;
        return $this;
    }

    /**
     * Executa um service internamente e coleta erros/sucessos
     *
     * @param class-string<BaseService>|BaseService $serviceClass
     *        - string: Service será instanciado e configurado pelo Orch
     *        - BaseService: Service pré-configurado será executado como está
     * @param array $params Parâmetros para o construtor do service
     * @return self
     */
    protected function runService(
        string|BaseService $serviceClass,
        array $params = [],
    ): static {
        $service = $serviceClass;

        // Instancia o service com os parâmetros do ORCH
        if (\is_string($serviceClass)) {
            /** @var BaseService $service  */
            $service = app($serviceClass, $params);

            // Aplica configurações do orquestrador
            if ($this->dryRun) {
                $service->dryRun(true);
            }

            if ($this->useTransaction) {
                $service->withTransaction();
            }

            if ($this->flush) {
                $service->withFlush();
            }
        }

        // Executa o service
        $service->execute();

        // Coleta erros e sucessos
        $this->collectServiceResults($service);

        return $this;
    }

    /**
     * Coleta os resultados do service executado
     */
    protected function collectServiceResults(BaseService $service): void
    {
        if ($service->failed()) {
            $this->failed = true;

            // Mescla os erros
            foreach ($service->getErrors() as $key => $messages) {
                foreach ((array) $messages as $message) {
                    $this->errors->add($key, $message);
                }
            }

            // Adiciona dados com falha
            $this->failedData = array_merge(
                $this->failedData,
                $service->getFailedData(),
            );
        }

        // Adiciona dados de sucesso
        $this->successData = array_merge(
            $this->successData,
            $service->getSuccessData(),
        );
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
     * Reseta o estado do orquestrador
     */
    public function reset(): static
    {
        $this->errors = new MessageBag();
        $this->failed = false;
        $this->successData = [];
        $this->failedData = [];
        $this->dryRun = false;
        $this->useTransaction = false;

        return $this;
    }

    /**
     * Retorna mensagem formatada de todos os erros
     */
    public function getFormattedErrors(string $separator = "\n"): string
    {
        return collect($this->errors->all())
            ->map(fn($msg) => "• $msg")
            ->implode($separator);
    }

    // Métodos originais mantidos
    public function create(array $data): static
    {
        // $this->validator->validateForCreate($data);
        // $record = $this->repository->fill($data);
        // EntityManager::persist($record);
        // return $this;

        return $this->runService($this->repository->createService(), [
            "repository" => $this->repository,
            "validator" => $this->validator,
            "data" => $data,
        ]);
    }

    public function update(int $id, array $data): static
    {
        return $this->runService($this->repository->saveService(), [
            "repository" => $this->repository,
            "validator" => $this->validator,
            "data" => $data,
            "id" => $id,
        ]);

        // $this->validator->validateForUpdate($data, $id);
        // $record = $this->repository->fill($data, $this->repository->find($id));
        // EntityManager::persist($record);
        // return $this;
    }

    public function delete(int $id): static
    {
        return $this->runService($this->repository->deleteService(), [
            "repository" => $this->repository,
            "validator" => $this->validator,
            "id" => $id,
        ]);

        // $record = $this->repository->find($id);
        // if ($record) {
        //     $this->validator->validateForDelete($record->toArray(), $id);
        //     EntityManager::remove($record);
        // }
        // return $this;
    }
}
