<?php

namespace Proho\Domain\Adapters;

use Proho\Domain\Enums\FieldTypesEnum;
use Proho\Domain\Field;
use Exception;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Leandrocfe\FilamentPtbrFormFields\PtbrCpfCnpj;
use Leandrocfe\FilamentPtbrFormFields\PtbrPhone;
use Proho\Domain\Components\HourMinuteInput;
use Proho\Domain\Interfaces\FieldInterface;
use Proho\Domain\Service;

class InputFilamentAdapter
{
    private $inputField;

    public function getInputField()
    {
        return $this->inputField;
    }

    function __construct(FieldInterface $field)
    {
        $this->inputField = null;

        // if ($field->getType() == FieldTypesEnum::Select) {
        //     dd($field);
        // }
        match ($field->getType()) {
            FieldTypesEnum::String => ($this->inputField = TextInput::make(
                $field->getName(),
            )),
            FieldTypesEnum::StringLong => ($this->inputField = TextInput::make(
                $field->getName(),
            )->maxLength(100)),
            FieldTypesEnum::TextArea => ($this->inputField = Textarea::make(
                $field->getName(),
            )),
            FieldTypesEnum::Decimal => ($this->inputField = TextInput::make(
                $field->getName(),
            )->numeric()),
            FieldTypesEnum::Date => ($this->inputField = DatePicker::make(
                $field->getName(),
            )->displayFormat("d/m/Y")),
            FieldTypesEnum::HourQty
                => ($this->inputField = HourMinuteInput::make(
                $field->getName(),
            )),
            FieldTypesEnum::DateTime
                => ($this->inputField = DateTimePicker::make(
                $field->getName(),
            )->displayFormat("d/m/Y H:i")),
            FieldTypesEnum::Radio => ($this->inputField = Radio::make(
                $field->getName(),
            )->options($field->getOptions())),
            FieldTypesEnum::Select => ($this->inputField = Select::make(
                $field->getName(),
            )),
            FieldTypesEnum::Integer => ($this->inputField = TextInput::make(
                $field->getName(),
            )->integer()),
            FieldTypesEnum::Boolean => ($this->inputField = Toggle::make(
                $field->getName(),
            )),
            FieldTypesEnum::Cpf => ($this->inputField = PtbrCpfCnpj::make(
                $field->getName(),
            )
                ->cpf()
                ->rule("cpf")),
            FieldTypesEnum::Fone => ($this->inputField = PtbrPhone::make(
                $field->getName(),
            )->minLength(10)),
        };

        if (in_array($field->getType(), [FieldTypesEnum::String])) {
            foreach ($field->getColumnAttr() as $key => $value) {
                if (
                    isset($value->getArguments()["length"]) &&
                    is_numeric($value->getArguments()["length"])
                ) {
                    $this->inputField->maxLength(
                        $value->getArguments()["length"],
                    );
                }
            }
        } elseif (
            in_array($field->getType(), [
                FieldTypesEnum::Radio,
                FieldTypesEnum::Select,
            ])
        ) {
            if ($field->getRelation()) {
                $relationship =
                    $field->getRelation()["relationship"] ?? "findOptions";

                $searchMethod = $relationship =
                    $field->getRelation()["relationship"] ?? "findOptions";

                $lazy = $field->getRelation()["lazyLoad"] ?? false;

                $limit = $field->getRelation()["limit"] ?? 49;
                $idRef = $field->getRelation()["ref"] ?? "id";

                $label = $field->getRelation()["label"] ?? null;
                $labelArray = is_array($label)
                    ? $label
                    : ($label !== null
                        ? [$label]
                        : []);

                if ($lazy) {
                    $this->inputField
                        ->searchable()
                        ->noSearchResultsMessage("Nenhuma registro encontrado")
                        ->searchPrompt("Digite 3 caracteres para buscar...")

                        ->getSearchResultsUsing(function (string $search) use (
                            $field,
                            $labelArray,
                            $limit,
                            $idRef,
                            $searchMethod,
                        ) {
                            if (strlen($search) < 3) {
                                return [];
                            }

                            $qb = null;
                            if ($searchMethod != "findOptions") {
                                $qb = EntityManager::getRepository(
                                    $field->getRelation()["class"],
                                )->$searchMethod();
                            }

                            $dadosFiltrados = EntityManager::getRepository(
                                $field->getRelation()["class"],
                            )->searchOptions(
                                id: $idRef,
                                fields: $labelArray,
                                orderBy: null,
                                search: $search,
                                limit: $limit,
                                qb: $qb,
                            );

                            $hasMore = count($dadosFiltrados) >= $limit;

                            // Adiciona mensagem no final
                            if ($hasMore) {
                                $dadosFiltrados[
                                    ""
                                ] = "--- Mais de {$limit} resultados. Refine sua busca para ver mais ---";
                            }

                            return $dadosFiltrados;
                        })
                        ->getOptionLabelUsing(function ($value) use (
                            $field,
                            $labelArray,
                            $idRef,
                        ): ?string {
                            $repo = EntityManager::getRepository(
                                $field->getRelation()["class"],
                            );

                            $dados = [
                                $repo->find([$idRef => $value])->toArray(),
                            ];

                            $dados = $repo->extractFields(
                                $dados,
                                $labelArray,
                                " - ",
                            );

                            return $dados[array_key_first($dados)] ?? null;
                        });
                } else {
                    $dadosFiltrados = [];

                    $qb = null;
                    if ($searchMethod != "findOptions") {
                        $qb = EntityManager::getRepository(
                            $field->getRelation()["class"],
                        )->$searchMethod();
                    }
                    // if ($qb != null) {
                    //     dd($qb->getDQL());
                    // }
                    //dd($relationship);
                    //

                    $dadosFiltrados = EntityManager::getRepository(
                        $field->getRelation()["class"],
                    )->findOptions(id: $idRef, fields: $labelArray, qb: $qb);

                    $this->inputField->options($dadosFiltrados)->searchable();
                }

                // if ($field->getRelation()[""]) {
                //     if ($relationship != "findOptions") {
                //         $dadosFiltrados = [];

                //         $dadosFiltrados = $this->extrairComCamposConcatenados(
                //             EntityManager::getRepository(
                //                 $field->getRelation()["class"],
                //             )->$relationship(),
                //             $labelArray,
                //         );

                //         $this->inputField
                //             ->options($dadosFiltrados)
                //             ->searchable();
                //     } else {
                //         $dadosFiltrados = [];

                //         $dadosFiltrados = $this->extrairComCamposConcatenados(
                //             EntityManager::getRepository(
                //                 $field->getRelation()["class"],
                //             )->$relationship($field->getRelation()["ref"], [
                //                 $labelArray[0],
                //             ]),
                //             $labelArray,
                //         );

                //         $this->inputField
                //             ->options($dadosFiltrados)
                //             ->searchable();
                //     }
            } else {
                foreach ($field->getColumnAttr() as $key => $value) {
                    if (isset($value->getArguments()["enumType"])) {
                        $enumClass = $value->getArguments()["enumType"];
                        $options = method_exists($enumClass, "toSelectArray")
                            ? $enumClass::toSelectArray()
                            : $enumClass::toArray();

                        $this->inputField->options($options);
                        //->colors(
                        // [
                        //     "primary" => static fn(
                        //         $state
                        //     ): bool => $state == 1 || $state == 4,
                        // ];
                        //);
                        // ->colors([
                        //     "primary" => static fn($state): bool => $state ==
                        //         1 || $state == 4,
                        //     "warning" => static fn($state): bool => $state == 2,
                        //     "success" => static fn($state): bool => $state == 3,
                        //     "secondary" => static fn($state): bool => in_array(
                        //         $state,
                        //         [5, 6, 7]
                        //     ),
                        // ]);
                    }
                }
            }
        }
        if ($this->inputField) {
            $this->inputField->disabled($field->isDisabled());

            $this->inputField->label(
                $field->getLabel() === ""
                    ? $field->getName()
                    : $field->getLabel(),
            );

            if ($field->getHint()) {
                if ($field->getHintType() == "float") {
                    $this->inputField->hintAction(
                        Action::make("time_info") // Prevent click
                            ->icon("heroicon-o-question-mark-circle")
                            ->tooltip($field->getHint())
                            ->label(""),
                    );
                } else {
                    $this->inputField->hint($field->getHint());
                }
            }

            if ($field->getDefault() !== null) {
                $this->inputField->default($field->getDefault());
            }

            if ($field->getDatalist() !== null) {
                if (is_array($field->getDatalist())) {
                    $this->inputField->datalist($field->getDatalist());
                } elseif (class_exists($field->getDatalist())) {
                    $this->inputField->datalist(
                        $field->getDatalist()::service()->query(),
                    );
                }
            }

            if ($field->getOptions() !== null) {
                // if ($field->getName() == "escolaridade_id") {
                //     dd($field->getOptions());
                // }

                if (is_array($field->getOptions())) {
                    $this->inputField->options($field->getOptions());
                } elseif (
                    is_object($field->getOptions()) &&
                    $field->getOptions() instanceof Service
                ) {
                    $this->inputField->options($field->getOptions()->query());
                } elseif (class_exists($field->getOptions())) {
                    $this->inputField->options(
                        $field->getOptions()::service()->query(),
                    );
                } else {
                    throw new Exception(
                        "Erro carregando options: " .
                            $field->getOptions() .
                            " não foi possível resolver. Para o campo: " .
                            $field->getName(),
                    );
                }
            }
        }
    }

    public static function make(Field $field): self
    {
        $static = app(static::class, ["field" => $field]);
        return $static;
    }
}

