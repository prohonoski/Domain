<?php

namespace App\Domain\Base;

use App\Domain\Base\Interfaces\ValidatorInterface;

class Validator
{
    /*    protected ValidatorInterface $validator;

    protected array $validatorList;

    public function __construct(
        ValidatorInterface $validator,
        array $data,
        array $rules
    ) {
        $this->validator = $validator->validate($data, $rules);
    }

    public static function make(array $data, array $rules): static
    {
        $static = app(static::class, ["data" => $data, "rules" => $rules]);
        $static->configure();

        return $static;
    }

    public function configure()
    {
        $this->setUp();
    }

    protected function setUp(): void
    {
    }

    public function validate(array ...$data, array $options): self
    {

        dd('aaaa');
        foreach ($data as $key => $row){
            $this->validatorList[] = $this->validator->validate($row, $options);
        }
        return $this;
    }

    public function failsAny()
    {
        $fails = [];
        foreach ($this->validatorList as $key => $value) {
            $fails = $value->fails();
            if ($fails) break;
        }
        return $fails;
    }

    public function messagesAll()
    {
        $messages = [];
        foreach ($this->validatorList as $key => $value) {
            $messages[] = $value->messages();
        }
        return $messages;
    }

    public function fails()
    {
        dd('aaa');
        return $this->failsAny();
    }

    public function messages()
    {
        return $this->messagesAll();
    }*/
}
