<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

// Le cabinet en français, écrit pour la France ; les marqueurs sont expliqués dans content/en.php.
return [
    'brand' => 'Moreau Garnier Avocats',
    'company' => 'moreaugarnier',

    'catalogue' => [
        'label' => 'Cabinet d’avocats',
        'description' => 'Un cabinet d’avocats d’affaires : domaines d’intervention, avocats par équipe, publications, bureaux sur la carte, demande de rendez-vous, honoraires expliqués et avis de clients.',
    ],

    'labels' => [
        'atrium-team::team.modules.members' => 'Avocats',
        'atrium-news::news.module' => 'Publications',
        'atrium-stores::stores.module' => 'Bureaux',
    ],

    'home' => ['Le droit des affaires, sans détour', '<p>:brand conseille entreprises, fondateurs et familles à :city, pour leurs opérations, leurs litiges et tout ce qui se joue entre les deux. Douze avocats, deux bureaux et une seule règle : vous savez toujours ce qui va se passer ensuite et combien cela vous coûtera.</p>'],

    'practice' => ['Domaines d’intervention', '<p>Six équipes, chacune dirigée par un avocat qui décroche lui-même son téléphone. La plupart des dossiers en mobilisent deux ou trois ; nous y affectons les bonnes personnes dès le premier jour.</p>'],

    'areas' => [
        'corporate' => ['Droit des sociétés et fusions-acquisitions', 'Constitution, pactes d’associés, levées de fonds, acquisitions et cessions. Nous lisons la term sheet avant que vous ne la signiez, pas après.'],
        'disputes' => ['Contentieux et arbitrage', 'Contentieux commercial et arbitrage, de la mise en demeure jusqu’à l’exécution de la décision. Nous vous disons très tôt quand une transaction est la meilleure issue.'],
        'employment' => ['Droit social', 'Contrats de travail, règlement intérieur, réorganisations et conversations difficiles. Pour les employeurs qui veulent bien faire du premier coup.'],
        'real-estate' => ['Droit immobilier', 'Baux commerciaux, acquisitions, promotion et marchés de travaux pour bureaux, commerces et entrepôts.'],
        'ip' => ['Propriété intellectuelle', 'Marques, licences de logiciels et contrats qui font que vos idées restent les vôtres.'],
        'private-clients' => ['Patrimoine et famille', 'Testaments, transmission d’entreprises familiales et contrats de mariage, en toute discrétion.'],
    ],

    'about' => ['Le cabinet', '<p>:founder et :partner ont fondé le cabinet en :founded, après dix ans passés dans de grandes structures, pour travailler avec des clients qu’ils rencontrent en personne. Aujourd’hui encore, nous proposons un forfait chaque fois que c’est possible et joignons un plan d’action à chaque proposition d’honoraires.</p><p>Nous sommes indépendants, n’appartenons à aucun réseau et vous orientons vers un confrère lorsqu’un dossier sort de notre cœur de métier.</p>'],

    'departments' => [
        'partners' => 'Associés',
        'associates' => 'Counsels et collaborateurs',
    ],

    'people' => [
        'managing-partner' => ['Associée gérante, droit des sociétés et fusions-acquisitions', 'Vingt ans d’opérations aux côtés de fondateurs et d’entreprises familiales.', '<p>:founder_first dirige le pôle droit des sociétés. Avant de fonder le cabinet, elle a passé dix ans au sein du département fusions-acquisitions d’un cabinet national ; elle enseigne aussi la rédaction des contrats à l’école de commerce de la ville.</p>'],
        'disputes-partner' => ['Associé, contentieux et arbitrage', 'A plaidé plus de quatre-vingt-dix dossiers commerciaux.', '<p>:partner_first défend des entreprises devant les juridictions commerciales, les cours d’appel et les tribunaux arbitraux. Il a la réputation de trouver un accord là où l’adversaire s’attendait à des années de procédure.</p>'],
        'employment' => ['Counsel, droit social', 'Rédige des règlements intérieurs que les salariés lisent vraiment.'],
        'real-estate' => ['Collaborateur senior, droit immobilier', 'Baux commerciaux, acquisitions et marchés de travaux.'],
        'ip' => ['Collaboratrice, propriété intellectuelle', 'Marques et licences de logiciels.'],
        'private-clients' => ['Collaboratrice, patrimoine et famille', 'Testaments, transmission et contrats de mariage.'],
    ],

    'news' => [
        'remote-work' => ['Télétravail : ce que les nouvelles règles changent pour les employeurs', 'Dès janvier, le télétravail devra être formalisé par écrit. Les points à vérifier d’ici deux mois.', '<p>À partir de janvier, tout accord de télétravail ou de travail hybride devra être formalisé par écrit : le lieu de travail, les plages horaires pendant lesquelles le salarié est joignable et la prise en charge du matériel.</p><p>Pour la plupart des employeurs, un avenant d’une page suffit. Nous en avons rédigé un modèle pour nos clients : n’hésitez pas à nous le demander.</p>'],
        'arbitration' => ['Sentence arbitrale favorable pour un client logisticien', 'Le tribunal arbitral a accordé à notre client l’intégralité de sa demande au titre d’un contrat d’entreposage, frais compris.', '<p>Après dix-huit mois de procédure, le tribunal arbitral a donné raison à notre client sur tous les points d’un litige portant sur un contrat d’entreposage et a condamné la partie adverse aux frais de l’arbitrage.</p>'],
        'trademarks' => ['Marques : cinq vérifications avant de lancer un produit', 'Un nom libre aujourd’hui peut être déposé l’an prochain sur les marchés où vous comptez vendre.', '<p>Faites une recherche d’antériorité dans chaque pays visé, vérifiez les noms de domaine et les comptes sur les réseaux sociaux, et déposez la marque avant toute annonce. C’est toujours moins cher qu’un changement de nom.</p>'],
        'new-office' => ['Notre deuxième bureau a ouvert ses portes', 'Plus d’espace pour l’équipe contentieux et des salles de réunion plus proches de nos clients logisticiens.', '<p>Le nouveau bureau compte quatre salles de réunion et une salle d’audience pour les arbitrages. Notre premier bureau reste à la même adresse.</p>'],
    ],

    'office' => ':brand – :district',
    'hours' => 'Du lundi au vendredi, de 9 h à 18 h 30',

    'form' => [
        'name' => 'Demande de rendez-vous',
        'button' => 'Prendre rendez-vous',
        'success' => 'Merci ! Un avocat vous rappelle sous un jour ouvré.',
        'fields' => [
            'name' => 'Nom et prénom',
            'phone' => 'Téléphone',
            'email' => 'Adresse e-mail',
            'area' => 'Domaine concerné',
            'message' => 'En quelques mots, de quoi s’agit-il ?',
            'consent' => 'J’accepte que mes données soient utilisées pour répondre à ma demande.',
        ],
    ],

    'faq' => [
        'name' => 'Honoraires et premier rendez-vous',
        'code' => 'honoraires',
        'questions' => [
            ['Le premier rendez-vous est-il gratuit ?', 'Les 30 premières minutes sont offertes, au cabinet ou en visioconférence. À l’issue de cet échange, vous savez si nous pouvons vous aider et pour quel budget.'],
            ['Combien coûte une heure d’associé ?', 'Comptez :fee_hour HT de l’heure. La relecture d’un contrat est le plus souvent forfaitaire, à partir de :fee_review HT.'],
            ['Travaillez-vous au forfait ?', 'Pour la plupart des opérations et des contrats, oui. Les contentieux sont facturés au temps passé, avec un plafond mensuel fixé à l’avance dans la convention d’honoraires.'],
        ],
    ],

    'reviews' => [
        [':name, fondatrice d’un éditeur de logiciels', 'En un seul rendez-vous, ils nous ont expliqué un pacte d’investissement de 60 pages et ont repéré les deux clauses qui comptaient vraiment.'],
        [':name, directeur des opérations d’une entreprise de logistique', 'Dix-huit mois d’arbitrage, et pas une seule fois nous n’avons eu à les relancer pour savoir où en était le dossier.'],
        [':name et sa famille', 'La transmission de notre entreprise s’est réglée en trois rendez-vous et pour un forfait, exactement comme convenu.'],
    ],

    'legal' => [
        'mentions-legales' => ['Mentions légales', '<p><strong>Texte d’exemple pour un site de démonstration, sans valeur de conseil juridique.</strong> Faites rédiger les mentions légales de votre propre cabinet.</p><h2>Éditeur du site</h2><p>Conformément à l’article 6 de la loi n° 2004-575 du 21 juin 2004 pour la confiance dans l’économie numérique (LCEN), ce site est édité par :brand, société d’exercice libéral à responsabilité limitée (SELARL) d’avocats au capital de 10 000 € (fictif).<br>:address</p><p>Téléphone : :phone<br>E-mail : :email</p><p>RCS Lyon 000 000 000 (fictif) – TVA intracommunautaire FR00 000000000 (fictif)</p><p>Directrice de la publication : la gérante de la SELARL.</p><h2>Profession réglementée</h2><p>Les avocats du cabinet sont inscrits au barreau de Lyon. Titre professionnel : avocat, délivré en France. Ils sont soumis à la loi n° 71-1130 du 31 décembre 1971, au décret n° 91-1197 du 27 novembre 1991 et au règlement intérieur national de la profession d’avocat (RIN), consultables sur le site du Conseil national des barreaux.</p><h2>Médiation</h2><p>En cas de litige relatif aux honoraires, le client consommateur peut saisir gratuitement le médiateur de la consommation de la profession d’avocat.</p><h2>Hébergement</h2><p>Indiquez ici le nom, l’adresse et le numéro de téléphone de votre hébergeur.</p>'],
        'politique-de-confidentialite' => ['Politique de confidentialité', '<p><strong>Texte d’exemple pour un site de démonstration, sans valeur de conseil juridique.</strong> Faites rédiger la politique de confidentialité de votre propre cabinet.</p><h2>Responsable du traitement</h2><p>:brand, :address. Pour toute question sur vos données : :email.</p><h2>Données collectées</h2><p>Lorsque vous envoyez une demande de rendez-vous, nous recevons vos nom et prénom, votre numéro de téléphone, votre adresse e-mail et le contenu de votre message. Ces données servent uniquement à traiter votre demande (article 6.1.b du RGPD) ; elles sont conservées le temps nécessaire au dossier, puis supprimées. Elles sont couvertes par le secret professionnel de l’avocat.</p><h2>Vos droits</h2><p>Vous disposez d’un droit d’accès, de rectification, d’effacement, de limitation, de portabilité et d’opposition, que vous pouvez exercer en écrivant à :email. Vous pouvez également introduire une réclamation auprès de la CNIL.</p><h2>Cookies</h2><p>Ce site n’utilise que les cookies strictement nécessaires à son fonctionnement.</p>'],
    ],
];
