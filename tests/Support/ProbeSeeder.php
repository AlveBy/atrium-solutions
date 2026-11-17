<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Support;

use Atrium\Solutions\SolutionSeeder;

/**
 * A solution seeder that fills nothing of its own and lets a test call the
 * helpers a real one uses.
 */
final class ProbeSeeder extends SolutionSeeder
{
    public bool $isFilled = false;

    public int $fills = 0;

    /**
     * @param  array<string,array<string,mixed>>  $content
     * @param  array<string,string>  $facts
     */
    public function with(array $content, array $facts = []): self
    {
        $this->content = $content;
        $this->facts = $facts;

        return $this;
    }

    public function invoke(string $method, mixed ...$arguments): mixed
    {
        return $this->{$method}(...$arguments);
    }

    public static function invokeStatic(string $method, mixed ...$arguments): mixed
    {
        return self::$method(...$arguments);
    }

    protected function filled(): bool
    {
        return $this->isFilled;
    }

    protected function fill(): void
    {
        $this->fills++;
    }
}
