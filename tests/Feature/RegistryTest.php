<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Feature;

use Atrium\Solutions\Solution;
use Atrium\Solutions\Solutions;
use Atrium\Solutions\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Where solutions are found, and which one the project was made from.
 */
final class RegistryTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir().'/atrium-registry-'.uniqid();
        mkdir($this->root);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->root);

        parent::tearDown();
    }

    /**
     * @param  array<string,mixed>  $with
     */
    private function folder(string $path, string $name, array $with = []): void
    {
        mkdir("{$this->root}/{$path}/{$name}", 0777, true);
        file_put_contents("{$this->root}/{$path}/{$name}/solution.json", json_encode(['name' => $name, ...$with]));
    }

    private function registry(string ...$paths): Solutions
    {
        config(['atrium-solutions.paths' => $paths]);

        return new Solutions;
    }

    public function test_every_folder_of_every_path_is_a_solution_by_name(): void
    {
        $this->folder('a', 'zeta');
        $this->folder('a', 'alpha');
        $this->folder('b', 'beta');
        mkdir("{$this->root}/b/no-json");

        $solutions = $this->registry("{$this->root}/a", "{$this->root}/b/")->all();

        $this->assertSame(['alpha', 'beta', 'zeta'], array_keys($solutions));
        $this->assertSame("{$this->root}/b/beta", $solutions['beta']->path);
    }

    public function test_the_first_path_wins_a_name_both_have(): void
    {
        $this->folder('a', 'alpha', ['brand' => 'First']);
        $this->folder('b', 'alpha', ['brand' => 'Second']);

        $this->assertSame('First', $this->registry("{$this->root}/a", "{$this->root}/b")->find('alpha')?->brand);
        $this->assertSame('Second', $this->registry("{$this->root}/b", "{$this->root}/a")->find('alpha')?->brand);
    }

    public function test_a_path_without_solutions_gives_none(): void
    {
        $this->assertSame([], $this->registry("{$this->root}/nothing-here")->all());
        $this->assertSame([], $this->registry()->all());
    }

    public function test_the_folders_are_read_once_until_flushed(): void
    {
        $this->folder('a', 'alpha');
        $registry = $this->registry("{$this->root}/a");
        $this->assertSame(['alpha'], array_keys($registry->all()));

        $this->folder('a', 'beta');
        $this->assertSame(['alpha'], array_keys($registry->all()));

        $registry->flush();
        $this->assertSame(['alpha', 'beta'], array_keys($registry->all()));

        config(['atrium-solutions.paths' => []]);
        $registry->flush();
        $this->assertSame([], $registry->all());
    }

    public function test_a_solution_added_by_hand_joins_the_ones_found(): void
    {
        $this->folder('a', 'alpha');
        $registry = $this->registry("{$this->root}/a");
        $probe = new Solution(name: 'probe', path: '/tmp', label: ['en' => 'Probe']);

        $registry->add($probe);

        $this->assertSame(['alpha', 'probe'], array_keys($registry->all()));
        $this->assertSame($probe, $registry->find('probe'));
    }

    public function test_a_solution_added_before_the_folders_are_read_does_not_hide_them(): void
    {
        $this->folder('a', 'alpha');
        $registry = $this->registry("{$this->root}/a");

        $registry->add(new Solution(name: 'probe', path: '/tmp', label: ['en' => 'Probe']));

        $this->assertNotNull($registry->find('alpha'));
    }

    public function test_a_solution_added_by_hand_replaces_the_one_of_its_name(): void
    {
        $this->folder('a', 'alpha', ['brand' => 'Found']);
        $registry = $this->registry("{$this->root}/a");

        $registry->add(new Solution(name: 'alpha', path: '/tmp', label: ['en' => 'Alpha'], brand: 'Added'));

        $this->assertSame('Added', $registry->find('alpha')?->brand);
    }

    /**
     * @return array<string,array{mixed,?string}>
     */
    public static function installed(): array
    {
        return [
            'not set' => [null, null],
            'empty' => ['', null],
            'a name nobody knows' => ['castle', null],
            'not a string' => [42, null],
            'a name' => ['alpha', 'alpha'],
            'a name in capitals' => [' ALPHA ', 'alpha'],
        ];
    }

    #[DataProvider('installed')]
    public function test_the_installed_solution_is_the_one_the_config_names(mixed $configured, ?string $expected): void
    {
        $this->folder('a', 'alpha');
        $registry = $this->registry("{$this->root}/a");
        config(['atrium-solutions.installed' => $configured]);

        $this->assertSame($expected, $registry->installed()?->name);
    }

    public function test_the_catalogue_lists_nothing_and_tells_how_to_get_solutions_when_there_are_none(): void
    {
        config(['atrium-solutions.paths' => []]);
        $this->app->make(Solutions::class)->flush();

        $this->artisan('atrium:solution:list')
            ->expectsOutputToContain('No solutions: `composer require alveby/atrium-solutions`.')
            ->doesntExpectOutputToContain('Install one')
            ->assertSuccessful();

        $this->assertSame(0, Artisan::call('atrium:solution:list', ['--json' => true]));
        $this->assertSame([], json_decode(Artisan::output(), true));
    }

    public function test_the_catalogue_says_which_solutions_lack_a_module(): void
    {
        $this->app->make(Solutions::class)->add(new Solution(
            name: 'probe',
            path: '/tmp',
            label: ['en' => 'Probe site'],
            modules: ['alveby/atrium-pages', 'alveby/atrium-ghost', 'alveby/atrium-phantom'],
        ));

        $this->artisan('atrium:solution:list')
            ->expectsTable(['Name', 'Site', 'Modules', 'Ready'], [
                ['law', 'Law firm', 'pages, team, news, stores, forms, faq, reviews', 'yes'],
                ['probe', 'Probe site', 'pages, ghost, phantom', 'composer require alveby/atrium-ghost alveby/atrium-phantom'],
            ])
            ->assertSuccessful();
    }

    public function test_the_catalogue_marks_a_solution_that_is_ready(): void
    {
        $this->artisan('atrium:solution:list')
            ->expectsTable(['Name', 'Site', 'Modules', 'Ready'], [[
                'law',
                'Law firm',
                'pages, team, news, stores, forms, faq, reviews',
                'yes',
            ]])
            ->assertSuccessful();
    }

    public function test_the_catalogue_leaves_a_line_between_the_table_and_the_way_to_install(): void
    {
        Artisan::call('atrium:solution:list');

        $this->assertMatchesRegularExpression('/[+┘]\n\n  Install one: php artisan atrium:solution:install NAME\n$/u', Artisan::output());
    }

    public function test_the_json_catalogue_is_a_list_of_the_solutions_written_for_reading(): void
    {
        $this->app->make(Solutions::class)->add(new Solution(
            name: 'probe',
            path: '/tmp',
            label: ['en' => 'Law/Tax', 'ru' => 'Юристы'],
        ));

        $this->assertSame(0, Artisan::call('atrium:solution:list', ['--json' => true]));
        $output = Artisan::output();
        $catalogue = json_decode($output, true);

        $this->assertSame([0, 1], array_keys($catalogue));
        $this->assertSame(['law', 'probe'], array_column($catalogue, 'name'));
        $this->assertStringContainsString("\n    {\n        \"name\": \"law\"", $output, 'Indented for reading.');
        $this->assertStringContainsString('"en": "Law/Tax"', $output, 'A slash is not escaped.');
        $this->assertStringContainsString('"ru": "Юристы"', $output, 'A letter is not escaped.');
    }

    public function test_paths_that_are_not_set_give_no_solutions(): void
    {
        config(['atrium-solutions.paths' => null]);

        $this->assertSame([], (new Solutions)->all());
    }
}
