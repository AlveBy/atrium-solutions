<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Feature;

use Atrium\Admin\Models\Language;
use Atrium\Admin\Models\Site;
use Atrium\Faq\Models\Category;
use Atrium\Faq\Models\Question;
use Atrium\Forms\Models\Form;
use Atrium\News\Models\News;
use Atrium\Pages\Models\Page;
use Atrium\Reviews\Models\Review;
use Atrium\Solutions\Seeders\LawSeeder;
use Atrium\Solutions\Solutions;
use Atrium\Solutions\Tests\TestCase;
use Atrium\Stores\Models\Store;
use Atrium\Team\Models\Department;
use Atrium\Team\Models\Member;

/**
 * Everything the law firm seeder writes, in the country it is set in.
 */
final class LawSeedTest extends TestCase
{
    private function seedFirm(string $locale): void
    {
        $solution = $this->app->make(Solutions::class)->find('law');

        $this->assertTrue((new LawSeeder)->forSolution($solution, $locale)->setContainer($this->app)->__invoke());
    }

    public function test_the_american_firm_has_these_pages_in_this_order(): void
    {
        $this->seedFirm('en');

        $pages = Page::query()->orderBy('id')->get();
        $codes = $pages->pluck('id', 'code');

        $this->assertSame([
            'home' => [10, null, 'default', 'Business law, plainly done'],
            'practice' => [100, null, 'children', 'Practice areas'],
            'corporate' => [10, 'practice', 'default', 'Corporate and M&A'],
            'disputes' => [20, 'practice', 'default', 'Disputes and arbitration'],
            'employment' => [30, 'practice', 'default', 'Employment'],
            'real-estate' => [40, 'practice', 'default', 'Real estate'],
            'ip' => [50, 'practice', 'default', 'Intellectual property'],
            'private-clients' => [60, 'practice', 'default', 'Private clients'],
            'about' => [300, null, 'default', 'About the firm'],
            'privacy' => [900, null, 'default', 'Privacy policy'],
        ], $pages->mapWithKeys(fn (Page $page): array => [$page->code => [
            $page->sort,
            $page->parent_id === null ? null : $codes->search($page->parent_id),
            $page->template,
            $page->getTranslation('name', 'en'),
        ]])->all());
    }

    public function test_the_pages_say_what_the_firm_is_in_the_main_language_and_in_every_other(): void
    {
        $this->seedFirm('en');
        $content = fn (string $code, string $language = 'en'): string => (string) Page::query()->where('code', $code)->sole()->getTranslation('content', $language);

        $this->assertSame('<p>Trademarks, software licenses and the contracts that keep your ideas yours.</p>', $content('ip'));
        $this->assertStringStartsWith('<p>Whitaker Brennan LLP advises companies, founders and families in Portland on deals', $content('home'));
        $this->assertStringStartsWith('<p>Emily Whitaker and Marcus Brennan founded the firm in 2011 after a decade', $content('about'));
        $this->assertStringContainsString('Whitaker Brennan LLP, 1120 NW Lovejoy Street, Portland, OR 97209', $content('privacy'));
        $this->assertStringStartsWith('<p>Whitaker Brennan LLP berät Unternehmen', $content('home', 'de'));
        $this->assertCount(15, Page::query()->where('code', 'home')->sole()->getTranslations('name'));
        $this->assertCount(15, Page::query()->where('code', 'ip')->sole()->getTranslations('content'));
        $this->assertSame(['en'], array_keys(Page::query()->where('code', 'privacy')->sole()->getTranslations('name')), 'A legal page is the main country\'s only.');
    }

