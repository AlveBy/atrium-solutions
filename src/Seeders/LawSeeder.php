<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

namespace Atrium\Solutions\Seeders;

use Atrium\Admin\Modules\Soft;
use Atrium\Solutions\SolutionSeeder;

/**
 * Vellmar Oake, a made-up business law firm: practice areas as pages, the
 * lawyers in two departments, insights, offices, a consultation request,
 * questions about fees and clients' words. English, German and Russian.
 */
final class LawSeeder extends SolutionSeeder
{
    protected function filled(): bool
    {
        return Soft::available(self::PAGE) && (self::PAGE)::query()->withoutGlobalScopes()->where('code', 'practice')->exists();
    }

    protected function fill(): void
    {
        $this->page('home', [
            'en' => ['Business law, plainly done', '<p>Vellmar Oake advises companies, founders and families on deals, disputes and everything in between. Twelve lawyers, two offices, one rule: you always know what happens next and what it costs.</p>'],
            'de' => ['Wirtschaftsrecht, klar gemacht', '<p>Vellmar Oake berät Unternehmen, Gründer und Familien bei Transaktionen, Streitigkeiten und allem dazwischen. Zwölf Anwältinnen und Anwälte, zwei Büros, eine Regel: Sie wissen immer, was als Nächstes passiert und was es kostet.</p>'],
            'ru' => ['Право для бизнеса — понятным языком', '<p>Vellmar Oake консультирует компании, основателей и семьи по сделкам, спорам и всему, что между ними. Двенадцать юристов, два офиса и одно правило: вы всегда знаете, что будет дальше и сколько это стоит.</p>'],
        ], sort: 10);

        $practice = $this->page('practice', [
            'en' => ['Practice areas', '<p>Six teams, each led by a partner who answers your call. Most matters touch two or three of them; we put the right people on it from the first day.</p>'],
            'de' => ['Tätigkeitsbereiche', '<p>Sechs Teams, jedes geführt von einer Partnerin oder einem Partner, die Ihren Anruf selbst annehmen. Die meisten Mandate berühren zwei oder drei davon; wir setzen vom ersten Tag an die richtigen Leute ein.</p>'],
            'ru' => ['Практики', '<p>Шесть команд, каждую ведёт партнёр, который сам отвечает на ваш звонок. Большинство дел касается двух-трёх практик сразу — нужных людей мы подключаем с первого дня.</p>'],
        ], sort: 100, template: 'children');

        $areas = [
            ['corporate', 'Corporate and M&A', 'Gesellschaftsrecht und M&A', 'Корпоративное право и M&A',
                'Formation, shareholder agreements, financing rounds, acquisitions and exits. We read the term sheet before you sign it, not after.',
                'Gründung, Gesellschaftervereinbarungen, Finanzierungsrunden, Übernahmen und Exits. Wir lesen das Term Sheet, bevor Sie unterschreiben, nicht danach.',
                'Создание компаний, корпоративные договоры, раунды инвестиций, покупка и продажа бизнеса. Мы читаем term sheet до того, как вы его подпишете, а не после.'],
            ['disputes', 'Disputes and arbitration', 'Prozesse und Schiedsverfahren', 'Споры и арбитраж',
                'Commercial litigation and arbitration, from the first letter to enforcement. We tell you early when a settlement is the better deal.',
                'Wirtschaftsprozesse und Schiedsverfahren, vom ersten Schreiben bis zur Vollstreckung. Wir sagen Ihnen früh, wenn ein Vergleich das bessere Geschäft ist.',
                'Коммерческие споры и арбитраж — от первой претензии до исполнения решения. Мы заранее скажем, если мировое соглашение выгоднее суда.'],
            ['employment', 'Employment', 'Arbeitsrecht', 'Трудовое право',
                'Contracts, policies, restructuring and the hard conversations. For employers who want to get it right the first time.',
                'Verträge, Richtlinien, Restrukturierungen und die schwierigen Gespräche. Für Arbeitgeber, die es beim ersten Mal richtig machen wollen.',
                'Трудовые договоры, внутренние политики, реорганизация и сложные разговоры. Для работодателей, которые хотят сделать всё правильно с первого раза.'],
            ['real-estate', 'Real estate', 'Immobilienrecht', 'Недвижимость',
                'Leases, purchases, development and construction contracts for offices, shops and warehouses.',
                'Mietverträge, Ankäufe, Projektentwicklung und Bauverträge für Büros, Läden und Lagerhallen.',
                'Аренда, покупка, девелопмент и строительные договоры — для офисов, магазинов и складов.'],
            ['ip', 'Intellectual property', 'Geistiges Eigentum', 'Интеллектуальная собственность',
                'Trademarks, software licences and the contracts that keep your ideas yours.',
                'Marken, Softwarelizenzen und die Verträge, mit denen Ihre Ideen Ihnen gehören.',
                'Товарные знаки, лицензии на ПО и договоры, благодаря которым ваши идеи остаются вашими.'],
            ['private-clients', 'Private clients', 'Privatmandanten', 'Частные клиенты',
                'Wills, succession of family businesses and prenuptial agreements, handled with discretion.',
                'Testamente, Nachfolge in Familienunternehmen und Eheverträge, diskret betreut.',
                'Завещания, передача семейного бизнеса и брачные договоры — конфиденциально.'],
        ];

        foreach ($areas as $index => [$code, $en, $de, $ru, $enText, $deText, $ruText]) {
            $this->page($code, [
                'en' => [$en, '<p>'.$enText.'</p>'],
                'de' => [$de, '<p>'.$deText.'</p>'],
                'ru' => [$ru, '<p>'.$ruText.'</p>'],
            ], $practice, ($index + 1) * 10);
        }

        $this->page('about', [
            'en' => ['About the firm', '<p>Ines Hartmann and Daniel Okafor founded the firm in 2011 after a decade at large firms, to work for clients they could meet in person. We still bill by fixed fee wherever we can and send a plan with every quote.</p><p>We are independent, not part of a network, and recommend other firms when a matter is outside what we do best.</p>'],
            'de' => ['Über die Kanzlei', '<p>Ines Hartmann und Daniel Okafor gründeten die Kanzlei 2011 nach zehn Jahren in Großkanzleien, um für Mandanten zu arbeiten, die sie persönlich kennen. Wo es geht, arbeiten wir bis heute zum Festpreis und schicken mit jedem Angebot einen Plan.</p><p>Wir sind unabhängig, gehören zu keinem Netzwerk und empfehlen andere Kanzleien, wenn ein Mandat nicht zu unseren Stärken passt.</p>'],
            'ru' => ['О фирме', '<p>Инес Хартманн и Дэниел Окафор основали фирму в 2011 году после десяти лет в крупных компаниях — чтобы работать с клиентами, которых знают лично. Мы до сих пор, где возможно, работаем по фиксированной цене и прикладываем план к каждому предложению.</p><p>Мы независимы, не входим в сети и рекомендуем коллег, если задача не наш профиль.</p>'],
        ], sort: 300);

        $partners = $this->department(['en' => 'Partners', 'de' => 'Partner', 'ru' => 'Партнёры'], 100);
        $lawyers = $this->department(['en' => 'Counsel and associates', 'de' => 'Counsel und Associates', 'ru' => 'Юристы'], 200);

        $people = [
            ['ines-hartmann', $partners, 'law-hartmann.svg', 'ines.hartmann@vellmar-oake.example', [
                'en' => ['Ines Hartmann', 'Managing partner, corporate and M&A', 'Twenty years of deals for founders and family companies.', '<p>Ines leads the corporate team. Before founding the firm she spent ten years in the M&A group of an international firm. She teaches contract drafting at the local business school.</p>'],
                'de' => ['Ines Hartmann', 'Managing Partnerin, Gesellschaftsrecht und M&A', 'Zwanzig Jahre Transaktionen für Gründer und Familienunternehmen.', '<p>Ines leitet das Team Gesellschaftsrecht. Vor der Gründung der Kanzlei war sie zehn Jahre in der M&A-Gruppe einer internationalen Kanzlei. Sie unterrichtet Vertragsgestaltung an der örtlichen Business School.</p>'],
                'ru' => ['Инес Хартманн', 'Управляющий партнёр, корпоративное право и M&A', 'Двадцать лет сделок для основателей и семейных компаний.', '<p>Инес руководит корпоративной практикой. До основания фирмы десять лет работала в группе M&A международной юридической компании. Преподаёт договорную работу в местной бизнес-школе.</p>'],
            ]],
            ['daniel-okafor', $partners, 'law-okafor.svg', 'daniel.okafor@vellmar-oake.example', [
                'en' => ['Daniel Okafor', 'Partner, disputes and arbitration', 'Has argued more than ninety commercial cases.', '<p>Daniel represents companies in commercial courts and in arbitration. He is known for settling cases his opponents expected to fight for years.</p>'],
                'de' => ['Daniel Okafor', 'Partner, Prozesse und Schiedsverfahren', 'Hat mehr als neunzig Wirtschaftsprozesse geführt.', '<p>Daniel vertritt Unternehmen vor Handelsgerichten und in Schiedsverfahren. Er ist dafür bekannt, Verfahren zu beenden, auf die sich die Gegenseite für Jahre eingestellt hatte.</p>'],
                'ru' => ['Дэниел Окафор', 'Партнёр, споры и арбитраж', 'Провёл более девяноста коммерческих дел.', '<p>Дэниел представляет компании в коммерческих судах и арбитраже. Известен тем, что заканчивает миром дела, к которым другая сторона готовилась годами.</p>'],
            ]],
            ['mira-lindqvist', $lawyers, 'law-lindqvist.svg', null, [
                'en' => ['Mira Lindqvist', 'Counsel, employment', 'Writes policies people actually read.'],
                'de' => ['Mira Lindqvist', 'Counsel, Arbeitsrecht', 'Schreibt Richtlinien, die tatsächlich gelesen werden.'],
                'ru' => ['Мира Линдквист', 'Советник, трудовое право', 'Пишет политики, которые сотрудники действительно читают.'],
            ]],
            ['tomas-reyes', $lawyers, 'law-reyes.svg', null, [
                'en' => ['Tomás Reyes', 'Senior associate, real estate', 'Leases, purchases and building contracts.'],
                'de' => ['Tomás Reyes', 'Senior Associate, Immobilienrecht', 'Mietverträge, Ankäufe und Bauverträge.'],
                'ru' => ['Томас Рейес', 'Старший юрист, недвижимость', 'Аренда, покупка и строительные договоры.'],
            ]],
            ['yuki-sato', $lawyers, 'law-sato.svg', null, [
                'en' => ['Yuki Sato', 'Associate, intellectual property', 'Trademarks and software licences.'],
                'de' => ['Yuki Sato', 'Associate, Geistiges Eigentum', 'Marken und Softwarelizenzen.'],
                'ru' => ['Юки Сато', 'Юрист, интеллектуальная собственность', 'Товарные знаки и лицензии на ПО.'],
            ]],
            ['olga-petrova', $lawyers, 'law-petrova.svg', null, [
                'en' => ['Olga Petrova', 'Associate, private clients', 'Wills, succession and family agreements.'],
                'de' => ['Olga Petrova', 'Associate, Privatmandanten', 'Testamente, Nachfolge und Familienverträge.'],
                'ru' => ['Ольга Петрова', 'Юрист, частные клиенты', 'Завещания, наследование и семейные соглашения.'],
            ]],
        ];

        foreach ($people as $index => [$code, $department, $photo, $email, $texts]) {
            $this->member($code, $texts, $department, $photo, ['email' => $email, 'sort' => ($index + 1) * 10]);
        }

        $this->news('new-employment-rules', '2026-10-28 09:00:00', [
            'en' => ['What the new remote work rules mean for employers', 'From January, employers must put remote work arrangements in writing. A checklist for the next two months.', '<p>From January, every remote or hybrid arrangement must be agreed in writing, with the place of work, the hours a person can be reached and who pays for the equipment.</p><p>Most employers can cover this with a one-page addendum. We have drafted one for our clients; ask us for a copy.</p>'],
            'de' => ['Was die neuen Regeln zur Telearbeit für Arbeitgeber bedeuten', 'Ab Januar müssen Arbeitgeber Telearbeit schriftlich vereinbaren. Eine Checkliste für die nächsten zwei Monate.', '<p>Ab Januar muss jede Remote- oder Hybridvereinbarung schriftlich festgehalten werden: Arbeitsort, Erreichbarkeit und wer die Ausstattung bezahlt.</p><p>Den meisten Arbeitgebern genügt dafür ein einseitiger Nachtrag. Wir haben einen für unsere Mandanten entworfen; fragen Sie uns nach einem Exemplar.</p>'],
            'ru' => ['Новые правила удалённой работы: что делать работодателю', 'С января договорённости об удалённой работе нужно оформлять письменно. Чек-лист на ближайшие два месяца.', '<p>С января любой удалённый или гибридный формат нужно закрепить письменно: место работы, часы, когда сотрудник на связи, и кто платит за технику.</p><p>Большинству работодателей хватит допсоглашения на одну страницу. Мы подготовили шаблон для клиентов — запросите копию.</p>'],
        ]);
        $this->news('arbitration-win', '2026-09-15 09:00:00', [
            'en' => ['Arbitration award for a logistics client', 'The tribunal awarded our client the full amount claimed under a warehouse contract, with costs.', '<p>After eighteen months, the tribunal found for our client on every point of a dispute over a warehouse services contract, and ordered the other side to pay the costs of the proceedings.</p>'],
            'de' => ['Schiedsspruch für einen Logistikmandanten', 'Das Schiedsgericht sprach unserem Mandanten den vollen Betrag aus einem Lagervertrag zu, samt Kosten.', '<p>Nach achtzehn Monaten gab das Schiedsgericht unserem Mandanten in allen Punkten eines Streits über einen Lagerdienstleistungsvertrag recht und legte der Gegenseite die Verfahrenskosten auf.</p>'],
            'ru' => ['Арбитраж в пользу логистической компании', 'Трибунал присудил нашему клиенту всю сумму по договору складских услуг и компенсацию расходов.', '<p>Через восемнадцать месяцев трибунал поддержал нашего клиента по всем пунктам спора о договоре складских услуг и возложил расходы на другую сторону.</p>'],
        ]);
        $this->news('trademark-checklist', '2026-08-02 09:00:00', [
            'en' => ['Five trademark checks before you launch a product', 'A name that is free today may not be free in the countries you sell to next year.', '<p>Search the register in every market you plan to enter, check domain names and social handles, and file before you announce. It costs less than a rebrand.</p>'],
            'de' => ['Fünf Markenprüfungen vor dem Produktstart', 'Ein Name, der heute frei ist, ist es in Ihren Exportmärkten nächstes Jahr vielleicht nicht mehr.', '<p>Prüfen Sie das Register in jedem geplanten Markt, sichern Sie Domains und Social-Media-Namen und melden Sie an, bevor Sie ankündigen. Das kostet weniger als ein Rebranding.</p>'],
            'ru' => ['Пять проверок товарного знака перед запуском', 'Название, свободное сегодня, может оказаться занятым в странах, куда вы выйдете через год.', '<p>Проверьте реестры всех рынков, куда планируете выходить, займите домены и аккаунты в соцсетях и подайте заявку до анонса. Это дешевле ребрендинга.</p>'],
        ]);
        $this->news('new-office', '2026-06-20 09:00:00', [
            'en' => ['Our second office opens in the Harbour District', 'More room for the disputes team and meeting rooms closer to our logistics clients.', '<p>The new office on Quay Street has four meeting rooms and a hearing room for arbitrations. The Old Town office stays where it is.</p>'],
            'de' => ['Unser zweites Büro im Hafenviertel ist eröffnet', 'Mehr Platz für das Prozessteam und Besprechungsräume näher an unseren Logistikmandanten.', '<p>Das neue Büro in der Quay Street hat vier Besprechungsräume und einen Verhandlungsraum für Schiedsverfahren. Das Büro in der Altstadt bleibt, wo es ist.</p>'],
            'ru' => ['Второй офис открылся в Портовом квартале', 'Больше места для команды по спорам и переговорные ближе к логистическим клиентам.', '<p>В новом офисе на Quay Street четыре переговорные и зал для арбитражных слушаний. Офис в Старом городе остаётся на месте.</p>'],
        ]);

        $this->office('old-town', 'Old Town office', "8 Chancery Lane, Old Town\nNortham", ['+1 555 0140'], 'office@vellmar-oake.example', ['lat' => 52.5163, 'lng' => 13.3777], 'Monday to Friday, 9:00–18:00');
        $this->office('harbour', 'Harbour office', "21 Quay Street, Harbour District\nNortham", ['+1 555 0141'], 'harbour@vellmar-oake.example', ['lat' => 52.5009, 'lng' => 13.4426], 'Monday to Friday, 9:00–18:00');

        $this->form('consultation', 'Consultation request', [
            'en' => 'Request a consultation', 'de' => 'Beratung anfragen', 'ru' => 'Записаться на консультацию',
        ], [
            'en' => 'Thank you. A lawyer will call you within one working day.',
            'de' => 'Vielen Dank. Eine Anwältin oder ein Anwalt ruft Sie innerhalb eines Werktags an.',
            'ru' => 'Спасибо! Юрист перезвонит вам в течение рабочего дня.',
        ], [
            ['name', 'text', ['en' => 'Your name', 'de' => 'Ihr Name', 'ru' => 'Ваше имя'], true],
            ['phone', 'tel', ['en' => 'Phone', 'de' => 'Telefon', 'ru' => 'Телефон'], true],
            ['email', 'email', ['en' => 'Email', 'de' => 'E-Mail', 'ru' => 'Эл. почта']],
            ['area', 'select', ['en' => 'Area', 'de' => 'Bereich', 'ru' => 'Направление'], false, [
                'en' => "Corporate and M&A\nDisputes\nEmployment\nReal estate\nIntellectual property\nPrivate clients",
                'de' => "Gesellschaftsrecht und M&A\nProzesse\nArbeitsrecht\nImmobilienrecht\nGeistiges Eigentum\nPrivatmandanten",
                'ru' => "Корпоративное право и M&A\nСпоры\nТрудовое право\nНедвижимость\nИнтеллектуальная собственность\nЧастные клиенты",
            ]],
            ['message', 'textarea', ['en' => 'Briefly, what is it about?', 'de' => 'Worum geht es, kurz?', 'ru' => 'Коротко о вопросе'], false],
            ['consent', 'checkbox', ['en' => 'I agree that my data is used to answer this request', 'de' => 'Ich bin einverstanden, dass meine Daten zur Beantwortung verwendet werden', 'ru' => 'Согласен на обработку данных для ответа на запрос'], true],
        ]);

        $this->questions(['en' => ['Fees and first meeting', 'fees'], 'de' => ['Honorar und erstes Gespräch', 'honorar'], 'ru' => ['Стоимость и первая встреча', 'stoimost']], [
            ['en' => ['Is the first consultation free?', 'The first 30 minutes are free, in person or by video. We tell you then whether we can help and what it would cost.'],
                'de' => ['Ist die Erstberatung kostenlos?', 'Die ersten 30 Minuten sind kostenlos, vor Ort oder per Video. Danach wissen Sie, ob wir helfen können und was es kostet.'],
                'ru' => ['Первая консультация бесплатная?', 'Первые 30 минут бесплатно — лично или по видео. За это время скажем, можем ли помочь и сколько это будет стоить.']],
            ['en' => ['Do you work for a fixed fee?', 'For most transactions and contracts, yes. Disputes are billed by the hour with a monthly cap you agree in advance.'],
                'de' => ['Arbeiten Sie zum Festpreis?', 'Bei den meisten Transaktionen und Verträgen ja. Prozesse rechnen wir nach Stunden ab, mit einer vorab vereinbarten monatlichen Obergrenze.'],
                'ru' => ['Вы работаете по фиксированной цене?', 'По большинству сделок и договоров — да. Споры оплачиваются почасово, с месячным лимитом, который согласуем заранее.']],
            ['en' => ['Which languages do you work in?', 'English, German and Russian; contracts can be bilingual.'],
                'de' => ['In welchen Sprachen arbeiten Sie?', 'Englisch, Deutsch und Russisch; Verträge auch zweisprachig.'],
                'ru' => ['На каких языках вы работаете?', 'На английском, немецком и русском; договоры можем делать двуязычными.']],
        ]);

        $this->review('Clara Weiss, founder of a software company', 5, 'They explained a 60-page investment agreement in one meeting and found the two clauses that mattered.', '2026-10-02 10:00:00');
        $this->review('Viktor Lang, logistics director', 5, 'Eighteen months of arbitration, and we never had to chase them for an update.', '2026-09-20 10:00:00');
        $this->review('Anja and Paul Brenner', 5, 'Our succession plan took three meetings and a fixed fee, exactly as promised.', '2026-07-11 10:00:00');
    }
}
