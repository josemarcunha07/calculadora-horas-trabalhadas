<?php

/*
|--------------------------------------------------------------------------
| Tabela de referência de médias salariais por categoria profissional
|--------------------------------------------------------------------------
| Valores de referência (média mensal bruta em R$) baseados em pesquisas
| de mercado (CAGED, IBGE, Glassdoor, Salario.com.br e sindicatos).
| São valores APROXIMADOS e servem apenas de ponto de partida para o
| cálculo. O usuário pode informar o próprio salário no formulário.
|--------------------------------------------------------------------------
*/

return [
    'Tecnologia da Informação' => [
        ['cargo' => 'Desenvolvedor(a) de Software', 'salario' => 6500.00],
        ['cargo' => 'Analista de Dados',            'salario' => 7200.00],
        ['cargo' => 'Analista de Suporte Técnico',  'salario' => 3200.00],
        ['cargo' => 'Designer UX/UI',               'salario' => 5500.00],
        ['cargo' => 'Analista de Segurança da Informação', 'salario' => 8500.00],
    ],
    'Saúde' => [
        ['cargo' => 'Médico(a) Clínico Geral',      'salario' => 15000.00],
        ['cargo' => 'Enfermeiro(a)',                'salario' => 4500.00],
        ['cargo' => 'Técnico(a) de Enfermagem',     'salario' => 2600.00],
        ['cargo' => 'Dentista',                     'salario' => 9000.00],
        ['cargo' => 'Farmacêutico(a)',              'salario' => 4800.00],
    ],
    'Educação' => [
        ['cargo' => 'Professor(a) do Ensino Fundamental', 'salario' => 3800.00],
        ['cargo' => 'Professor(a) do Ensino Médio',       'salario' => 4500.00],
        ['cargo' => 'Professor(a) Universitário(a)',      'salario' => 9500.00],
        ['cargo' => 'Auxiliar de Educação Infantil',      'salario' => 2200.00],
    ],
    'Construção Civil' => [
        ['cargo' => 'Engenheiro(a) Civil',          'salario' => 9000.00],
        ['cargo' => 'Pedreiro(a)',                  'salario' => 2600.00],
        ['cargo' => 'Servente de Obras',            'salario' => 1800.00],
        ['cargo' => 'Mestre de Obras',              'salario' => 3500.00],
    ],
    'Comércio e Vendas' => [
        ['cargo' => 'Vendedor(a)',                  'salario' => 2500.00],
        ['cargo' => 'Gerente de Loja',              'salario' => 4000.00],
        ['cargo' => 'Operador(a) de Caixa',         'salario' => 1800.00],
        ['cargo' => 'Promotor(a) de Vendas',        'salario' => 2200.00],
    ],
    'Indústria' => [
        ['cargo' => 'Engenheiro(a) de Produção',    'salario' => 8500.00],
        ['cargo' => 'Mecânico(a) Industrial',       'salario' => 3200.00],
        ['cargo' => 'Operador(a) de Máquinas',      'salario' => 2400.00],
        ['cargo' => 'Soldador(a)',                  'salario' => 2800.00],
    ],
    'Serviços' => [
        ['cargo' => 'Motorista',                    'salario' => 2500.00],
        ['cargo' => 'Cozinheiro(a)',                'salario' => 2200.00],
        ['cargo' => 'Recepcionista',                'salario' => 1900.00],
        ['cargo' => 'Auxiliar de Limpeza',          'salario' => 1600.00],
        ['cargo' => 'Garçom/Garçonete',             'salario' => 1800.00],
    ],
    'Administração e Financeiro' => [
        ['cargo' => 'Auxiliar Administrativo',      'salario' => 2100.00],
        ['cargo' => 'Contador(a)',                  'salario' => 4500.00],
        ['cargo' => 'Analista Financeiro',          'salario' => 5200.00],
        ['cargo' => 'Analista de RH',               'salario' => 3800.00],
    ],
    'Segurança' => [
        ['cargo' => 'Vigilante',                    'salario' => 2200.00],
        ['cargo' => 'Bombeiro(a) Civil',            'salario' => 2500.00],
        ['cargo' => 'Porteiro(a)',                  'salario' => 1700.00],
    ],
    'Entregas e Aplicativos' => [
        ['cargo' => 'Motorista de Aplicativo',      'salario' => 3000.00],
        ['cargo' => 'Entregador(a) / Motoboy',      'salario' => 2200.00],
        ['cargo' => 'Ciclista de Delivery',         'salario' => 1800.00],
    ],
];
