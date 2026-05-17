<?php

declare(strict_types=1);

namespace CommonPHP\Config;

use CommonPHP\Config\Contracts\ConfigSchemaInterface;
use CommonPHP\Config\Exceptions\ConfigSchemaException;
use CommonPHP\Config\Exceptions\ConfigValidationException;

final class ConfigSchemaValidator
{
    public function validate(array $config, ConfigSchemaInterface|array $schema): array
    {
        $rules = $schema instanceof ConfigSchemaInterface ? $schema->rules() : $schema;
        $repository = new ConfigRepository($config);
        $errors = [];

        foreach ($rules as $field => $definition) {
            $errors = array_merge(
                $errors,
                $this->validateField((string) $field, $definition, $repository, $config),
            );
        }

        return $errors;
    }

    public function assertValid(array $config, ConfigSchemaInterface|array $schema): void
    {
        $errors = $this->validate($config, $schema);

        if ($errors !== []) {
            throw new ConfigValidationException(
                'Configuration did not match schema: ' . implode(' ', $errors)
            );
        }
    }

    private function validateField(
        string $field,
        mixed $definition,
        ConfigRepository $repository,
        array $config,
    ): array {
        $rule = $this->normalizeDefinition($definition);
        $exists = $repository->has($field);
        $value = $repository->get($field);

        if (($rule['required'] ?? false) && !$exists) {
            return ['Missing required configuration key: ' . $field . '.'];
        }

        if (!$exists) {
            return [];
        }

        if ($value === null && ($rule['nullable'] ?? false)) {
            return [];
        }

        $errors = [];

        if (($rule['types'] ?? []) !== [] && !$this->matchesAnyType($value, $rule['types'])) {
            $errors[] = 'Configuration key ' . $field . ' must be '
                . implode('|', $rule['types'])
                . ', got '
                . get_debug_type($value)
                . '.';
        }

        if (array_key_exists('allowed', $rule) && !in_array($value, $rule['allowed'], true)) {
            $errors[] = 'Configuration key ' . $field . ' contains an unsupported value.';
        }

        if (isset($rule['pattern']) && is_string($value) && !$this->matchesPattern($rule['pattern'], $value)) {
            $errors[] = 'Configuration key ' . $field . ' does not match the required pattern.';
        }

        foreach ($rule['callbacks'] ?? [] as $callback) {
            $result = $callback($value, $field, $config);

            if ($result === false) {
                $errors[] = 'Configuration key ' . $field . ' failed validation.';
            } elseif (is_string($result) && $result !== '') {
                $errors[] = $result;
            }
        }

        return $errors;
    }

    private function normalizeDefinition(mixed $definition): array
    {
        $rule = [
            'required' => false,
            'nullable' => false,
            'types' => [],
            'callbacks' => [],
        ];

        if (is_string($definition)) {
            return $this->applyRuleTokens($rule, explode('|', $definition));
        }

        if (is_callable($definition)) {
            $rule['callbacks'][] = $definition;

            return $rule;
        }

        if (!is_array($definition)) {
            $rule['types'][] = $this->normalizeType(get_debug_type($definition));

            return $rule;
        }

        if (array_is_list($definition)) {
            return $this->applyRuleTokens($rule, $definition);
        }

        if (isset($definition['required'])) {
            $rule['required'] = (bool) $definition['required'];
        }

        if (isset($definition['nullable'])) {
            $rule['nullable'] = (bool) $definition['nullable'];
        }

        if (isset($definition['type'])) {
            $rule['types'] = $this->normalizeTypes($definition['type']);
        } elseif (isset($definition['types'])) {
            $rule['types'] = $this->normalizeTypes($definition['types']);
        }

        if (isset($definition['allowed'])) {
            $rule['allowed'] = is_array($definition['allowed'])
                ? array_values($definition['allowed'])
                : [$definition['allowed']];
        } elseif (isset($definition['enum'])) {
            $rule['allowed'] = is_array($definition['enum'])
                ? array_values($definition['enum'])
                : [$definition['enum']];
        }

        if (isset($definition['pattern']) && is_string($definition['pattern'])) {
            $rule['pattern'] = $definition['pattern'];
        }

        if (isset($definition['rules'])) {
            $rule = $this->applyRuleTokens(
                $rule,
                is_array($definition['rules']) ? $definition['rules'] : explode('|', (string) $definition['rules']),
            );
        }

        if (array_key_exists('callback', $definition)) {
            $rule = $this->addCallback($rule, $definition['callback']);
        }

        if (array_key_exists('callbacks', $definition)) {
            $callbacks = [$definition['callbacks']];

            if (!is_callable($definition['callbacks']) && is_array($definition['callbacks'])) {
                $callbacks = $definition['callbacks'];
            }

            foreach ($callbacks as $callback) {
                $rule = $this->addCallback($rule, $callback);
            }
        }

        return $rule;
    }

