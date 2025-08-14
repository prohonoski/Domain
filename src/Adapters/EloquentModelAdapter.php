<?php

namespace Proho\Domain\Adapters;

use Proho\Domain\Interfaces\ModelInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Throwable;

class EloquentModelAdapter implements ModelInterface
{
    private $model = null;
    private Builder $qb;
    private Collection|null $data;

    function __construct(Model $model)
    {
        $this->configure($model);
        return $this;
    }

    public static function make(Model $model): self
    {

        $static = app(static::class, ["model" => $model]);

        return $static;
    }

    public function configure(Model $model)
    {
        $this->model = $model;
        $this->initQueryBuilder();
        $this->setUp();
    }

    public function setUp()
    {
    }

    public function getQueryBuilder(): Builder
    {
        return $this->qb;
    }


    public function batchUpdate2(array $dataRows, array $keys){

        // foreach ($keys as $key) {
        //     $cond += [$key => $row[$key]];
        // }

        dd($dataRows);




        // foreach ($dataRows as $row) {
        //     $cond = [];



        //     $records[] = ["conditions" => $cond, "columns" => $row];
        // }



        batch()->update($this->model, $dataRows, 'id');

        $this->model::whereIn();
    }

    public function batchUpdate3(array $dataRows, array $keys)
    {
        $records = [];

        // 'conditions' => ['id' => 1, 'status' => 'active'],
        // 'columns'    => [
        //     'status' => 'invalid',
        //     'nickname' => 'mohammad',
        // ],
        // $arrays = [
        //     [
        //         'conditions' => ['id' => 1, 'status' => 'active'],
        //         'columns'    => [
        //             'status' => 'invalid',
        //             'nickname' => 'mohammad',
        //         ],
        //     ],
        //     [
        //         'conditions' => ['id' => 2],
        //         'columns'    => [
        //             'nickname' => 'mavinoo',
        //             'name' => 'mohammad',
        //         ],
        //     ],
        //     [
        //         'conditions' => ['id' => 3],
        //         'columns'    => [
        //             'nickname' => 'ali',
        //         ],
        //     ],
        // ];

        foreach ($dataRows as $row) {
            $cond = [];

            foreach ($keys as $key) {
                $cond += [$key => $row[$key]];
            }

            $records[] = ["conditions" => $cond, "columns" => $row];
        }

        $keyname = $keys[0];

        dd(batch()->updateMultipleCondition($this->model, $records, $keyname));
    }

    public function batchUpdate(array $dataRows, array $keys):bool
    {

        // if ($this->model instanceof Pessoa){
        //     dd($dataRows);
        // }

        batch()->update($this->model, $dataRows, $keys[0]);
        return true;
    }

    public function update(array $data, array $keys)
    {
        return $this->model::where($keys)->update($data);
    }

    public function updateInsert(array $data, array $keys)
    {
        $this->model->upsert($data, $keys);
        return true;
    }

    public function getModel(): Model|null
    {
        return $this->model;
    }

    private function initQueryBuilder() {
        $this->qb = $this->model::class::select();
        return $this->qb;
    }

    public function where(
        string|null $field = "id",
        string|null $cond = "=",
        string|null $value = "1"
    ): self {

        //$this->qb = $this->model::class::where($field, $cond, $value);
        $this->qb = $this->qb->where($field, $cond, $value);

        return $this;
    }

    public function distinct(array $fields = []): self {
        $this->qb = $this->qb->distinct($fields);
        return $this;
    }

    public function whereIn(
            string|null $field,
            array $value
        ): self {

        $this->qb = $this->model::class::whereIn($field, $value);

        return $this;
    }

    public function get(): self
    {
        $this->data = $this->qb->get();
        return $this;
    }

    public function first(): self
    {
        $this->data = collect([$this->qb->first()]);
        return $this;
    }

    public function hasData(): bool
    {
        try {
            if ($this->data) {
                return true;
            }
        } catch (Throwable $e) {
            return false;
        }

        return false;

    }

    public function getData(): array {

        if (!$this->hasData()) {
            return null;
        }

        return $this->data->toArray() ?? [];
    }

}
