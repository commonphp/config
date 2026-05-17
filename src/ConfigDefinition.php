<?php

declare(strict_types=1);

namespace CommonPHP\Config;

use CommonPHP\Config\Contracts\ConfigSchemaInterface;
use CommonPHP\Config\Exceptions\ConfigException;

final class ConfigDefinition
{
    public function __construct(
        private readonly string $path,
        private readonly ?string $format = null,
        private readonly array $defaults = [],
        private readonly ?ConfigSchemaInterface $schema = null,
        private readonly bool $required = true,
        private readonly bool $writable = true,
        private readonly bool $mergeDefaults = true,
    ) {
        if (trim($path) === '') {
            throw new ConfigException('Configuration definition path cannot be empty.');
        }
    }

    public static function file(
        string $path,
        ?string $format = null,
        array $defaults = [],
        ?ConfigSchemaInterface $schema = null,
        bool $required = true,
        bool $writable = true,
        bool $mergeDefaults = true,
    ): self {
        return new self($path, $format, $defaults, $schema, $required, $writable, $mergeDefaults);
    }

    public function path(): string
    {
        return $this->path;
    }

    public function format(): ?string
    {
        return $this->format;
    }

    public function defaults(): array
    {
        return $this->defaults;
    }

    public function schema(): ?ConfigSchemaInterface
    {
        return $this->schema;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function shouldMergeDefaults(): bool
    {
        return $this->mergeDefaults;
    }

    public function withPath(string $path): self
    {
        return new self(
            $path,
            $this->format,
            $this->defaults,
            $this->schema,
            $this->required,
            $this->writable,
            $this->mergeDefaults,
        );
    }

    public function withFormat(?string $format): self
    {
        return new self(
            $this->path,
            $format,
            $this->defaults,
            $this->schema,
            $this->required,
            $this->writable,
            $this->mergeDefaults,
        );
    }

    public function withDefaults(array $defaults, bool $mergeDefaults = true): self
    {
        return new self(
            $this->path,
            $this->format,
            $defaults,
            $this->schema,
            $this->required,
            $this->writable,
            $mergeDefaults,
        );
    }

    public function withMergeDefaults(bool $mergeDefaults = true): self
    {
        return new self(
            $this->path,
            $this->format,
            $this->defaults,
            $this->schema,
            $this->required,
            $this->writable,
            $mergeDefaults,
        );
    }

    public function withSchema(?ConfigSchemaInterface $schema): self
    {
        return new self(
            $this->path,
            $this->format,
            $this->defaults,
            $schema,
            $this->required,
            $this->writable,
            $this->mergeDefaults,
        );
    }

    public function optional(): self
    {
        return new self(
            $this->path,
            $this->format,
            $this->defaults,
            $this->schema,
            false,
            $this->writable,
            $this->mergeDefaults,
        );
    }

    public function required(): self
    {
        return new self(
            $this->path,
            $this->format,
            $this->defaults,
            $this->schema,
            true,
            $this->writable,
            $this->mergeDefaults,
        );
    }

    public function writable(bool $writable = true): self
    {
        return new self(
            $this->path,
            $this->format,
            $this->defaults,
            $this->schema,
            $this->required,
            $writable,
            $this->mergeDefaults,
        );
    }
}
