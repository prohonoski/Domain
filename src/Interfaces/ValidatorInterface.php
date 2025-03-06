<?php

namespace Proho\Domain\Interfaces;

use Proho\Domain\Service;

interface ValidatorInterface
{
    public function setUp();
    public function validate(array $data, array $options): self;
    public function fails();
    public function messages();
    public function messagesAll();
    public function getService();
    public function setService(ServiceRepositoryInterface $service): self;
    public function autoNotify(string $notifyType): self;
    public function setIdentify(array $identify): self;
}
