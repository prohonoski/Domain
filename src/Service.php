<?php

namespace App\Domain\Base;

use App\Domain\Base\Interfaces\DomainModelInterface;
use App\Domain\Base\Interfaces\DomainServiceInterface;
use App\Domain\Base\Interfaces\NotificationInterface;
use App\Domain\Base\Interfaces\ValidatorInterface;
use Illuminate\Support\Facades\Log;

class Service implements DomainServiceInterface
{
    protected DomainModelInterface $dm;

    protected string $notifyType = "default";
    protected NotificationInterface $notificator;
    private array $arrayValidator = [];
    protected array $dataRows;
    protected array $keys;
    private array $rules = [];
    protected ?DomainServiceInterface $nextService = null;
    protected string $label = "";
    protected static string $domainClass;

    public function __construct(
        ValidatorInterface $validator,
        DomainModelInterface $dm,
        array $params
    ) {
        $this->dm = $dm ?? static::$domainClass::make();
        $this->notificator = app(NotificationInterface::class);
        //    $this->validator = $validator;
        //    $this->validator->setService($this);

        foreach ($params as $key => $value) {
            $this->$key = $value;
        }

        //$this->execute();
    }

    public function query(?array $params = []): array
    {
        return [];
    }

    public function set(array $data): self
    {
        if (isset($data["keys"])) {
            $this->setKeys($data["keys"]);
        }
        if (isset($data["dataRows"])) {
            $this->setdata($data["dataRows"]);
        }
        return $this;
    }

    public function setData(array $dataRows): self
    {
        $this->dataRows = $dataRows;
        return $this;
    }

    public function setKeys(array $keys): self
    {
        $this->keys = $keys;
        return $this;
    }

    public function autoNotify(string $type): self
    {
        $this->notifyType = $type;
        return $this;
    }

    public function notifyType(): string
    {
        return $this->notifyType;
    }

    public function execute(): self
    {
        $this->setValidator(
            ...[
                $this->dm->getValidator(),
                ...$this->nextService ? $this->nextService->getValidator() : [],
            ]
        );

        // if ($this instanceof InstrutorBatchUpdate) {
        //     dd($this->notifyType);
        // }

        Log::debug("notifiy  type " . $this->notifyType);

        if ($this->notifyType != "none" && $this->notifyType != "parent") {
            // if ($this instanceof PessoaBatchUpdate) {
            //     dd($this->getValidator());
            // }
            //
            //

            match ($this->notifyType) {
                // "success" => $this->notificator->notifyValidatorSucess(
                //     ...$this->getValidator()
                // ),
                // "ifFail" => $this->anyValidatorFail()
                //     ? $this->notificator->notifyValidatorFail(
                //         ...$this->getValidator()
                //     )
                //     : $this->notificator->notifyValidatorSucess(
                //         ...$this->getValidator()
                //     ),
                "oneSuccess" => $this->notificator->notifyValidatorOneSuccess(
                    ...$this->getValidator()
                ),
                default => $this->notificator->notifyValidatorDefault(
                    ...$this->getValidator()
                ),
            };
        }
        return $this;
    }

    public function anyValidatorFail(): bool
    {
        foreach ($this->getValidator() as $validator) {
            if ($validator->fails()) {
                return true;
            }
        }
        return false;
    }

    public function getValidator(): array
    {
        return $this->arrayValidator;
    }

    public function addRules(array $rules): self
    {
        foreach ($rules as $rule) {
            $this->rules[] = $rule;
        }
        return $this;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function validate(array $dataRows): DomainModelInterface
    {
        $fieldRules = $this->getRules();

        $this->dm->validate($dataRows, $fieldRules);

        $this->addValidator(
            $this->dm
                ->getValidator()
                ->setService($this)
                ->autoNotify($this->notifyType)
        );

        return $this->dm;
    }

    public function setValidator(ValidatorInterface ...$arrayValidator)
    {
        $this->arrayValidator = [];

        foreach ($arrayValidator as $validator) {
            $this->arrayValidator[] = $validator;
        }
    }

    public function addValidator(ValidatorInterface ...$arrayValidator)
    {
        foreach ($arrayValidator as $validator) {
            $this->arrayValidator[] = $validator;
        }
    }

    public static function service(
        ?DomainModelInterface $domainModel = null
    ): DomainServiceInterface {
        $serviceClass = static::class;

        $service = app($serviceClass, [
            "dm" => $domainModel ?? static::$domainClass::make(),
            "params" => $params ?? [],
        ]);

        return $service;
    }
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }
    public function getLabel(): string
    {
        return $this->label;
    }
}
