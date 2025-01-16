<?php

namespace App\Domain\Base\Interfaces;

interface NotificationInterface
{
    public function notifyValidator(ValidatorInterface ...$validators);
    public function notifyValidatorOneSuccess(
        ValidatorInterface ...$validators
    );
    public function notifyValidatorDefault(ValidatorInterface ...$validators);
}