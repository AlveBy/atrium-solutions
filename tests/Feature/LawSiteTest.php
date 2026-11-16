<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Feature;

use Atrium\Admin\Models\Language;
use Atrium\Admin\Support\PublicUrl;
use Atrium\Pages\Models\Page;
use Atrium\Solutions\Seeders\LawSeeder;
use Atrium\Solutions\Solutions;
use Atrium\Solutions\Tests\TestCase;
use Atrium\Team\Models\Member;

/**
 * The law firm as a visitor sees it once `atrium:solution:install law` ran:
 * its pages, its names for the modules, its menu, in three languages.
 */
final class LawSiteTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('atrium-solutions.installed', 'law');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $solution = $this->app->make(Solutions::class)->find('law');
        $this->assertTrue((new LawSeeder)->forSolution($solution)->setContainer($this->app)->__invoke());
        PublicUrl::forgetPaths();
    }

    public function test_every_page_the_solution_lists_opens(): void
    {
        $solution = $this->app->make(Solutions::class)->find('law');

        foreach ($solution->pages as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_the_modules_live_under_the_solution_s_addresses_and_names(): void
    {
        $this->get('/lawyers')
            ->assertOk()
            ->assertSee('<h1>Lawyers</h1>', false)
            ->assertSee('Partners')
            ->assertSee('Ines Hartmann');

        $this->get('/team')->assertNotFound();
        $this->get('/offices')->assertOk()->assertSee('Harbour office');
        $this->get('/offices/harbour')->assertOk()->assertSee('Northam');
        $this->get('/insights')->assertOk();
    }

    public function test_the_menu_is_the_solution_s(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        preg_match('~<nav class="menu".*?</nav>~s', (string) $html, $nav);

        $this->assertNotEmpty($nav);
        $this->assertSame(
            ['Practice areas', 'Lawyers', 'Insights', 'About the firm', 'Offices'],
            array_map(static fn (string $label): string => trim(html_entity_decode(strip_tags($label))), $this->links($nav[0])),
        );
    }

    public function test_the_site_speaks_german_and_russian(): void
    {
        $this->assertSame(['en', 'de', 'ru'], Language::query()->orderBy('sort')->pluck('code')->all());
        $this->assertSame('en', Language::query()->where('is_default', true)->value('code'));

        $this->get('/de/lawyers')->assertOk()->assertSee('Anwälte');
        $this->get('/ru/lawyers')->assertOk()->assertSee('Юристы');
        $this->get('/ru/practice')->assertOk();
    }

    public function test_the_content_is_put_in_once(): void
    {
        $pages = Page::query()->count();
        $people = Member::query()->count();

        $solution = $this->app->make(Solutions::class)->find('law');
        $this->assertFalse((new LawSeeder)->forSolution($solution)->setContainer($this->app)->__invoke());

        $this->assertSame($pages, Page::query()->count());
        $this->assertSame($people, Member::query()->count());
        $this->assertSame(6, $people);
    }

    /**
     * @return array<int,string>
     */
    private function links(string $html): array
    {
        preg_match_all('~<a\b[^>]*>(.*?)</a>~s', $html, $links);

        return $links[1];
    }
}
