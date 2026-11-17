<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Feature;

use Atrium\Pages\Models\Page;
use Atrium\Solutions\Solution;
use Atrium\Solutions\Solutions;
use Atrium\Solutions\Tests\Support\FakeMigrate;
use Atrium\Solutions\Tests\Support\RecordingSeeder;
use Atrium\Solutions\Tests\TestCase;
use Atrium\Team\Models\Member;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use PHPUnit\Framework\Attributes\DataProvider;

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
            ->doesntExpectOutputToContain('FAIL')
            ->assertSuccessful();

        $config = (string) file_get_contents($this->root.'/config/atrium.php');
        $this->assertStringContainsString("'alveby/atrium-team'", $config);
        $this->assertStringNotContainsString("'alveby/atrium-catalog'", $config);

        $env = (string) file_get_contents($this->root.'/.env');
        $this->assertStringContainsString('ATRIUM_SOLUTION=law', $env);
        $this->assertStringContainsString('APP_NAME="Whitaker Brennan LLP"', $env);
        $this->assertSame('law', config('atrium-solutions.installed'));

        $this->assertTrue(Page::query()->where('code', 'practice')->exists());
        $this->assertSame(6, Member::query()->count());

        // Once more: the content is there already, nothing is doubled.
        $this->artisan('atrium:solution:install', ['name' => 'law'])
            ->expectsOutputToContain('already has content')
            ->assertSuccessful();
        $this->assertSame(6, Member::query()->count());
    }

    public function test_the_site_is_named_after_the_business_of_the_installer_s_country(): void
    {
        $this->artisan('atrium:solution:install', ['name' => 'law', '--locale' => 'de'])->assertSuccessful();

        $this->assertStringContainsString('APP_NAME="Brandt Vogler Rechtsanwälte PartG mbB"', (string) file_get_contents($this->root.'/.env'));
        $this->assertSame('Brandt Vogler Rechtsanwälte PartG mbB', config('app.name'));
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

    private function env(): string
    {
        return (string) file_get_contents($this->root.'/.env');
    }

    /**
     * A solution of the test's own, known to the registry.
     *
     * @param  array<string,mixed>  $with
     */
    private function offer(array $with = []): Solution
    {
        $solution = new Solution(...[
            'name' => 'probe',
            'path' => $this->root,
            'label' => ['en' => 'Probe site'],
            'brand' => 'Probe Ltd',
            ...$with,
        ]);
        $this->app->make(Solutions::class)->add($solution);

        return $solution;
    }

    /**
     * The theme's skins, as the installer asks for them: a class the theme
     * brings, here a container binding of the same name.
     *
     * @param  array<int,string>  $names
     */
    private function themeWithSkins(array $names): void
    {
        $this->app->bind('Atrium\Theme\Skins\Skins', fn (): object => new class($names)
        {
            /** @param  array<int,string>  $names */
            public function __construct(private readonly array $names) {}

            public function find(string $name): ?string
            {
                return in_array($name, $this->names, true) ? $name : null;
            }
        });
    }

    public function test_the_migrations_are_run_for_the_new_modules_without_asking(): void
    {
        $this->app->make(Kernel::class)->registerCommand(new FakeMigrate);
        FakeMigrate::$calls = [];
        FakeMigrate::$exit = 0;
        $this->offer();

        $this->artisan('atrium:solution:install', ['name' => 'probe'])
            ->expectsOutputToContain('Running migrations')
            ->expectsOutputToContain('DONE')
            ->doesntExpectOutputToContain('FAIL')
            ->assertSuccessful();

        $this->assertSame([true], FakeMigrate::$calls);
    }

    public function test_migrations_that_fail_are_reported_and_the_rest_goes_on(): void
    {
        $this->app->make(Kernel::class)->registerCommand(new FakeMigrate);
        FakeMigrate::$calls = [];
        FakeMigrate::$exit = 1;
        $this->offer();

        $this->artisan('atrium:solution:install', ['name' => 'probe'])
            ->expectsOutputToContain('Running migrations')
            ->expectsOutputToContain('FAIL')
            ->expectsOutputToContain('Probe site is ready')
            ->assertSuccessful();

        $this->assertSame('ATRIUM_SOLUTION=probe', trim(implode("\n", array_filter(explode("\n", $this->env()), static fn (string $line): bool => str_starts_with($line, 'ATRIUM_SOLUTION')))));
    }

    public function test_the_site_is_filled_in_the_country_asked_for_and_the_paths_are_forgotten_around_it(): void
    {
        RecordingSeeder::reset();
        RecordingSeeder::cache()->setValue(null, ['from before' => []]);
        $this->offer(['seeder' => RecordingSeeder::class]);

        $this->artisan('atrium:solution:install', ['name' => 'probe', '--locale' => 'de'])
            ->expectsOutputToContain('Filling the site')
            ->expectsOutputToContain('DONE')
            ->doesntExpectOutputToContain('FAIL')
            ->doesntExpectOutputToContain('already has content')
            ->assertSuccessful();

        $this->assertSame('de', RecordingSeeder::$language);
        $this->assertSame([], RecordingSeeder::$cacheOnStart, 'The paths of before the switch are dropped before the seeder runs.');
        $this->assertSame([], RecordingSeeder::cache()->getValue(), 'And those it made are dropped after.');
    }

    public function test_without_a_country_the_site_is_filled_in_the_language_of_the_app(): void
    {
        RecordingSeeder::reset();
        config(['app.locale' => 'ja']);
        $this->offer(['seeder' => RecordingSeeder::class]);

        $this->artisan('atrium:solution:install', ['name' => 'probe'])->assertSuccessful();

        $this->assertSame('ja', RecordingSeeder::$language);
    }

    public function test_a_site_that_has_content_is_left_as_it_is_and_the_paths_are_forgotten_all_the_same(): void
    {
        RecordingSeeder::reset();
        RecordingSeeder::$already = true;
        $this->offer(['seeder' => RecordingSeeder::class]);

        $this->artisan('atrium:solution:install', ['name' => 'probe'])
            ->expectsOutputToContain('Filling the site')
            ->expectsOutputToContain('DONE')
            ->expectsOutputToContain('The site already has content; nothing was added.')
            ->assertSuccessful();

        $this->assertSame([], RecordingSeeder::cache()->getValue());
    }

    public function test_it_ends_with_the_address_of_the_site(): void
    {
        $this->offer();

        $this->artisan('atrium:solution:install', ['name' => 'probe'])
            ->expectsOutputToContain('Probe site is ready: '.url('/'))
            ->assertSuccessful();
    }

    public function test_without_a_name_it_asks_which_site(): void
    {
        $this->artisan('atrium:solution:install')
            ->expectsChoice('Which site?', 'law', ['law' => 'Law firm'])
            ->expectsOutputToContain('Law firm is ready')
            ->assertSuccessful();

        $this->assertStringContainsString('ATRIUM_SOLUTION=law', $this->env());
    }

    public function test_without_a_name_and_without_a_terminal_it_names_the_solutions(): void
    {
        $this->artisan('atrium:solution:install', ['--no-interaction' => true])
            ->expectsOutputToContain('Name the solution.')
            ->expectsOutputToContain('Solutions: law')
            ->assertFailed();

        $this->assertStringNotContainsString('ATRIUM_SOLUTION', $this->env());
    }

    public function test_with_no_solutions_at_all_it_says_so_and_asks_nothing(): void
    {
        config(['atrium-solutions.paths' => []]);
        $this->app->make(Solutions::class)->flush();

        $this->artisan('atrium:solution:install')
            ->expectsOutputToContain('Name the solution.')
            ->expectsOutputToContain('Solutions: none')
            ->assertFailed();
    }

    public function test_a_solution_whose_modules_composer_lacks_is_refused_before_anything_changes(): void
    {
        $this->offer(['modules' => ['alveby/atrium-pages', 'alveby/atrium-ghost', 'alveby/atrium-phantom']]);

        $this->artisan('atrium:solution:install', ['name' => 'probe'])
            ->expectsOutputToContain('The Probe site needs module packages Composer does not have.')
            ->expectsOutputToContain('composer require alveby/atrium-ghost alveby/atrium-phantom')
            ->assertFailed();

        $this->assertStringNotContainsString('ATRIUM_SOLUTION', $this->env());
        $this->assertNull(config('atrium-solutions.installed'));
        $this->assertStringContainsString("'alveby/atrium-catalog'", (string) file_get_contents($this->root.'/config/atrium.php'));
    }

    public function test_a_skin_the_theme_does_not_have_leaves_the_look_alone(): void
    {
        $this->offer(['skin' => 'nordic']);

        $this->artisan('atrium:solution:install', ['name' => 'probe'])
            ->expectsOutputToContain('The nordic skin is not in this theme yet')
            ->assertSuccessful();

        $this->assertStringNotContainsString('ATRIUM_THEME_SKIN', $this->env());
        $this->assertNotSame('nordic', config('atrium-theme.skin'));
        $this->assertSame('probe', config('atrium-solutions.installed'));
    }

    public function test_a_skin_the_theme_has_is_put_on_and_its_assets_published(): void
    {
        $this->themeWithSkins(['nordic']);
        $this->offer(['skin' => 'nordic']);

        $source = $this->root.'/skin-assets.css';
        $target = $this->root.'/published/skin.css';
        file_put_contents($source, 'new look');
        mkdir(dirname($target));
        file_put_contents($target, 'old look');
        $publisher = new class($this->app) extends ServiceProvider {};
        (fn () => $this->publishes([$source => $target], 'atrium-theme-assets'))->call($publisher);

        $this->artisan('atrium:solution:install', ['name' => 'probe'])
            ->expectsOutputToContain('Wearing the nordic skin')
            ->assertSuccessful();

        $this->assertStringContainsString('ATRIUM_THEME_SKIN=nordic', $this->env());
        $this->assertSame('nordic', config('atrium-theme.skin'));
        $this->assertSame('new look', file_get_contents($target), 'The assets are published over the old ones.');
    }

    public function test_a_skin_the_theme_lacks_is_not_put_on_when_it_knows_other_skins(): void
    {
        $this->themeWithSkins(['classic']);
        $this->offer(['skin' => 'nordic']);

        $this->artisan('atrium:solution:install', ['name' => 'probe'])
            ->expectsOutputToContain('The nordic skin is not in this theme yet')
            ->assertSuccessful();

        $this->assertStringNotContainsString('ATRIUM_THEME_SKIN', $this->env());
    }

    public function test_a_solution_without_a_skin_or_a_seeder_only_switches_modules(): void
    {
        $this->offer();

        $this->artisan('atrium:solution:install', ['name' => 'probe'])
            ->doesntExpectOutputToContain('skin')
            ->expectsOutputToContain('Probe site is ready')
            ->assertSuccessful();

        $this->assertStringNotContainsString('ATRIUM_THEME_SKIN', $this->env());
        $this->assertStringContainsString('ATRIUM_SOLUTION=probe', $this->env());
        $this->assertSame(0, Member::query()->count());
    }

    public function test_a_seeder_that_is_not_a_solution_seeder_is_refused(): void
    {
        $this->offer(['seeder' => \stdClass::class]);

        $this->artisan('atrium:solution:install', ['name' => 'probe'])
            ->expectsOutputToContain('stdClass is not a solution seeder.')
            ->assertFailed();
    }

    /**
     * @return array<string,array{?string,string,string}>
     */
    public static function siteNames(): array
    {
        return [
            'a fresh project is renamed' => ['Laravel', 'Probe Ltd', 'Probe Ltd'],
            'an empty name is renamed' => ['', 'Probe Ltd', 'Probe Ltd'],
            'no name is renamed' => [null, 'Probe Ltd', 'Probe Ltd'],
            'a name of its own stays' => ['My shop', 'Probe Ltd', 'My shop'],
            'a solution without a brand renames nothing' => ['Laravel', '', 'Laravel'],
        ];
    }

    #[DataProvider('siteNames')]
    public function test_the_site_takes_the_brand_only_while_it_is_still_called_laravel(?string $current, string $brand, string $expected): void
    {
        config(['app.name' => $current, 'atrium.seo.title_suffix' => 'unchanged']);
        $this->offer(['brand' => $brand]);

        $this->artisan('atrium:solution:install', ['name' => 'probe'])->assertSuccessful();

        $renamed = $expected === 'Probe Ltd';
        $this->assertSame($expected, config('app.name'));
        $this->assertSame($renamed, str_contains($this->env(), 'APP_NAME="Probe Ltd"'));
        $this->assertSame($renamed ? ' | Probe Ltd' : 'unchanged', config('atrium.seo.title_suffix'));
    }
}
