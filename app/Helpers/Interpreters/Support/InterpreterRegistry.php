<?php

namespace App\Helpers\Interpreters\Support;

use App\Helpers\Interpreters\Contracts\TestInterpreter;
use App\Helpers\Interpreters\HADSInterpreter;
use App\Helpers\Interpreters\PCL5Interpreter;
use App\Helpers\Interpreters\PHQ9Interpreter;

final class InterpreterRegistry
{
    /** @return array<string, class-string<TestInterpreter>> */
    public static function map(): array
    {
        return [
            'PHQ9' => PHQ9Interpreter::class,
            'HADS' => HADSInterpreter::class,
            'PCL-5' => \App\Helpers\Interpreters\PCL5Interpreter::class,
            'PCL5'  => \App\Helpers\Interpreters\PCL5Interpreter::class,
            // добавить остальные: 'GAD7' => Gad7Interpreter::class, ...
        ];
    }

    public static function make(string $code): TestInterpreter
    {
        $map = self::map();
        if (!isset($map[$code])) {
            throw new \InvalidArgumentException("Unknown test code: {$code}");
        }
        return app($map[$code]);
    }
}
