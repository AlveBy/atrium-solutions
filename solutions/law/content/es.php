<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

// El despacho en español, escrito para España; los marcadores se explican en content/en.php.
return [
    'brand' => 'Navarro Ortega Abogados, S.L.P.',
    'company' => 'navarroortega',

    'catalogue' => [
        'label' => 'Despacho de abogados',
        'description' => 'Un despacho de derecho de los negocios: áreas de práctica, abogados por departamento, publicaciones, oficinas en el mapa, solicitud de consulta, honorarios explicados y opiniones de clientes.',
    ],

    'labels' => [
        'atrium-team::team.modules.members' => 'Abogados',
        'atrium-news::news.module' => 'Publicaciones',
        'atrium-stores::stores.module' => 'Oficinas',
    ],

    'home' => ['Derecho de empresa, sin rodeos', '<p>:brand asesora a empresas, emprendedores y familias en :city en operaciones, conflictos y todo lo que queda entre medias. Doce abogados, dos oficinas y una sola regla: usted siempre sabe qué va a pasar después y cuánto le va a costar.</p>'],

    'practice' => ['Áreas de práctica', '<p>Seis equipos, cada uno dirigido por un abogado que atiende personalmente sus llamadas. La mayoría de los asuntos tocan dos o tres de ellos; desde el primer día ponemos en el suyo a las personas adecuadas.</p>'],

    'areas' => [
        'corporate' => ['Mercantil y M&A', 'Constitución de sociedades, pactos de socios, rondas de financiación, compraventas de empresas y salidas. Leemos el term sheet antes de que lo firme, no después.'],
        'disputes' => ['Litigación y arbitraje', 'Litigios mercantiles y arbitraje, desde el primer requerimiento hasta la ejecución. Le decimos pronto cuándo un acuerdo es mejor negocio que un pleito.'],
        'employment' => ['Laboral', 'Contratos, políticas internas, reestructuraciones y las conversaciones difíciles. Para empresas que quieren hacerlo bien a la primera.'],
        'real-estate' => ['Inmobiliario', 'Arrendamientos, compraventas, promociones y contratos de obra para oficinas, locales y naves.'],
        'ip' => ['Propiedad industrial e intelectual', 'Marcas, licencias de software y los contratos que hacen que sus ideas sigan siendo suyas.'],
        'private-clients' => ['Clientes privados', 'Testamentos, sucesión en la empresa familiar y capitulaciones matrimoniales, con total discreción.'],
    ],

    'about' => ['El despacho', '<p>:founder y :partner fundaron el despacho en :founded, después de una década en grandes firmas, para trabajar con clientes a los que pudieran conocer en persona. Siempre que podemos, seguimos trabajando con honorarios cerrados y acompañamos cada presupuesto de un plan de trabajo.</p><p>Somos independientes, no pertenecemos a ninguna red y le derivamos a otro despacho cuando un asunto se sale de lo que mejor sabemos hacer.</p>'],

    'departments' => [
        'partners' => 'Socios',
        'associates' => 'Counsel y asociados',
    ],

    'people' => [
        'managing-partner' => ['Socia directora, mercantil y M&A', 'Veinte años de operaciones para emprendedores y empresas familiares.', '<p>:founder_first dirige el área mercantil. Antes de fundar el despacho pasó diez años en el departamento de M&A de una firma nacional, y da clases de redacción de contratos en la escuela de negocios de la ciudad.</p>'],
        'disputes-partner' => ['Socio, litigación y arbitraje', 'Ha llevado más de noventa pleitos mercantiles.', '<p>:partner_first defiende a empresas ante los juzgados de lo mercantil, las audiencias provinciales y en arbitrajes. Tiene fama de cerrar acuerdos en asuntos que la otra parte pensaba alargar durante años.</p>'],
        'employment' => ['Counsel, laboral', 'Redacta políticas internas que la plantilla se lee de verdad.'],
        'real-estate' => ['Asociado sénior, inmobiliario', 'Arrendamientos, compraventas y contratos de obra.'],
        'ip' => ['Asociada, propiedad industrial e intelectual', 'Marcas y licencias de software.'],
        'private-clients' => ['Asociada, clientes privados', 'Testamentos, sucesiones y acuerdos familiares.'],
    ],

    'news' => [
        'remote-work' => ['Teletrabajo: lo que cambia para las empresas con las nuevas reglas', 'A partir de enero, los acuerdos de trabajo a distancia tendrán que constar por escrito. Una lista de comprobación para los próximos dos meses.', '<p>Desde enero, cualquier acuerdo de teletrabajo o de trabajo híbrido deberá formalizarse por escrito: dónde trabaja la persona, en qué horario se la puede localizar y quién paga los equipos.</p><p>A la mayoría de las empresas les basta con un anexo de una página al contrato. Hemos preparado un modelo para nuestros clientes: pídanos una copia.</p>'],
        'arbitration' => ['Laudo favorable para un cliente del sector logístico', 'El tribunal arbitral ha concedido a nuestro cliente la totalidad de lo reclamado por un contrato de almacenaje, con costas.', '<p>Tras dieciocho meses, el tribunal arbitral ha dado la razón a nuestro cliente en todos los puntos de una controversia sobre un contrato de almacenaje y ha condenado a la otra parte al pago de las costas del procedimiento.</p>'],
        'trademarks' => ['Cinco comprobaciones de marca antes de lanzar un producto', 'Un nombre que hoy está libre puede estar registrado el año que viene en los mercados a los que quiere vender.', '<p>Busque en el registro de cada mercado en el que piense entrar, compruebe los dominios y los perfiles en redes sociales, y solicite la marca antes de anunciar nada. Sale más barato que cambiar de nombre.</p>'],
        'new-office' => ['Abrimos nuestra segunda oficina', 'Más espacio para el equipo de litigación y salas de reuniones más cerca de nuestros clientes logísticos.', '<p>La nueva oficina cuenta con cuatro salas de reuniones y una sala de vistas para arbitrajes. Nuestra primera oficina sigue donde estaba.</p>'],
    ],

    'office' => ':brand (:district)',
    'hours' => 'De lunes a jueves, de 9:00 a 14:00 y de 16:00 a 19:00; viernes, de 9:00 a 15:00',

    'form' => [
        'name' => 'Solicitud de consulta',
        'button' => 'Solicitar una consulta',
        'success' => '¡Gracias! Un abogado le llamará en el plazo de un día hábil.',
        'fields' => [
            'name' => 'Nombre y apellidos',
            'phone' => 'Teléfono',
            'email' => 'Correo electrónico',
            'area' => 'Área de práctica',
            'message' => '¿De qué se trata? Cuéntenoslo en pocas palabras.',
            'consent' => 'Acepto que mis datos se utilicen para responder a esta solicitud.',
        ],
    ],

    'faq' => [
        'name' => 'Honorarios y primera consulta',
        'code' => 'honorarios',
        'questions' => [
            ['¿La primera consulta es gratuita?', 'Los primeros 30 minutos son gratuitos, en el despacho o por videollamada. Al terminar sabrá si podemos ayudarle y cuánto le costaría.'],
            ['¿Cuánto cuesta una hora de un socio?', ':fee_hour la hora, más IVA. La revisión de la mayoría de los contratos tiene un precio cerrado desde :fee_review.'],
            ['¿Trabajan con honorarios cerrados?', 'En la mayoría de las operaciones y contratos, sí. Los pleitos se facturan por horas, con un tope mensual que fijamos de antemano en la hoja de encargo.'],
        ],
    ],

    'reviews' => [
        [':name, fundadora de una empresa de software', 'Nos explicaron un acuerdo de inversión de 60 páginas en una sola reunión y dieron con las dos cláusulas que de verdad importaban.'],
        [':name, director de operaciones de una empresa logística', 'Dieciocho meses de arbitraje y ni una sola vez tuvimos que perseguirles para saber cómo iba el asunto.'],
        [':name y familia', 'Nuestro plan de sucesión nos llevó tres reuniones y unos honorarios cerrados, tal y como nos habían prometido.'],
    ],

    'legal' => [
        'aviso-legal' => ['Aviso legal', '<p><strong>Texto de ejemplo para un sitio de demostración; no constituye asesoramiento jurídico.</strong> Encargue un aviso legal adaptado a su propio despacho.</p><h2>Datos identificativos</h2><p>En cumplimiento del artículo 10 de la Ley 34/2002, de 11 de julio, de servicios de la sociedad de la información y de comercio electrónico (LSSI-CE), le informamos de que este sitio web pertenece a :brand, sociedad limitada profesional con domicilio en :address.</p><p>Teléfono: :phone<br>Correo electrónico: :email</p><p>NIF: B00000000 (ficticio). Inscrita en el Registro Mercantil de Valencia, tomo 0000, folio 00, hoja V-00000 (datos ficticios), y en el Registro de Sociedades Profesionales del Colegio de la Abogacía de Valencia.</p><h2>Datos profesionales</h2><p>Los abogados del despacho están colegiados en el Colegio de la Abogacía de Valencia. Titulación: grado o licenciatura en Derecho y título profesional de abogado, obtenidos en España. Normas profesionales: Estatuto General de la Abogacía Española (Real Decreto 135/2021) y Código Deontológico de la Abogacía Española, disponibles en la web del Consejo General de la Abogacía Española.</p><h2>Contenidos</h2><p>La información de este sitio es de carácter general y no sustituye al asesoramiento profesional sobre un caso concreto.</p>'],
        'politica-de-privacidad' => ['Política de privacidad', '<p><strong>Texto de ejemplo para un sitio de demostración; no constituye asesoramiento jurídico.</strong> Encargue una política de privacidad adaptada a su propio despacho.</p><h2>Responsable del tratamiento</h2><p>:brand, :address. Para cualquier cuestión sobre sus datos: :email.</p><h2>Qué datos tratamos y para qué</h2><p>Cuando nos envía la solicitud de consulta, recibimos su nombre, su teléfono, su correo electrónico y lo que nos cuente. Los utilizamos únicamente para atender su solicitud, con base en la aplicación de medidas precontractuales a petición suya (art. 6.1.b del RGPD), y los conservamos mientras dure el asunto y los plazos legales de prescripción; después los suprimimos. Están amparados por el secreto profesional.</p><h2>Sus derechos</h2><p>Puede ejercer sus derechos de acceso, rectificación, supresión, oposición, limitación del tratamiento y portabilidad escribiendo a :email, conforme al RGPD y a la Ley Orgánica 3/2018, de 5 de diciembre, de Protección de Datos Personales y garantía de los derechos digitales (LOPDGDD). Si considera que no hemos atendido debidamente su solicitud, puede presentar una reclamación ante la Agencia Española de Protección de Datos (AEPD).</p><h2>Cookies</h2><p>Este sitio solo utiliza las cookies técnicas imprescindibles para su funcionamiento.</p>'],
    ],
];
