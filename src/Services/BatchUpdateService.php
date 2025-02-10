<?php

namespace Proho\Domain\Services;

use Proho\Domain\Service;

class BatchUpdateService extends Service
{
    protected string $label = "Atualização dos dados";

    public function execute(): self
    {
        $this->validate($this->dataRows)->batchUpdate(
            $this->dataRows,
            $this->keys
        );

        return parent::execute();
    }
}
