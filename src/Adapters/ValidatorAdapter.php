<?php

namespace Proho\Domain\Adapters;

use Proho\Domain\Interfaces\ValidatorInterface;
use Proho\Domain\Service;
use Illuminate\Support\Facades\Validator;
use Proho\Domain\Interfaces\ServiceRepositoryInterface;

class ValidatorAdapter implements ValidatorInterface
{
    private $validator = null;
    private array $validatorList = [];
    private array $identify = [];
    private bool $notify = true;
    private string $notifyType = "all";
    private ?ServiceRepositoryInterface $service;

    function __construct(Validator $validator)
    {
        $this->configure($validator);
        return $this;
    }

    public static function make(Validator $validator): self
    {
        $static = app(static::class, [
            "validator" => $validator,
        ]);
        return $static;
    }

    public function configure(Validator $validator)
    {
        $this->validator = $validator;
        $this->setUp();
    }

    public function setUp() {}

    // public function validate(array $data, array $options): self
    // {
    //     $this->validator = Validator::make($data, $options);
    //     return $this;
    // }

    // public function fails()
    // {
    //     return $this->validator->fails();
    // }

    // public function messages()
    // {
    //     return $this->validator->messages();
    // }

    public function setIdentify(array $identify): self
    {
        $this->identify = $identify;
        return $this;
    }

    public function validate(
        array $data,
        array $options,
        array $messages = []
    ): self {
        $id = 0;

        foreach ($data as $key => $row) {
            $custom_id = null;

            foreach ($this->getIdentify() as $key2 => $value) {
                if ($value == "row") {
                    $custom_id .= $key + 1 . "|";
                } else {
                    $custom_id .= $row[$value] . "|";
                }
            }

            $this->validatorList[$custom_id ?? $id] = Validator::make(
                $row,
                $options,
                $messages
            );

            $id++;
        }
        return $this;
    }

    public function failsAny()
    {
        $fails = [];
        foreach ($this->validatorList as $key => $value) {
            $fails = $value->fails();
            if ($fails) {
                break;
            }
        }
        return $fails;
    }

    public function messagesAll()
    {
        $messages = [];
        foreach ($this->validatorList as $key => $value) {
            $mb = $value->messages();

            $msg_identify = "for ";

            // foreach ($identify as $key => $value) {
            //     $msg_identify .= $key .':'.
            // }

            if ($mb->getMessages()) {
                $mb->add("identify", $key);
            }

            $messages[] = $mb;
        }
        return $messages;
    }

    public function fails()
    {
        return $this->failsAny();
    }

    public function messages()
    {
        return $this->messagesAll();
    }
    public function getIdentify()
    {
        return $this->identify ? $this->identify : ["Linha" => "row"];
    }

    public function notifyType(): string
    {
        return $this->notifyType;
    }

    public function autoNotify(string $notifyType): self
    {
        $this->notifyType = $notifyType;
        return $this;
    }

    public function setService(ServiceRepositoryInterface $service): self
    {
        $this->service = $service;
        return $this;
    }

    public function getService(): ServiceRepositoryInterface
    {
        return $this->service;
    }
}
