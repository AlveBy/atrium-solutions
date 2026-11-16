# Atrium Solutions

Ready-made sites for [Atrium](https://github.com/AlveBy/atrium). A solution is a whole site in one command: the modules it needs, its look, its languages, its menu and its demo content, with a made-up brand you replace with your own.

| Solution | Site | Modules |
|---|---|---|
| `law` | A business law firm: practice areas, lawyers by department, insights, offices, a consultation form, fees FAQ, client reviews | pages, team, news, stores, forms, faq, reviews |

More are on the way; `php artisan atrium:solution:list` shows the ones your version has.

## Install

```bash
composer require alveby/atrium-solutions alveby/atrium-pages alveby/atrium-team alveby/atrium-news alveby/atrium-stores alveby/atrium-forms alveby/atrium-faq alveby/atrium-reviews
php artisan atrium:solution:install law --locale=de
```

`atrium:solution:list` prints every solution and the `composer require` line for the modules a solution still lacks. `atrium:solution:install NAME`:

1. installs the solution's modules (`atrium:module:install`) and turns the others off, keeping their data; `--keep-modules` leaves them on;
2. runs the migrations;
3. turns on the solution's content languages, the installer's first and the default, and names the site after the brand;
4. sets `ATRIUM_SOLUTION` (and `ATRIUM_THEME_SKIN` when the theme has the solution's skin) in `.env`, and `APP_NAME` while it still says Laravel;
5. fills the site with its content in all 15 languages. Run it again and nothing is doubled: a site that has the content already is left alone.

Install a solution into a fresh project: one solution per installation. Everything it adds is ordinary content, edited in the admin like any other.

## A business in your country

`--locale` (or `APP_LOCALE` when it is not given) says where the business is. `--locale=de` gives a German law firm: a German name, a Berlin address and phone numbers, prices in euros written the German way, and an Impressum and a Datenschutzerklärung; `--locale=ja` a firm in Fukuoka with prices in yen and a プライバシーポリシー; `--locale=pt_BR` one in Curitiba with a política de privacidade. The other 14 languages are translations of that same firm. A locale it does not know gives the American firm.

Every name, address, phone number and register entry is made up: phone numbers come from ranges kept for fiction or unassigned, and the legal pages are marked as sample texts, not legal advice. Have your own written before the site goes live.

The languages: en (United States, USD), ru (Russia, RUB), uk (Ukraine, UAH), be (Belarus, BYN), kk (Kazakhstan, KZT), uz (Uzbekistan, UZS), de (Germany, EUR), fr (France, EUR), es (Spain, EUR), pt_BR (Brazil, BRL), it (Italy, EUR), pl (Poland, PLN), tr (Türkiye, TRY), zh_CN (China, CNY), ja (Japan, JPY).

## What a solution changes

A solution renames modules to fit the site ("Lawyers" for the team, "Insights" for the news, "Offices" for the stores), in the admin and on the site; moves their pages (`/lawyers`, `/insights`, `/offices`); and gives the site its own menu (practice areas, lawyers, insights, about, offices). All of it comes from the solution's `solution.json` while `ATRIUM_SOLUTION` names it; remove the variable and the modules are themselves again.

## The catalogue

```bash
php artisan atrium:solution:list --json
```

prints the catalogue: for each solution its name, label and description in every language, brand, skin, modules, languages, palette, fonts, screenshots (paths in the solution's folder), the pages worth opening, tags and the install command.

## A solution of your own

A solution is a folder with a `solution.json`, a seeder and its pictures:

```
solutions/law/
├── solution.json
├── content/         # en.php, de.php, … one file per language
└── demo/            # pictures, copied to the uploads disk under demo/
```

```json
{
    "name": "law",
    "brand": "Whitaker Brennan LLP",
    "skin": "law",
    "modules": ["alveby/atrium-pages", "alveby/atrium-team"],
    "seeder": "Atrium\\Solutions\\Seeders\\LawSeeder",
    "urls": {"Atrium\\Team\\Models\\Member": "lawyers"},
    "menu": [{"page": "practice"}, {"module": "Atrium\\Team\\Models\\Member"}],
    "pages": ["/", "/practice", "/lawyers"]
}
```

Texts are in `content/{locale}.php`, each written for its country rather than translated: `brand`, `company` (for e-mail addresses), `catalogue` (label and description), `labels` (the solution's names for the modules), `legal` (that country's legal pages) and the site's texts. The main language's file describes the business; the others speak of it through placeholders the seeder fills in (`:brand`, `:city`, `:founder`, `:fee_hour`…). People, addresses, phone numbers and prices come from `Atrium\Solutions\Support\Country`. A test checks that every file has the keys and placeholders of English.

The seeder extends `Atrium\Solutions\SolutionSeeder`: `filled()` says whether the content is there already, `fill()` puts it in with helpers that skip a module that is not installed (`page()`, `news()`, `department()`, `member()`, `form()`, `office()`, `questions()`, `review()`). Add the folder that holds your solutions to `config('atrium-solutions.paths')`.

## Tests

From the repository root:

```bash
vendor/bin/phpunit packages/solutions/tests
```

The tests check every shipped `solution.json`, install the law firm into a copy of the config and `.env`, open every page it lists, and install it for Germany, Japan, Russia, Brazil and an unknown locale.

## Contributing

This package lives in `packages/solutions` of [AlveBy/atrium](https://github.com/AlveBy/atrium). Issues and pull requests go there.

## License

MIT
