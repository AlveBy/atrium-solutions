<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Console;

use Atrium\Admin\Modules\Modules;
use Atrium\Admin\Support\PublicUrl;
use Atrium\Solutions\Solution;
use Atrium\Solutions\Solutions;
use Atrium\Solutions\SolutionSeeder;
use Atrium\Solutions\Support\Env;
use Illuminate\Console\Command;

/**
 * Turns a project into one of the ready-made sites: its modules on (and the
 * others off, their data kept), their tables, the skin, the languages, the
 * brand and the content.
 */
final class InstallSolutionCommand extends Command
{
    protected $signature = 'atrium:solution:install
                            {name? : The solution, as atrium:solution:list names it}
                            {--locale= : The country the site is set in, by its language (de, ja, pt_BR); the app locale when not given}
                            {--keep-modules : Leave the modules the solution does not use on}';

    protected $description = 'Set up a ready-made site: its modules, look, languages and content';

    public function handle(Solutions $solutions, Modules $modules): int
    {
        $name = $this->argument('name') ?? ($this->input->isInteractive() && $solutions->all() !== []
            ? $this->choice('Which site?', array_map(static fn (Solution $s): string => Solution::text($s->label, 'en'), $solutions->all()))
            : null);
        $solution = is_string($name) ? $solutions->find($name) : null;

        if ($solution === null) {
            $this->components->error($name === null ? 'Name the solution.' : "There is no solution \"{$name}\".");
            $this->line('  Solutions: '.(implode(', ', array_keys($solutions->all())) ?: 'none'));

            return self::FAILURE;
        }

        $missing = array_values(array_filter($solution->modules, static fn (string $module): bool => $modules->find($module) === null));

        if ($missing !== []) {
            $this->components->error('The '.Solution::text($solution->label, 'en').' needs module packages Composer does not have.');
            $this->line('  composer require '.implode(' ', $missing));

            return self::FAILURE;
        }

        foreach ($solution->modules as $module) {
            if (! $modules->isInstalled($module)) {
                $this->components->task("Installing {$module}", fn (): bool => $modules->install($modules->find($module)));
            }
        }

        if (! $this->option('keep-modules')) {
            foreach (array_diff(array_keys($modules->installed()), $solution->modules) as $module) {
                $this->components->task("Turning off {$module} (its data stays)", fn (): bool => $modules->remove($module));
            }
        }

        $this->components->task('Running migrations', fn (): bool => $this->callSilently('migrate', ['--force' => true]) === 0);

        if ($solution->skin !== null && ! $this->skinExists($solution->skin)) {
            $this->components->warn("The {$solution->skin} skin is not in this theme yet: the site keeps the theme's own look.");
        } elseif ($solution->skin !== null) {
            $this->components->task("Wearing the {$solution->skin} skin", function () use ($solution): bool {
                config(['atrium-theme.skin' => $solution->skin]);

                return Env::set('ATRIUM_THEME_SKIN', $solution->skin);
            });
            $this->callSilently('vendor:publish', ['--tag' => 'atrium-theme-assets', '--force' => true]);
        }

        config(['atrium-solutions.installed' => $solution->name]);
        Env::set('ATRIUM_SOLUTION', $solution->name);

        // Page titles end in " | APP_NAME", and a fresh project is still called Laravel.
        $brand = $solution->brandIn($this->option('locale') ?: config('app.locale'));

        if (in_array(config('app.name'), [null, '', 'Laravel'], true) && $brand !== '') {
            Env::set('APP_NAME', $brand);
            config(['app.name' => $brand, 'atrium.seo.title_suffix' => ' | '.$brand]);
        }
        PublicUrl::forgetPaths();

        if ($solution->seeder !== null) {
            $seeder = $this->laravel->make($solution->seeder);

            if (! $seeder instanceof SolutionSeeder) {
                $this->components->error("{$solution->seeder} is not a solution seeder.");

                return self::FAILURE;
            }

            $seeded = false;
            $this->components->task('Filling the site', function () use ($seeder, $solution, &$seeded): bool {
                $seeded = $seeder->forSolution($solution, $this->option('locale') ?: null)->setContainer($this->laravel)->__invoke() !== false;

                return true;
            });
            PublicUrl::forgetPaths();

            if (! $seeded) {
                $this->components->info('The site already has content; nothing was added.');
            }
        }

        $this->components->info(Solution::text($solution->label, 'en').' is ready: '.url('/'));

        return self::SUCCESS;
    }

    private function skinExists(string $name): bool
    {
        $skins = 'Atrium\\Theme\\Skins\\Skins';

        return class_exists($skins) && $this->laravel->make($skins)->find($name) !== null;
    }
}
