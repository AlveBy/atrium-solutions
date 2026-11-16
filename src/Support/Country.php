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

    public static function for(?string $locale): self
    {
        $locale = self::normalise($locale);

        return self::all()[$locale] ?? self::all()['en'];
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
        $step = 10 ** max(0, (int) floor(log10(max($amount, 1))) - 1);
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
        $first = ($woman ? $this->women : $this->men)[$index % count($woman ? $this->women : $this->men)];
        $last = $this->surnames[$index % count($this->surnames)][$woman ? 1 : 0];

        return $this->familyNameFirst ? $last.$this->nameSeparator.$first : $first.$this->nameSeparator.$last;
    }

    /**
     * The first name of that person, as a text addresses them.
     */
    public function firstName(int $index, bool $woman): string
    {
        return ($woman ? $this->women : $this->men)[$index % count($woman ? $this->women : $this->men)];
    }

    /**
     * @return array<string,self>
     */
    private static function all(): array
    {
        static $all = null;

        return $all ??= [
            'en' => new self('en', 'US', 'USD', 'en_US', 'Portland', 'OR', "{number} {street}\n{city}, {region} {postcode}", [
                ['street' => 'NW Lovejoy Street', 'number' => '1120', 'postcode' => '97209', 'district' => 'Pearl District', 'lat' => 45.5299, 'lng' => -122.6830],
                ['street' => 'SE Water Avenue', 'number' => '415', 'postcode' => '97214', 'district' => 'Central Eastside', 'lat' => 45.5185, 'lng' => -122.6650],
            ], '+1 503 555 01{n}', 'com', 1.0,
                ['Emily', 'Grace', 'Hannah', 'Olivia', 'Megan', 'Rachel'],
                ['Daniel', 'Marcus', 'Ethan', 'Ryan', 'Nathan', 'Caleb'],
                [['Whitaker', 'Whitaker'], ['Brennan', 'Brennan'], ['Calloway', 'Calloway'], ['Delgado', 'Delgado'], ['Foster', 'Foster'], ['Hollis', 'Hollis'], ['Langley', 'Langley'], ['Mercer', 'Mercer']]),

            'ru' => new self('ru', 'RU', 'RUB', 'ru_RU', 'Казань', 'Республика Татарстан', "{street}, {number}\n{city}, {postcode}", [
                ['street' => 'ул. Профсоюзная', 'number' => 'д. 17', 'postcode' => '420111', 'district' => 'Вахитовский район', 'lat' => 55.7907, 'lng' => 49.1137],
                ['street' => 'ул. Чистопольская', 'number' => 'д. 64', 'postcode' => '420066', 'district' => 'Ново-Савиновский район', 'lat' => 55.8187, 'lng' => 49.1305],
            ], '+7 843 555-01-{n}', 'ru', 55.0,
                ['Анна', 'Мария', 'Елена', 'Ольга', 'Наталья', 'Ксения'],
                ['Дмитрий', 'Алексей', 'Сергей', 'Михаил', 'Андрей', 'Илья'],
                [['Соколов', 'Соколова'], ['Вершинин', 'Вершинина'], ['Гордеев', 'Гордеева'], ['Лаптев', 'Лаптева'], ['Миронов', 'Миронова'], ['Ершов', 'Ершова'], ['Белов', 'Белова'], ['Зимин', 'Зимина']]),

            'uk' => new self('uk', 'UA', 'UAH', 'uk_UA', 'Львів', 'Львівська область', "вул. {street}, {number}\n{city}, {postcode}", [
                ['street' => 'Городоцька', 'number' => '48', 'postcode' => '79007', 'district' => 'Галицький район', 'lat' => 49.8387, 'lng' => 24.0124],
                ['street' => 'Стрийська', 'number' => '112', 'postcode' => '79026', 'district' => 'Франківський район', 'lat' => 49.8110, 'lng' => 24.0330],
            ], '+380 32 555 01 {n}', 'ua', 25.0,
                ['Олена', 'Ірина', 'Оксана', 'Наталія', 'Марія', 'Христина'],
                ['Андрій', 'Тарас', 'Олег', 'Богдан', 'Максим', 'Василь'],
                [['Коваленко', 'Коваленко'], ['Мельничук', 'Мельничук'], ['Гнатюк', 'Гнатюк'], ['Савчук', 'Савчук'], ['Литвин', 'Литвин'], ['Бойко', 'Бойко'], ['Ткачук', 'Ткачук'], ['Дмитрук', 'Дмитрук']]),

            'be' => new self('be', 'BY', 'BYN', 'be_BY', 'Гродна', 'Гродзенская вобласць', "вул. {street}, {number}\n{postcode}, г. {city}", [
                ['street' => 'Савецкая', 'number' => '21', 'postcode' => '230025', 'district' => 'Цэнтр', 'lat' => 53.6789, 'lng' => 23.8296],
                ['street' => 'Горкага', 'number' => '79', 'postcode' => '230009', 'district' => 'Ленінскі раён', 'lat' => 53.6895, 'lng' => 23.8550],
            ], '+375 152 55-01-{n}', 'by', 2.4,
                ['Алена', 'Вольга', 'Ганна', 'Наталля', 'Марына', 'Святлана'],
                ['Андрэй', 'Алесь', 'Дзмітрый', 'Павел', 'Сяргей', 'Уладзімір'],
                [['Каваленка', 'Каваленка'], ['Шыманскі', 'Шыманская'], ['Лукашэвіч', 'Лукашэвіч'], ['Бародзіч', 'Бародзіч'], ['Жукоўскі', 'Жукоўская'], ['Мацкевіч', 'Мацкевіч'], ['Сямашка', 'Сямашка'], ['Галубовіч', 'Галубовіч']]),

            'kk' => new self('kk', 'KZ', 'KZT', 'kk_KZ', 'Алматы', '', "{city}, {street}, {number}\n{postcode}", [
                ['street' => 'Абай даңғылы', 'number' => '52', 'postcode' => 'A15E3X1', 'district' => 'Алмалы ауданы', 'lat' => 43.2402, 'lng' => 76.9122],
                ['street' => 'Достық даңғылы', 'number' => '180', 'postcode' => 'A05H8T4', 'district' => 'Медеу ауданы', 'lat' => 43.2240, 'lng' => 76.9570],
            ], '+7 727 355 01 {n}', 'kz', 250.0,
                ['Айгерім', 'Әсем', 'Динара', 'Жанар', 'Мадина', 'Гүлнар'],
                ['Ерлан', 'Нұрлан', 'Асқар', 'Дәурен', 'Ержан', 'Арман'],
                [['Сейітов', 'Сейітова'], ['Жұмабеков', 'Жұмабекова'], ['Омаров', 'Омарова'], ['Тұрсынов', 'Тұрсынова'], ['Қасымов', 'Қасымова'], ['Әбенов', 'Әбенова'], ['Байжанов', 'Байжанова'], ['Мұқанов', 'Мұқанова']]),

            'uz' => new self('uz', 'UZ', 'UZS', 'uz_Latn_UZ', 'Toshkent', '', "{street}, {number}\n{postcode}, {city}", [
                ['street' => 'Amir Temur shoh ko‘chasi', 'number' => '24-uy', 'postcode' => '100000', 'district' => 'Yunusobod tumani', 'lat' => 41.3260, 'lng' => 69.2810],
                ['street' => 'Shota Rustaveli ko‘chasi', 'number' => '53-uy', 'postcode' => '100070', 'district' => 'Yakkasaroy tumani', 'lat' => 41.2930, 'lng' => 69.2560],
            ], '+998 71 555 01 {n}', 'uz', 6000.0,
                ['Dilnoza', 'Nilufar', 'Gulnora', 'Madina', 'Sevara', 'Zarina'],
                ['Jasur', 'Bekzod', 'Sardor', 'Otabek', 'Shoxrux', 'Akmal'],
                [['Karimov', 'Karimova'], ['Yusupov', 'Yusupova'], ['Rahimov', 'Rahimova'], ['Nazarov', 'Nazarova'], ['Toshmatov', 'Toshmatova'], ['Ergashev', 'Ergasheva'], ['Xolmatov', 'Xolmatova'], ['Saidov', 'Saidova']]),

            'de' => new self('de', 'DE', 'EUR', 'de_DE', 'Berlin', 'Berlin', "{street} {number}\n{postcode} {city}", [
                ['street' => 'Linienstraße', 'number' => '141', 'postcode' => '10115', 'district' => 'Mitte', 'lat' => 52.5280, 'lng' => 13.3980],
                ['street' => 'Oranienstraße', 'number' => '185', 'postcode' => '10999', 'district' => 'Kreuzberg', 'lat' => 52.5010, 'lng' => 13.4180],
            ], '+49 30 23125 0{n}', 'de', 0.95,
                ['Katharina', 'Julia', 'Sabine', 'Lena', 'Miriam', 'Anke'],
                ['Thomas', 'Jan', 'Matthias', 'Florian', 'Stefan', 'Lukas'],
                [['Brandt', 'Brandt'], ['Vogler', 'Vogler'], ['Engel', 'Engel'], ['Kessler', 'Kessler'], ['Lindner', 'Lindner'], ['Albrecht', 'Albrecht'], ['Sommer', 'Sommer'], ['Hagedorn', 'Hagedorn']]),

            'fr' => new self('fr', 'FR', 'EUR', 'fr_FR', 'Lyon', 'Auvergne-Rhône-Alpes', "{number}, {street}\n{postcode} {city}", [
                ['street' => 'rue de la République', 'number' => '38', 'postcode' => '69002', 'district' => 'Presqu’île', 'lat' => 45.7614, 'lng' => 4.8357],
                ['street' => 'cours Lafayette', 'number' => '112', 'postcode' => '69003', 'district' => 'Part-Dieu', 'lat' => 45.7636, 'lng' => 4.8570],
            ], '+33 4 99 00 01 {n}', 'fr', 0.95,
                ['Camille', 'Claire', 'Sophie', 'Élodie', 'Juliette', 'Margaux'],
                ['Julien', 'Nicolas', 'Antoine', 'Mathieu', 'Thomas', 'Hugo'],
                [['Moreau', 'Moreau'], ['Garnier', 'Garnier'], ['Lefèvre', 'Lefèvre'], ['Rousseau', 'Rousseau'], ['Fontaine', 'Fontaine'], ['Chevalier', 'Chevalier'], ['Marchand', 'Marchand'], ['Perrin', 'Perrin']]),

            'es' => new self('es', 'ES', 'EUR', 'es_ES', 'Valencia', 'Comunitat Valenciana', "{street}, {number}\n{postcode} {city}", [
                ['street' => 'Calle de Colón', 'number' => '42', 'postcode' => '46004', 'district' => 'Ciutat Vella', 'lat' => 39.4697, 'lng' => -0.3712],
                ['street' => 'Avenida de Aragón', 'number' => '15', 'postcode' => '46021', 'district' => 'El Pla del Real', 'lat' => 39.4723, 'lng' => -0.3570],
            ], '+34 960 55 01 {n}', 'es', 0.75,
                ['Lucía', 'Marta', 'Carmen', 'Elena', 'Paula', 'Irene'],
                ['Javier', 'Pablo', 'Álvaro', 'Sergio', 'Diego', 'Andrés'],
                [['Navarro Ruiz', 'Navarro Ruiz'], ['Ortega Gil', 'Ortega Gil'], ['Serrano Vidal', 'Serrano Vidal'], ['Morales Peña', 'Morales Peña'], ['Castillo Rey', 'Castillo Rey'], ['Romero Sanz', 'Romero Sanz'], ['Iglesias Mora', 'Iglesias Mora'], ['Herrero Lozano', 'Herrero Lozano']]),

            'pt_BR' => new self('pt_BR', 'BR', 'BRL', 'pt_BR', 'Curitiba', 'PR', "{street}, {number} – {district}\n{city} – {region}, {postcode}", [
                ['street' => 'Rua XV de Novembro', 'number' => '1250', 'postcode' => '80060-000', 'district' => 'Centro', 'lat' => -25.4296, 'lng' => -49.2713],
                ['street' => 'Avenida Sete de Setembro', 'number' => '4380', 'postcode' => '80250-210', 'district' => 'Batel', 'lat' => -25.4434, 'lng' => -49.2865],
            ], '+55 41 5550-01{n}', 'com.br', 3.2,
                ['Mariana', 'Fernanda', 'Juliana', 'Camila', 'Beatriz', 'Larissa'],
                ['Rafael', 'Lucas', 'Gustavo', 'Thiago', 'Felipe', 'Bruno'],
                [['Almeida', 'Almeida'], ['Carvalho', 'Carvalho'], ['Ribeiro', 'Ribeiro'], ['Monteiro', 'Monteiro'], ['Teixeira', 'Teixeira'], ['Barbosa', 'Barbosa'], ['Moura', 'Moura'], ['Pacheco', 'Pacheco']]),

            'it' => new self('it', 'IT', 'EUR', 'it_IT', 'Bologna', 'BO', "{street}, {number}\n{postcode} {city} {region}", [
                ['street' => 'Via dell’Indipendenza', 'number' => '56', 'postcode' => '40121', 'district' => 'Centro storico', 'lat' => 44.4990, 'lng' => 11.3434],
                ['street' => 'Via Saragozza', 'number' => '118', 'postcode' => '40135', 'district' => 'Saragozza', 'lat' => 44.4900, 'lng' => 11.3220],
            ], '+39 051 555 01{n}', 'it', 0.9,
                ['Giulia', 'Francesca', 'Chiara', 'Elena', 'Silvia', 'Valentina'],
                ['Marco', 'Luca', 'Andrea', 'Matteo', 'Davide', 'Stefano'],
                [['Bianchi', 'Bianchi'], ['Ferraresi', 'Ferraresi'], ['Galli', 'Galli'], ['Conti', 'Conti'], ['Marchetti', 'Marchetti'], ['Rinaldi', 'Rinaldi'], ['Benassi', 'Benassi'], ['Fontana', 'Fontana']]),

            'pl' => new self('pl', 'PL', 'PLN', 'pl_PL', 'Wrocław', '', "ul. {street} {number}\n{postcode} {city}", [
                ['street' => 'Świdnicka', 'number' => '24', 'postcode' => '50-068', 'district' => 'Stare Miasto', 'lat' => 51.1060, 'lng' => 17.0310],
                ['street' => 'Legnicka', 'number' => '65', 'postcode' => '54-206', 'district' => 'Fabryczna', 'lat' => 51.1180, 'lng' => 16.9900],
            ], '+48 71 555 01 {n}', 'pl', 3.0,
                ['Anna', 'Katarzyna', 'Magdalena', 'Agnieszka', 'Joanna', 'Monika'],
                ['Piotr', 'Tomasz', 'Michał', 'Paweł', 'Krzysztof', 'Marcin'],
                [['Nowakowski', 'Nowakowska'], ['Wiśniewski', 'Wiśniewska'], ['Kaczmarek', 'Kaczmarek'], ['Zieliński', 'Zielińska'], ['Pawlak', 'Pawlak'], ['Sikora', 'Sikora'], ['Głowacki', 'Głowacka'], ['Wróblewski', 'Wróblewska']]),

            'tr' => new self('tr', 'TR', 'TRY', 'tr_TR', 'İzmir', '', "{street} No: {number}\n{postcode} {district}/{city}", [
                ['street' => 'Cumhuriyet Bulvarı', 'number' => '142', 'postcode' => '35210', 'district' => 'Konak', 'lat' => 38.4300, 'lng' => 27.1390],
                ['street' => 'Kıbrıs Şehitleri Caddesi', 'number' => '58', 'postcode' => '35220', 'district' => 'Alsancak', 'lat' => 38.4370, 'lng' => 27.1440],
            ], '+90 232 555 01 {n}', 'com.tr', 22.0,
                ['Elif', 'Zeynep', 'Ayşe', 'Merve', 'Selin', 'Deniz'],
                ['Emre', 'Burak', 'Can', 'Mehmet', 'Kerem', 'Onur'],
                [['Yıldırım', 'Yıldırım'], ['Aydın', 'Aydın'], ['Kaya', 'Kaya'], ['Özdemir', 'Özdemir'], ['Arslan', 'Arslan'], ['Doğan', 'Doğan'], ['Korkmaz', 'Korkmaz'], ['Çelik', 'Çelik']]),

            'zh_CN' => new self('zh_CN', 'CN', 'CNY', 'zh_CN', '杭州', '浙江省', "{region}{city}{district}{street}{number}\n邮编 {postcode}", [
                ['street' => '延安路', 'number' => '398号', 'postcode' => '310006', 'district' => '上城区', 'lat' => 30.2590, 'lng' => 120.1650],
                ['street' => '文三路', 'number' => '217号', 'postcode' => '310012', 'district' => '西湖区', 'lat' => 30.2790, 'lng' => 120.1350],
            ], '+86 571 5550 01{n}', 'cn', 4.5,
                ['丽华', '静怡', '婷', '雪梅', '晓燕', '思雨'],
                ['伟', '浩然', '建国', '志强', '明轩', '子涵'],
                [['王', '王'], ['陈', '陈'], ['林', '林'], ['周', '周'], ['沈', '沈'], ['许', '许'], ['何', '何'], ['钱', '钱']], true, ''),

            'ja' => new self('ja', 'JP', 'JPY', 'ja_JP', '福岡市', '福岡県', "〒{postcode}\n{region}{city}{district}{street}{number}", [
                ['street' => '天神', 'number' => '2-8-14', 'postcode' => '810-0001', 'district' => '中央区', 'lat' => 33.5902, 'lng' => 130.3990],
                ['street' => '博多駅前', 'number' => '3-21-5', 'postcode' => '812-0011', 'district' => '博多区', 'lat' => 33.5890, 'lng' => 130.4180],
            ], '+81 92-555-01{n}', 'jp', 120.0,
                ['美咲', '陽子', '恵美', '由美', '彩', '真由美'],
                ['健太', '大輔', '翔', '拓也', '誠', '浩二'],
                [['佐藤', '佐藤'], ['高橋', '高橋'], ['中村', '中村'], ['小林', '小林'], ['渡辺', '渡辺'], ['山本', '山本'], ['松本', '松本'], ['井上', '井上']], true, ' '),
        ];
    }
}
