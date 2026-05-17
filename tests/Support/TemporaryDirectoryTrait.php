<?php

declare(strict_types=1);

namespace CommonPHP\Config\Tests\Support;

trait TemporaryDirectoryTrait
{
    private string $temporaryDirectory;

    protected function createTemporaryDirectory(string $prefix = 'comphp_config_'): string
    {
        $this->temporaryDirectory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . $prefix
            . bin2hex(random_bytes(8));

        mkdir($this->temporaryDirectory, 0777, true);

        return $this->temporaryDirectory;
    }

    protected function temporaryPath(string $name): string
    {
        return $this->temporaryDirectory . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $name);
    }

    protected function removeTemporaryDirectory(): void
    {
        if (!isset($this->temporaryDirectory)) {
            return;
        }

        $this->removeDirectory($this->temporaryDirectory);
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);

        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path) && !is_link($path)) {
                $this->removeDirectory($path);
            } else {
                @chmod($path, 0600);
                @unlink($path);
            }
        }

        @chmod($directory, 0700);
        @rmdir($directory);
    }
}
