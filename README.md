# Atrium Solutions

Ready-made sites for [Atrium](https://github.com/AlveBy/atrium). A solution is a whole site in one command: the modules it needs, its look, its languages, its menu and its demo content, with a made-up brand you replace with your own.

| Solution | Site | Modules |
|---|---|---|
| `law` | Vellmar Oake, a law firm: practice areas, lawyers by department, insights, offices, a consultation form, fees FAQ, client reviews | pages, team, news, stores, forms, faq, reviews |

More are on the way; `php artisan atrium:solution:list` shows the ones your version has.

## Install

```bash
composer require alveby/atrium-solutions alveby/atrium-pages alveby/atrium-team alveby/atrium-news alveby/atrium-stores alveby/atrium-forms alveby/atrium-faq alveby/atrium-reviews
php artisan atrium:solution:install law
```

`atrium:solution:list` prints every solution and the `composer require` line for the modules a solution still lacks. `atrium:solution:install NAME`:

1. installs the solution's modules (`atrium:module:install`) and turns the others off, keeping their data; `--keep-modules` leaves them on;
2. runs the migrations;
3. turns on the solution's content languages (the first one is the default) and names the site after the brand;
4. sets `ATRIUM_SOLUTION` (and `ATRIUM_THEME_SKIN` when the theme has the solution's skin) in `.env`, and `APP_NAME` while it still says Laravel;
5. fills the site with its content in English, German and Russian. Run it again and nothing is doubled: a site that has the content already is left alone.

Install a solution into a fresh project: one solution per installation. Everything it adds is ordinary content, edited in the admin like any other.

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
└── demo/            # pictures, copied to the uploads disk under demo/
```

```json
{
    "name": "law",
    "label": {"en": "Law firm", "de": "Anwaltskanzlei", "ru": "Юридическая фирма"},
    "description": {"en": "…", "de": "…", "ru": "…"},
    "brand": "Vellmar Oake",
    "skin": "law",
    "modules": ["alveby/atrium-pages", "alveby/atrium-team"],
    "languages": ["en", "de", "ru"],
    "seeder": "Atrium\\Solutions\\Seeders\\LawSeeder",
    "labels": {"en": {"atrium-team::team.modules.members": "Lawyers"}},
    "urls": {"Atrium\\Team\\Models\\Member": "lawyers"},
    "menu": [{"page": "practice"}, {"module": "Atrium\\Team\\Models\\Member"}],
    "pages": ["/", "/practice", "/lawyers"]
}
```

The seeder extends `Atrium\Solutions\SolutionSeeder`: `filled()` says whether the content is there already, `fill()` puts it in with helpers that skip a module that is not installed (`page()`, `news()`, `department()`, `member()`, `form()`, `office()`, `questions()`, `review()`). Add the folder that holds your solutions to `config('atrium-solutions.paths')`.

## Tests

From the repository root:

```bash
vendor/bin/phpunit packages/solutions/tests
```

The tests check every shipped `solution.json`, install the law firm into a copy of the config and `.env`, and open every page it lists in three languages.

## Contributing

This package lives in `packages/solutions` of [AlveBy/atrium](https://github.com/AlveBy/atrium). Issues and pull requests go there.

## License

MIT
