<?php

/*
|--------------------------------------------------------------------------
| Funções auxiliares e lógica de cálculo
|--------------------------------------------------------------------------
*/

/**
 * Formata um valor numérico como moeda brasileira (R$).
 */
function formataMoeda(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

/**
 * Formata um número de horas com até 2 casas decimais.
 */
function formataHoras(float $horas): string
{
    return number_format($horas, 2, ',', '.') . ' h';
}

/**
 * Lê um valor numérico vindo do formulário, aceitando vírgula como
 * separador decimal.
 */
function valorNumerico($valor, float $padrao = 0.0): float
{
    if ($valor === null || $valor === '') {
        return $padrao;
    }
    $valor = str_replace(['.', ' '], '', (string) $valor);
    $valor = str_replace(',', '.', $valor);
    return is_numeric($valor) ? (float) $valor : $padrao;
}

/**
 * Valida os dados informados no formulário.
 *
 * @return array Lista de mensagens de erro (vazia se tudo estiver ok).
 */
function validaEntrada(array $dados): array
{
    $erros = [];

    if (($dados['profissao'] ?? '') === '') {
        $erros[] = 'Selecione uma profissão.';
    } elseif (($dados['profissao'] ?? '') === '__outra__'
        && trim((string) ($dados['profissao_custom'] ?? '')) === '') {
        $erros[] = 'Informe o nome da sua profissão.';
    }

    if (($dados['salario_base'] ?? 0) <= 0) {
        $erros[] = 'Informe um salário base válido (maior que zero).';
    }

    if (($dados['horas_dia'] ?? 0) <= 0) {
        $erros[] = 'Informe quantas horas normais você trabalha por dia.';
    }

    if (($dados['horas_dia'] ?? 0) > 24) {
        $erros[] = 'As horas por dia não podem passar de 24.';
    }

    if (($dados['dias_semana'] ?? 0) < 1 || ($dados['dias_semana'] ?? 0) > 7) {
        $erros[] = 'Os dias trabalhados por semana devem ficar entre 1 e 7.';
    }

    if (($dados['horas_extras'] ?? 0) < 0 || ($dados['horas_fds'] ?? 0) < 0) {
        $erros[] = 'As horas extras não podem ser negativas.';
    }

    if (($dados['horas_fds'] ?? 0) > 0 && empty($dados['trabalha_fds'])) {
        $erros[] = 'Marque a opção "trabalho em finais de semana" para contabilizar essas horas.';
    }

    return $erros;
}

/**
 * Realiza o cálculo completo da remuneração por horas trabalhadas.
 *
 * @param array $dados   Dados já validados e convertidos.
 * @param array $config  Configurações (adicionais e semanas por mês).
 * @return array Detalhamento do cálculo.
 */
function calcularRemuneracao(array $dados, array $config): array
{
    $semanasMes = (float) $config['semanas_por_mes'];
    $addExtra   = (float) $config['adicional_hora_extra'];
    $addFds     = (float) $config['adicional_fim_semana'];

    $salarioBase = (float) $dados['salario_base'];
    $horasDia    = (float) $dados['horas_dia'];
    $diasSemana  = (float) $dados['dias_semana'];
    $horasExtras = (float) ($dados['horas_extras'] ?? 0);
    $horasFds    = !empty($dados['trabalha_fds']) ? (float) ($dados['horas_fds'] ?? 0) : 0.0;

    // Jornada mensal normal (ex.: 8h/dia * 5 dias * 4,333 = 173,33 h)
    $jornadaMensal = $horasDia * $diasSemana * $semanasMes;
    $jornadaSemanal = $horasDia * $diasSemana;

    // Valor da hora normal
    $valorHora = $jornadaMensal > 0 ? $salarioBase / $jornadaMensal : 0.0;

    // Valores das horas com adicional
    $valorHoraExtra = $valorHora * (1 + $addExtra);
    $valorHoraFds   = $valorHora * (1 + $addFds);

    // Quantidade de horas por mês
    $horasExtrasMes = $horasExtras * $semanasMes;
    $horasFdsMes    = $horasFds * $semanasMes;

    // Ganhos adicionais
    $totalExtras = $valorHoraExtra * $horasExtrasMes;
    $totalFds    = $valorHoraFds * $horasFdsMes;

    $totalMensal = $salarioBase + $totalExtras + $totalFds;
    $totalAnual  = $totalMensal * 12;

    $totalHorasMes = $jornadaMensal + $horasExtrasMes + $horasFdsMes;
    $valorHoraEfetivo = $totalHorasMes > 0 ? $totalMensal / $totalHorasMes : 0.0;

    return [
        'salario_base'        => $salarioBase,
        'jornada_semanal'     => $jornadaSemanal,
        'jornada_mensal'      => $jornadaMensal,
        'valor_hora'          => $valorHora,
        'valor_hora_extra'    => $valorHoraExtra,
        'valor_hora_fds'      => $valorHoraFds,
        'horas_extras_mes'    => $horasExtrasMes,
        'horas_fds_mes'       => $horasFdsMes,
        'total_extras'        => $totalExtras,
        'total_fds'           => $totalFds,
        'total_horas_mes'     => $totalHorasMes,
        'valor_hora_efetivo'  => $valorHoraEfetivo,
        'total_mensal'        => $totalMensal,
        'total_anual'         => $totalAnual,
        'adicional_extra_pct' => $addExtra * 100,
        'adicional_fds_pct'   => $addFds * 100,
    ];
}
