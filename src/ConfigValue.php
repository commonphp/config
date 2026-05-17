<?php

declare(strict_types=1);

namespace CommonPHP\Config;

use CommonPHP\Config\Exceptions\ConfigValidationException;
use JsonException;

final class ConfigValue
{
    public function __construct(
        private readonly mixed $value,
        private readonly ?string $key = null,
        private readonly bool $exists = true,
    ) {
    }

    public static function found(string $key, mixed $value): self
    {
        return new self($value, $key);
    }

    public static function missing(string $key, mixed $default = null): self
    {
        return new self($default, $key, false);
    }

    public function key(): ?string
    {
        return $this->key;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function raw(): mixed
    {
        return $this->value;
    }

    public function get(mixed $default = null): mixed
    {
        if ($this->exists) {
            return $this->value;
        }

        return func_num_args() > 0 ? $default : $this->value;
    }

    public function isNull(): bool
    {
        return $this->get() === null;
    }

    public function asString(?string $default = null): ?string
    {
        $value = func_num_args() > 0 ? $this->get($default) : $this->get();

        if ($value === null) {
            return null;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        throw $this->typeError('string', $value);
    }

    public function asInt(?int $default = null): ?int
    {
        $value = func_num_args() > 0 ? $this->get($default) : $this->get();

        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^-?(0|[1-9][0-9]*)$/', trim($value)) === 1) {
            return (int) $value;
        }

        throw $this->typeError('integer', $value);
    }

    public function asFloat(?float $default = null): ?float
    {
        $value = func_num_args() > 0 ? $this->get($default) : $this->get();

        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value) || (is_string($value) && is_numeric($value))) {
            return (float) $value;
        }

        throw $this->typeError('float', $value);
    }

    public function asBool(?bool $default = null): ?bool
    {
        $value = func_num_args() > 0 ? $this->get($default) : $this->get();

        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value !== 0;
        }

        if (is_string($value)) {
            $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($filtered !== null) {
                return $filtered;
            }
        }

        throw $this->typeError('boolean', $value);
    }

    public function asArray(?array $default = null): ?array
    {
        $value = func_num_args() > 0 ? $this->get($default) : $this->get();

        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        throw $this->typeError('array', $value);
    }

    public function __toString(): string
    {
        $value = $this->get('');

        if ($value === null) {
            return '';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        try {
            return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (JsonException) {
            return get_debug_type($value);
        }
    }

    private function typeError(string $expected, mixed $value): ConfigValidationException
    {
        $key = $this->key !== null ? ' for "' . $this->key . '"' : '';

        return new ConfigValidationException(
            'Configuration value' . $key . ' must be ' . $expected . ', got ' . get_debug_type($value) . '.'
        );
    }
}
