<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Support;

use Atrium\Admin\Support\PublicUrl;
use Atrium\Solutions\SolutionSeeder;
use ReflectionProperty;

/**
 * A solution seeder that writes down what it was started with and leaves
 * the public paths cache in a state the installer must clear.
 */
final class RecordingSeeder extends SolutionSeeder
{
    public static bool $already = false;

    /** @var array<string,mixed>|null the cache of public paths when the seeder started */
    public static ?array $cacheOnStart = null;

    public static ?string $language = null;

    public static function reset(): void
    {
        self::$already = false;
        self::$cacheOnStart = null;
        self::$language = null;
    }

    public static function cache(): ReflectionProperty
    {
        return new ReflectionProperty(PublicUrl::class, 'pathCache');
    }

    protected function filled(): bool
    {
        self::$cacheOnStart = self::cache()->getValue();
        self::$language = $this->country->language();
        self::cache()->setValue(null, ['made by the seeder' => []]);

        return self::$already;
    }

    protected function fill(): void {}
}