    public function test_the_firm_has_two_departments_and_six_lawyers(): void
    {
        $this->seedFirm('en');

        $this->assertSame([['Partners', 100], ['Counsel and associates', 200]], Department::query()->orderBy('sort')->get()->map(fn (Department $d): array => [$d->getTranslation('name', 'en'), $d->sort])->all());

        $members = Member::query()->orderBy('sort')->get();
        $this->assertSame([
            ['emily-whitaker', 'Emily Whitaker', 'Partners', 10, 'emily@whitakerbrennan.com', 'demo/law-hartmann.svg', 'Managing partner, corporate and M&A'],
            ['marcus-brennan', 'Marcus Brennan', 'Partners', 20, 'marcus@whitakerbrennan.com', 'demo/law-okafor.svg', 'Partner, disputes and arbitration'],
            ['hannah-calloway', 'Hannah Calloway', 'Counsel and associates', 30, null, 'demo/law-lindqvist.svg', 'Counsel, employment'],
            ['ryan-delgado', 'Ryan Delgado', 'Counsel and associates', 40, null, 'demo/law-reyes.svg', 'Senior associate, real estate'],
            ['megan-foster', 'Megan Foster', 'Counsel and associates', 50, null, 'demo/law-sato.svg', 'Associate, intellectual property'],
            ['rachel-hollis', 'Rachel Hollis', 'Counsel and associates', 60, null, 'demo/law-petrova.svg', 'Associate, private clients'],
        ], $members->map(fn (Member $m): array => [
            $m->getTranslation('code', 'en'),
            $m->getTranslation('name', 'en'),
            Department::query()->find($m->department_id)->getTranslation('name', 'en'),
            $m->sort,
            $m->email,
            $m->preview_picture,
            $m->getTranslation('position', 'en'),
        ])->all());

        $this->assertSame('<p>Twenty years of deals for founders and family companies.</p>', $members[0]->getTranslation('preview_text', 'en'));
        $this->assertStringStartsWith('<p>Emily leads the corporate team.', $members[0]->getTranslation('detail_text', 'en'));
        $this->assertStringStartsWith('<p>Marcus represents companies', $members[1]->getTranslation('detail_text', 'en'));
        $this->assertNull($members[2]->getTranslation('detail_text', 'en', false));
        $this->assertSame('Emily Whitaker', $members[0]->getTranslation('name', 'de'), 'A name stays a name in every language.');
        $this->assertSame('Managing Partnerin, Gesellschaftsrecht und M&A', $members[0]->getTranslation('position', 'de'));
    }

    public function test_the_news_are_dated_newest_first_at_nine(): void
    {
        $this->seedFirm('en');

        $this->assertSame([
            'remote-work' => '2026-10-28 09:00:00',
            'arbitration' => '2026-09-15 09:00:00',
            'trademarks' => '2026-08-02 09:00:00',
            'new-office' => '2026-06-20 09:00:00',
        ], News::query()->orderBy('id')->get()->mapWithKeys(fn (News $n): array => [$n->getTranslation('code', 'en') => $n->active_from->format('Y-m-d H:i:s')])->all());

        $news = News::query()->orderBy('id')->first();
        $this->assertSame('What the new remote work rules mean for employers', $news->getTranslation('name', 'en'));
        $this->assertStringStartsWith('<p>From January, remote work arrangements', $news->getTranslation('preview_text', 'en'));
        $this->assertStringStartsWith('<p>From January, every remote', $news->getTranslation('detail_text', 'en'));
        $this->assertCount(15, $news->getTranslations('name'));
    }

    public function test_the_firm_has_two_offices_with_their_hours_and_places(): void
    {
        $this->seedFirm('en');

        $stores = Store::query()->orderBy('id')->get();

        $this->assertSame(['main', 'second'], $stores->pluck('code')->all());
        $this->assertSame(['Whitaker Brennan LLP, Pearl District', 'Whitaker Brennan LLP, Central Eastside'], $stores->pluck('name')->all());
        $this->assertSame(["1120 NW Lovejoy Street\nPortland, OR 97209", "415 SE Water Avenue\nPortland, OR 97214"], $stores->pluck('address')->all());
        $this->assertSame([['+1 503 555 0140'], ['+1 503 555 0141']], $stores->pluck('phones')->all());
        $this->assertSame(['office@whitakerbrennan.com', 'office2@whitakerbrennan.com'], $stores->pluck('email')->all());
        $this->assertEquals([
            ['lat' => 45.5299, 'lng' => -122.683, 'address' => '1120 NW Lovejoy Street'],
            ['lat' => 45.5185, 'lng' => -122.665, 'address' => '415 SE Water Avenue'],
        ], $stores->pluck('location')->all());
        $this->assertSame(['<p>Monday to Friday, 9 am to 6 pm</p>', '<p>Monday to Friday, 9 am to 6 pm</p>'], $stores->pluck('detail_text')->all());
    }

