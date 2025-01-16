<?php

namespace App\Domain\Base\Adapters;

use App\Domain\Base\Adapters\RulesFilament\AfterOrEqual;
use App\Domain\Base\Adapters\RulesFilament\Gt;
use App\Domain\Base\Adapters\RulesFilament\Gte;
use App\Domain\Base\Adapters\RulesFilament\MaxDigits;
use App\Domain\Base\Adapters\RulesFilament\Lte;
use App\Domain\Base\Adapters\RulesFilament\Regex;
use App\Domain\Base\Adapters\RulesFilament\Required;
use App\Domain\Base\Adapters\RulesFilament\RequiredWith;
use App\Domain\Base\Adapters\RulesFilament\RuleDefault;
use App\Domain\Base\Adapters\RulesFilament\Sometimes;
use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\Log;

class RulesFilamentAdapter
{
    public static function make(Field $field, string $rule): Field
    {
        $rule .= "|";

        foreach (explode("|", $rule) as $key => $value) {
            $rule_str = $value;

            if (strpos($value, ":") !== false) {
                $tmp_rule = explode(":", $value);
                $rule_str = $tmp_rule[0];
                $value = $tmp_rule[1] ?? null;
            }

            if (!$rule_str) {
                continue;
            }

            Log::debug($value . $rule_str);
            match ($rule_str) {
                "required" => ($field = Required::make($field, $value)),
                "required_with" => ($field = RequiredWith::make(
                    $field,
                    $value
                )),
                "lte" => ($field = Lte::make($field, $value)),
                "gte" => ($field = Gte::make($field, $value)),
                "gt" => ($field = Gt::make($field, $value)),
                "max_digits" => ($field = MaxDigits::make($field, $value)),
                "regex" => ($field = Regex::make($field, $value)),
                "after_or_equal" => ($field = AfterOrEqual::make(
                    $field,
                    $value
                )),
                default => ($field = RuleDefault::make($field, $rule_str)),
            };
        }

        return $field;
    }
}
