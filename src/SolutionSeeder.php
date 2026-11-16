<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions;

use Atrium\Admin\Models\Language;
use Atrium\Admin\Modules\DemoSeeder;
use Atrium\Admin\Modules\Soft;
use Atrium\Admin\Support\ContentLanguages;
use Atrium\Admin\Support\Sites;
use Illuminate\Database\Eloquent\Model;

/**
 * The content of a ready-made site. A solution fills the modules itself
 * rather than running their own demo: a law firm's news are not a shop's.
 *
 * Texts are given per language, `['en' => …, 'de' => …, 'ru' => …]`; a
 * module that is not installed is skipped by the helpers, so a solution
 * still installs with one module less.
 */
abstract class SolutionSeeder extends DemoSeeder
{
    public const PAGE = 'Atrium\Pages\Models\Page';

    public const NEWS = 'Atrium\News\Models\News';

    public const MEMBER = 'Atrium\Team\Models\Member';

    public const DEPARTMENT = 'Atrium\Team\Models\Department';

    public const FORM = 'Atrium\Forms\Models\Form';

    public const FORM_FIELD = 'Atrium\Forms\Models\FormField';

    public const STORE = 'Atrium\Stores\Models\Store';

    public const FAQ_CATEGORY = 'Atrium\Faq\Models\Category';

    public const QUESTION = 'Atrium\Faq\Models\Question';

    public const REVIEW = 'Atrium\Reviews\Models\Review';

    protected Solution $solution;

    public function forSolution(Solution $solution): static
    {
        $this->solution = $solution;

        return $this;
    }

    /**
     * Whether the site has content already: then nothing is added.
     */
    abstract protected function filled(): bool;

    /**
     * Add the content.
     */
    abstract protected function fill(): void;

    public function run(): bool
    {
        if ($this->filled()) {
            return false;
        }

        $this->prepareSite();
        $this->copyFiles($this->solution->file('demo'));
        $this->fill();

        return true;
    }

    /**
     * The languages of the solution, the first one the default, and the
     * brand as the default site's name.
     */
    protected function prepareSite(): void
    {
        $names = ['en' => 'English', 'de' => 'Deutsch', 'ru' => 'Русский', 'fr' => 'Français', 'es' => 'Español', 'it' => 'Italiano', 'pl' => 'Polski', 'uk' => 'Українська'];
        $languages = $this->solution->languages;

        foreach ($languages as $sort => $code) {
            Language::query()->firstOrCreate(['code' => $code], ['name' => $names[$code] ?? strtoupper($code), 'active' => true, 'sort' => ($sort + 1) * 100]);
        }

        Language::query()->where('code', $languages[0])->update(['is_default' => true]);
        Language::query()->where('code', '!=', $languages[0])->update(['is_default' => false]);

        $site = app(Sites::class)->default();
        $site->forceFill(array_filter([
            'name' => $this->solution->brand ?: null,
            'languages' => $languages,
            'default_language' => $languages[0],
        ]))->save();

        app(Sites::class)->flush();
        app(ContentLanguages::class)->flush();
    }

    /**
     * A page of the Pages module.
     *
     * @param  array<string,array{0:string,1:string}>  $texts  language => [name, content]
     */
    protected function page(string $code, array $texts, ?Model $parent = null, int $sort = 500, string $template = 'default'): ?Model
    {
        if (! Soft::available(self::PAGE)) {
            return null;
        }

        $page = new (self::PAGE)(['parent_id' => $parent?->getKey(), 'code' => $code, 'sort' => $sort, 'template' => $template]);

        foreach ($texts as $language => [$name, $content]) {
            $page->setTranslation('name', $language, $name)->setTranslation('content', $language, $content);
        }

        $page->save();

        return $page;
    }

    /**
     * A news item.
     *
     * @param  array<string,array{0:string,1:string,2:string}>  $texts  language => [name, preview, detail]
     */
    protected function news(string $code, string $date, array $texts, ?string $picture = null): ?Model
    {
        if (! Soft::available(self::NEWS)) {
            return null;
        }

        $news = new (self::NEWS)(['active_from' => $date, 'preview_picture' => $picture === null ? null : $this->path($picture)]);

        foreach ($texts as $language => [$name, $preview, $detail]) {
            $news->setTranslation('name', $language, $name)
                ->setTranslation('code', $language, $code)
                ->setTranslation('preview_text', $language, '<p>'.$preview.'</p>')
                ->setTranslation('detail_text', $language, $detail);
        }

        $news->save();

        return $news;
    }