    public function test_the_consultation_form_asks_six_things_in_order(): void
    {
        $this->seedFirm('en');

        $form = Form::query()->sole();
        $this->assertSame('consultation', $form->code);
        $this->assertSame('Consultation request', $form->name);
        $this->assertTrue($form->active);
        $this->assertSame('Request a consultation', $form->getTranslation('button_label', 'en'));
        $this->assertSame('Thank you. A lawyer will call you within one business day.', $form->getTranslation('success_message', 'en'));
        $this->assertCount(15, $form->getTranslations('button_label'));

        $fields = $form->fields()->orderBy('sort')->get();
        $this->assertSame([
            ['name', 'text', 'Your name', true, 10],
            ['phone', 'tel', 'Phone', true, 20],
            ['email', 'email', 'Email', false, 30],
            ['area', 'select', 'Practice area', false, 40],
            ['message', 'textarea', 'Briefly, what is it about?', false, 50],
            ['consent', 'checkbox', 'I agree that my details are used to answer this request', true, 60],
        ], $fields->map(fn ($f): array => [$f->name, $f->type, $f->getTranslation('label', 'en'), (bool) $f->required, $f->sort])->all());
        $this->assertSame(
            "Corporate and M&A\nDisputes and arbitration\nEmployment\nReal estate\nIntellectual property\nPrivate clients",
            $fields[3]->getTranslation('options', 'en'),
        );
        $this->assertSame('', $fields[0]->getTranslation('options', 'en', false));
    }

    public function test_the_questions_about_fees_carry_the_prices(): void
    {
        $this->seedFirm('en');

        $category = Category::query()->sole();
        $this->assertSame('Fees and the first meeting', $category->getTranslation('name', 'en'));
        $this->assertSame('fees', $category->getTranslation('code', 'en'));
        $this->assertCount(15, $category->getTranslations('name'));
        $this->assertSame(500, $category->sort);

        $questions = Question::query()->orderBy('sort')->get();
        $this->assertSame([10, 20, 30], $questions->pluck('sort')->all());
        $this->assertSame(['Is the first consultation free?', 'What does an hour of a partner cost?', 'Do you work for a flat fee?'], $questions->map(fn (Question $q): string => $q->getTranslation('question', 'en'))->all());
        $this->assertSame('<p>$320 an hour. Most contracts are reviewed for a flat fee from $450.</p>', $questions[1]->getTranslation('answer', 'en'));
        $this->assertCount(15, $questions[0]->getTranslations('answer'));
        $this->assertSame([$category->site_id], $questions->pluck('site_id')->unique()->values()->all());
    }

    public function test_three_clients_have_their_say_on_their_dates(): void
    {
        $this->seedFirm('en');

        $reviews = Review::query()->orderBy('id')->get();

        $this->assertSame([
            ['Emily Langley, founder of a software company', '2026-10-02 10:00:00'],
            ['Marcus Mercer, operations director at a logistics company', '2026-09-20 10:00:00'],
            ['Nathan Foster and family', '2026-07-11 10:00:00'],
        ], $reviews->map(fn (Review $r): array => [$r->author_name, $r->posted_at->format('Y-m-d H:i:s')])->all());
        $this->assertSame([5, 5, 5], $reviews->pluck('rating')->all());
        $this->assertSame(['published', 'published', 'published'], $reviews->map(fn (Review $r): string => $r->status->value)->all());
        $this->assertSame('Eighteen months of arbitration, and we never once had to chase them for an update.', $reviews[1]->text);
    }

    public function test_the_site_is_the_firm_in_fifteen_languages_english_first(): void
    {
        $this->seedFirm('en');

        $site = Site::query()->orderBy('id')->first();
        $this->assertSame('Whitaker Brennan LLP', $site->name);
        $this->assertSame('en', $site->default_language);
        $this->assertCount(15, $site->languages);
        $this->assertSame('en', $site->languages[0]);
        $this->assertSame('en', Language::query()->where('is_default', true)->sole()->code);
    }

    public function test_where_a_name_has_no_latin_letters_the_lawyers_are_found_by_their_role(): void
    {
        $this->seedFirm('ja');

        $members = Member::query()->orderBy('sort')->get();

        $this->assertSame(['managing-partner', 'disputes-partner', 'employment', 'real-estate', 'ip', 'private-clients'], $members->map(fn (Member $m): string => $m->getTranslation('code', 'ja'))->all());
        $this->assertSame(['佐藤 美咲', '高橋 大輔', '中村 恵美', '小林 拓也', '渡辺 彩', '山本 真由美'], $members->map(fn (Member $m): string => $m->getTranslation('name', 'ja'))->all());
        $this->assertSame(['managing-partner@sato-takahashi-law.jp', 'disputes-partner@sato-takahashi-law.jp', null, null, null, null], $members->pluck('email')->all());
    }
}
