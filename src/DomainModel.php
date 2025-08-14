<?php

namespace Proho\Domain;

use Proho\Domain\Interfaces\DomainModelInterface;
use Proho\Domain\Interfaces\DomainServiceInterface;
use Proho\Domain\Interfaces\ModelInterface;
use Proho\Domain\Interfaces\ValidatorInterface;
use App\Domain\GestaoConhecimento\Instrutor\DisponibilidadeModel;
use App\Domain\Sistema\Pessoa\PessoaModel;
use Exception;
use Maatwebsite\Excel\Facades\Excel;

class DomainModel implements DomainModelInterface
{
    protected ModelInterface $modelClass;
    protected static string $modelClassName = "";
    protected ValidatorInterface $validator;

    protected static string $domainService = "";
    protected string $fieldLabel = "id";

    private array $fields;

    public function getFieldLabel(): string
    {
        return $this->fieldLabel;
    }
    /**
     * Definir o campo identificador da tabela, preferencia por campos unicos
     *
     * campo pode ser utilizado em varios locais como por exemplo query automatica para select

     * @return self
     */
    public function setFieldLabel(string $fieldLabel): self
    {
        $this->fieldLabel = $fieldLabel;
        return $this;
    }

    public function validate(array $data, array $fieldRules = []): self
    {
        if (!$fieldRules) {
            $fieldRules = $this->getFieldRules()->getRules();
        }

        $this->validator->validate($data, $fieldRules);

        // if ($this instanceof DisponibilidadeModel) {
        //     dd($this->validator);
        // }

        return $this;
    }

    public function model(): ModelInterface
    {
        return $this->modelClass;
    }

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public static function make(): self
    {
        $static = app()->make(static::class);
        $modelClass = static::$modelClassName;

        if ($modelClass && class_exists($modelClass)) {
            $mi = app(ModelInterface::class, [
                "model" => new $modelClass(),
            ]);

            $static->configure($mi);
        } else {
            throw new Exception("Domain withou model: " . $modelClass);
        }
        return $static;
    }

    public function configure(ModelInterface $mi)
    {
        $this->modelClass = $mi;
        $this->setUp();
        $this->updateFields();
    }

    protected function setUp(): void {}

    public function updateInsert(array $dataRows, array $keys)
    {
        //somente executa se não tem erro de validacao
        if ($this->getValidator()->fails()) {
            return false;
        }

        // deve filtrar o array para so conter os campos fillable
        //
        $fieldList = array_keys($this->getFieldsFill());
        $fieldList = array_merge($fieldList, $keys);

        // dd($fieldList);

        array_walk($dataRows, function (&$value, $key) use ($fieldList) {
            foreach ($value as $fieldName => $fieldValue) {
                if (!in_array($fieldName, $fieldList)) {
                    unset($value[$fieldName]);
                }
            }
        });

        // if ($this instanceof DisponibilidadeModel) {
        //     dd($dataRows, $fieldList);
        // }

        //return $this->modelClass->batchUpdate($dataRows, $keys);

        return $this->modelClass->updateInsert($dataRows, $keys);
    }

    // public function update(array $data, array $keys)
    // {
    //     $this->modelClass->update($data, $keys);
    // }

    public function filterFieldForModify(array $dataRows, array $keys = [])
    {
        // deve filtrar o array para so conter os campos fillable
        //
        $fieldList = array_keys($this->getFieldsFill());
        $fieldList = array_merge($fieldList, $keys);

        // dd($fieldList);

        array_walk($dataRows, function (&$value, $key) use ($fieldList) {
            foreach ($value as $fieldName => $fieldValue) {
                if (!in_array($fieldName, $fieldList)) {
                    unset($value[$fieldName]);
                }
            }
        });

        return $dataRows;
    }

    public function batchUpdate(array $dataRows, array $keys): bool
    {
        //somente executa se não tem erro de validacao
        if ($this->getValidator()->fails()) {
            return false;
        }
        // if ($this instanceof PessoaModel) {
        //     dd($this->getValidator());
        // }
        //

        $dataRows = $this->filterFieldForModify($dataRows, $keys);

        return $this->modelClass->batchUpdate($dataRows, $keys);
    }

    public function batchUpdateInsert(array $dataRows, array $keys): bool
    {
        //somente executa se não tem erro de validacao
        if ($this->getValidator()->fails()) {
            return false;
        }

        $dataRows = $this->filterFieldForModify($dataRows, $keys);
        $this->updateInsert($dataRows, $keys);

        return true;
    }

    // public function batchUpdate2(array $dataRows, array $keys, array $cond = [])
    // {
    //     dd($this->modelClass->batchUpdate2($dataRows, $keys));

    //     //$this->modelClass->batchUpdate($dataRows, $keys);
    // }

    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    protected function updateFields()
    {
        foreach (get_object_vars($this) as $name => $value) {
            if (
                is_object($this->$name) &&
                get_class($this->$name) == "Proho\Domain\Field"
            ) {
                $this->fields[$this->$name->getName()] = $this->$name;
            }
        }
    }

    // public function hasBuffer(): bool
    // {
    //     if (!$this->modelClass->hasBuffer()) {
    //         return false;
    //     }
    //     return true;
    // }

    public function getFieldRules(): Rules
    {
        $rules = [];
        foreach ($this->getFields() as $key => $value) {
            if ($value->rules ?? false) {
                $rules[$key] = $value->rules ?? "";
            }
        }

        return Rules::make($rules);
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getFieldsFill(): array
    {
        //dd($this->fields["id"]->isFillable());

        return array_filter(
            $this->fields,
            function ($value, $key) {
                return $value->isFillable();
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    public function where(
        string $field,
        string|null $cond = null,
        mixed $value = null
    ): self {
        //throw new Exception("sdfasdf");

        $p_field = $field;
        $p_cond = $cond;
        $p_value = $value;

        if ($value == null && $cond) {
            $p_cond = "=";
            $p_value = $cond;
        }

        $this->modelClass->where($p_field, $p_cond, $p_value);

        return $this;
    }

    // public function getQueryBuilder()
    // {
    //     return $this->modelClass->getQueryBuilder();
    // }

    public function whereIn(string|null $field, array $value): self
    {
        $this->modelClass->whereIn($field, $value);

        return $this;
    }

    public function get(): self
    {
        $this->modelClass = $this->modelClass->get();

        return $this;
    }

    public function first(): self
    {
        $this->modelClass = $this->modelClass->first();

        return $this;
    }

    public function distinct(array $fields): self
    {
        $this->modelClass = $this->modelClass->distinct($fields);

        return $this;
    }

    public function records(): array
    {
        return $this->modelClass->getData();
    }

    public function record(): array
    {
        return $this->modelClass->getData()[0] ?? [];
    }

    public function setValidator(ValidatorInterface $validator): self
    {
        $this->validator = $validator;
        return $this;
    }
}
