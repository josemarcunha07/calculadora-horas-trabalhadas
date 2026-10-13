<?php

/*
|--------------------------------------------------------------------------
| Configurações gerais do projeto
|--------------------------------------------------------------------------
*/

return [
    'app_name' => 'Calculadora de Horas Trabalhadas',

    // Percentual de adicional da hora extra em dias úteis (CLT: 50%).
    'adicional_hora_extra' => 0.50,

    // Percentual de adicional da hora extra em fins de semana/feriados (CLT: 100%).
    'adicional_fim_semana' => 1.00,

    // Semanas consideradas em um mês para o cálculo da jornada mensal.
    'semanas_por_mes' => 52 / 12,

    /*
    |----------------------------------------------------------------------
    | Barra de pesquisa do Google integrada
    |----------------------------------------------------------------------
    | Para exibir os resultados do Google DENTRO da página (sem sair dela),
    | informe o ID de um "Programmable Search Engine" (Custom Search).
    | Crie o seu gratuitamente em: https://programmablesearchengine.google.com/
    | O ID tem o formato parecido com: a1b2c3d4e5f6g7h8i
    |
    | Se deixar vazio, a pesquisa abrirá em uma NOVA aba do Google.
    |----------------------------------------------------------------------
    */
    'google_cse_id' => '',

    // Idioma e região padrão das pesquisas.
    'google_hl' => 'pt-BR',
    'google_gl' => 'br',

    /*
    |----------------------------------------------------------------------
    | Parâmetros trabalhistas (valores de referência 2026)
    |----------------------------------------------------------------------
    */
    'salario_minimo' => 1621.00,

    // Alíquotas progressivas do INSS (empregado, empregado doméstico e avulso).
    'inss_faixas' => [
        ['ate' => 1621.00, 'aliquota' => 0.075],
        ['ate' => 2902.84, 'aliquota' => 0.09],
        ['ate' => 4354.27, 'aliquota' => 0.12],
        ['ate' => 8475.55, 'aliquota' => 0.14],
    ],

    // Tabela progressiva do IRRF (base mensal).
    'irrf_faixas' => [
        ['ate' => 2428.80, 'aliquota' => 0.000, 'deduzir' => 0.00],
        ['ate' => 2826.65, 'aliquota' => 0.075, 'deduzir' => 182.16],
        ['ate' => 3751.05, 'aliquota' => 0.150, 'deduzir' => 394.16],
        ['ate' => 4664.68, 'aliquota' => 0.225, 'deduzir' => 675.49],
        ['ate' => null,    'aliquota' => 0.275, 'deduzir' => 908.73],
    ],
    'irrf_desconto_simplificado' => 607.20,
    'irrf_dependente' => 189.59,
    // Redução da Lei 15.270/2025 (isenção até R$ 5.000 e redução decrescente).
    'irrf_reducao_isencao' => 5000.00,
    'irrf_reducao_limite' => 7350.00,
    'irrf_reducao_constante' => 978.62,
    'irrf_reducao_coeficiente' => 0.133145,

    // FGTS: depósito mensal e multa rescisória.
    'fgts_aliquota' => 0.08,
    'fgts_multa_sem_justa_causa' => 0.40,
    'fgts_multa_acordo' => 0.20,
];
