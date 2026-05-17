<?php

declare(strict_types=1);

namespace CommonPHP\Config\Contracts;

use CommonPHP\Config\ConfigSchemaValidator;

abstract class AbstractConfigSchema implements ConfigSchemaInterface
{
    public function __construct(
        private readonly array $rules = [],
    ) {
    }

    public function rules(): array
    {
        return $this->rules;
    }

    public function validate(array $config): array
    {
        return (new ConfigSchemaValidator())->validate($config, $this);
    }

    public function assertValid(array $config): void
    {
        (new ConfigSchemaValidator())->assertValid($config, $this);
    }
}
