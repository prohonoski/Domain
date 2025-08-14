<?php

namespace Proho\Domain\Enums;

enum FieldTypesEnum: string
{
    case String = "string";
    case StringFull = "stringfull";
    case StringShort = "stringshort";
    case StringLong = "stringlong";
    case TextArea = "textarea";
    case Editor = "textareaeditor";
    case Date = "date";
    case Time = "time";
    case DateTime = "datetime";
    case Decimal = "decimal";
    case Integer = "integer";
    case Boolean = "boolean";
    case Radio = "radio";
    case Select = "select";
}
