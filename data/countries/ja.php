<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

return [
    'locale' => 'ja',
    'country' => 'JP',
    'currency' => 'JPY',
    'intl' => 'ja_JP',
    'city' => '福岡市',
    'region' => '福岡県',
    'addressFormat' => '〒{postcode}
{region}{city}{district}{street}{number}',
    'addresses' => [
        0 => [
            'street' => '天神',
            'number' => '2-8-14',
            'postcode' => '810-0001',
            'district' => '中央区',
            'lat' => 33.5902,
            'lng' => 130.399,
        ],
        1 => [
            'street' => '博多駅前',
            'number' => '3-21-5',
            'postcode' => '812-0011',
            'district' => '博多区',
            'lat' => 33.589,
            'lng' => 130.418,
        ],
    ],
    'phoneFormat' => '+81 92-555-01{n}',
    'domain' => 'jp',
    'priceLevel' => 120.0,
    'women' => [
        0 => '美咲',
        1 => '陽子',
        2 => '恵美',
        3 => '由美',
        4 => '彩',
        5 => '真由美',
    ],
    'men' => [
        0 => '健太',
        1 => '大輔',
        2 => '翔',
        3 => '拓也',
        4 => '誠',
        5 => '浩二',
    ],
    'surnames' => [
        0 => [
            0 => '佐藤',
            1 => '佐藤',
        ],
        1 => [
            0 => '高橋',
            1 => '高橋',
        ],
        2 => [
            0 => '中村',
            1 => '中村',
        ],
        3 => [
            0 => '小林',
            1 => '小林',
        ],
        4 => [
            0 => '渡辺',
            1 => '渡辺',
        ],
        5 => [
            0 => '山本',
            1 => '山本',
        ],
        6 => [
            0 => '松本',
            1 => '松本',
        ],
        7 => [
            0 => '井上',
            1 => '井上',
        ],
    ],
    'familyNameFirst' => true,
];
