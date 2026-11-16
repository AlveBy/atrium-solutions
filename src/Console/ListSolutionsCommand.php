<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Console;

use Atrium\Admin\Modules\Modules;
use Atrium\Solutions\Solution;
use Atrium\Solutions\Solutions;
use Illuminate\Console\Command;

/**
 * The ready-made sites there are; `--json` is the catalogue the Atrium
 * website shows.
 */
final class ListSolutionsCommand extends Command
{
    protected $signature = 'atrium:solution:list {--json : The catalogue as JSON: texts in every language, modules, look, screenshots}';

    protected $description = 'List the ready-made sites that atrium:solution:install can set up';

    public function handle(Solutions $solutions, Modules $modules): int
    {
        if ($this->option('json')) {
            $this->line((string) json_encode(
                array_values(array_map(static fn (Solution $solution): array => $solution->toCatalogue(), $solutions->all())),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            ));

            return self::SUCCESS;
        }

        if ($solutions->all() === []) {
            $this->components->info('No solutions: `composer require alveby/atrium-solutions`.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($solutions->all() as $solution) {
            $missing = array_filter($solution->modules, static fn (string $name): bool => $modules->find($name) === null);
            $rows[] = [
                $solution->name,
                Solution::text($solution->label, 'en'),
                implode(', ', array_map(static fn (string $name): string => str_replace('alveby/atrium-', '', $name), $solution->modules)),
                $missing === [] ? 'yes' : 'composer require '.implode(' ', $missing),
            ];
        }

        $this->table(['Name', 'Site', 'Modules', 'Ready'], $rows);
        $this->newLine();
        $this->line('  Install one: <info>php artisan atrium:solution:install NAME</info>');

        return self::SUCCESS;
    }
}
