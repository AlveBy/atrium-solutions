<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions;

use Atrium\Admin\Modules\Modules;
use Atrium\Solutions\Console\InstallSolutionCommand;
use Atrium\Solutions\Console\ListSolutionsCommand;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\Translator;

final class SolutionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/atrium-solutions.php', 'atrium-solutions');
        $this->app->singleton(Solutions::class);

        // The solutions of this package; other packages add their folders
        // the same way.
        $this->app['config']->set('atrium-solutions.paths', [
            ...(array) $this->app['config']->get('atrium-solutions.paths', []),
            dirname(__DIR__).'/solutions',
        ]);
    }

    public function boot(): void
    {
        // The installed solution's names for the modules: "Lawyers" for the
        // team, in the admin and on the site.
        $this->callAfterResolving('translator', function (Translator $translator): void {
            $solution = $this->app->make(Solutions::class)->installed();

            foreach ($solution === null ? [] : $solution->labels as $locale => $lines) {
                Labels::apply($translator, $locale, $lines);

                // The site sets the content language as the locale: pt-br.
                if (Support\Country::languageOf($locale) !== $locale) {
                    Labels::apply($translator, Support\Country::languageOf($locale), $lines);
                }
            }
        });

        $solution = $this->app->make(Solutions::class)->installed();

        if ($solution !== null) {
            $this->moveModulePages($solution);

            // The solution's menu instead of one item per module. Composers
            // run in the order they were added: after every provider booted,
            // this one comes after the theme's and has the last word.
            if ($solution->menu !== []) {
                $this->app->booted(fn () => View::composer(['atrium-theme::layout', 'atrium-theme::home', 'atrium-theme::list', 'atrium-theme::page', 'atrium-theme::skins.*', 'atrium-solutions::skins.*'], function (\Illuminate\View\View $view) use ($solution): void {
                    $view->with('menu', $this->app->make(Menu::class)->items($solution));
                }));
            }
        }

        if ($this->app->runningInConsole()) {
            $this->commands([InstallSolutionCommand::class, ListSolutionsCommand::class]);

            $this->publishes([
                __DIR__.'/../config/atrium-solutions.php' => config_path('atrium-solutions.php'),
            ], 'atrium-solutions-config');
        }
    }

    /**
     * The prefixes the solution gives the modules' pages: /offices for the
     * stores of a law firm. Set before the theme registers its routes; the
     * rest of the module's rule stays.
     */
    private function moveModulePages(Solution $solution): void
    {
        $config = $this->app['config'];

        foreach ($solution->urls as $class => $prefix) {
            $rule = $config->get('atrium.public_urls.'.$class)
                ?? $this->app->make(Modules::class)->ownerOf($class)->publicUrls[$class]
                ?? [];

            if (is_array($rule)) {
                $config->set('atrium.public_urls.'.$class, ['prefix' => $prefix] + $rule);
            }
        }
    }
}
