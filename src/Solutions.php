<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions;

/**
 * The ready-made sites there are: a solution.json in each sub-folder of the
 * folders in config('atrium-solutions.paths'). This package adds its own
 * folder; any package or the project may add more.
 */
final class Solutions
{
    /** @var array<string,Solution>|null */
    private ?array $solutions = null;

    /**
     * @return array<string,Solution> name => solution, by name
     */
    public function all(): array
    {
        if ($this->solutions !== null) {
            return $this->solutions;
        }

        $this->solutions = [];

        foreach ((array) config('atrium-solutions.paths', []) as $path) {
            foreach (glob(rtrim((string) $path, '/').'/*/solution.json') ?: [] as $file) {
                $solution = Solution::fromFile($file);
                $this->solutions[$solution->name] ??= $solution;
            }
        }

        ksort($this->solutions);

        return $this->solutions;
    }

    public function find(string $name): ?Solution
    {
        return $this->all()[strtolower(trim($name))] ?? null;
    }

    /**
     * Make a solution known without a folder in the config: tests.
     */
    public function add(Solution $solution): void
    {
        $this->all();
        $this->solutions[$solution->name] = $solution;
    }

    /**
     * The solution this project was made from, when it was.
     */
    public function installed(): ?Solution
    {
        $name = config('atrium-solutions.installed');

        return is_string($name) && $name !== '' ? $this->find($name) : null;
    }

    public function flush(): void
    {
        $this->solutions = null;
    }
}
