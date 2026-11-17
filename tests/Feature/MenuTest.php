<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Feature;

use Atrium\Admin\Support\PublicUrl;
use Atrium\Pages\Models\Page;
use Atrium\Solutions\Menu;
use Atrium\Solutions\Seeders\LawSeeder;
use Atrium\Solutions\Solution;
use Atrium\Solutions\Solutions;
use Atrium\Solutions\Tests\TestCase;
use Atrium\Team\Models\Member;

/**
 * The menu a solution lists: pages by code and lists of modules.
 */
final class MenuTest extends TestCase
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

    /**
     * @param  array<int,array<string,string>>  $menu
     */
    private function items(array $menu): array
    {
        return $this->app->make(Menu::class)->items(new Solution(name: 'probe', path: '/tmp', label: ['en' => 'Probe'], menu: $menu));
    }

    public function test_the_law_firm_menu_is_its_pages_and_lists_in_order(): void
    {
        $items = $this->app->make(Menu::class)->items($this->app->make(Solutions::class)->find('law'));

        $this->assertSame(['Practice areas', 'Lawyers', 'Insights', 'About the firm', 'Offices'], array_column($items, 'label'));
        $this->assertSame([null, 'lawyers', 'insights', null, 'offices'], array_column($items, 'prefix'));
        $this->assertSame(
            [url('/practice'), url('/lawyers'), url('/insights'), url('/about'), url('/offices')],
            array_column($items, 'url'),
        );
    }

    public function test_a_page_that_is_not_there_is_left_out(): void
    {
        $items = $this->items([['page' => 'no-such-page'], ['page' => 'about']]);

        $this->assertSame(['About the firm'], array_column($items, 'label'));
    }

    public function test_an_inactive_page_is_left_out(): void
    {
        Page::query()->where('code', 'about')->update(['active' => false]);

        $this->assertSame([], $this->items([['page' => 'about']]));
    }

    public function test_a_page_inside_another_is_not_a_menu_item(): void
    {
        $this->assertTrue(Page::query()->where('code', 'disputes')->whereNotNull('parent_id')->exists());

        $this->assertSame([], $this->items([['page' => 'disputes']]));
    }

    public function test_a_page_without_a_name_in_the_language_of_the_visitor_is_left_out(): void
    {
        $page = new Page(['code' => 'only-english', 'sort' => 500, 'template' => 'default']);
        $page->setTranslation('name', 'en', 'Only English')->setTranslation('content', 'en', '<p>x</p>');
        $page->save();

        $this->assertSame(['Only English'], array_column($this->items([['page' => 'only-english']]), 'label'));

        $this->app->setLocale('ru');
        $this->assertSame([], $this->items([['page' => 'only-english']]));
    }

    public function test_a_module_that_is_not_there_or_has_no_address_is_left_out(): void
    {
        $this->assertSame([], $this->items([['module' => 'Atrium\Ghost\Models\Phantom']]));

        config(['atrium.public_urls.'.Member::class => ['prefix' => '/']]);
        $this->assertSame([], $this->items([['module' => Member::class]]));
    }

    public function test_a_module_that_was_switched_off_is_left_out(): void
    {
        config(['atrium.modules' => ['alveby/atrium-pages']]);

        $this->assertSame([], $this->items([['module' => Member::class]]));
    }

    public function test_pages_are_left_out_when_the_pages_module_is_off(): void
    {
        config(['atrium.modules' => ['alveby/atrium-team']]);

        $this->assertSame([], $this->items([['page' => 'about']]));
        $this->assertSame(['Lawyers'], array_column($this->items([['page' => 'about'], ['module' => Member::class]]), 'label'));
    }

    public function test_an_entry_that_names_neither_a_page_nor_a_module_is_left_out(): void
    {
        $this->assertSame(['About the firm'], array_column($this->items([['label' => 'Nothing'], ['page' => 'about'], []]), 'label'));
    }

    public function test_an_entry_with_both_is_the_page(): void
    {
        $items = $this->items([['page' => 'about', 'module' => Member::class]]);

        $this->assertSame(['About the firm'], array_column($items, 'label'));
        $this->assertSame([null], array_column($items, 'prefix'));
    }
}
