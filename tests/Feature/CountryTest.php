<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Feature;

use Atrium\Solutions\Support\Country;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The made-up facts of a country: names, addresses, phones, prices.
 */
final class CountryTest extends TestCase
{
    /**
     * @return array<string,array{?string,string}>
     */
    public static function locales(): array
    {
        return [
            'known' => ['de', 'de'],
            'with an underscore' => ['pt_BR', 'pt_BR'],
            'with a dash' => ['pt-br', 'pt_BR'],
            'in capitals' => ['PT-BR', 'pt_BR'],
            'Chinese' => ['zh-cn', 'zh_CN'],
            'with spaces round it' => [' de ', 'de'],
            'unknown' => ['xx', 'en'],
            'not a locale' => ['de-', 'en'],
            'empty' => ['', 'en'],
            'none' => [null, 'en'],
        ];
    }

    #[DataProvider('locales')]
    public function test_a_locale_is_found_however_it_is_written(?string $given, string $expected): void
    {
        $this->assertSame($expected, Country::normalise($given));
        $this->assertSame($expected, Country::for($given)->locale);
    }

    /**
     * @return array<string,array{string}>
     */
    public static function everyLocale(): array
    {
        return array_combine(Country::LOCALES, array_map(static fn (string $locale): array => [$locale], Country::LOCALES));
    }

    #[DataProvider('everyLocale')]
    public function test_every_locale_has_a_country_that_is_complete(string $locale): void
    {
        $country = Country::for($locale);

        $this->assertSame($locale, $country->locale);
        $this->assertSame($country, Country::for($locale), 'Loaded once.');
        $this->assertCount(2, $country->addresses);
        $this->assertCount(6, $country->women);
        $this->assertCount(6, $country->men);
        $this->assertCount(8, $country->surnames);
        $this->assertMatchesRegularExpression('/^\+\d/', $country->phone(40));
        $this->assertMatchesRegularExpression('/^office@x\.[a-z.]+$/', $country->email('office', 'x'));
        $this->assertMatchesRegularExpression('/^[A-Z]{3}$/', $country->currency);
        $this->assertNotSame('', $country->city);
        $this->assertNotSame($country->person(0, true), $country->person(0, false));

        foreach ($country->addresses as $address) {
            $this->assertNotSame('', $address['street']);
            $this->assertNotSame('', $address['district']);
            $this->assertEqualsWithDelta($country->location(0)['lat'], $country->addresses[0]['lat'], 0.0);
        }

        foreach ($country->surnames as $pair) {
            $this->assertCount(2, $pair);
        }
    }

    public function test_the_language_of_a_locale_is_the_code_of_a_content_language(): void
    {
        $this->assertSame('pt-br', Country::for('pt_BR')->language());
        $this->assertSame('de', Country::for('de')->language());
        $this->assertSame('zh-cn', Country::languageOf('zh_CN'));
        $this->assertSame('en', Country::languageOf('EN'));
        $this->assertSame('pt-br', Country::languageOf('PT_BR'));
    }

    /**
     * @return array<string,array{int,string}>
     */
    public static function lines(): array
    {
        return [
            'the business line' => [40, '+1 503 555 0140'],
            'a single digit is padded' => [5, '+1 503 555 0105'],
            'zero' => [0, '+1 503 555 0100'],
            'ninety nine' => [99, '+1 503 555 0199'],
            'a hundred wraps' => [100, '+1 503 555 0100'],
            'past a hundred' => [145, '+1 503 555 0145'],
        ];
    }

    #[DataProvider('lines')]
    public function test_a_phone_number_is_the_countrys_format_with_the_line_in_it(int $line, string $expected): void
    {
        $this->assertSame($expected, Country::for('en')->phone($line));
    }

    public function test_the_other_countries_write_phones_their_way(): void
    {
        $this->assertSame('+49 30 23125 040', Country::for('de')->phone(40));
        $this->assertSame('+7 843 555-01-40', Country::for('ru')->phone(40));
        $this->assertSame('+81 92-555-0140', Country::for('ja')->phone(40));
    }

    public function test_an_email_is_a_user_at_a_company_in_the_country_s_domain(): void
    {
        $this->assertSame('anna@acme.com', Country::for('en')->email('anna', 'acme'));
        $this->assertSame('anna@acme.de', Country::for('de')->email('anna', 'acme'));
        $this->assertSame('anna@acme.com.br', Country::for('pt_BR')->email('anna', 'acme'));
    }

    public function test_an_address_is_the_country_s_format_with_the_office_in_it(): void
    {
        $en = Country::for('en');

        $this->assertSame("1120 NW Lovejoy Street\nPortland, OR 97209", $en->address(0));
        $this->assertSame("415 SE Water Avenue\nPortland, OR 97214", $en->address(1));
        $this->assertSame($en->address(0), $en->address(2), 'The third office is the first again.');
        $this->assertSame($en->address(1), $en->address(3));
        $this->assertSame("〒810-0001\n福岡県福岡市中央区天神2-8-14", Country::for('ja')->address(0));
        $this->assertSame("〒812-0011\n福岡県福岡市博多区博多駅前3-21-5", Country::for('ja')->address(1));
        $this->assertSame("ул. Профсоюзная, д. 17\nКазань, 420111", Country::for('ru')->address(0));
    }