    private function applyRuleTokens(array $rule, array $tokens): array
    {
        foreach ($tokens as $token) {
            if (is_callable($token)) {
                $rule['callbacks'][] = $token;
                continue;
            }

            if (
                is_string($token)
                && str_contains($token, '|')
                && !str_starts_with(strtolower(trim($token)), 'type:')
            ) {
                $rule = $this->applyRuleTokens($rule, explode('|', $token));
                continue;
            }

            $token = strtolower(trim((string) $token));

            if ($token === '') {
                continue;
            }

            if ($token === 'required') {
                $rule['required'] = true;
                continue;
            }

            if ($token === 'optional') {
                $rule['required'] = false;
                continue;
            }

            if ($token === 'nullable') {
                $rule['nullable'] = true;
                continue;
            }

            if (str_starts_with($token, 'type:')) {
                $rule['types'] = $this->normalizeTypes(substr($token, 5));
                continue;
            }

            if (str_starts_with($token, 'in:')) {
                $rule['allowed'] = array_map('trim', explode(',', substr($token, 3)));
                continue;
            }

            $rule['types'][] = $this->normalizeType($token);
        }

        $rule['types'] = array_values(array_unique($rule['types']));

        return $rule;
    }

    private function normalizeTypes(mixed $types): array
    {
        if (is_string($types)) {
            $types = preg_split('/[\|,]/', $types) ?: [];
        } elseif (!is_array($types)) {
            $types = [$types];
        }

        return array_values(array_unique(array_map(
            fn (mixed $type): string => $this->normalizeType((string) $type),
            array_filter($types, static fn (mixed $type): bool => trim((string) $type) !== ''),
        )));
    }

    private function normalizeType(string $type): string
    {
        return match (strtolower(trim($type))) {
            'bool' => 'boolean',
            'int' => 'integer',
            'double' => 'float',
            'numeric' => 'number',
            default => strtolower(trim($type)),
        };
    }

    private function matchesAnyType(mixed $value, array $types): bool
    {
        foreach ($types as $type) {
            if ($this->matchesType($value, $type)) {
                return true;
            }
        }

        return false;
    }

    private function matchesType(mixed $value, string $type): bool
    {
        return match ($type) {
            'mixed' => true,
            'null' => $value === null,
            'string' => is_string($value),
            'integer' => is_int($value),
            'float' => is_float($value),
            'number' => is_int($value) || is_float($value) || (is_string($value) && is_numeric($value)),
            'boolean' => is_bool($value),
            'array' => is_array($value),
            'list' => is_array($value) && array_is_list($value),
            'iterable' => is_iterable($value),
            'scalar' => is_scalar($value),
            'callable' => is_callable($value),
            'object' => is_object($value),
            default => is_object($value) && is_a($value, $type),
        };
    }

    private function addCallback(array $rule, mixed $callback): array
    {
        if (!is_callable($callback)) {
            throw new ConfigSchemaException('Configuration schema callbacks must be callable.');
        }

        $rule['callbacks'][] = $callback;

        return $rule;
    }

    private function matchesPattern(string $pattern, string $value): bool
    {
        $error = null;

        set_error_handler(static function (int $severity, string $message) use (&$error): bool {
            $error = $message;

            return true;
        });

        try {
            $result = preg_match($pattern, $value);
        } finally {
            restore_error_handler();
        }

        if ($result === false) {
            throw new ConfigSchemaException(
                'Invalid configuration schema pattern' . ($error !== null ? ': ' . $error : '.')
            );
        }

        return $result === 1;
    }
}
