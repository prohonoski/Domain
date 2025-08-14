<?php

namespace Proho\Domain\Services;

use Proho\Domain\Service;

class OptionsService extends Service
{
    public function query(?array $params = []): array
    {
        return collect($this->dm->get()->records())
            ->pluck($this->dm->getFieldLabel(), "id")
            ->toArray();
    }
}