    /**
     * A department of the team.
     *
     * @param  array<string,string>  $name  language => name
     */
    protected function department(array $name, int $sort = 500): ?Model
    {
        return Soft::available(self::DEPARTMENT) ? (self::DEPARTMENT)::query()->create(['name' => $name, 'sort' => $sort]) : null;
    }

    /**
     * A person of the team.
     *
     * @param  array<string,array{0:string,1:string,2?:string,3?:string}>  $texts  language => [name, position, summary, biography]
     * @param  array<string,mixed>  $attributes  email, phone, sort
     */
    protected function member(string $code, array $texts, ?Model $department = null, ?string $photo = null, array $attributes = []): ?Model
    {
        if (! Soft::available(self::MEMBER)) {
            return null;
        }

        $member = new (self::MEMBER)(['department_id' => $department?->getKey(), 'preview_picture' => $photo === null ? null : $this->path($photo), ...$attributes]);

        foreach ($texts as $language => $text) {
            $member->setTranslation('name', $language, $text[0])
                ->setTranslation('code', $language, $code)
                ->setTranslation('position', $language, $text[1]);

            if (isset($text[2])) {
                $member->setTranslation('preview_text', $language, '<p>'.$text[2].'</p>');
            }

            if (isset($text[3])) {
                $member->setTranslation('detail_text', $language, $text[3]);
            }
        }

        $member->save();

        return $member;
    }

    /**
     * A form with its fields.
     *
     * @param  array<string,string>  $button  language => button label
     * @param  array<string,string>  $success  language => message after sending
     * @param  array<int,array{0:string,1:string,2:array<string,string>,3?:bool,4?:array<string,string>}>  $fields  [name, type, label per language, required, options per language]
     */
    protected function form(string $code, string $name, array $button, array $success, array $fields): ?Model
    {
        if (! Soft::available(self::FORM)) {
            return null;
        }

        $form = (self::FORM)::query()->create(['code' => $code, 'name' => $name, 'active' => true, 'button_label' => $button, 'success_message' => $success]);

        foreach ($fields as $sort => $field) {
            (self::FORM_FIELD)::query()->create([
                'form_id' => $form->getKey(),
                'name' => $field[0],
                'type' => $field[1],
                'label' => $field[2],
                'required' => $field[3] ?? false,
                'options' => $field[4] ?? null,
                'sort' => ($sort + 1) * 10,
            ]);
        }

        return $form;
    }

    /**
     * An office or a store, with its place on the map.
     *
     * @param  array<int,string>  $phones
     * @param  array{lat:float,lng:float}  $location
     */
    protected function office(string $code, string $name, string $address, array $phones, string $email, array $location, string $hours = '', ?string $picture = null): ?Model
    {
        if (! Soft::available(self::STORE)) {
            return null;
        }

        return (self::STORE)::query()->create([
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'phones' => $phones,
            'email' => $email,
            'location' => [...$location, 'address' => strtok($address, "\n") ?: $address],
            'detail_text' => $hours === '' ? null : '<p>'.e($hours).'</p>',
            'preview_picture' => $picture === null ? null : $this->path($picture),
        ]);
    }

    /**
     * A category of questions with its questions.
     *
     * @param  array<string,array{0:string,1:string}>  $category  language => [name, code]
     * @param  array<int,array<string,array{0:string,1:string}>>  $questions  each language => [question, answer]
     */
    protected function questions(array $category, array $questions, int $sort = 500): void
    {
        if (! Soft::available(self::FAQ_CATEGORY)) {
            return;
        }

        $group = (self::FAQ_CATEGORY)::query()->create([
            'name' => array_map(static fn (array $text): string => $text[0], $category),
            'code' => array_map(static fn (array $text): string => $text[1], $category),
            'sort' => $sort,
        ]);

        foreach ($questions as $index => $texts) {
            (self::QUESTION)::query()->create([
                'category_id' => $group->getKey(),
                'site_id' => $group->getAttribute('site_id'),
                'question' => array_map(static fn (array $text): string => $text[0], $texts),
                'answer' => array_map(static fn (array $text): string => '<p>'.$text[1].'</p>', $texts),
                'sort' => ($index + 1) * 10,
            ]);
        }
    }

    /**
     * A published review of the site, or of a record.
     */
    protected function review(string $author, int $rating, string $text, string $date, ?Model $about = null): ?Model
    {
        if (! Soft::available(self::REVIEW)) {
            return null;
        }

        return (self::REVIEW)::query()->create([
            'reviewable_type' => $about === null ? null : $about::class,
            'reviewable_id' => $about?->getKey(),
            'author_name' => $author,
            'rating' => $rating,
            'text' => $text,
            'status' => 'published',
            'posted_at' => $date,
        ]);
    }
}
