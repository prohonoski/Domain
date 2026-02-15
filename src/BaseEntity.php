<?php

namespace Proho\Domain;

use DateTime;
use LaravelDoctrine\ORM\Facades\EntityManager;

class BaseEntity
{
    /**
     * @return array<mixed, mixed>
     */
    public function toArray(?object $entity = null): array
    {
        $entity = $entity ?: $this;
        $includeRelations = true;

        $metadata = EntityManager::getClassMetadata(\get_class($entity));
        $data = [];

        // Campos simples
        foreach ($metadata->getFieldNames() as $field) {
            $value = $metadata->getFieldValue($entity, $field);

            // Converter DateTime para string
            if ($value instanceof DateTime) {
                $value = $value->format("Y-m-d H:i:s");
            }

            $data[$field] = $value;
        }

        // Relacionamentos (opcional)
        if ($includeRelations) {
            foreach ($metadata->getAssociationNames() as $association) {
                $value = $metadata->getFieldValue($entity, $association);

                if ($value !== null) {
                    if (\is_object($value) && method_exists($value, "getId")) {
                        $data[$association] = $value->getId();
                    }
                }
            }
        }
        return $data;
    }
}
