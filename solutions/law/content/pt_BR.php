<?php

/*
 * This file is part of Atrium (https://github.com/AlveBy/atrium).
 * Copyright (c) 2026 AlveBy. Licensed under the MIT License, see LICENSE.
 */

declare(strict_types=1);

// O escritório em português, escrito para o Brasil; os marcadores são explicados em content/en.php.
return [
    'brand' => 'Almeida Carvalho Advogados Associados',
    'company' => 'almeidacarvalho',

    'catalogue' => [
        'label' => 'Escritório de advocacia',
        'description' => 'Um escritório de advocacia empresarial: áreas de atuação, advogados por equipe, artigos, unidades no mapa, pedido de consulta, honorários explicados e depoimentos de clientes.',
    ],

    'labels' => [
        'atrium-team::team.modules.members' => 'Advogados',
        'atrium-news::news.module' => 'Artigos',
        'atrium-stores::stores.module' => 'Unidades',
    ],

    'home' => ['Direito empresarial, sem complicação', '<p>:brand assessora empresas, empreendedores e famílias em :city em operações, disputas e tudo o que fica no meio do caminho. Doze advogados, duas unidades e uma regra: você sempre sabe o que vem a seguir e quanto vai custar.</p>'],

    'practice' => ['Áreas de atuação', '<p>Seis equipes, cada uma liderada por um advogado que atende o próprio telefone. A maioria dos casos envolve duas ou três delas; desde o primeiro dia, colocamos as pessoas certas no seu.</p>'],

    'areas' => [
        'corporate' => ['Societário e M&A', 'Constituição de empresas, acordos de sócios, rodadas de investimento, aquisições e saídas. Lemos o term sheet antes de você assinar, não depois.'],
        'disputes' => ['Contencioso e arbitragem', 'Contencioso empresarial e arbitragem, da primeira notificação extrajudicial ao cumprimento de sentença. Avisamos cedo quando um acordo é o melhor negócio.'],
        'employment' => ['Trabalhista', 'Contratos, regulamentos internos, reestruturações e as conversas difíceis. Para empregadores que querem acertar de primeira.'],
        'real-estate' => ['Imobiliário', 'Locações, compra e venda, incorporação e contratos de obra para escritórios, lojas e galpões.'],
        'ip' => ['Propriedade intelectual', 'Marcas, licenças de software e os contratos que garantem que suas ideias continuem sendo suas.'],
        'private-clients' => ['Clientes privados', 'Testamentos, planejamento sucessório de empresas familiares e pactos antenupciais, com toda a discrição.'],
    ],

    'about' => ['O escritório', '<p>:founder e :partner fundaram o escritório em :founded, depois de uma década em grandes bancas, para atender clientes que pudessem conhecer pessoalmente. Sempre que possível, ainda cobramos honorários fixos e enviamos um plano de trabalho junto com cada proposta.</p><p>Somos independentes, não fazemos parte de nenhuma rede e indicamos outro escritório quando um caso foge daquilo que fazemos melhor.</p>'],

    'departments' => [
        'partners' => 'Sócios',
        'associates' => 'Consultores e associados',
    ],

    'people' => [
        'managing-partner' => ['Sócia-fundadora, societário e M&A', 'Vinte anos de operações para empreendedores e empresas familiares.', '<p>:founder_first lidera a área societária. Antes de fundar o escritório, passou dez anos na equipe de M&A de um escritório de atuação nacional, e dá aulas de redação de contratos na escola de negócios da cidade.</p>'],
        'disputes-partner' => ['Sócio, contencioso e arbitragem', 'Já atuou em mais de noventa processos empresariais.', '<p>:partner_first representa empresas na Justiça estadual e federal e em arbitragens. É conhecido por fechar acordos em casos que a outra parte esperava arrastar por anos.</p>'],
        'employment' => ['Consultora, trabalhista', 'Escreve regulamentos internos que as pessoas realmente leem.'],
        'real-estate' => ['Associado sênior, imobiliário', 'Locações, compra e venda e contratos de obra.'],
        'ip' => ['Associada, propriedade intelectual', 'Marcas e licenças de software.'],
        'private-clients' => ['Associada, clientes privados', 'Testamentos, sucessões e acordos de família.'],
    ],

    'news' => [
        'remote-work' => ['O que as novas regras de teletrabalho mudam para as empresas', 'A partir de janeiro, os acordos de trabalho remoto precisam ser feitos por escrito. Um checklist para os próximos dois meses.', '<p>A partir de janeiro, todo acordo de trabalho remoto ou híbrido precisa ser firmado por escrito: onde a pessoa trabalha, em que horários pode ser contatada e quem paga pelos equipamentos.</p><p>Para a maioria das empresas, um aditivo de uma página ao contrato de trabalho resolve. Preparamos um modelo para nossos clientes; é só pedir uma cópia.</p>'],
        'arbitration' => ['Sentença arbitral favorável a um cliente do setor logístico', 'O tribunal arbitral concedeu ao nosso cliente o valor integral pedido com base em um contrato de armazenagem, com as custas.', '<p>Depois de dezoito meses, o tribunal arbitral deu razão ao nosso cliente em todos os pontos de uma disputa sobre um contrato de armazenagem e condenou a outra parte a pagar as custas do procedimento.</p>'],
        'trademarks' => ['Cinco verificações de marca antes de lançar um produto', 'Um nome que está livre hoje pode estar registrado no ano que vem nos mercados para onde você quer vender.', '<p>Pesquise o registro de cada mercado em que pretende entrar, verifique domínios e perfis nas redes sociais e deposite o pedido antes de anunciar. Sai mais barato do que mudar de nome.</p>'],
        'new-office' => ['Nossa segunda unidade está aberta', 'Mais espaço para a equipe de contencioso e salas de reunião mais perto dos nossos clientes de logística.', '<p>A nova unidade tem quatro salas de reunião e uma sala de audiências para arbitragens. A primeira continua onde sempre esteve.</p>'],
    ],

    'office' => ':brand – Unidade :district',
    'hours' => 'Segunda a sexta, das 9h às 18h',

    'form' => [
        'name' => 'Pedido de consulta',
        'button' => 'Agendar uma consulta',
        'success' => 'Obrigado! Um advogado vai ligar para você em até um dia útil.',
        'fields' => [
            'name' => 'Seu nome',
            'phone' => 'Telefone',
            'email' => 'E-mail',
            'area' => 'Área de atuação',
            'message' => 'Conte em poucas palavras do que se trata',
            'consent' => 'Concordo com o uso dos meus dados para responder a este pedido.',
        ],
    ],

    'faq' => [
        'name' => 'Honorários e primeira reunião',
        'code' => 'honorarios',
        'questions' => [
            ['A primeira consulta é gratuita?', 'Os primeiros 30 minutos são gratuitos, no escritório ou por videochamada. Ao final, você já sabe se podemos ajudar e quanto custaria.'],
            ['Quanto custa uma hora de um sócio?', ':fee_hour por hora. A revisão da maioria dos contratos tem honorários fixos a partir de :fee_review.'],
            ['Vocês trabalham com honorários fixos?', 'Na maioria das operações e contratos, sim. Processos são cobrados por hora, com um teto mensal combinado com antecedência no contrato de honorários.'],
        ],
    ],

    'reviews' => [
        [':name, fundadora de uma empresa de software', 'Explicaram um acordo de investimento de 60 páginas em uma única reunião e encontraram as duas cláusulas que realmente importavam.'],
        [':name, diretor de operações de uma empresa de logística', 'Dezoito meses de arbitragem e nunca precisamos correr atrás deles para saber como estava o caso.'],
        [':name e família', 'Nosso planejamento sucessório levou três reuniões e saiu por um valor fixo, exatamente como prometido.'],
    ],

    'legal' => [
        'politica-de-privacidade' => ['Política de Privacidade', '<p><strong>Texto de exemplo para um site de demonstração; não é orientação jurídica.</strong> Peça a um profissional a política de privacidade do seu próprio escritório.</p><h2>Quem somos</h2><p>:brand, sociedade de advogados registrada na OAB/PR sob o n.º 0000 (fictício), com sede em :address. Telefone: :phone.</p><h2>Encarregado de dados</h2><p>Dúvidas sobre o tratamento dos seus dados pessoais podem ser enviadas ao nosso encarregado pelo e-mail :email.</p><h2>Quais dados coletamos e por quê</h2><p>Quando você envia o pedido de consulta, recebemos seu nome, telefone, e-mail e a mensagem que escreveu. Usamos esses dados apenas para responder ao seu pedido, com base no art. 7º, inciso V, da Lei Geral de Proteção de Dados Pessoais (Lei n.º 13.709/2018 – LGPD), e os guardamos pelo tempo necessário ao atendimento e ao cumprimento de obrigações legais; depois disso, eles são eliminados. Todas as informações estão protegidas pelo sigilo profissional.</p><h2>Seus direitos</h2><p>Você pode pedir a confirmação de que tratamos seus dados, o acesso a eles, a correção, a anonimização, a portabilidade e a eliminação, além de informações sobre com quem eles são compartilhados (art. 18 da LGPD). Também pode apresentar reclamação à Autoridade Nacional de Proteção de Dados (ANPD).</p><h2>Cookies</h2><p>Este site usa apenas os cookies necessários para funcionar.</p>'],
    ],
];
