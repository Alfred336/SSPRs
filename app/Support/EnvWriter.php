<?php

namespace App\Support;

use RuntimeException;

class EnvWriter
{
    /**
     * @param  array<string, string|null>  $values
     */
    public function write(array $values): void
    {
        $path = base_path('.env');

        if (! file_exists($path)) {
            $example = base_path('.env.example');

            if (file_exists($example)) {
                copy($example, $path);
            } else {
                touch($path);
            }
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException('Unable to read the environment file.');
        }

        foreach ($values as $key => $value) {
            $contents = $this->setValue($contents, $key, $value);
        }

        if (file_put_contents($path, $contents, LOCK_EX) === false) {
            throw new RuntimeException('Unable to write the environment file.');
        }
    }

    protected function setValue(string $contents, string $key, ?string $value): string
    {
        $encoded = $this->encodeValue($value);
        $pattern = "/^{$key}=.*$/m";
        $line = "{$key}={$encoded}";

        if (preg_match($pattern, $contents) === 1) {
            return preg_replace($pattern, $line, $contents) ?? $contents;
        }

        return rtrim($contents).PHP_EOL.$line.PHP_EOL;
    }

    protected function encodeValue(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (preg_match('/\s|#|"|\'|=/', $value) !== 1) {
            return $value;
        }

        return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
    }
}
