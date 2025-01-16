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
use Filament\Forms\Components\Select;

class InputFilamentAdapter
{
    private $inputField;

    public function getInputField()
    {
        return $this->inputField;
    }

    function __construct(Field $field)
    {
        $this->inputField = null;

        match ($field->getType()) {
            FieldTypesEnum::String => ($this->inputField = TextInput::make(
                $field->getName()
            )),
            FieldTypesEnum::StringLong => ($this->inputField = TextInput::make(
                $field->getName()
            )->maxLength(100)),
            FieldTypesEnum::TextArea => ($this->inputField = Textarea::make(
                $field->getName()
            )),
            FieldTypesEnum::Decimal => ($this->inputField = TextInput::make(
                $field->getName()
            )->numeric()),
            FieldTypesEnum::Date => ($this->inputField = DatePicker::make(
                $field->getName()
            )->displayFormat("d/m/Y")),
            FieldTypesEnum::Radio => ($this->inputField = Radio::make(
                $field->getName()
            )->options($field->getOptions())),
            FieldTypesEnum::Select => ($this->inputField = Select::make(
                $field->getName()
            )),
            FieldTypesEnum::Integer => ($this->inputField = TextInput::make(
                $field->getName()
            )->integer()),
            FieldTypesEnum::Boolean => ($this->inputField = Toggle::make(
                $field->getName()
            )),
        };

        if ($this->inputField) {
            $this->inputField->label(
                $field->getLabel() === ""
                    ? $field->getName()
                    : $field->getLabel()
            );

            if ($field->getHint()) {
                if ($field->getHintType() == "float") {
                    $this->inputField->hintAction(
                        Action::make("time_info") // Prevent click
                            ->icon("heroicon-o-question-mark-circle")
                            ->tooltip($field->getHint())
                            ->label("")
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
                        $field->getDatalist()::service()->query()
                    );
                }
            }

            if ($field->getOptions() !== null) {
                // if ($field->getName() == "escolaridade_id") {
                //     dd($field->getOptions());
                // }

                if (is_array($field->getOptions())) {
                    $this->inputField->options($field->getOptions());
                } elseif (class_exists($field->getOptions())) {
                    $this->inputField->options(
                        $field->getOptions()::service()->query()
                    );
                } else {
                    throw new Exception(
                        "Erro carregando options: " .
                            $field->getOptions() .
                            " não foi possível resolver. Para o campo: " .
                            $field->getName()
                    );
                }
            }
        }
    }

    public static function make(Field $field): self
    {
        dd('$field');

        $static = app(static::class, ["field" => $field]);
        return $static;
    }
}
