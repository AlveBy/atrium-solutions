<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

/*
 * The law firm in English, written for the United States.
 *
 * When English is the installer's language, the firm is an American one:
 * `brand` and `company` name it, and `legal` gives its privacy page. In any
 * other installation these texts are the English version of that country's
 * firm, so they speak of it through placeholders the seeder fills in:
 *
 *   :brand        the firm's name           :city      its city
 *   :founder      the managing partner      :founder_first
 *   :partner      the second partner        :partner_first
 *   :founded      the year it was founded   :phone, :email
 *   :fee_hour     an hour of a partner      :fee_review  a contract review
 *
 * Prices are amounts of the country's currency, written its way.
 */
return [
    'brand' => 'Whitaker Brennan LLP',
    'company' => 'whitakerbrennan',

    'catalogue' => [
        'label' => 'Law firm',
        'description' => 'A business law firm: practice areas, lawyers by department, insights, offices on the map, a consultation request, fees explained and clients\' words.',
    ],

    'labels' => [
        'atrium-team::team.modules.members' => 'Lawyers',
        'atrium-news::news.module' => 'Insights',
        'atrium-stores::stores.module' => 'Offices',
    ],

    'home' => ['Business law, plainly done', '<p>:brand advises companies, founders and families in :city on deals, disputes and everything in between. Twelve lawyers, two offices, one rule: you always know what happens next and what it costs.</p>'],

    'practice' => ['Practice areas', '<p>Six teams, each led by a lawyer who answers your call. Most matters touch two or three of them; we put the right people on yours from the first day.</p>'],

    'areas' => [
        'corporate' => ['Corporate and M&A', 'Formation, shareholder agreements, financing rounds, acquisitions and exits. We read the term sheet before you sign it, not after.'],
        'disputes' => ['Disputes and arbitration', 'Commercial litigation and arbitration, from the first demand letter to enforcement. We tell you early when a settlement is the better deal.'],
        'employment' => ['Employment', 'Offer letters, handbooks, restructuring and the hard conversations. For employers who want to get it right the first time.'],
        'real-estate' => ['Real estate', 'Leases, purchases, development and construction contracts for offices, shops and warehouses.'],
        'ip' => ['Intellectual property', 'Trademarks, software licenses and the contracts that keep your ideas yours.'],
        'private-clients' => ['Private clients', 'Wills and trusts, succession of family businesses and prenuptial agreements, handled with discretion.'],
    ],

    'about' => ['About the firm', '<p>:founder and :partner founded the firm in :founded after a decade at large firms, to work for clients they could meet in person. We still bill a flat fee wherever we can and send a plan with every quote.</p><p>We are independent, not part of a network, and we refer you to another firm when a matter is outside what we do best.</p>'],

    'departments' => [
        'partners' => 'Partners',
        'associates' => 'Counsel and associates',
    ],

    'people' => [
        'managing-partner' => ['Managing partner, corporate and M&A', 'Twenty years of deals for founders and family companies.', '<p>:founder_first leads the corporate team. Before founding the firm she spent ten years in the M&A group of a national firm, and she teaches contract drafting at the local business school.</p>'],
        'disputes-partner' => ['Partner, disputes and arbitration', 'Has tried more than ninety commercial cases.', '<p>:partner_first represents companies in state and federal court and in arbitration. He is known for settling cases the other side expected to fight for years.</p>'],
        'employment' => ['Counsel, employment', 'Writes handbooks people actually read.'],
        'real-estate' => ['Senior associate, real estate', 'Leases, purchases and construction contracts.'],
        'ip' => ['Associate, intellectual property', 'Trademarks and software licenses.'],
        'private-clients' => ['Associate, private clients', 'Wills, trusts and family agreements.'],
    ],

    'news' => [
        'remote-work' => ['What the new remote work rules mean for employers', 'From January, remote work arrangements have to be in writing. A checklist for the next two months.', '<p>From January, every remote or hybrid arrangement has to be agreed in writing: where the person works, the hours they can be reached and who pays for the equipment.</p><p>Most employers can cover it with a one-page addendum. We have drafted one for our clients; ask us for a copy.</p>'],
        'arbitration' => ['Arbitration award for a logistics client', 'The tribunal awarded our client the full amount claimed under a warehouse contract, with costs.', '<p>After eighteen months, the tribunal found for our client on every point of a dispute over a warehousing contract, and ordered the other side to pay the costs of the proceedings.</p>'],
        'trademarks' => ['Five trademark checks before you launch a product', 'A name that is free today may be taken in the markets you sell to next year.', '<p>Search the register in every market you plan to enter, check domain names and social handles, and file before you announce. It costs less than a rebrand.</p>'],
        'new-office' => ['Our second office is open', 'More room for the disputes team, and meeting rooms closer to our logistics clients.', '<p>The new office has four meeting rooms and a hearing room for arbitrations. Our first office stays where it is.</p>'],
    ],

    'office' => ':brand, :district',
    'hours' => 'Monday to Friday, 9 am to 6 pm',

    'form' => [
        'name' => 'Consultation request',
        'button' => 'Request a consultation',
        'success' => 'Thank you. A lawyer will call you within one business day.',
        'fields' => [
            'name' => 'Your name',
            'phone' => 'Phone',
            'email' => 'Email',
            'area' => 'Practice area',
            'message' => 'Briefly, what is it about?',
            'consent' => 'I agree that my details are used to answer this request',
        ],
    ],

    'faq' => [
        'name' => 'Fees and the first meeting',
        'code' => 'fees',
        'questions' => [
            ['Is the first consultation free?', 'The first 30 minutes are free, in person or by video. By the end of them you know whether we can help and what it would cost.'],
            ['What does an hour of a partner cost?', ':fee_hour an hour. Most contracts are reviewed for a flat fee from :fee_review.'],
            ['Do you work for a flat fee?', 'For most transactions and contracts, yes. Disputes are billed by the hour with a monthly cap you agree to in advance.'],
        ],
    ],

    'reviews' => [
        [':name, founder of a software company', 'They explained a 60-page investment agreement in one meeting and found the two clauses that mattered.'],
        [':name, operations director at a logistics company', 'Eighteen months of arbitration, and we never once had to chase them for an update.'],
        [':name and family', 'Our succession plan took three meetings and a flat fee, exactly as promised.'],
    ],

    'legal' => [
        'privacy' => ['Privacy policy', '<p><strong>A sample text for a demo site, not legal advice.</strong> Have your own policy written for your business.</p><h2>Who we are</h2><p>:brand, :address. Questions about your data: :email.</p><h2>What we collect</h2><p>When you send the consultation request we receive your name, phone number, email address and what you write. We use them only to answer your request and keep them for as long as the matter needs, then delete them.</p><h2>Your choices</h2><p>You may ask us what we hold about you, to correct it or to delete it. California residents have the rights the CCPA gives them; we do not sell personal information.</p><h2>Cookies</h2><p>This site sets only the cookies it needs to work.</p>'],
    ],
];
