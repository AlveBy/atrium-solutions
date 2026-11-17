<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Feature;

use Atrium\Admin\Models\Language;
use Atrium\Admin\Support\ContentLanguages;
use Atrium\Admin\Support\Sites;
use Atrium\Solutions\Solution;
use Atrium\Solutions\SolutionSeeder;
use Atrium\Solutions\Tests\Support\ProbeSeeder;
use Atrium\Solutions\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * What every solution seeder does before its own content: the languages and
 * the site, the files, the placeholders and the texts.
 */
final class SeederBaseTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir().'/atrium-seeder-'.uniqid();
        mkdir($this->root.'/demo/folder', 0777, true);
        mkdir($this->root.'/content', 0777, true);
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->root);

        parent::tearDown();
    }

    /**
     * @param  array<string,mixed>  $content
     */
    private function write(string $locale, array $content): void
    {
        file_put_contents("{$this->root}/content/{$locale}.php", '<?php return '.var_export($content, true).';');
    }

    private function probe(?string $locale = 'en', string $brand = 'Solution Brand'): ProbeSeeder
    {
        $solution = new Solution(name: 'probe', path: $this->root, label: ['en' => 'Probe'], brand: $brand);

        return (new ProbeSeeder)->forSolution($solution, $locale)->setContainer($this->app);
    }

    public function test_it_fills_a_site_that_has_no_content_and_loads_its_texts_files_and_languages(): void
    {
        $this->write('en', ['brand' => 'Content Brand', 'company' => 'cb']);
        $this->write('de', ['brand' => 'Inhalt GmbH']);
        file_put_contents($this->root.'/demo/pic.txt', 'picture');
        file_put_contents($this->root.'/demo/folder/inner.txt', 'not copied');
        $seeder = $this->probe('de');

        $this->assertTrue($seeder->run());

        $this->assertSame(1, $seeder->fills);
        $this->assertSame('picture', Storage::disk('public')->get('demo/pic.txt'));
        Storage::disk('public')->assertMissing('demo/folder/inner.txt');
        $this->assertSame('Inhalt GmbH', app(Sites::class)->default()->name);
        $this->assertSame(['de', 'en'], app(Sites::class)->default()->languages);
        $this->assertSame('Inhalt GmbH', $seeder->invoke('mainText', 'brand'));
        $this->assertSame('Berlin', $seeder->invoke('facts')[':city']);
    }

    public function test_it_adds_nothing_to_a_site_that_has_content(): void
    {
        $this->write('en', ['brand' => 'Content Brand']);
        file_put_contents($this->root.'/demo/pic.txt', 'picture');
        $seeder = $this->probe();
        $seeder->isFilled = true;
        $name = app(Sites::class)->default()->name;

        $this->assertFalse($seeder->run());

        $this->assertSame(0, $seeder->fills);
        Storage::disk('public')->assertMissing('demo/pic.txt');
        $this->assertSame($name, app(Sites::class)->default()->fresh()->name);
    }

    public function test_the_texts_of_a_solution_are_its_content_files_by_locale(): void
    {
        $this->write('en', ['a' => 1]);
        $this->write('pt_BR', ['a' => 2]);
        file_put_contents($this->root.'/content/xx.php', '<?php return ["a" => 3];');

        $content = SolutionSeeder::content($this->probe()->country()->locale === 'en' ? new Solution(name: 'probe', path: $this->root, label: ['en' => 'Probe']) : throw new \LogicException);

        $this->assertSame(['en' => ['a' => 1], 'pt_BR' => ['a' => 2]], $content);
    }

    public function test_the_placeholders_come_from_the_main_country_and_its_texts(): void
    {
        $this->write('en', ['brand' => 'Content Brand', 'company' => 'cb']);

        $facts = $this->probe('en')->with(['en' => ['brand' => 'Content Brand', 'company' => 'cb']])->invoke('facts');

        $this->assertSame([
            ':brand' => 'Content Brand',
            ':city' => 'Portland',
            ':phone' => '+1 503 555 0140',
            ':email' => 'office@cb.com',
            ':address' => '1120 NW Lovejoy Street, Portland, OR 97209',
        ], $facts);
    }

    public function test_without_a_brand_or_a_company_in_the_texts_the_solution_s_are_used(): void
    {
        $facts = $this->probe('en')->with(['en' => []])->invoke('facts');

        $this->assertSame('Solution Brand', $facts[':brand']);
        $this->assertSame('office@probe.com', $facts[':email']);
    }

    public function test_the_main_texts_are_those_of_the_country_then_english_then_none(): void
    {
        $content = ['en' => ['k' => 'english'], 'de' => ['k' => 'deutsch']];

        $this->assertSame(['k' => 'deutsch'], $this->probe('de')->with($content)->invoke('main'));
        $this->assertSame(['k' => 'english'], $this->probe('fr')->with($content)->invoke('main'));
        $this->assertSame([], $this->probe('fr')->with(['de' => ['k' => 'deutsch']])->invoke('main'));
    }

    public function test_a_text_is_given_in_every_language_that_has_it_with_the_placeholders_filled(): void
    {
        $seeder = $this->probe('de')->with([
            'en' => ['t' => 'Hello :brand :extra', 'u' => 'only English'],
            'de' => ['t' => 'Hallo :brand :extra', 'e' => '', 'n' => null, 'l' => []],
            'pt_BR' => ['t' => 'Olá :brand'],
            'ja' => [],
        ], [':brand' => 'Acme']);

        $this->assertSame(
            ['de' => 'Hallo Acme X', 'en' => 'Hello Acme X', 'pt-br' => 'Olá Acme'],
            $seeder->invoke('translated', 't', [':extra' => 'X']),
        );
        $this->assertSame(['en' => 'only English'], $seeder->invoke('translated', 'u'));
        $this->assertSame([], $seeder->invoke('translated', 'e'));
        $this->assertSame([], $seeder->invoke('translated', 'n'));
        $this->assertSame([], $seeder->invoke('translated', 'l'));
        $this->assertSame([], $seeder->invoke('translated', 'missing'));
    }

    public function test_a_text_of_zero_is_kept(): void
    {
        $seeder = $this->probe('en')->with(['en' => ['z' => '0', 'n' => 0, 'f' => false]]);

        $this->assertSame(['en' => '0'], $seeder->invoke('translated', 'z'));
        $this->assertSame(['en' => 0], $seeder->invoke('translated', 'n'));
        $this->assertSame(['en' => false], $seeder->invoke('translated', 'f'));
    }

    public function test_a_list_per_language_becomes_a_language_per_item(): void
    {
        $this->assertSame(
            [['en' => 'a', 'de' => 'x'], ['en' => 'b', 'de' => 'y'], ['en' => 'c']],
            ProbeSeeder::invokeStatic('perItem', ['en' => ['a', 'b', 'c'], 'de' => [3 => 'x', 7 => 'y']]),
        );
        $this->assertSame([], ProbeSeeder::invokeStatic('perItem', []));
    }

    public function test_a_main_text_falls_back_to_english_and_fills_the_placeholders_in(): void
    {
        $seeder = $this->probe('de')->with(
            ['en' => ['a' => 'english a', 'b' => 'english :brand :x'], 'de' => ['a' => 'deutsch a :brand', 'lines' => ['one :x', 'two']]],
            [':brand' => 'Acme', ':x' => 'class'],
        );

        $this->assertSame('deutsch a Acme', $seeder->invoke('mainText', 'a'));
        $this->assertSame('english Acme extra', $seeder->invoke('mainText', 'b', [':x' => 'extra']));
        $this->assertSame(['one extra', 'two'], $seeder->invoke('mainText', 'lines', [':x' => 'extra']));
        $this->assertNull($seeder->invoke('mainText', 'nothing'));
    }

    public function test_a_main_text_of_a_country_without_english_texts_is_missing_not_an_error(): void
    {
        $this->assertNull($this->probe('de')->with(['de' => ['a' => 'x']])->invoke('mainText', 'b'));
        $this->assertSame('x', $this->probe('de')->with(['en' => ['b' => 'x']])->invoke('mainText', 'b'));
    }

    public function test_placeholders_are_replaced_in_strings_and_lists_and_other_values_stay(): void
    {
        $seeder = $this->probe()->with([], [':a' => 'A']);

        $this->assertSame('A and B', $seeder->invoke('fillIn', ':a and :b', [':b' => 'B']));
        $this->assertSame(['A', ['A!', 7, null, true]], $seeder->invoke('fillIn', [':a', [':a!', 7, null, true]]));
        $this->assertSame(7, $seeder->invoke('fillIn', 7));
        $this->assertSame('given', $seeder->invoke('fillIn', ':a', [':a' => 'given']));
    }

    public function test_the_locales_are_the_country_s_first_then_the_texts_without_repeats(): void
    {
        $this->assertSame(['de', 'en', 'ja'], $this->probe('de')->with(['en' => [], 'de' => [], 'ja' => []])->invoke('locales'));
        $this->assertSame(['en'], $this->probe('en')->with(['en' => []])->invoke('locales'));
        $this->assertSame(['fr', 'en'], $this->probe('fr')->with(['en' => []])->invoke('locales'));
    }

    public function test_the_languages_of_the_site_are_the_ones_with_texts_the_main_one_first_and_default(): void
    {
        Language::query()->where('code', 'ru')->delete();
        Language::query()->create(['code' => 'ru', 'name' => 'Custom Russian', 'sort' => 5, 'active' => false, 'is_default' => true]);
        Language::query()->where('code', 'de')->delete();
        $seeder = $this->probe('de')->with(['en' => [], 'de' => [], 'xx' => [], 'pt_BR' => []], [':brand' => '']);

        $seeder->invoke('prepareSite');

        $languages = Language::query()->orderBy('sort')->get();
        $this->assertSame(
            ['de' => [10, true, true, 'Deutsch'], 'en' => [20, true, false, 'English'], 'xx' => [30, true, false, 'XX'], 'pt-br' => [40, true, false, 'Português (Brasil)']],
            $languages->whereIn('code', ['de', 'en', 'xx', 'pt-br'])->mapWithKeys(fn (Language $l): array => [$l->code => [$l->sort, (bool) $l->active, (bool) $l->is_default, $l->name]])->all(),
        );
        $this->assertSame(['de'], $languages->where('is_default', true)->pluck('code')->all(), 'Only the main language is the default.');
        $this->assertSame('Custom Russian', Language::query()->where('code', 'ru')->value('name'));
        $this->assertSame(['de', 'en', 'xx', 'pt-br'], app(Sites::class)->default()->languages);
        $this->assertSame('de', app(Sites::class)->default()->default_language);
        $this->assertSame('Solution Brand', app(Sites::class)->default()->name, 'An empty brand leaves the solution\'s.');
    }

    public function test_a_language_that_exists_keeps_its_name_but_is_switched_on_and_moved(): void
    {
        Language::query()->where('code', 'en')->update(['name' => 'Mine', 'sort' => 99, 'active' => false]);

        $this->probe('en')->with(['en' => []], [':brand' => 'Acme'])->invoke('prepareSite');

        $en = Language::query()->where('code', 'en')->sole();
        $this->assertSame('Mine', $en->name);
        $this->assertSame(10, $en->sort);
        $this->assertTrue((bool) $en->active);
        $this->assertSame('Acme', app(Sites::class)->default()->name);
    }

    public function test_the_site_is_prepared_once_a_second_run_adds_no_language(): void
    {
        $seeder = $this->probe('de')->with(['en' => [], 'de' => []], [':brand' => 'Acme']);

        $seeder->invoke('prepareSite');
        $count = Language::query()->count();
        $seeder->invoke('prepareSite');

        $this->assertSame($count, Language::query()->count());
    }

    public function test_what_the_site_and_the_editors_remember_is_forgotten_once_the_languages_are_set(): void
    {
        $site = app(Sites::class)->default();
        $this->assertSame(['en'], app(ContentLanguages::class)->codes());

        $this->probe('de')->with(['en' => [], 'de' => []], [':brand' => 'Acme'])->invoke('prepareSite');

        $this->assertNotSame($site, app(Sites::class)->default(), 'The sites are read again.');
        $this->assertSame(['de', 'en'], app(ContentLanguages::class)->codes());
        $this->assertSame('de', app(ContentLanguages::class)->default());
    }
}
