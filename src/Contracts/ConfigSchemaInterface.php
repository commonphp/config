<?php

declare(strict_types=1);

namespace CommonPHP\Config\Contracts;

interface ConfigSchemaInterface
{
    public function rules(): array;

    public function validate(array $config): array;

    public function assertValid(array $config): void;
}
