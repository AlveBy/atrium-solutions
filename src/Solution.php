<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions;

use InvalidArgumentException;

/**
 * One ready-made site, as its solution.json and content/{locale}.php
 * describe it:
 *
 *     {
 *         "name": "law",
 *         "brand": "Whitaker Brennan LLP",
 *         "skin": "law",
 *         "modules": ["alveby/atrium-pages", "alveby/atrium-team"],
 *         "seeder": "Atrium\\Solutions\\Seeders\\LawSeeder",
 *         "palette": {"background": "#f7f4ee", "text": "#1d232b", "accent": "#7a2e2e"},
 *         "fonts": ["Fraunces", "Inter"],
 *         "screenshots": {"desktop": "screenshot.png", "mobile": "screenshot-mobile.png"},
 *         "pages": ["/", "/team", "/practice"],
 *         "urls": {"Atrium\\Stores\\Models\\Store": "offices"},
 *         "menu": [{"page": "practice"}, {"module": "Atrium\\Team\\Models\\Member"}]
 *     }
 *
 * The label, the description, the languages and `labels` (what the modules
 * are called, in the admin and on the site) come from the content files;
 * the same keys in solution.json are the fallback of a solution without
 * them. `pages` are the addresses worth opening: the smoke test opens them
 * and the catalogue lists them. `urls` move a module's pages to another
 * prefix (/offices rather than /stores). `menu` is the site's menu: pages of
 * the Pages module by code and module lists, in this order.
 */
final class Solution
{
    /**
     * @param  array<string,string>  $label  language => text
     * @param  array<string,string>  $description  language => text
     * @param  array<int,string>  $modules  package names
     * @param  array<int,string>  $languages  content languages, the default first
     * @param  array<string,array<string,string>>  $labels  language => [translation key => text]
     * @param  array<string,string>  $palette
     * @param  array<int,string>  $fonts
     * @param  array<string,string>  $screenshots  kind => file in the solution's folder
     * @param  array<int,string>  $pages  addresses on the site
     * @param  array<int,string>  $tags
     * @param  array<class-string,string>  $urls  model => prefix
     * @param  array<int,array<string,string>>  $menu  {"page": code} or {"module": model}
     * @param  array<string,string>  $brands  locale => the business set in that country
     */
    public function __construct(
        public readonly string $name,
        public readonly string $path,
        public readonly array $label,
        public readonly array $description = [],
        public readonly string $brand = '',
        public readonly ?string $skin = null,
        public readonly array $modules = [],
        public readonly array $languages = ['en'],
        public readonly ?string $seeder = null,
        public readonly array $labels = [],
        public readonly array $palette = [],
        public readonly array $fonts = [],
        public readonly array $screenshots = [],
        public readonly array $pages = ['/'],
        public readonly array $tags = [],
        public readonly array $urls = [],
        public readonly array $menu = [],
        public readonly array $brands = [],
    ) {}

    public static function fromFile(string $file): self
    {
        $data = json_decode((string) file_get_contents($file), true);

        if (! is_array($data) || ! is_string($data['name'] ?? null) || ! preg_match('/^[a-z][a-z0-9-]*$/', $data['name'])) {
            throw new InvalidArgumentException("{$file} is not a solution: it needs a \"name\" of lowercase letters, digits and dashes.");
        }

        $strings = static fn (mixed $value): array => array_map('strval', array_filter((array) $value, 'is_scalar'));

        // A solution with content/{locale}.php files speaks the languages it
        // has files for, and names its modules in their `labels`.
        $languages = [];
        $labels = array_filter((array) ($data['labels'] ?? []), 'is_array');
        $label = (array) ($data['label'] ?? [$data['name']]);
        $description = (array) ($data['description'] ?? []);
        $brands = [];

        foreach (Support\Country::LOCALES as $locale) {
            $texts = dirname($file).'/content/'.$locale.'.php';

            if (is_file($texts)) {
                $languages[] = $locale;
                $content = require $texts;
                $labels[$locale] = [...(array) ($content['labels'] ?? []), ...($labels[$locale] ?? [])];
                $label[$locale] = $content['catalogue']['label'] ?? $label[$locale] ?? null;
                $description[$locale] = $content['catalogue']['description'] ?? $description[$locale] ?? null;
                $brands[$locale] = $content['brand'] ?? null;
            }
        }

        $languages = $languages ?: ($data['languages'] ?? ['en']);

        return new self(
            name: $data['name'],
            path: dirname($file),
            label: $strings($label),
            description: $strings($description),
            brand: (string) ($data['brand'] ?? ''),
            skin: isset($data['skin']) ? (string) $data['skin'] : null,
            modules: array_values(array_map('strtolower', $strings($data['modules'] ?? []))),
            languages: array_values($strings($languages)) ?: ['en'],
            seeder: isset($data['seeder']) ? (string) $data['seeder'] : null,
            labels: array_map($strings, $labels),
            palette: $strings($data['palette'] ?? []),
            fonts: array_values($strings($data['fonts'] ?? [])),
            screenshots: $strings($data['screenshots'] ?? []),
            pages: array_values($strings($data['pages'] ?? ['/'])) ?: ['/'],
            tags: array_values($strings($data['tags'] ?? [])),
            urls: $strings($data['urls'] ?? []),
            menu: array_values(array_map($strings, array_filter((array) ($data['menu'] ?? []), 'is_array'))),
            brands: $strings($brands),
        );
    }

    /**
     * The business's name when the site is set in the country of a locale:
     * a German law firm for de.
     */
    public function brandIn(?string $locale): string
    {
        return $this->brands[Support\Country::normalise($locale)] ?? $this->brands['en'] ?? $this->brand;
    }

    /**
     * A text of the solution in a language, English when it has none there.
     *
     * @param  array<string,string>  $texts
     */
    public static function text(array $texts, ?string $language = null): string
    {
        $language ??= app()->getLocale();

        return $texts[$language] ?? $texts['en'] ?? (string) reset($texts);
    }

    public function file(string $name): string
    {
        return $this->path.'/'.ltrim($name, '/');
    }

    /**
     * What the catalogue of solutions shows: the texts in every language,
     * the modules, the look and the screenshots (paths in the solution's
     * folder).
     *
     * @return array<string,mixed>
     */
    public function toCatalogue(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'description' => $this->description,
            'brand' => $this->brand,
            'skin' => $this->skin,
            'modules' => $this->modules,
            'languages' => $this->languages,
            'palette' => $this->palette,
            'fonts' => $this->fonts,
            'screenshots' => $this->screenshots,
            'pages' => $this->pages,
            'tags' => $this->tags,
            'install' => 'php artisan atrium:solution:install '.$this->name,
        ];
    }
}
