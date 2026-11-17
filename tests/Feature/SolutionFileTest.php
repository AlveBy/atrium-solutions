<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Tests\Feature;

use Atrium\Solutions\Solution;
use Atrium\Solutions\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Reading a solution.json and the content files next to it.
 */
final class SolutionFileTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir().'/atrium-solution-'.uniqid();
        mkdir($this->root.'/content', 0777, true);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->root);

        parent::tearDown();
    }

    /**
     * @param  array<string,mixed>|string  $json
     */
    private function read(array|string $json): Solution
    {
        file_put_contents($this->root.'/solution.json', is_string($json) ? $json : json_encode($json));

        return Solution::fromFile($this->root.'/solution.json');
    }

    /**
     * @param  array<string,mixed>  $content
     */
    private function content(string $locale, array $content): void
    {
        file_put_contents("{$this->root}/content/{$locale}.php", '<?php return '.var_export($content, true).';');
    }

    /**
     * @return array<string,array{string}>
     */
    public static function badFiles(): array
    {
        return [
            'not JSON' => ['{'],
            'a number' => ['42'],
            'a string' => ['"law"'],
            'empty' => [''],
            'an empty list' => ['[]'],
            'no name' => ['{"brand":"x"}'],
            'a name that is a number' => ['{"name":123}'],
            'a name that is null' => ['{"name":null}'],
            'capitals' => ['{"name":"Law"}'],
            'a space' => ['{"name":"law firm"}'],
            'a leading digit' => ['{"name":"1law"}'],
            'a leading dash' => ['{"name":"-law"}'],
            'an underscore' => ['{"name":"law_firm"}'],
            'a trailing symbol' => ['{"name":"law!"}'],
            'a slash' => ['{"name":"law/firm"}'],
            'Cyrillic' => ['{"name":"юрист"}'],
            'empty name' => ['{"name":""}'],
        ];
    }

    #[DataProvider('badFiles')]
    public function test_a_file_without_a_proper_name_is_not_a_solution(string $json): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('is not a solution');

        $this->read($json);
    }

    /**
     * @return array<string,array{string}>
     */
    public static function goodNames(): array
    {
        return [
            'a word' => ['law'],
            'one letter' => ['a'],
            'with a digit and a dash' => ['law-2-firm'],
            'a trailing dash' => ['law-'],
        ];
    }

    #[DataProvider('goodNames')]
    public function test_a_lowercase_name_with_digits_and_dashes_is_a_solution(string $name): void
    {
        $solution = $this->read(['name' => $name]);

        $this->assertSame($name, $solution->name);
        $this->assertSame($this->root, $solution->path);
    }

    public function test_a_file_with_only_a_name_gets_the_defaults(): void
    {
        $solution = $this->read(['name' => 'plain']);

        $this->assertSame(['plain'], $solution->label);
        $this->assertSame('plain', Solution::text($solution->label, 'en'));
        $this->assertSame([], $solution->description);
        $this->assertSame('', $solution->brand);
        $this->assertNull($solution->skin);
        $this->assertSame([], $solution->modules);
        $this->assertSame(['en'], $solution->languages);
        $this->assertNull($solution->seeder);
        $this->assertSame([], $solution->labels);
        $this->assertSame([], $solution->palette);
        $this->assertSame([], $solution->fonts);
        $this->assertSame([], $solution->screenshots);
        $this->assertSame(['/'], $solution->pages);
        $this->assertSame([], $solution->tags);
        $this->assertSame([], $solution->urls);
        $this->assertSame([], $solution->menu);
        $this->assertSame([], $solution->brands);
    }

    public function test_the_constructor_s_own_defaults_are_a_site_in_english_with_a_home_page(): void
    {
        $solution = new Solution(name: 'x', path: '/tmp', label: ['en' => 'X']);

        $this->assertSame(['en'], $solution->languages);
        $this->assertSame(['/'], $solution->pages);
        $this->assertSame([], $solution->description);
        $this->assertSame('', $solution->brand);
        $this->assertNull($solution->skin);
        $this->assertNull($solution->seeder);
    }

    public function test_every_field_is_read_and_made_a_string(): void
    {
        $solution = $this->read([
            'name' => 'full',
            'label' => ['en' => 'Full', 'de' => 'Voll', 'fr' => ['nested']],
            'description' => ['en' => 'A full one'],
            'brand' => 'Full Ltd',
            'skin' => 'dark',
            'modules' => ['Alveby/Atrium-Pages', 'alveby/atrium-team', ['nested']],
            'languages' => ['en', 'de'],
            'seeder' => 'App\\FullSeeder',
            'labels' => ['en' => ['atrium-team::team.modules.members' => 'Crew'], 'de' => 'not a list'],
            'palette' => ['accent' => '#fff', 'size' => 12],
            'fonts' => ['Inter', 5],
            'screenshots' => ['desktop' => 'a.png'],
            'pages' => ['/', '/team', 5],
            'tags' => ['b2b'],
            'urls' => ['App\\Team' => 'crew'],
            'menu' => [['page' => 'about'], 'text', ['module' => 'App\\Team'], 7],
        ]);

        $this->assertSame(['en' => 'Full', 'de' => 'Voll'], $solution->label);
        $this->assertSame(['en' => 'A full one'], $solution->description);
        $this->assertSame('Full Ltd', $solution->brand);
        $this->assertSame('dark', $solution->skin);
        $this->assertSame(['alveby/atrium-pages', 'alveby/atrium-team'], $solution->modules);
        $this->assertSame(['en', 'de'], $solution->languages);
        $this->assertSame('App\\FullSeeder', $solution->seeder);
        $this->assertSame(['en' => ['atrium-team::team.modules.members' => 'Crew']], $solution->labels);
        $this->assertSame(['accent' => '#fff', 'size' => '12'], $solution->palette);
        $this->assertSame(['Inter', '5'], $solution->fonts);
        $this->assertSame(['desktop' => 'a.png'], $solution->screenshots);
        $this->assertSame(['/', '/team', '5'], $solution->pages);
        $this->assertSame(['b2b'], $solution->tags);
        $this->assertSame(['App\\Team' => 'crew'], $solution->urls);
        $this->assertSame([['page' => 'about'], ['module' => 'App\\Team']], $solution->menu);
    }

    public function test_a_list_of_nothing_falls_back_to_the_defaults(): void
    {
        $solution = $this->read(['name' => 'empty', 'languages' => [], 'pages' => [], 'label' => [], 'modules' => []]);

        $this->assertSame(['en'], $solution->languages);
        $this->assertSame(['/'], $solution->pages);
        $this->assertSame([], $solution->label);
        $this->assertSame([], $solution->modules);
    }

    public function test_indexes_are_reset_so_lists_stay_lists(): void
    {
        $solution = $this->read([
            'name' => 'lists',
            'modules' => ['a' => 'x', 'b' => 'y'],
            'languages' => ['k' => 'en', 'j' => 'de'],
            'fonts' => ['k' => 'Inter'],
            'pages' => ['k' => '/'],
            'tags' => ['k' => 'b2b'],
            'menu' => ['one' => ['page' => 'about'], 'two' => ['page' => 'team']],
        ]);

        $this->assertSame(['x', 'y'], $solution->modules);
        $this->assertSame(['en', 'de'], $solution->languages);
        $this->assertSame(['Inter'], $solution->fonts);
        $this->assertSame(['/'], $solution->pages);
        $this->assertSame(['b2b'], $solution->tags);
        $this->assertSame([['page' => 'about'], ['page' => 'team']], $solution->menu);
    }

    public function test_the_content_files_give_the_languages_the_texts_and_the_names_of_the_modules(): void
    {
        $this->content('de', [
            'brand' => 'Voll GmbH',
            'catalogue' => ['label' => 'Kanzlei', 'description' => 'Eine Kanzlei'],
            'labels' => ['atrium-team::team.modules.members' => 'Anwälte', 'atrium-news::news.module' => 'Artikel'],
        ]);
        $this->content('en', ['brand' => 'Full Ltd', 'catalogue' => ['label' => 'Law firm', 'description' => 'A law firm']]);
        $this->content('pt_BR', ['brand' => 'Voll Ltda']);
        file_put_contents("{$this->root}/content/xx.php", '<?php return ["brand" => "ignored"];');

        $solution = $this->read([
            'name' => 'files',
            'languages' => ['fr'],
            'label' => ['pt_BR' => 'Escritório', 'fr' => 'Cabinet'],
            'description' => ['fr' => 'Un cabinet'],
            'labels' => ['de' => ['atrium-news::news.module' => 'Beiträge'], 'en' => ['atrium-team::team.modules.members' => 'Lawyers']],
        ]);

        $this->assertSame(['en', 'de', 'pt_BR'], $solution->languages, 'The languages are the files, in the order of Atrium\'s.');
        $this->assertSame(['pt_BR' => 'Escritório', 'fr' => 'Cabinet', 'en' => 'Law firm', 'de' => 'Kanzlei'], $solution->label);
        $this->assertSame(['fr' => 'Un cabinet', 'en' => 'A law firm', 'de' => 'Eine Kanzlei'], $solution->description);
        $this->assertSame(['en' => 'Full Ltd', 'de' => 'Voll GmbH', 'pt_BR' => 'Voll Ltda'], $solution->brands);
        $this->assertSame(
            ['atrium-team::team.modules.members' => 'Anwälte', 'atrium-news::news.module' => 'Beiträge'],
            $solution->labels['de'],
            'The solution.json lines win over the content file\'s.',
        );
        $this->assertSame(['atrium-team::team.modules.members' => 'Lawyers'], $solution->labels['en']);
        $this->assertSame([], $solution->labels['pt_BR']);
        $this->assertArrayNotHasKey('xx', $solution->labels);
    }

    public function test_a_content_file_without_a_catalogue_leaves_the_json_texts_alone(): void
    {
        $this->content('en', []);

        $solution = $this->read(['name' => 'bare', 'label' => ['en' => 'Json label'], 'description' => ['en' => 'Json description']]);

        $this->assertSame(['en' => 'Json label'], $solution->label);
        $this->assertSame(['en' => 'Json description'], $solution->description);
        $this->assertSame([], $solution->brands);
        $this->assertSame(['en'], $solution->languages);
    }

    /**
     * @return array<string,array{?string,string}>
     */
    public static function brands(): array
    {
        return [
            'the country of the locale' => ['de', 'Voll GmbH'],
            'written with a dash' => ['pt-br', 'Voll Ltda'],
            'a country without one gets the English' => ['fr', 'Full Ltd'],
            'an unknown locale too' => ['xx', 'Full Ltd'],
            'no locale' => [null, 'Full Ltd'],
        ];
    }

    #[DataProvider('brands')]
    public function test_the_brand_is_the_business_of_the_installer_s_country(?string $locale, string $expected): void
    {
        $solution = new Solution(name: 'x', path: '/tmp', label: ['en' => 'X'], brand: 'Solution brand', brands: ['en' => 'Full Ltd', 'de' => 'Voll GmbH', 'pt_BR' => 'Voll Ltda']);

        $this->assertSame($expected, $solution->brandIn($locale));
    }

    public function test_without_an_english_brand_the_solution_s_own_is_used(): void
    {
        $solution = new Solution(name: 'x', path: '/tmp', label: ['en' => 'X'], brand: 'Solution brand', brands: ['de' => 'Voll GmbH']);

        $this->assertSame('Solution brand', $solution->brandIn('fr'));
        $this->assertSame('Voll GmbH', $solution->brandIn('de'));
    }

    public function test_a_text_is_the_language_asked_for_english_or_the_first(): void
    {
        $texts = ['fr' => 'Cabinet', 'en' => 'Law firm', 'de' => 'Kanzlei'];

        $this->assertSame('Kanzlei', Solution::text($texts, 'de'));
        $this->assertSame('Law firm', Solution::text($texts, 'ja'));
        $this->assertSame('Cabinet', Solution::text(['fr' => 'Cabinet', 'de' => 'Kanzlei'], 'ja'));
        $this->assertSame('', Solution::text([], 'ja'));
    }

    public function test_a_text_without_a_language_is_in_the_language_of_the_app(): void
    {
        $texts = ['en' => 'Law firm', 'de' => 'Kanzlei'];

        $this->app->setLocale('de');
        $this->assertSame('Kanzlei', Solution::text($texts));

        $this->app->setLocale('fr');
        $this->assertSame('Law firm', Solution::text($texts));
    }

    public function test_a_file_of_the_solution_is_in_its_folder(): void
    {
        $solution = new Solution(name: 'x', path: '/srv/solutions/x', label: []);

        $this->assertSame('/srv/solutions/x/demo/a.svg', $solution->file('demo/a.svg'));
        $this->assertSame('/srv/solutions/x/demo/a.svg', $solution->file('/demo/a.svg'));
        $this->assertSame('/srv/solutions/x/demo/a.svg', $solution->file('///demo/a.svg'));
    }

    public function test_the_catalogue_is_everything_a_website_shows(): void
    {
        $solution = new Solution(
            name: 'x',
            path: '/tmp',
            label: ['en' => 'X'],
            description: ['en' => 'About X'],
            brand: 'X Ltd',
            skin: 'dark',
            modules: ['alveby/atrium-pages'],
            languages: ['en', 'de'],
            seeder: 'App\\XSeeder',
            labels: ['en' => ['a' => 'b']],
            palette: ['accent' => '#fff'],
            fonts: ['Inter'],
            screenshots: ['desktop' => 'a.png'],
            pages: ['/', '/team'],
            tags: ['b2b'],
            urls: ['App\\Team' => 'crew'],
            menu: [['page' => 'about']],
            brands: ['de' => 'X GmbH'],
        );

        $this->assertSame([
            'name' => 'x',
            'label' => ['en' => 'X'],
            'description' => ['en' => 'About X'],
            'brand' => 'X Ltd',
            'skin' => 'dark',
            'modules' => ['alveby/atrium-pages'],
            'languages' => ['en', 'de'],
            'palette' => ['accent' => '#fff'],
            'fonts' => ['Inter'],
            'screenshots' => ['desktop' => 'a.png'],
            'pages' => ['/', '/team'],
            'tags' => ['b2b'],
            'install' => 'php artisan atrium:solution:install x',
        ], $solution->toCatalogue());
    }

    public function test_a_label_written_as_one_text_is_kept_beside_the_ones_of_the_content_files(): void
    {
        $this->content('en', ['catalogue' => ['label' => 'From the file']]);

        $solution = $this->read(['name' => 'probe', 'label' => 'Written once', 'description' => 'Short']);

        $this->assertSame([0 => 'Written once', 'en' => 'From the file'], $solution->label);
        $this->assertSame([0 => 'Short'], $solution->description);
    }

    public function test_the_content_file_names_the_solution_over_the_json_in_its_language_only(): void
    {
        $this->content('en', ['catalogue' => ['label' => 'File label', 'description' => 'File description']]);
        $this->content('de', []);

        $solution = $this->read([
            'name' => 'probe',
            'label' => ['en' => 'Json label', 'de' => 'Json Name', 'fr' => 'Json nom'],
            'description' => ['en' => 'Json description', 'de' => 'Json Beschreibung'],
        ]);

        $this->assertSame(['en' => 'File label', 'de' => 'Json Name', 'fr' => 'Json nom'], $solution->label);
        $this->assertSame(['en' => 'File description', 'de' => 'Json Beschreibung'], $solution->description);
    }

    public function test_the_labels_of_the_content_file_are_overridden_by_those_of_the_json_one_by_one(): void
    {
        $this->content('en', ['labels' => ['a::b.one' => 'File one', 'a::b.two' => 'File two']]);

        $solution = $this->read(['name' => 'probe', 'labels' => ['en' => ['a::b.two' => 'Json two'], 'fr' => ['a::b.one' => 'Un']]]);

        $this->assertSame(['en' => ['a::b.one' => 'File one', 'a::b.two' => 'Json two'], 'fr' => ['a::b.one' => 'Un']], $solution->labels);
    }

    public function test_labels_that_are_not_texts_are_left_out_and_numbers_become_texts(): void
    {
        $solution = $this->read(['name' => 'probe', 'labels' => ['en' => ['a::b.one' => 5, 'a::b.two' => ['x'], 'a::b.three' => 'Three'], 'de' => 'not a list']]);

        $this->assertSame(['en' => ['a::b.one' => '5', 'a::b.three' => 'Three']], $solution->labels);
    }

    public function test_a_skin_and_a_seeder_are_names_even_when_written_as_numbers(): void
    {
        $solution = $this->read(['name' => 'probe', 'skin' => 2, 'seeder' => 7]);

        $this->assertSame('2', $solution->skin);
        $this->assertSame('7', $solution->seeder);
        $this->assertNull($this->read(['name' => 'probe'])->skin);
        $this->assertNull($this->read(['name' => 'probe'])->seeder);
    }

    public function test_a_menu_keeps_its_entries_as_lists_of_texts(): void
    {
        $solution = $this->read(['name' => 'probe', 'menu' => [['page' => 5, 'extra' => ['x']], 'not an entry', ['module' => 'M']]]);

        $this->assertSame([['page' => '5'], ['module' => 'M']], $solution->menu);
    }

    public function test_without_pages_or_languages_the_solution_has_the_home_page_and_english(): void
    {
        $solution = $this->read(['name' => 'probe']);

        $this->assertSame(['/'], $solution->pages);
        $this->assertSame(['en'], $solution->languages);
    }

    public function test_the_languages_of_the_json_are_used_when_there_are_no_content_files(): void
    {
        $this->assertSame(['de', 'fr'], $this->read(['name' => 'probe', 'languages' => ['de', 'fr']])->languages);
        $this->content('ja', []);
        $this->assertSame(['ja'], $this->read(['name' => 'probe', 'languages' => ['de', 'fr']])->languages);
    }
}
