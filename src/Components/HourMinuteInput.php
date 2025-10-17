<?php

// File: app/Components/HourMinuteInput.php

namespace Proho\Domain\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Contracts\Support\Htmlable;

class HourMinuteInput extends TextInput
{
    protected string $view = "proho-domain::components.hour-minute-input";

    protected function setUp(): void
    {
        parent::setUp();

        // Allow typing, set inputmode for numeric keyboards on mobile
        $this->extraAttributes([
            "inputmode" => "numeric",
            "autocomplete" => "off",
        ]);

        // Validation: accept pattern HHH:MM where HHH 0-999 and MM 00-59
        // We'll also register a server-side rule to ensure minutes < 60 and hours between 0 and 999
        // $this->rules([
        //     function ($attribute, $value, $fail) {
        //         if (
        //             !is_string($value) ||
        //             !preg_match('/^\d{1,3}:\d{2}$/', $value)
        //         ) {
        //             return $fail("O formato deve ser HHH:MM.");
        //         }

        //         [$h, $m] = explode(":", $value);
        //         $h = intval($h);
        //         $m = intval($m);

        //         if ($h < 0 || $h > 999) {
        //             return $fail("Horas devem estar entre 0 e 999.");
        //         }

        //         if ($m < 0 || $m > 59) {
        //             return $fail("Minutos devem estar entre 00 e 59.");
        //         }
        //     },
        // ]);

        // Dehydrate as the string HHH:MM by default; expose helper to get total minutes if needed
        $this->dehydrated(true);

        // Provide a helper to set from total minutes
        $this->afterStateHydrated(function ($component, $state) {
            // ensure the state is in HHH:MM format if someone provided integer minutes
            if (is_int($state) || ctype_digit((string) $state)) {
                $minutes = intval($state);
                $h = intdiv($minutes, 60);
                $m = $minutes % 60;
                $component->state(sprintf("%d:%02d", $h, $m));
            }
        });

        // // 💾 Antes de salvar no banco (HHH:MM → inteiro)
        // $this->mutateDehydratedStateUsing(function ($state) {
        //     dd("333");
        //     if (is_string($state) && str_contains($state, ":")) {
        //         [$h, $m] = explode(":", $state);
        //         $total = (int) $h * 60 + (int) $m;
        //         return $total;
        //     }

        //     return (int) $state;
        // });

        // Mutate input when saving: normalize e.g. "5:7" => "005:07"? We'll keep HHH without leading zeros but pad minutes
        $this->dehydrateStateUsing(function ($state) {
            //    dd($state);
            if (!$state) {
                return null;
            }
            if (!is_string($state)) {
                return $state;
            }
            if (preg_match('/^(\d{1,3}):(\d{1,2})$/', $state, $m)) {
                $h = intval($m[1]);
                $min = intval($m[2]);
                if ($h < 0) {
                    $h = 0;
                }
                if ($h > 999) {
                    $h = 999;
                }
                if ($min < 0) {
                    $min = 0;
                }
                if ($min > 59) {
                    $min = 59;
                }
                //dd(sprintf("%d:%02d", $h, $min));
                return $h * 60 + $min;
            }
            return $state;
        });
    }

    /**
     * Helper: return value in total minutes (server-side)
     */
    public static function toTotalMinutes(?string $value): ?int
    {
        if (!$value) {
            return null;
        }
        if (!is_string($value)) {
            return null;
        }
        if (!preg_match('/^(\d{1,3}):(\d{2})$/', $value, $m)) {
            return null;
        }
        return intval($m[1]) * 60 + intval($m[2]);
    }
}
