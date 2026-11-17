<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Feature;

use Atrium\Faq\Models\Category;
use Atrium\Faq\Models\Question;
use Atrium\Forms\Models\Form;
use Atrium\Forms\Models\FormField;
use Atrium\News\Models\News;
use Atrium\Pages\Models\Page;
use Atrium\Reviews\Models\Review;
use Atrium\Solutions\Solution;
use Atrium\Solutions\Tests\Support\ProbeSeeder;
use Atrium\Solutions\Tests\TestCase;
use Atrium\Stores\Models\Store;
use Atrium\Team\Models\Department;
use Atrium\Team\Models\Member;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * What a solution seeder's helpers write, and what they do when the module
 * is not there.
 */
final class SeederHelpersTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir().'/atrium-seeder-'.uniqid();
        mkdir($this->root.'/demo', 0777, true);
        mkdir($this->root.'/content', 0777, true);
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->root);

        parent::tearDown();
    }

    private function probe(?string $locale = 'en', string $brand = 'Probe Ltd'): ProbeSeeder
    {
        $solution = new Solution(name: 'probe', path: $this->root, label: ['en' => 'Probe'], brand: $brand);

        return (new ProbeSeeder)->forSolution($solution, $locale)->setContainer($this->app);
    }

    public function test_the_country_is_the_one_of_the_locale_asked_for_or_the_app_s(): void
    {
        $this->assertSame('de', $this->probe('de')->country()->locale);
        $this->assertSame('Berlin', $this->probe('de')->country()->city);

        config(['app.locale' => 'ja']);
        $this->assertSame('ja', $this->probe(null)->country()->locale);

        config(['app.locale' => null]);
        $this->assertSame('en', $this->probe(null)->country()->locale);
    }

    public function test_a_page_has_the_texts_of_every_language_and_the_defaults_of_a_page(): void
    {
        $page = $this->probe()->invoke('page', 'about', ['en' => ['About', '<p>Us</p>'], 'de' => ['Über uns', '<p>Wir</p>']]);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertTrue($page->exists);
        $this->assertSame('about', $page->code);
        $this->assertNull($page->parent_id);
        $this->assertSame(500, $page->sort);
        $this->assertSame('default', $page->template);
        $this->assertEquals(['en' => 'About', 'de' => 'Über uns'], $page->getTranslations('name'));
        $this->assertEquals(['en' => '<p>Us</p>', 'de' => '<p>Wir</p>'], $page->getTranslations('content'));
    }

    public function test_a_page_can_have_a_parent_a_sort_and_a_template(): void
    {
        $parent = $this->probe()->invoke('page', 'practice', ['en' => ['Practice', '']]);
        $child = $this->probe()->invoke('page', 'ip', ['en' => ['IP', '']], $parent, 70, 'children');

        $this->assertSame($parent->getKey(), $child->parent_id);
        $this->assertSame(70, $child->sort);
        $this->assertSame('children', $child->template);
    }

    public function test_news_has_a_date_a_wrapped_preview_and_a_code_in_every_language(): void
    {
        $news = $this->probe()->invoke('news', 'launch', '2026-10-28 09:00:00', [
            'en' => ['Launch', 'Preview', '<p>Detail</p>'],
            'de' => ['Start', 'Vorschau', '<p>Detail de</p>'],
        ]);

        $this->assertInstanceOf(News::class, $news);
        $this->assertSame('2026-10-28 09:00:00', $news->active_from->format('Y-m-d H:i:s'));
        $this->assertEquals(['en' => 'Launch', 'de' => 'Start'], $news->getTranslations('name'));
        $this->assertEquals(['en' => 'launch', 'de' => 'launch'], $news->getTranslations('code'));
        $this->assertEquals(['en' => '<p>Preview</p>', 'de' => '<p>Vorschau</p>'], $news->getTranslations('preview_text'));
        $this->assertEquals(['en' => '<p>Detail</p>', 'de' => '<p>Detail de</p>'], $news->getTranslations('detail_text'));
        $this->assertNull($news->preview_picture);
    }

    public function test_news_may_have_a_picture_from_the_demo_files(): void
    {
        $news = $this->probe()->invoke('news', 'launch', '2026-10-28', ['en' => ['Launch', 'P', 'D']], 'launch.svg');

        $this->assertSame('demo/launch.svg', $news->preview_picture);
    }

    public function test_a_department_has_a_name_and_a_sort(): void
    {
        $default = $this->probe()->invoke('department', ['en' => 'Partners']);
        $sorted = $this->probe()->invoke('department', ['en' => 'Associates', 'de' => 'Mitarbeiter'], 200);

        $this->assertInstanceOf(Department::class, $default);
        $this->assertSame(500, $default->sort);
        $this->assertSame(200, $sorted->sort);
        $this->assertEquals(['en' => 'Associates', 'de' => 'Mitarbeiter'], $sorted->getTranslations('name'));
    }

    public function test_a_person_has_every_text_a_language_gives(): void
    {
        $department = $this->probe()->invoke('department', ['en' => 'Partners']);

        $member = $this->probe()->invoke('member', 'emily', [
            'en' => ['Emily Whitaker', 'Partner', 'Short', '<p>Long</p>'],
            'de' => ['Emily Whitaker', 'Partnerin', 'Kurz', '<p>Lang</p>'],
        ], $department, 'emily.svg', ['email' => 'emily@example.com', 'sort' => 30]);

        $this->assertInstanceOf(Member::class, $member);
        $this->assertSame($department->getKey(), $member->department_id);
        $this->assertSame('demo/emily.svg', $member->preview_picture);
        $this->assertSame('emily@example.com', $member->email);
        $this->assertSame(30, $member->sort);
        $this->assertEquals(['en' => 'Emily Whitaker', 'de' => 'Emily Whitaker'], $member->getTranslations('name'));
        $this->assertEquals(['en' => 'emily', 'de' => 'emily'], $member->getTranslations('code'));
        $this->assertEquals(['en' => 'Partner', 'de' => 'Partnerin'], $member->getTranslations('position'));
        $this->assertEquals(['en' => '<p>Short</p>', 'de' => '<p>Kurz</p>'], $member->getTranslations('preview_text'));
        $this->assertEquals(['en' => '<p>Long</p>', 'de' => '<p>Lang</p>'], $member->getTranslations('detail_text'));
    }

    public function test_a_person_without_a_summary_or_a_biography_has_neither(): void
    {
        $member = $this->probe()->invoke('member', 'bare', ['en' => ['Bare', 'Clerk'], 'de' => ['Bare', 'Referent', 'Kurz']]);

        $this->assertNull($member->department_id);
        $this->assertNull($member->preview_picture);
        $this->assertSame(['de' => '<p>Kurz</p>'], $member->getTranslations('preview_text'));
        $this->assertSame([], $member->getTranslations('detail_text'));
        $this->assertEquals(['en' => 'Clerk', 'de' => 'Referent'], $member->getTranslations('position'));
    }

    public function test_a_form_has_its_fields_in_order_with_their_rules(): void
    {
        $form = $this->probe()->invoke('form', 'contact', 'Contact us', ['en' => 'Send'], ['en' => 'Thanks'], [
            ['name', 'text', ['en' => 'Your name'], true],
            ['email', 'email', ['en' => 'Email']],
            ['topic', 'select', ['en' => 'Topic'], false, ['en' => "A\nB"]],
        ]);

        $this->assertInstanceOf(Form::class, $form);
        $this->assertSame('contact', $form->code);
        $this->assertSame('Contact us', $form->name);
        $this->assertTrue($form->active);
        $this->assertSame(['en' => 'Send'], $form->getTranslations('button_label'));
        $this->assertSame(['en' => 'Thanks'], $form->getTranslations('success_message'));

        $fields = FormField::query()->where('form_id', $form->getKey())->orderBy('sort')->get();
        $this->assertSame(['name', 'email', 'topic'], $fields->pluck('name')->all());
        $this->assertSame(['text', 'email', 'select'], $fields->pluck('type')->all());
        $this->assertSame([10, 20, 30], $fields->pluck('sort')->all());
        $this->assertSame([true, false, false], $fields->pluck('required')->map(fn ($v): bool => (bool) $v)->all());
        $this->assertSame(['en' => 'Your name'], $fields[0]->getTranslations('label'));
        $this->assertSame(['en' => 'Topic'], $fields[2]->getTranslations('label'));
        $this->assertSame(['en' => "A\nB"], $fields[2]->getTranslations('options'));
        $this->assertSame([], $fields[0]->getTranslations('options'));
    }

    public function test_an_office_has_its_place_on_the_map_and_its_hours(): void
    {
        $store = $this->probe()->invoke(
            'office',
            'main',
            'Main office',
            "1 Main Street\nPortland",
            ['+1 555 0100', '+1 555 0101'],
            'office@example.com',
            ['lat' => 45.5, 'lng' => -122.6],
            'Mon <to> Fri',
            'office.svg',
        );

        $this->assertInstanceOf(Store::class, $store);
        $this->assertSame('main', $store->code);
        $this->assertSame('Main office', $store->name);
        $this->assertSame("1 Main Street\nPortland", $store->address);
        $this->assertSame(['+1 555 0100', '+1 555 0101'], $store->phones);
        $this->assertSame('office@example.com', $store->email);
        $this->assertEquals(['lat' => 45.5, 'lng' => -122.6, 'address' => '1 Main Street'], $store->location);
        $this->assertSame('<p>Mon &lt;to&gt; Fri</p>', $store->detail_text);
        $this->assertSame('demo/office.svg', $store->preview_picture);
    }

    public function test_an_office_without_hours_or_a_picture_has_neither_and_a_one_line_address_stays_whole(): void
    {
        $store = $this->probe()->invoke('office', 'main', 'Main office', 'Portland', [], 'o@example.com', ['lat' => 1.5, 'lng' => 2.5]);

        $this->assertNull($store->detail_text);
        $this->assertNull($store->preview_picture);
        $this->assertEquals(['lat' => 1.5, 'lng' => 2.5, 'address' => 'Portland'], $store->location);
    }

    public function test_an_empty_address_is_kept_as_it_is_on_the_map(): void
    {
        $store = $this->probe()->invoke('office', 'main', 'Main office', '', [], 'o@example.com', ['lat' => 1.5, 'lng' => 2.5]);

        $this->assertSame('', $store->location['address']);
    }

    public function test_questions_are_a_category_and_its_questions_in_order(): void
    {
        $this->probe()->invoke('questions', ['en' => ['Fees', 'fees'], 'de' => ['Honorar', 'honorar']], [
            ['en' => ['First?', 'One'], 'de' => ['Erste?', 'Eins']],
            ['en' => ['Second?', 'Two']],
        ], 70);

        $category = Category::query()->sole();
        $this->assertEquals(['en' => 'Fees', 'de' => 'Honorar'], $category->getTranslations('name'));
        $this->assertEquals(['en' => 'fees', 'de' => 'honorar'], $category->getTranslations('code'));
        $this->assertSame(70, $category->sort);

        $questions = Question::query()->orderBy('sort')->get();
        $this->assertSame([10, 20], $questions->pluck('sort')->all());
        $this->assertSame([$category->getKey(), $category->getKey()], $questions->pluck('category_id')->all());
        $this->assertSame([$category->site_id, $category->site_id], $questions->pluck('site_id')->all());
        $this->assertEquals(['en' => 'First?', 'de' => 'Erste?'], $questions[0]->getTranslations('question'));
        $this->assertEquals(['en' => '<p>One</p>', 'de' => '<p>Eins</p>'], $questions[0]->getTranslations('answer'));
        $this->assertSame(['en' => 'Second?'], $questions[1]->getTranslations('question'));
        $this->assertSame(['en' => '<p>Two</p>'], $questions[1]->getTranslations('answer'));
    }

    public function test_questions_without_a_sort_go_to_the_middle(): void
    {
        $this->probe()->invoke('questions', ['en' => ['Fees', 'fees']], []);

        $this->assertSame(500, Category::query()->sole()->sort);
        $this->assertSame(0, Question::query()->count());
    }

    public function test_a_review_is_published_and_may_be_about_a_record(): void
    {
        $page = $this->probe()->invoke('page', 'about', ['en' => ['About', '']]);

        $site = $this->probe()->invoke('review', 'Ann', 5, 'Great', '2026-10-02 10:00:00');
        $about = $this->probe()->invoke('review', 'Bob', 3, 'Fine', '2026-09-20 10:00:00', $page);

        $this->assertInstanceOf(Review::class, $site);
        $this->assertSame('Ann', $site->author_name);
        $this->assertSame(5, $site->rating);
        $this->assertSame('Great', $site->text);
        $this->assertSame('published', $site->status->value);
        $this->assertSame('2026-10-02 10:00:00', $site->posted_at->format('Y-m-d H:i:s'));
        $this->assertNull($site->reviewable_type);
        $this->assertNull($site->reviewable_id);
        $this->assertSame(Page::class, $about->reviewable_type);
        $this->assertSame($page->getKey(), $about->reviewable_id);
        $this->assertSame(3, $about->rating);
    }

    /**
     * @return array<string,array{string,array<int,mixed>,class-string}>
     */
    public static function helpers(): array
    {
        return [
            'page' => ['page', ['about', ['en' => ['About', '']]], Page::class],
            'news' => ['news', ['launch', '2026-10-28', ['en' => ['N', 'P', 'D']]], News::class],
            'department' => ['department', [['en' => 'Partners']], Department::class],
            'member' => ['member', ['emily', ['en' => ['Emily', 'Partner']]], Member::class],
            'form' => ['form', ['contact', 'Contact', ['en' => 'Send'], ['en' => 'Thanks'], []], Form::class],
            'office' => ['office', ['main', 'Main', 'Portland', [], 'o@example.com', ['lat' => 1.5, 'lng' => 2.5]], Store::class],
            'questions' => ['questions', [['en' => ['Fees', 'fees']], []], Category::class],
            'review' => ['review', ['Ann', 5, 'Great', '2026-10-02'], Review::class],
        ];
    }

    /**
     * @param  array<int,mixed>  $arguments
     * @param  class-string  $model
     */
    #[DataProvider('helpers')]
    public function test_a_module_that_is_not_installed_is_skipped(string $helper, array $arguments, string $model): void
    {
        $this->probe()->invoke($helper, ...$arguments);
        $this->assertSame(1, $model::query()->withoutGlobalScopes()->count(), 'With the module on it writes.');

        $model::query()->withoutGlobalScopes()->delete();
        config(['atrium.modules' => []]);

        $this->assertNull($this->probe()->invoke($helper, ...$arguments));
        $this->assertSame(0, $model::query()->withoutGlobalScopes()->count(), 'With the module off it writes nothing.');
    }
}
