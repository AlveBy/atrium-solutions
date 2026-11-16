<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Feature;

use Atrium\Pages\Models\Page;
use Atrium\Solutions\Tests\TestCase;
use Atrium\Team\Models\Member;
use Illuminate\Filesystem\Filesystem;

/**
 * `atrium:solution:install`: modules on and off, the .env, the content.
 */
final class InstallSolutionTest extends TestCase
{
    private string $root;

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        // The command edits config/atrium.php and .env: copies, never the package's.
        $this->root = sys_get_temp_dir().'/atrium-solutions-'.uniqid();
        mkdir($this->root.'/config', 0777, true);
        file_put_contents($this->root.'/config/atrium.php', str_replace(
            "        // 'alveby/atrium-brands',\n",
            "        'alveby/atrium-pages',\n        'alveby/atrium-catalog',\n",
            (string) file_get_contents(dirname(__DIR__, 3).'/admin/config/atrium.php'),
        ));
        file_put_contents($this->root.'/.env', "APP_NAME=Laravel\nAPP_ENV=testing\n");
        $app->useConfigPath($this->root.'/config');
        $app->useEnvironmentPath($this->root);

        $app['config']->set('app.name', 'Laravel');
        // The project had the shop before; the law firm does without it.
        $app['config']->set('atrium.modules', ['alveby/atrium-pages', 'alveby/atrium-catalog']);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->root);

        parent::tearDown();
    }

    public function test_it_sets_the_project_up_as_the_law_firm(): void
    {
        $this->artisan('atrium:solution:install', ['name' => 'law'])
            ->expectsOutputToContain('Turning off alveby/atrium-catalog')
            ->expectsOutputToContain('Law firm is ready')
            ->assertSuccessful();

        $config = (string) file_get_contents($this->root.'/config/atrium.php');
        $this->assertStringContainsString("'alveby/atrium-team'", $config);
        $this->assertStringNotContainsString("'alveby/atrium-catalog'", $config);

        $env = (string) file_get_contents($this->root.'/.env');
        $this->assertStringContainsString('ATRIUM_SOLUTION=law', $env);
        $this->assertStringContainsString('APP_NAME="Vellmar Oake"', $env);
        $this->assertSame('law', config('atrium-solutions.installed'));

        $this->assertTrue(Page::query()->where('code', 'practice')->exists());
        $this->assertSame(6, Member::query()->count());

        // Once more: the content is there already, nothing is doubled.
        $this->artisan('atrium:solution:install', ['name' => 'law'])
            ->expectsOutputToContain('already has content')
            ->assertSuccessful();
        $this->assertSame(6, Member::query()->count());
    }

    public function test_keep_modules_leaves_the_others_on(): void
    {
        $this->artisan('atrium:solution:install', ['name' => 'law', '--keep-modules' => true])->assertSuccessful();

        $this->assertStringContainsString("'alveby/atrium-catalog'", (string) file_get_contents($this->root.'/config/atrium.php'));
    }

    public function test_a_name_it_does_not_know_lists_the_ones_there_are(): void
    {
        $this->artisan('atrium:solution:install', ['name' => 'castle'])
            ->expectsOutputToContain('There is no solution "castle"')
            ->expectsOutputToContain('law')
            ->assertFailed();
    }
}
