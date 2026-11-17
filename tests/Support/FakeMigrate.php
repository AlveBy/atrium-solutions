<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Support;

use Illuminate\Console\Command;

/**
 * Stands in for `migrate`: the installer's call is recorded, not run.
 */
final class FakeMigrate extends Command
{
    /** @var array<int,bool> whether --force was given, per call */
    public static array $calls = [];

    public static int $exit = 0;

    protected $signature = 'migrate {--force}';

    public function handle(): int
    {
        self::$calls[] = (bool) $this->option('force');

        return self::$exit;
    }
}
