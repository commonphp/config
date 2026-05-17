<?php

declare(strict_types=1);

namespace CommonPHP\Config\Contracts;

use CommonPHP\Config\Exceptions\ConfigReadException;
use CommonPHP\Config\Exceptions\ConfigWriteException;

abstract class AbstractConfigDriver implements ConfigDriverInterface
{

    public function read(string $filename): array
    {
        if (!file_exists($filename)) {
            throw new ConfigReadException('Configuration file does not exist: ' . $filename);
        }

        if (!is_file($filename)) {
            throw new ConfigReadException('Configuration file is not a file: ' . $filename);
        }

        if (!is_readable($filename)) {
            throw new ConfigReadException('Configuration file is not readable: ' . $filename);
        }

        $data = file_get_contents($filename);

        if ($data === false) {
            throw new ConfigReadException('Unexpected error reading configuration file: ' . $filename);
        }

        return $this->decode($data);
    }

    public function write(string $filename, array $config): void
    {
        if (!file_exists($filename)) {
            $dir = dirname($filename);

            if (!file_exists($dir)) {
                throw new ConfigWriteException('Configuration file directory does not exist: ' . $dir);
            }

            if (!is_dir($dir)) {
                throw new ConfigWriteException('Configuration file directory is not a directory: ' . $dir);
            }

            if (!is_writable($dir)) {
                throw new ConfigWriteException('Configuration file directory is not writable: ' . $dir);
            }
        } else {
            if (!is_file($filename)) {
                throw new ConfigWriteException('Configuration file is not a file: ' . $filename);
            }

            if (!is_writable($filename)) {
                throw new ConfigWriteException('Configuration file is not writable: ' . $filename);
            }
        }

        $result = file_put_contents($filename, $this->encode($config), LOCK_EX);

        if ($result === false) {
            throw new ConfigWriteException('Unexpected error writing configuration file: ' . $filename);
        }
    }
}