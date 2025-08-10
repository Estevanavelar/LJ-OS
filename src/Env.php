<?php

namespace LJOS;

class Env
{
    private static bool $loaded = false;

    public static function load(string $path = __DIR__ . '/../.env'): void
    {
        if (self::$loaded) {
            return;
        }
        if (!is_file($path)) {
            self::$loaded = true;
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }
            [$name, $value] = $parts;
            $name = trim($name);
            $value = trim($value);
            if (preg_match('/^".*"$/', $value) || preg_match('/^\'."'.'*\'."'$/', $value)) {
                $value = substr($value, 1, -1);
            }
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
            }
            if (!getenv($name)) {
                putenv($name . '=' . $value);
            }
        }
        self::$loaded = true;
    }

    public static function get(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);
        return $value !== false && $value !== null ? $value : $default;
    }
}
