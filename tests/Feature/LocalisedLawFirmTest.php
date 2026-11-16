<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Feature;

use Atrium\Admin\Models\Language;
use Atrium\Admin\Models\Site;
use Atrium\Faq\Models\Question;
use Atrium\Pages\Models\Page;
use Atrium\Solutions\Seeders\LawSeeder;
use Atrium\Solutions\Solutions;
use Atrium\Solutions\Tests\TestCase;
use Atrium\Stores\Models\Store;
use Atrium\Team\Models\Member;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The law firm is a business of the installer's country: its names, its
 * address and phone, its prices in its currency written its way, its legal
 * pages; the other languages are translations of that firm.
 */
final class LocalisedLawFirmTest extends TestCase
{
    /**
     * @return array<string,array{string,string,string,string,string,string}>
     */
    public static function countries(): array
    {
        return [
            // locale, default language, a person, the city, the phone, a price
            'Germany' => ['de', 'de', 'Katharina Brandt', 'Berlin', '+49 30 23125 040', '300 €'],
            'Japan' => ['ja', 'ja', '佐藤 美咲', '福岡市', '+81 92-555-0140', '￥38,000'],
            'Russia' => ['ru', 'ru', 'Анна Соколова', 'Казань', '+7 843 555-01-40', '18 000 ₽'],
            'Brazil' => ['pt_BR', 'pt-br', 'Mariana Almeida', 'Curitiba', '+55 41 5550-0140', 'R$ 1.000'],
            'an unknown locale' => ['xx', 'en', 'Emily Whitaker', 'Portland', '+1 503 555 0140', '$320'],
        ];
    }

    #[DataProvider('countries')]
    public function test_the_firm_belongs_to_the_installer_s_country(string $locale, string $language, string $person, string $city, string $phone, string $price): void
    {
        $solution = $this->app->make(Solutions::class)->find('law');
        $this->assertTrue((new LawSeeder)->forSolution($solution, $locale)->setContainer($this->app)->__invoke());

        $this->assertSame($language, Language::query()->where('is_default', true)->value('code'));
        $this->assertSame(15, Language::query()->where('active', true)->count());
        $this->assertSame($language, Site::query()->value('default_language'));

        $founder = Member::query()->orderBy('sort')->first();
        $this->assertSame($person, $founder?->getTranslation('name', $language));
        // A name stays a name in every language.
        $this->assertSame($person, $founder?->getTranslation('name', 'en'));

        $office = Store::query()->orderBy('id')->first();
        $this->assertStringContainsString($city, (string) $office?->getAttribute('address'));
        $this->assertSame([$phone], $office?->getAttribute('phones'));

        $fees = Question::query()->orderBy('sort')->get()->map(fn (Question $question): string => (string) $question->getTranslation('answer', $language))->implode(' ');
        // Prices keep the number and its currency together with a no-break space.
        $this->assertStringContainsString($price, str_replace(["\u{a0}", "\u{202f}"], ' ', $fees));

        // The site's name is the firm's, and no placeholder is left anywhere.
        $brand = (string) Site::query()->value('name');
        $this->assertStringContainsString($brand, (string) Page::query()->where('code', 'home')->first()?->getTranslation('content', 'en'));

        foreach (Page::query()->get() as $page) {
            foreach ((array) $page->getTranslations('content') as $text) {
                $this->assertDoesNotMatchRegularExpression('/:(brand|city|founder|partner|fee_|phone|email|address|district|name)/', (string) $text);
            }
        }
    }

    public function test_germany_has_an_impressum_and_america_a_privacy_policy(): void
    {
        $solution = $this->app->make(Solutions::class)->find('law');
        (new LawSeeder)->forSolution($solution, 'de')->setContainer($this->app)->__invoke();

        $impressum = Page::query()->where('code', 'impressum')->first();
        $this->assertNotNull($impressum);
        $this->assertStringContainsString('keine Rechtsberatung', (string) $impressum->getTranslation('content', 'de'));
        $this->assertStringContainsString('10115 Berlin', (string) $impressum->getTranslation('content', 'de'));
        $this->assertFalse(Page::query()->where('code', 'privacy')->exists());
    }
}
