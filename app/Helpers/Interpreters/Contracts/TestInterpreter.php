<?php

namespace App\Helpers\Interpreters\Contracts;

use App\Helpers\Interpreters\DTO\TestResult;

interface TestInterpreter {
    public function code(): string;
    public function name(): string;

    /**
     * @param array $answers associative: ['q1' => 2, 'q2' => 0, ...]
     * @param array $context e.g. ['sex' => 'M|F', 'age' => '30]
     */
    public function interpret(array $answers, array $context = []): TestResult;
}
