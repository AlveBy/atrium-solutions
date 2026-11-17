<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Seeders;

use Atrium\Admin\Modules\Soft;
use Atrium\Solutions\SolutionSeeder;
use Illuminate\Support\Str;

/**
 * A made-up business law firm in the installer's country: practice areas as
 * pages, six lawyers in two departments, insights, two offices, a
 * consultation request, questions about fees, clients' words and a privacy
 * page. Texts in solutions/law/content/{locale}.php.
 */
final class LawSeeder extends SolutionSeeder
{
    /**
     * The lawyers: their key in the texts, department, portrait and whether
     * the text speaks of a woman. The first two founded the firm.
     */
    private const PEOPLE = [
        ['managing-partner', 'partners', 'law-hartmann.svg', true],
        ['disputes-partner', 'partners', 'law-okafor.svg', false],
        ['employment', 'associates', 'law-lindqvist.svg', true],
        ['real-estate', 'associates', 'law-reyes.svg', false],
        ['ip', 'associates', 'law-sato.svg', true],
        ['private-clients', 'associates', 'law-petrova.svg', true],
    ];

    protected function filled(): bool
    {
        return Soft::available(self::PAGE) && (self::PAGE)::query()->withoutGlobalScopes()->where('code', 'practice')->exists();
    }

    protected function facts(): array
    {
        return [
            ...parent::facts(),
            ':founder' => $this->country->person(0, true),
            ':founder_first' => $this->country->firstName(0, true),
            ':partner' => $this->country->person(1, false),
            ':partner_first' => $this->country->firstName(1, false),
            ':founded' => '2011',
            // US dollars at US prices, in the country's currency and price list.
            ...array_map($this->country->money(...), require $this->solution->file('prices.php')),
        ];
    }

    protected function fill(): void
    {
        $this->page('home', $this->translated('home'), sort: 10);
        $practice = $this->page('practice', $this->translated('practice'), sort: 100, template: 'children');

        foreach (array_keys($this->main()['areas'] ?? []) as $index => $code) {
            $this->page($code, array_map(
                static fn (array $area): array => [$area[0], '<p>'.$area[1].'</p>'],
                $this->translated("areas.{$code}"),
            ), $practice, ($index + 1) * 10);
        }

        $this->page('about', $this->translated('about'), sort: 300);

        foreach (array_keys($this->main()['legal'] ?? []) as $code) {
            [$name, $content] = $this->mainText("legal.{$code}");
            $this->page($code, [$this->country->language() => [$name, $content]], sort: 900);
        }

        $departments = [
            'partners' => $this->department($this->translated('departments.partners'), 100),
            'associates' => $this->department($this->translated('departments.associates'), 200),
        ];

        $company = $this->main()['company'] ?? 'firm';

        foreach (self::PEOPLE as $index => [$key, $department, $photo, $woman]) {
            $name = $this->country->person($index, $woman);
            $texts = array_map(static fn (array $text): array => [$name, ...$text], $this->translated("people.{$key}"));

            $this->member(Str::slug(Str::ascii($name)) ?: $key, $texts, $departments[$department], $photo, [
                'email' => $index < 2 ? $this->country->email(Str::slug(Str::ascii($this->country->firstName($index, $woman)), '.') ?: $key, $company) : null,
                'sort' => ($index + 1) * 10,
            ]);
        }

        $dates = ['remote-work' => '2026-10-28 09:00:00', 'arbitration' => '2026-09-15 09:00:00', 'trademarks' => '2026-08-02 09:00:00', 'new-office' => '2026-06-20 09:00:00'];

        foreach ($dates as $code => $date) {
            $this->news($code, $date, $this->translated("news.{$code}"));
        }

        foreach ([0, 1] as $index) {
            $this->office(
                $index === 0 ? 'main' : 'second',
                (string) $this->mainText('office', [':district' => $this->country->district($index)]),
                $this->country->address($index),
                [$this->country->phone(40 + $index)],
                $this->country->email($index === 0 ? 'office' : 'office2', $company),
                $this->country->location($index),
                (string) $this->mainText('hours'),
            );
        }

        $areas = array_map(
            static fn (array $areas): string => implode("\n", array_column($areas, 0)),
            $this->translated('areas'),
        );
        $label = fn (string $field): array => $this->translated("form.fields.{$field}");

        $this->form('consultation', (string) $this->mainText('form.name'), $this->translated('form.button'), $this->translated('form.success'), [
            ['name', 'text', $label('name'), true],
            ['phone', 'tel', $label('phone'), true],
            ['email', 'email', $label('email')],
            ['area', 'select', $label('area'), false, $areas],
            ['message', 'textarea', $label('message')],
            ['consent', 'checkbox', $label('consent'), true],
        ]);

        $codes = $this->translated('faq.code');
        $category = [];

        foreach ($this->translated('faq.name') as $language => $name) {
            $category[$language] = [$name, $codes[$language] ?? $language];
        }

        $this->questions($category, self::perItem($this->translated('faq.questions')));

        $reviewers = [$this->country->person(6, true), $this->country->person(7, false), $this->country->person(4, false)];

        foreach ($this->mainText('reviews') as $index => [$author, $text]) {
            $this->review(strtr($author, [':name' => $reviewers[$index]]), 5, $text, ['2026-10-02 10:00:00', '2026-09-20 10:00:00', '2026-07-11 10:00:00'][$index]);
        }
    }
}
