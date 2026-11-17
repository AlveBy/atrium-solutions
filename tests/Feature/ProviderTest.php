<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Feature;

use Atrium\Admin\Modules\Module;
use Atrium\Admin\Modules\Modules;
use Atrium\Solutions\Solutions;
use Atrium\Solutions\SolutionsServiceProvider;
use Atrium\Solutions\Tests\TestCase;
use Atrium\Team\Models\Member;
use Illuminate\Console\Application as Console;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use ReflectionProperty;

/**
 * What the package does to the application when it starts.
 */
final class ProviderTest extends TestCase
{
    private const GHOST = 'Atrium\\Nobody\\Models\\Ghost';

    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir().'/atrium-provider-'.uniqid();
        mkdir($this->root);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->root);

        parent::tearDown();
    }

    /**
     * Start the package afresh, as the application does: with the solution
     * the project was made from, if any.
     *
     * @param  array<string,mixed>|null  $solution  its solution.json, null for a project made from none
     */
    private function start(?array $solution): SolutionsServiceProvider
    {
        if ($solution !== null) {
            mkdir("{$this->root}/one");
            file_put_contents("{$this->root}/one/solution.json", json_encode(['name' => 'one', ...$solution]));
        }

        config([
            'atrium-solutions.paths' => [$this->root],
            'atrium-solutions.installed' => $solution === null ? null : 'one',
        ]);
        $this->app->forgetInstance(Solutions::class);

        $provider = new SolutionsServiceProvider($this->app);
        $provider->register();
        $provider->boot();

        return $provider;
    }

    public function test_the_folder_of_the_package_comes_after_the_folders_already_there(): void
    {
        config(['atrium-solutions.paths' => ['/first', '/second']]);

        (new SolutionsServiceProvider($this->app))->register();

        $this->assertSame(['/first', '/second', dirname(__DIR__, 2).'/solutions'], config('atrium-solutions.paths'));
    }

    public function test_a_project_with_no_folders_set_still_finds_the_package_s(): void
    {
        config(['atrium-solutions.paths' => null]);

        (new SolutionsServiceProvider($this->app))->register();

        $this->assertSame([dirname(__DIR__, 2).'/solutions'], config('atrium-solutions.paths'));
    }

    public function test_the_registry_is_one_for_the_application(): void
    {
        $this->assertSame($this->app->make(Solutions::class), $this->app->make(Solutions::class));
    }

    public function test_the_config_of_the_package_is_there_for_what_the_project_did_not_set(): void
    {
        config(['atrium-solutions' => ['installed' => 'mine']]);

        (new SolutionsServiceProvider($this->app))->register();

        $this->assertSame('mine', config('atrium-solutions.installed'));
        $this->assertSame([dirname(__DIR__, 2).'/solutions'], config('atrium-solutions.paths'), 'The default of the package: no folders but its own.');

        config(['atrium-solutions' => []]);

        (new SolutionsServiceProvider($this->app))->register();

        $this->assertArrayHasKey('installed', config('atrium-solutions'));
    }

    public function test_the_commands_and_the_config_to_publish_are_there_in_the_console_only(): void
    {
        $bootstrappers = new ReflectionProperty(Console::class, 'bootstrappers');
        $before = count($bootstrappers->getValue());
        $this->app['config']->set('atrium-solutions.installed', null);

        $this->start(null);

        $this->assertSame($before + 1, count($bootstrappers->getValue()));
        $publish = ServiceProvider::pathsToPublish(SolutionsServiceProvider::class, 'atrium-solutions-config');
        $this->assertSame([realpath(dirname(__DIR__, 2).'/config/atrium-solutions.php')], array_map('realpath', array_keys($publish)));
        $this->assertSame([config_path('atrium-solutions.php')], array_values($publish));

        $this->setConsole(false);
        $before = count($bootstrappers->getValue());
        $this->resetPublishing();

        $this->start(null);

        $this->assertSame($before, count($bootstrappers->getValue()));
        $this->assertSame([], ServiceProvider::pathsToPublish(SolutionsServiceProvider::class, 'atrium-solutions-config'));
    }

    private function setConsole(bool $is): void
    {
        $property = new ReflectionProperty($this->app, 'isRunningInConsole');
        $property->setValue($this->app, $is);
    }

    private function resetPublishing(): void
    {
        foreach (['publishes', 'publishGroups'] as $name) {
            (new ReflectionProperty(ServiceProvider::class, $name))->setValue(null, []);
        }
    }

    public function test_a_project_made_from_no_solution_keeps_its_own_names_pages_and_menu(): void
    {
        $this->app['config']->set('atrium.public_urls.'.Member::class, ['prefix' => 'team']);

        $this->start(null);

        $this->assertSame('Team', trans('atrium-team::team.modules.members'));
        $this->assertSame('team', config('atrium.public_urls.'.Member::class.'.prefix'));
        $this->assertFalse($this->app['events']->hasListeners('composing: atrium-theme::skins.*'));
    }

    public function test_the_solution_names_the_modules_in_its_language_and_in_the_site_s(): void
    {
        $this->start(['labels' => ['pt_BR' => ['atrium-team::team.modules.members' => 'Advogados']]]);

        $this->assertSame('Advogados', trans('atrium-team::team.modules.members', [], 'pt_BR'));
        $this->assertSame('Advogados', trans('atrium-team::team.modules.members', [], 'pt-br'));
        $this->assertSame('Team', trans('atrium-team::team.modules.members', [], 'en'));
    }

    public function test_a_language_with_no_region_is_named_once_and_only_in_its_own_locale(): void
    {
        $this->start(['labels' => ['de' => ['atrium-team::team.modules.members' => 'Anwälte']]]);

        $this->assertSame('Anwälte', trans('atrium-team::team.modules.members', [], 'de'));
        $this->assertSame('Team', trans('atrium-team::team.modules.members', [], 'en'));
    }

    public function test_the_solution_moves_the_pages_of_a_module_and_keeps_the_rest_of_its_rule(): void
    {
        $this->app['config']->set('atrium.public_urls.'.Member::class, ['prefix' => 'team', 'list' => 'index']);

        $this->start(['urls' => [Member::class => 'lawyers']]);

        $this->assertSame(['prefix' => 'lawyers', 'list' => 'index'], config('atrium.public_urls.'.Member::class));
    }

    public function test_a_module_the_application_has_no_rule_for_gives_its_own_besides_the_prefix(): void
    {
        $this->app->make(Modules::class)->add(new Module('alveby/atrium-ghost', resources: [self::GHOST], publicUrls: [self::GHOST => ['prefix' => 'ghost', 'index' => 'list']]));

        $this->start(['urls' => [self::GHOST => 'ghosts']]);

        $this->assertSame(['prefix' => 'ghosts', 'index' => 'list'], config('atrium.public_urls.'.self::GHOST));
    }

    public function test_a_class_no_module_owns_gets_the_prefix_alone(): void
    {
        $this->start(['urls' => [self::GHOST => 'ghosts']]);

        $this->assertSame(['prefix' => 'ghosts'], config('atrium.public_urls.'.self::GHOST));
    }

    public function test_a_rule_that_is_not_a_list_of_settings_is_left_as_it_is(): void
    {
        $this->app['config']->set('atrium.public_urls.'.self::GHOST, 'somewhere');

        $this->start(['urls' => [self::GHOST => 'ghosts']]);

        $this->assertSame('somewhere', config('atrium.public_urls.'.self::GHOST));
    }

    public function test_a_solution_with_a_menu_gives_it_to_the_layouts(): void
    {
        $this->start(['menu' => [['page' => 'practice']]]);

        $this->assertTrue($this->app['events']->hasListeners('composing: atrium-theme::skins.*'));
        $this->assertTrue($this->app['events']->hasListeners('composing: atrium-theme::layout'));
    }

    public function test_a_solution_without_a_menu_leaves_the_layouts_to_the_theme(): void
    {
        $this->start(['menu' => []]);

        $this->assertFalse($this->app['events']->hasListeners('composing: atrium-theme::skins.*'));
    }
}
