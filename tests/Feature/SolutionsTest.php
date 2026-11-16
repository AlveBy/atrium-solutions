<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Feature;

use Atrium\Solutions\Solution;
use Atrium\Solutions\Solutions;
use Atrium\Solutions\SolutionSeeder;
use Atrium\Solutions\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The solutions this package ships and what it says about them.
 */
final class SolutionsTest extends TestCase
{
    /**
     * @return array<string,array{string}>
     */
    public static function shipped(): array
    {
        $names = [];

        foreach (glob(dirname(__DIR__, 2).'/solutions/*/solution.json') ?: [] as $file) {
            $names[basename(dirname($file))] = [$file];
        }

        return $names;
    }

    #[DataProvider('shipped')]
    public function test_a_shipped_solution_is_complete(string $file): void
    {
        $solution = Solution::fromFile($file);
        $root = dirname(__DIR__, 3);

        $this->assertSame(basename(dirname($file)), $solution->name, 'The folder is named after the solution.');
        $this->assertNotSame('', $solution->brand);

        foreach ($solution->languages as $language) {
            $this->assertNotSame('', $solution->label[$language] ?? '', "A label in {$language}.");
            $this->assertNotSame('', $solution->description[$language] ?? '', "A description in {$language}.");
        }

        // Every module is a package of this repository.
        $packages = [];

        foreach (glob($root.'/*/composer.json') ?: [] as $composer) {
            $packages[] = json_decode((string) file_get_contents($composer), true)['name'] ?? null;
        }

        foreach ($solution->modules as $module) {
            $this->assertContains($module, $packages, "{$module} is a package of the repository.");
        }

        $this->assertNotNull($solution->seeder);
        $this->assertTrue(is_subclass_of($solution->seeder, SolutionSeeder::class), "{$solution->seeder} is a solution seeder.");

        foreach (array_keys($solution->urls) as $model) {
            $this->assertTrue(class_exists($model), "{$model} exists.");
        }

        foreach ($solution->menu as $entry) {
            $this->assertTrue(isset($entry['page']) || (isset($entry['module']) && class_exists($entry['module'])), 'A menu entry is a page code or a model.');
        }

        // The labels rename keys that are there, in the languages of the site.
        foreach ($solution->labels as $language => $lines) {
            $this->assertContains($language, $solution->languages);

            foreach (array_keys($lines) as $key) {
                $this->assertNotSame($key, trans($key, [], 'en'), "{$key} is a translation.");
            }
        }
    }

    public function test_the_registry_finds_the_solutions_of_the_package(): void
    {
        $solutions = $this->app->make(Solutions::class);

        $this->assertArrayHasKey('law', $solutions->all());
        $this->assertSame('Vellmar Oake', $solutions->find(' LAW ')?->brand);
        $this->assertNull($solutions->find('nothing'));
        $this->assertNull($solutions->installed());
    }

    public function test_a_folder_without_a_proper_name_is_not_a_solution(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'solution');
        file_put_contents($file, json_encode(['name' => 'Law Firm']));

        try {
            $this->expectException(InvalidArgumentException::class);
            Solution::fromFile($file);
        } finally {
            unlink($file);
        }
    }

    public function test_a_text_falls_back_to_english(): void
    {
        $texts = ['en' => 'Law firm', 'de' => 'Anwaltskanzlei'];

        $this->assertSame('Anwaltskanzlei', Solution::text($texts, 'de'));
        $this->assertSame('Law firm', Solution::text($texts, 'ru'));
        $this->assertSame('Seul', Solution::text(['fr' => 'Seul'], 'ru'));
    }

    public function test_the_catalogue_lists_every_solution_with_its_install_command(): void
    {
        $this->artisan('atrium:solution:list')
            ->expectsOutputToContain('Law firm')
            ->expectsOutputToContain('atrium:solution:install NAME')
            ->assertSuccessful();

        $this->assertSame(0, Artisan::call('atrium:solution:list', ['--json' => true]));
        $catalogue = json_decode(Artisan::output(), true);

        $this->assertIsArray($catalogue);
        $law = collect($catalogue)->firstWhere('name', 'law');
        $this->assertSame('php artisan atrium:solution:install law', $law['install']);
        $this->assertSame(['en', 'de', 'ru'], $law['languages']);
        $this->assertContains('alveby/atrium-team', $law['modules']);
    }
}