    public function test_an_office_has_a_place_on_the_map_and_a_district(): void
    {
        $en = Country::for('en');

        $this->assertSame(['lat' => 45.5299, 'lng' => -122.683], $en->location(0));
        $this->assertSame(['lat' => 45.5185, 'lng' => -122.665], $en->location(1));
        $this->assertSame($en->location(0), $en->location(2));
        $this->assertSame('Pearl District', $en->district(0));
        $this->assertSame('Central Eastside', $en->district(1));
        $this->assertSame('Pearl District', $en->district(2));
    }

    /**
     * @return array<string,array{string,int,bool,string,string}>
     */
    public static function people(): array
    {
        return [
            'a woman' => ['en', 0, true, 'Emily Whitaker', 'Emily'],
            'a man' => ['en', 0, false, 'Daniel Whitaker', 'Daniel'],
            'the second woman' => ['en', 1, true, 'Grace Brennan', 'Grace'],
            'first names wrap after six, surnames after eight' => ['en', 7, false, 'Marcus Mercer', 'Marcus'],
            'a woman past the lists' => ['en', 8, true, 'Hannah Whitaker', 'Hannah'],
            'a man past the lists' => ['en', 11, false, 'Caleb Delgado', 'Caleb'],
            'a woman at the end of the first names' => ['en', 6, true, 'Emily Langley', 'Emily'],
            'a Russian woman has a feminine surname' => ['ru', 0, true, 'Анна Соколова', 'Анна'],
            'a Russian man has a masculine one' => ['ru', 0, false, 'Дмитрий Соколов', 'Дмитрий'],
            'a Russian woman, second' => ['ru', 1, true, 'Мария Вершинина', 'Мария'],
            'a Russian man, past the lists' => ['ru', 7, false, 'Алексей Зимин', 'Алексей'],
            'Japanese: the family name first, a space between' => ['ja', 0, true, '佐藤 美咲', '美咲'],
            'Japanese man' => ['ja', 7, false, '井上 大輔', '大輔'],
            'Chinese: the family name first, nothing between' => ['zh_CN', 0, true, '王丽华', '丽华'],
            'Chinese man' => ['zh_CN', 7, false, '钱浩然', '浩然'],
            'German' => ['de', 1, true, 'Julia Vogler', 'Julia'],
        ];
    }

    #[DataProvider('people')]
    public function test_a_person_is_the_nth_woman_or_man_of_the_country(string $locale, int $index, bool $woman, string $name, string $first): void
    {
        $this->assertSame($name, Country::for($locale)->person($index, $woman));
        $this->assertSame($first, Country::for($locale)->firstName($index, $woman));
    }

    /**
     * @return array<string,array{float,string}>
     */
    public static function prices(): array
    {
        return [
            'a small amount is kept' => [5, '$5'],
            'zero' => [0, '$0'],
            'half a unit up' => [9.5, '$10'],
            'cents are rounded away' => [14.4, '$14'],
            'two digits are kept' => [99, '$99'],
            'three digits are rounded to ten' => [324, '$320'],
            'three digits up' => [326, '$330'],
            'a round price' => [450, '$450'],
            'five digits to a thousand' => [15432, '$15,000'],
            'seven digits to a hundred thousand' => [1234567, '$1,200,000'],
        ];
    }

    #[DataProvider('prices')]
    public function test_a_price_is_rounded_the_way_a_price_list_is_and_written_in_the_currency(float $usd, string $expected): void
    {
        $this->assertSame($expected, Country::for('en')->money($usd));
    }

    /**
     * @return array<string,array{string,float,string,string}>
     */
    public static function localPrices(): array
    {
        return [
            'euro' => ['de', 320, '300', '€'],
            'euro, more' => ['de', 450, '430', '€'],
            'rouble' => ['ru', 320, '18000', '₽'],
            'rouble, more' => ['ru', 450, '25000', '₽'],
            'yen' => ['ja', 320, '38000', '￥'],
            'yuan' => ['zh_CN', 450, '2000', '¥'],
            'real' => ['pt_BR', 320, '1000', 'R$'],
        ];
    }

    #[DataProvider('localPrices')]
    public function test_a_price_is_turned_into_the_country_s_currency_at_its_price_level(string $locale, float $usd, string $digits, string $symbol): void
    {
        $price = Country::for($locale)->money($usd);

        $this->assertSame($digits, preg_replace('/\D/u', '', $price));
        $this->assertStringContainsString($symbol, $price);
    }

    public function test_a_price_of_a_digit_is_rounded_to_a_whole_currency_unit_upwards_from_a_half(): void
    {
        $this->assertSame('$5', Country::for('en')->money(4.5));
        $this->assertSame('$3', Country::for('en')->money(2.5));
        $this->assertSame('$4', Country::for('en')->money(4.4));
    }
}
