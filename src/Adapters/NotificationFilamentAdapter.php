<?php

namespace Proho\DomainAdapters;

use Proho\DomainInterfaces\NotificationInterface;
use Proho\DomainInterfaces\ValidatorInterface;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class NotificationFilamentAdapter implements NotificationInterface
{
    private $validator = null;

    function __construct()
    {
        $this->configure();
        return $this;
    }

    public static function make(): self
    {
        $static = app(static::class, []);
        return $static;
    }

    public function configure()
    {
        $this->setUp();
    }

    public function setUp()
    {
    }

    public function mountMessagesForHtml(array $identify, array $messageBags)
    {
        $message = "";

        foreach ($messageBags as $key => $value) {
            if (count($value->getMessages()) > 0) {
                $idsList = explode("|", $value->getMessages()["identify"][0]);

                foreach ($identify as $idr => $idv) {
                    $message .= $idv . ": " . $idsList[$idr] . " ";
                }

                $message .= $message != "" ? "<li>" . $message . "</li>" : "";
                // if ($message == "<br>") {
                //     $message .= "</br>";
                // } else {
                //     $message = "";
                // }

                foreach ($value->getMessages() as $keym => $valuem) {
                    if ($keym == "identify") {
                        continue;
                    }
                    foreach ($valuem as $valuem2) {
                        $message .= "<li>" . $valuem2 . "</li>";
                    }
                }
            }
        }

        //$message .= "<li>teste</li>";
        //$message .= "<ul>" . $message . "</ul>";
        //dd($message);
        return $message;
    }

    public function notifyValidatorFail(ValidatorInterface ...$validators)
    {
        $validators = $this->getValidatorByType("fail", ...$validators);

        foreach ($validators as $validator) {
            if ($validator->notifyType() != "none") {
                $this->notifyFail(
                    $validator->getService()->getLabel(),
                    $this->mountMessagesForHtml(
                        array_keys($validator->getIdentify()),
                        $validator->messagesAll()
                    )
                );
            }
        }
    }

    public function notifyValidatorSuccess(ValidatorInterface ...$validators)
    {
        $validators = $this->getValidatorByType("success", ...$validators);

        $body = "Operação concluída: ";

        foreach ($validators as $validator) {
            if ($validator->notifyType() != "none") {
                if ($validator->notifyType() != "parent") {
                    $title = $validator->getService()->getLabel();
                }
                $body .= $validator->getService()->getLabel();
            }
        }

        $this->notifySuccess($title, $body);
    }

    public function getValidatorByType(
        string $type,
        ValidatorInterface ...$validators
    ): array {
        $result = [];

        foreach ($validators as $validator) {
            if ($validator->fails() && $type == "fail") {
                $result[] = $validator;
            } elseif (!$validator->fails() && $type == "success") {
                $result[] = $validator;
            }
        }
        return $result;
    }

    public function notifyFail($title, $body)
    {
        Notification::make()
            ->title("ERRO: " . $title)
            ->body($body)
            ->danger()
            ->persistent()
            ->send();
    }

    public function notifySuccess($title, $body)
    {
        Notification::make()->title($title)->body($body)->success()->send();
    }

    public function notifyValidator(ValidatorInterface ...$validators)
    {
        foreach ($validators as $validator) {
            log::debug(
                $validator->getService()->notifyType() . $validator::class
            );

            $title = $validator->getService()->getLabel();
            $body = null;

            if ($validator->fails()) {
                $this->notifyFail($title, $body, $validator);
            } else {
                $this->notifySuccess($title, $body, $validator);
            }
        }
    }

    //comportamento padrao se algum validator deu erro retorna erro
    //se nenhum validator deu erro retorna success
    public function notifyValidatorDefault(ValidatorInterface ...$validators)
    {
        Log::debug($this->anyValidatorFail($validators) . $this::class);

        //    dd($validators);

        if ($this->anyValidatorFail($validators)) {
            $this->notifyValidatorFail(...$validators);
        } else {
            $this->notifyValidatorSuccess(...$validators);
        }
    }

    //comportamento padrao se algum validator deu erro retorna erro
    //se nenhum validator deu erro retorna success
    public function notifyValidatorOneSuccess(ValidatorInterface ...$validators)
    {
        if ($this->anyValidatorFail($validators)) {
            $this->notifyValidatorFail(...$validators);
        } else {
            foreach ($validators as $validator) {
                if (
                    $validator->getService()->notifyType() != "none" &&
                    $validator->getService()->notifyType() != "parent"
                ) {
                    $this->notifyValidatorSuccess($validator);
                }
            }

            //$this->notifyValidatorSuccess(...$validators);
        }
    }

    public function notify(
        string $title,
        string $body,
        string $type = "success",
        bool $persistent = false
    ) {
        $ntType = match ($type) {
            "danger" => "danger",
            "warning" => "warning",
            default => "success",
        };

        Notification::make()
            ->title($title)
            ->body($body)
            ->$ntType()
            ->persistent($persistent)
            ->send();
    }

    // public function processValidate()
    // {
    //     if ($this->getValidator()->fails()) {
    //         Notification::make()
    //             ->title("erwwerwe")
    //             //->body($this->validator->messages())
    //             ->danger()
    //             ->send();
    //     }
    // }
    //
    public function anyValidatorFail($validators): bool
    {
        foreach ($validators as $validator) {
            if ($validator->fails()) {
                return true;
            }
        }
        return false;
    }
}
