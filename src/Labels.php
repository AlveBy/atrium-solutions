<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions;

use Illuminate\Translation\Translator;

/**
 * Lines of the modules' translations that a solution says differently.
 * Each group is loaded before its lines are replaced: lines added to a
 * group that is not loaded yet would keep the rest of its file from loading.
 */
final class Labels
{
    /**
     * @param  array<string,string>  $lines  "atrium-team::team.modules.members" => "Lawyers"
     */
    public static function apply(Translator $translator, string $locale, array $lines): void
    {
        $groups = [];

        foreach ($lines as $key => $text) {
            [$namespace, $group, $item] = $translator->parseKey($key);

            if ($group === null || $item === null) {
                continue;
            }

            $groups[$namespace][$group][$item] = $text;
        }

        foreach ($groups as $namespace => $items) {
            foreach ($items as $group => $texts) {
                $translator->load($namespace, $group, $locale);

                $prefixed = [];

                foreach ($texts as $item => $text) {
                    $prefixed[$group.'.'.$item] = $text;
                }

                $translator->addLines($prefixed, $locale, $namespace);
            }
        }
    }
}
