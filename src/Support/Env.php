<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Support;

/**
 * Sets a variable in the project's .env: replaces its line, or adds one at
 * the end. False when there is no .env to write to.
 */
final class Env
{
    public static function set(string $key, string $value, ?string $file = null): bool
    {
        $file ??= app()->environmentFilePath();

        if (! is_file($file) || ! is_writable($file)) {
            return false;
        }

        $line = $key.'='.(preg_match('/^[A-Za-z0-9_.\/:-]*$/', $value) ? $value : '"'.addcslashes($value, '"\\').'"');
        $env = (string) file_get_contents($file);
        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

        $env = preg_match($pattern, $env)
            ? (string) preg_replace($pattern, str_replace(['\\', '$'], ['\\\\', '\\$'], $line), $env)
            : rtrim($env, "\n").($env === '' ? '' : "\n").$line."\n";

        return file_put_contents($file, $env) !== false;
    }
}
