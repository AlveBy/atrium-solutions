<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Support;

use NumberFormatter;

/**
 * The country a demo site is set in, picked by the installer's locale: a
 * German installer gets a business in Germany, with German names, a Berlin
 * address, a +49 phone and prices in euros written the German way.
 *
 * Everything here is made up but believable: real cities, invented streets
 * and numbers, phone numbers from ranges kept for fiction where the country
 * has one. The admin's DemoLocale will hold these facts for every module;
 * until then they live here, in one place.
 */
final class Country
{
    /** The fifteen locales of Atrium; any other falls back to English. */
    public const LOCALES = ['en', 'ru', 'uk', 'be', 'kk', 'uz', 'de', 'fr', 'es', 'pt_BR', 'it', 'pl', 'tr', 'zh_CN', 'ja'];

    /**
     * @param  array<int,array{street:string,number:string,postcode:string,district:string,lat:float,lng:float}>  $addresses
     * @param  array<int,string>  $women  first names
     * @param  array<int,string>  $men  first names
     * @param  array<int,array{0:string,1:string}>  $surnames  [for a man, for a woman]
     */
    private function __construct(
        public readonly string $locale,
        public readonly string $country,
        public readonly string $currency,
        public readonly string $intl,
        public readonly string $city,
        public readonly string $region,
        public readonly string $addressFormat,
        public readonly array $addresses,
        public readonly string $phoneFormat,
        public readonly string $domain,
        public readonly float $priceLevel,
        public readonly array $women,
        public readonly array $men,
        public readonly array $surnames,
        public readonly bool $familyNameFirst = false,
        public readonly string $nameSeparator = ' ',
    ) {}

    /**
     * The country of a locale, from its file in data/countries.
     */
    public static function for(?string $locale): self
    {
        static $countries = [];

        $locale = self::normalise($locale);

        return $countries[$locale] ??= new self(...require dirname(__DIR__, 2).'/data/countries/'.$locale.'.php');
    }

    /**
     * pt-br, pt_BR, PT-BR are one locale; an unknown one is English.
     */
    public static function normalise(?string $locale): string
    {
        $locale = str_replace('-', '_', trim((string) $locale));

        foreach (self::LOCALES as $known) {
            if (strcasecmp($known, $locale) === 0) {
                return $known;
            }
        }

        return 'en';
    }

    /**
     * The code of the locale as a content language of the site: pt-br.
     */
    public function language(): string
    {
        return self::languageOf($this->locale);
    }

    public static function languageOf(string $locale): string
    {
        return strtolower(str_replace('_', '-', $locale));
    }

    /**
     * A price of the site: an amount in US dollars at US prices, turned
     * into the country's currency at its price level, rounded the way a
     * price list would be, and written the country's way.
     */
    public function money(float $usd): string
    {
        $amount = $usd * $this->priceLevel;
        $step = 10 ** max(0, strlen((string) (int) $amount) - 2);
        $amount = round($amount / $step) * $step;

        $format = new NumberFormatter($this->intl, NumberFormatter::CURRENCY);
        $format->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

        return (string) $format->formatCurrency($amount, $this->currency);
    }

    /**
     * A phone number of the site's business, from a range kept for fiction
     * where there is one.
     */
    public function phone(int $line): string
    {
        return str_replace('{n}', str_pad((string) ($line % 100), 2, '0', STR_PAD_LEFT), $this->phoneFormat);
    }

    public function email(string $user, string $company): string
    {
        return $user.'@'.$company.'.'.$this->domain;
    }

    /**
     * The postal address of the business's office number $index.
     */
    public function address(int $index): string
    {
        $address = $this->addresses[$index % count($this->addresses)];

        return strtr($this->addressFormat, [
            '{street}' => $address['street'],
            '{number}' => $address['number'],
            '{postcode}' => $address['postcode'],
            '{district}' => $address['district'],
            '{city}' => $this->city,
            '{region}' => $this->region,
        ]);
    }

    /**
     * @return array{lat:float,lng:float}
     */
    public function location(int $index): array
    {
        $address = $this->addresses[$index % count($this->addresses)];

        return ['lat' => $address['lat'], 'lng' => $address['lng']];
    }

    public function district(int $index): string
    {
        return $this->addresses[$index % count($this->addresses)]['district'];
    }

    /**
     * A person's full name: the $index-th woman or man of the country, in
     * the order the country writes names.
     */
    public function person(int $index, bool $woman): string
    {
        $first = $this->firstName($index, $woman);
        $last = $this->surnames[$index % count($this->surnames)][$woman ? 1 : 0];

        return $this->familyNameFirst ? $last.$this->nameSeparator.$first : $first.$this->nameSeparator.$last;
    }

    /**
     * The first name of that person, as a text addresses them.
     */
    public function firstName(int $index, bool $woman): string
    {
        $names = $woman ? $this->women : $this->men;

        return $names[$index % count($names)];
    }
}
