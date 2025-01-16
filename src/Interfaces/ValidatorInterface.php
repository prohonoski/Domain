<?php

namespace App\Domain\Base\Interfaces;

use App\Domain\Base\Service;

interface ValidatorInterface
{
    public function setUp();
    public function validate(array $data, array $options): self;
    public function fails();
    public function messages();
    public function messagesAll();
    public function getService();
    public function setService(Service $service): self;
    public function autoNotify(string $notifyType): self;
}
