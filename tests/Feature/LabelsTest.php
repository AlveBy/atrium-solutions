<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Feature;

use Atrium\Solutions\Labels;
use Atrium\Solutions\Tests\TestCase;
use Illuminate\Translation\Translator;

/**
 * The solution's own names for the modules' translations.
 */
final class LabelsTest extends TestCase
{
    private function translator(): Translator
    {
        $translator = $this->app->make('translator');
        $this->assertInstanceOf(Translator::class, $translator);

        return $translator;
    }

    public function test_a_line_is_replaced_and_the_rest_of_its_file_is_kept(): void
    {
        $translator = $this->translator();

        Labels::apply($translator, 'en', ['atrium-team::team.modules.members' => 'Lawyers']);

        $this->assertSame('Lawyers', $translator->get('atrium-team::team.modules.members', [], 'en'));
        $this->assertSame('Departments', $translator->get('atrium-team::team.modules.departments', [], 'en'));
        $this->assertSame('Add a person', $translator->get('atrium-team::team.create.member', [], 'en'));
    }

    public function test_lines_of_several_groups_and_languages_apply_each_to_its_own(): void
    {
        $translator = $this->translator();

        Labels::apply($translator, 'en', [
            'atrium-team::team.modules.members' => 'Lawyers',
            'atrium-team::team.create.member' => 'Add a lawyer',
        ]);
        Labels::apply($translator, 'de', ['atrium-team::team.modules.members' => 'Anwälte']);

        $this->assertSame('Lawyers', $translator->get('atrium-team::team.modules.members', [], 'en'));
        $this->assertSame('Add a lawyer', $translator->get('atrium-team::team.create.member', [], 'en'));
        $this->assertSame('Anwälte', $translator->get('atrium-team::team.modules.members', [], 'de'));
        $this->assertSame('Команда', $translator->get('atrium-team::team.modules.members', [], 'ru'), 'Another language keeps its own line.');
    }

    public function test_a_key_without_a_group_or_an_item_is_skipped_and_the_others_still_apply(): void
    {
        $translator = $this->translator();

        Labels::apply($translator, 'en', [
            'atrium-team::team' => 'a whole group',
            'justaword' => 'no group at all',
            'atrium-team::team.modules.members' => 'Lawyers',
        ]);

        $this->assertSame('Lawyers', $translator->get('atrium-team::team.modules.members', [], 'en'));
        $this->assertSame('Departments', $translator->get('atrium-team::team.modules.departments', [], 'en'));
        $this->assertSame('justaword', $translator->get('justaword', [], 'en'));
    }
}
