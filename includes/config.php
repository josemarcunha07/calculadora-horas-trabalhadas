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
];
