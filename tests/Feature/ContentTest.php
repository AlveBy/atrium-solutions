<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Feature;

use Atrium\Solutions\Support\Country;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Every solution is written in all fifteen languages of Atrium, each file
 * with every text English has and no other. Legal pages are the country's
 * own, so their names differ; each country has at least one.
 */
final class ContentTest extends TestCase
{
    /**
     * @return array<string,array{string}>
     */
    public static function solutions(): array
    {
        $solutions = [];

        foreach (glob(dirname(__DIR__, 2).'/solutions/*/content') ?: [] as $folder) {
            $solutions[basename(dirname($folder))] = [$folder];
        }

        return $solutions;
    }

    #[DataProvider('solutions')]
    public function test_every_language_has_the_english_texts(string $folder): void
    {
        $english = $this->keys(require $folder.'/en.php');

        foreach (Country::LOCALES as $locale) {
            $this->assertFileExists("{$folder}/{$locale}.php");
            $content = require "{$folder}/{$locale}.php";

            $this->assertSame($english, $this->keys($content), $locale);
            $this->assertNotContains('', Arr::dot($content), $locale);
            $this->assertNotEmpty($content['legal'] ?? [], "{$locale} has a legal page.");

            foreach (array_keys($content['legal'] ?? []) as $code) {
                $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', (string) $code, "{$locale}: a legal page's code is its address.");
            }
        }
    }

    #[DataProvider('solutions')]
    public function test_the_placeholders_are_the_same_in_every_language(string $folder): void
    {
        $english = $this->placeholders(require $folder.'/en.php');

        foreach (Country::LOCALES as $locale) {
            $placeholders = $this->placeholders(require "{$folder}/{$locale}.php");

            foreach ($english as $key => $names) {
                $this->assertSame($names, $placeholders[$key] ?? [], "{$locale}: {$key}");
            }
        }
    }

    /**
     * @param  array<string,mixed>  $content
     * @return array<int,string>
     */
    private function keys(array $content): array
    {
        unset($content['legal']);

        return array_keys(Arr::dot($content));
    }

    /**
     * The placeholders each text uses, but those of the legal pages.
     *
     * @param  array<string,mixed>  $content
     * @return array<string,array<int,string>>
     */
    private function placeholders(array $content): array
    {
        unset($content['legal']);
        $found = [];

        foreach (Arr::dot($content) as $key => $text) {
            preg_match_all('/:[a-z_]+/', (string) $text, $matches);
            $names = array_values(array_unique($matches[0]));
            sort($names);
            $found[$key] = $names;
        }

        return $found;
    }
}
