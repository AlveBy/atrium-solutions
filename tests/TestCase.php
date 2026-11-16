<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests;

use Atrium\Admin\AtriumServiceProvider;
use Atrium\Admin\Support\PublicUrl;
use Atrium\Faq\FaqServiceProvider;
use Atrium\Forms\FormsServiceProvider;
use Atrium\News\NewsServiceProvider;
use Atrium\Pages\PagesServiceProvider;
use Atrium\Reviews\ReviewsServiceProvider;
use Atrium\Solutions\SolutionsServiceProvider;
use Atrium\Stores\StoresServiceProvider;
use Atrium\Team\TeamServiceProvider;
use Atrium\Theme\ThemeServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\ServiceProvider as InertiaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Tighten\Ziggy\ZiggyServiceProvider;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    /** The modules the solutions of these tests use. */
    protected const MODULES = [
        'alveby/atrium-pages',
        'alveby/atrium-team',
        'alveby/atrium-news',
        'alveby/atrium-stores',
        'alveby/atrium-forms',
        'alveby/atrium-faq',
        'alveby/atrium-reviews',
    ];

    protected function getPackageProviders($app): array
    {
        return [
            InertiaServiceProvider::class,
            ZiggyServiceProvider::class,
            AtriumServiceProvider::class,
            ThemeServiceProvider::class,
            PagesServiceProvider::class,
            TeamServiceProvider::class,
            NewsServiceProvider::class,
            StoresServiceProvider::class,
            FormsServiceProvider::class,
            FaqServiceProvider::class,
            ReviewsServiceProvider::class,
            SolutionsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $config = $app['config'];
        $config->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        // CI runs the suite against MySQL and PostgreSQL too.
        $target = env('DB_CONNECTION');
        $config->set('database.default', in_array($target, ['mysql', 'pgsql'], true) ? $target : 'testing');
        $config->set('atrium.demo', false);
        $config->set('atrium.auth.two_factor.enabled', false);
        $config->set('atrium.dashboard.timings.enabled', false);
        $config->set('atrium.modules', self::MODULES);
        // No solution unless a test installs one, whatever the .env says.
        $config->set('atrium-solutions.installed', null);
    }

    /**
     * On MySQL and PostgreSQL the whole suite shares one database: the
     * tables of every module a solution here uses.
     */
    protected function defineDatabaseMigrations(): void
    {
        foreach (['pages', 'team', 'news', 'stores', 'forms', 'faq', 'reviews'] as $package) {
            $this->loadMigrationsFrom(dirname(__DIR__, 2).'/'.$package.'/database/migrations');
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        PublicUrl::forgetPaths();
    }
}
