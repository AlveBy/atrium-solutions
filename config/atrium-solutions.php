<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Installed solution
    |--------------------------------------------------------------------------
    |
    | The ready-made site this project was made from: its names for the
    | modules ("Lawyers" for the team) apply in the admin and on the site.
    | `atrium:solution:install law` sets ATRIUM_SOLUTION in .env. Empty for a
    | project that was not made from one.
    |
    */
    'installed' => env('ATRIUM_SOLUTION'),

    /*
    |--------------------------------------------------------------------------
    | Where solutions are
    |--------------------------------------------------------------------------
    |
    | Folders with one sub-folder per solution, each holding a solution.json.
    | Packages add theirs in their service provider; a project may add a
    | folder of its own here.
    |
    */
    'paths' => [],

];
