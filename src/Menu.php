<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions;

use Atrium\Admin\Modules\Soft;
use Atrium\Admin\Support\PublicUrl;
use Atrium\Theme\Pages;

/**
 * The site menu a solution lists: pages of the Pages module by code and the
 * lists of modules, in its order, in the theme's shape (label, url,
 * prefix). Items whose page or module is not there are left out.
 */
final class Menu
{
    private const PAGE = 'Atrium\Pages\Models\Page';

    public function __construct(private readonly Pages $pages) {}

    /**
     * @return array<int,array{label:string,url:string,prefix:?string}>
     */
    public function items(Solution $solution): array
    {
        $items = [];

        foreach ($solution->menu as $entry) {
            if (isset($entry['page'])) {
                $item = $this->page($entry['page']);
            } elseif (isset($entry['module'])) {
                $item = $this->module($entry['module']);
            } else {
                $item = null;
            }

            if ($item !== null) {
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * @return array{label:string,url:string,prefix:?string}|null
     */
    private function page(string $code): ?array
    {
        if (! Soft::available(self::PAGE) || ! class_exists('Atrium\Pages\PageTree')) {
            return null;
        }

        $page = self::PAGE::query()->where('code', $code)->where('active', true)->whereNull('parent_id')->first();
        $url = $page === null ? null : app('Atrium\Pages\PageTree')->url($page);

        return $url === null ? null : ['label' => (string) $page->getAttribute('name'), 'url' => $url, 'prefix' => null];
    }

    /**
     * @return array{label:string,url:string,prefix:?string}|null
     */
    private function module(string $class): ?array
    {
        $rule = Soft::available($class) ? PublicUrl::ruleFor($class) : null;

        if ($rule === null || $rule['prefix'] === '') {
            return null;
        }

        return ['label' => $this->pages->label($class), 'url' => $this->pages->indexUrl($rule['prefix']), 'prefix' => $rule['prefix']];
    }
}
