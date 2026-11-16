<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

// Lo studio in italiano, scritto per l’Italia; i segnaposto sono spiegati in content/en.php.
return [
    'brand' => 'Studio Legale Bianchi Ferraresi',
    'company' => 'bianchiferraresi',

    'catalogue' => [
        'label' => 'Studio legale',
        'description' => 'Uno studio legale d’affari: aree di attività, avvocati per dipartimento, approfondimenti, sedi sulla mappa, richiesta di consulenza, onorari spiegati e opinioni dei clienti.',
    ],

    'labels' => [
        'atrium-team::team.modules.members' => 'Avvocati',
        'atrium-news::news.module' => 'Approfondimenti',
        'atrium-stores::stores.module' => 'Sedi',
    ],

    'home' => ['Diritto d’impresa, senza giri di parole', '<p>:brand assiste imprese, fondatori e famiglie a :city in operazioni straordinarie, contenziosi e tutto ciò che sta nel mezzo. Dodici avvocati, due sedi e una sola regola: Lei sa sempre che cosa succederà dopo e quanto Le costerà.</p>'],

    'practice' => ['Aree di attività', '<p>Sei team, ciascuno guidato da un avvocato che risponde personalmente al telefono. La maggior parte delle pratiche ne coinvolge due o tre: fin dal primo giorno assegniamo alla Sua le persone giuste.</p>'],

    'areas' => [
        'corporate' => ['Societario e M&A', 'Costituzioni, patti parasociali, round di investimento, acquisizioni ed exit. Leggiamo il term sheet prima che Lei lo firmi, non dopo.'],
        'disputes' => ['Contenzioso e arbitrato', 'Contenzioso commerciale e arbitrati, dalla prima diffida all’esecuzione. Le diciamo subito quando una transazione è l’affare migliore.'],
        'employment' => ['Diritto del lavoro', 'Contratti, regolamenti aziendali, riorganizzazioni e le conversazioni difficili. Per i datori di lavoro che vogliono fare le cose per bene fin dalla prima volta.'],
        'real-estate' => ['Immobiliare', 'Locazioni commerciali, compravendite, sviluppo immobiliare e appalti per uffici, negozi e magazzini.'],
        'ip' => ['Proprietà intellettuale', 'Marchi, licenze software e i contratti che fanno sì che le Sue idee restino Sue.'],
        'private-clients' => ['Clienti privati', 'Testamenti, patti di famiglia per il passaggio generazionale dell’impresa e convenzioni matrimoniali, con la massima riservatezza.'],
    ],

    'about' => ['Lo studio', '<p>:founder e :partner hanno fondato lo studio nel :founded, dopo dieci anni in grandi studi, per lavorare con clienti da incontrare di persona. Ancora oggi, ovunque sia possibile, lavoriamo a forfait e alleghiamo un piano di lavoro a ogni preventivo.</p><p>Siamo indipendenti, non facciamo parte di alcun network e La indirizziamo a un collega quando una pratica esce da ciò che sappiamo fare meglio.</p>'],

    'departments' => [
        'partners' => 'Soci',
        'associates' => 'Counsel e collaboratori',
    ],

    'people' => [
        'managing-partner' => ['Socia fondatrice, societario e M&A', 'Vent’anni di operazioni per fondatori e imprese di famiglia.', '<p>:founder_first guida il dipartimento societario. Prima di fondare lo studio ha lavorato dieci anni nel team M&A di uno studio nazionale, e insegna redazione dei contratti alla business school della città.</p>'],
        'disputes-partner' => ['Socio, contenzioso e arbitrato', 'Ha seguito oltre novanta cause commerciali.', '<p>:partner_first assiste imprese davanti al Tribunale delle imprese, in Corte d’appello e negli arbitrati. È noto per chiudere con una transazione cause che la controparte si preparava a combattere per anni.</p>'],
        'employment' => ['Counsel, diritto del lavoro', 'Scrive regolamenti aziendali che qualcuno legge davvero.'],
        'real-estate' => ['Senior associate, immobiliare', 'Locazioni, compravendite e appalti.'],
        'ip' => ['Associate, proprietà intellettuale', 'Marchi e licenze software.'],
        'private-clients' => ['Associate, clienti privati', 'Testamenti, successioni e accordi di famiglia.'],
    ],

    'news' => [
        'remote-work' => ['Lavoro agile: che cosa cambia per i datori di lavoro con le nuove regole', 'Da gennaio gli accordi di lavoro da remoto dovranno essere messi per iscritto. Una checklist per i prossimi due mesi.', '<p>Da gennaio ogni accordo di lavoro agile o ibrido dovrà essere formalizzato per iscritto: dove si lavora, in quali fasce orarie si è reperibili e chi paga la dotazione.</p><p>Alla maggior parte dei datori di lavoro basta un’integrazione di una pagina al contratto. Ne abbiamo predisposto un modello per i nostri clienti: ce ne chieda pure una copia.</p>'],
        'arbitration' => ['Lodo arbitrale favorevole a un cliente della logistica', 'Il collegio arbitrale ha riconosciuto al nostro cliente l’intero importo richiesto in base a un contratto di deposito, con le spese.', '<p>Dopo diciotto mesi, il collegio arbitrale ha dato ragione al nostro cliente su ogni punto di una controversia relativa a un contratto di deposito e ha condannato la controparte al pagamento delle spese del procedimento.</p>'],
        'trademarks' => ['Cinque verifiche sul marchio prima di lanciare un prodotto', 'Un nome libero oggi potrebbe essere già registrato l’anno prossimo nei mercati in cui venderà.', '<p>Faccia una ricerca di anteriorità in ogni mercato in cui intende entrare, controlli i nomi a dominio e i profili social e depositi il marchio prima di qualsiasi annuncio. Costa meno di un cambio di nome.</p>'],
        'new-office' => ['Abbiamo aperto la seconda sede', 'Più spazio per il team del contenzioso e sale riunioni più vicine ai nostri clienti della logistica.', '<p>La nuova sede ha quattro sale riunioni e una sala udienze per gli arbitrati. La prima sede resta dov’è.</p>'],
    ],

    'office' => ':brand – sede :district',
    'hours' => 'Dal lunedì al venerdì, 9:00–13:00 e 14:30–18:30',

    'form' => [
        'name' => 'Richiesta di consulenza',
        'button' => 'Richiedi una consulenza',
        'success' => 'Grazie! Un avvocato La richiamerà entro un giorno lavorativo.',
        'fields' => [
            'name' => 'Nome e cognome',
            'phone' => 'Telefono',
            'email' => 'E-mail',
            'area' => 'Area di attività',
            'message' => 'Di che cosa si tratta, in breve?',
            'consent' => 'Acconsento al trattamento dei miei dati per ricevere risposta a questa richiesta.',
        ],
    ],

    'faq' => [
        'name' => 'Onorari e primo incontro',
        'code' => 'onorari',
        'questions' => [
            ['Il primo colloquio è gratuito?', 'I primi 30 minuti sono gratuiti, in studio o in videochiamata. Alla fine saprà se possiamo aiutarLa e quanto costerebbe.'],
            ['Quanto costa un’ora di un socio?', ':fee_hour l’ora, oltre accessori di legge. La revisione di un contratto ha di solito un compenso forfettario, a partire da :fee_review.'],
            ['Lavorate a forfait?', 'Per la maggior parte delle operazioni e dei contratti, sì. Il contenzioso è fatturato a ore, con un tetto mensile concordato in anticipo nel preventivo scritto.'],
        ],
    ],

    'reviews' => [
        [':name, fondatrice di una software house', 'In un solo incontro ci hanno spiegato un accordo di investimento di 60 pagine e hanno trovato le due clausole che contavano davvero.'],
        [':name, direttore operativo di un’azienda di logistica', 'Diciotto mesi di arbitrato e non abbiamo mai dovuto sollecitarli per avere un aggiornamento.'],
        [':name e famiglia', 'Il passaggio generazionale ha richiesto tre incontri e un compenso forfettario, esattamente come ci era stato promesso.'],
    ],

    'legal' => [
        'note-legali' => ['Note legali', '<p><strong>Testo di esempio per un sito dimostrativo, non costituisce consulenza legale.</strong> Le note legali del Suo studio vanno redatte su misura da un professionista.</p><h2>Titolare del sito</h2><p>Informazioni rese ai sensi dell’art. 7 del d.lgs. 9 aprile 2003, n. 70.</p><p>:brand, associazione professionale<br>:address</p><p>Telefono: :phone<br>E-mail: :email<br>PEC: indicare qui l’indirizzo di posta elettronica certificata</p><p>P.IVA 00000000000 (fittizia)</p><h2>Informazioni professionali</h2><p>Gli avvocati dello studio sono iscritti all’Albo dell’Ordine degli Avvocati di Bologna. Titolo professionale: avvocato, conseguito in Italia. Norme professionali: legge 31 dicembre 2012, n. 247, sulla disciplina dell’ordinamento della professione forense, e Codice deontologico forense, consultabili sul sito del Consiglio Nazionale Forense.</p><h2>Assicurazione professionale</h2><p>Lo studio è coperto da polizza di responsabilità civile professionale, come previsto dall’art. 12 della legge n. 247/2012.</p>'],
        'privacy' => ['Informativa privacy', '<p><strong>Testo di esempio per un sito dimostrativo, non costituisce consulenza legale.</strong> L’informativa del Suo studio va redatta su misura da un professionista.</p><h2>Titolare del trattamento</h2><p>:brand, :address. Per qualsiasi domanda sui Suoi dati: :email.</p><h2>Dati trattati e finalità</h2><p>Quando invia la richiesta di consulenza riceviamo nome, numero di telefono, indirizzo e-mail e il testo del messaggio. Li usiamo esclusivamente per rispondere alla richiesta (art. 6, par. 1, lett. b, del GDPR) e li conserviamo per il tempo necessario alla pratica, dopodiché li cancelliamo. Sono coperti dal segreto professionale.</p><h2>I Suoi diritti</h2><p>Può chiedere l’accesso ai Suoi dati, la rettifica, la cancellazione, la limitazione del trattamento e la portabilità, oppure opporsi al trattamento, scrivendo a :email (artt. 15–22 del GDPR). Ha inoltre il diritto di proporre reclamo al Garante per la protezione dei dati personali, secondo il Codice privacy (d.lgs. 196/2003, come modificato dal d.lgs. 101/2018).</p><h2>Cookie</h2><p>Questo sito utilizza soltanto cookie tecnici, necessari al suo funzionamento.</p>'],
    ],
];
