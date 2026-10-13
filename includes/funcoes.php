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

/**
 * Formata uma data no formato Y-m-d para d/m/Y.
 */
function formataData(?string $ymd): string
{
    if (empty($ymd)) {
        return '—';
    }
    $dt = DateTime::createFromFormat('Y-m-d', $ymd);
    return $dt ? $dt->format('d/m/Y') : '—';
}

/**
 * Calcula o desconto progressivo do INSS para empregado.
 */
function inssEmpregado(float $base, array $faixas): float
{
    if ($base <= 0) {
        return 0.0;
    }
    $total = 0.0;
    $anterior = 0.0;

    foreach ($faixas as $faixa) {
        $teto = min($base, (float) $faixa['ate']);
        if ($teto > $anterior) {
            $total += ($teto - $anterior) * (float) $faixa['aliquota'];
            $anterior = $teto;
        }
        if ($base <= (float) $faixa['ate']) {
            break;
        }
    }

    return round($total, 2);
}

/**
 * Calcula o IRRF mensal (tabela 2026 + redução da Lei 15.270/2025).
 *
 * @param float $rendimento  Rendimento tributável bruto do mês.
 * @param float $inss        INSS já descontado do rendimento.
 * @param int   $dependentes Número de dependentes.
 */
function irrfMensal(float $rendimento, float $inss, int $dependentes, array $config): float
{
    if ($rendimento <= 0) {
        return 0.0;
    }

    $deducaoLegal = $inss + ($dependentes * (float) $config['irrf_dependente']);
    $deducao = max($deducaoLegal, (float) $config['irrf_desconto_simplificado']);
    $base = max(0.0, $rendimento - $deducao);

    $imposto = 0.0;
    foreach ($config['irrf_faixas'] as $faixa) {
        if ($faixa['ate'] === null || $base <= (float) $faixa['ate']) {
            $imposto = $base * (float) $faixa['aliquota'] - (float) $faixa['deduzir'];
            break;
        }
    }
    $imposto = max(0.0, $imposto);

    // Redução da Lei 15.270/2025 (usa o rendimento bruto do mês).
    $reducao = 0.0;
    if ($rendimento <= (float) $config['irrf_reducao_isencao']) {
        $reducao = $imposto;
    } elseif ($rendimento <= (float) $config['irrf_reducao_limite']) {
        $reducao = (float) $config['irrf_reducao_constante']
            - ((float) $config['irrf_reducao_coeficiente'] * $rendimento);
        $reducao = max(0.0, $reducao);
    }

    return round(max(0.0, $imposto - $reducao), 2);
}

/**
 * Calcula o valor das férias (com 1/3 constitucional e abono pecuniário).
 */
function calcFerias(array $dados, array $config): array
{
    $salario    = (float) $dados['salario'];
    $dias       = (float) $dados['dias'];
    $dependentes = (int) ($dados['dependentes'] ?? 0);
    $vender     = !empty($dados['vender10']);

    $valorDia = $salario / 30;

    $feriasBase = $valorDia * $dias;
    $terco      = $feriasBase / 3;

    $abonoDias  = $vender ? 10 : 0;
    $abono      = $valorDia * $abonoDias;
    $abonoTerco = $abono / 3;

    $bruto = $feriasBase + $terco + $abono + $abonoTerco;

    $inss = inssEmpregado($bruto, $config['inss_faixas']);
    $irrf = irrfMensal($bruto, $inss, $dependentes, $config);
    $liquido = $bruto - $inss - $irrf;

    return [
        'salario'     => $salario,
        'valor_dia'   => $valorDia,
        'dias'        => $dias,
        'ferias_base' => $feriasBase,
        'terco'       => $terco,
        'abono_dias'  => $abonoDias,
        'abono'       => $abono,
        'abono_terco' => $abonoTerco,
        'bruto'       => $bruto,
        'inss'        => $inss,
        'irrf'        => $irrf,
        'liquido'     => $liquido,
        'dependentes' => $dependentes,
    ];
}

/**
 * Diferença em anos completos entre duas datas.
 */
function anosCompletos(DateTime $inicio, DateTime $fim): int
{
    return $inicio->diff($fim)->y;
}

/**
 * Quantidade de meses (com fração de 15 dias ou mais) entre duas datas.
 */
function mesesComFracao(DateTime $inicio, DateTime $fim): int
{
    $intervalo = $inicio->diff($fim);
    $meses = $intervalo->y * 12 + $intervalo->m;
    if ($intervalo->d >= 15) {
        $meses++;
    }
    return $meses;
}

/**
 * Calcula as principais verbas rescisórias (valores estimados).
 */
function calcRescisao(array $dados, array $config): array
{
    $salario    = (float) $dados['salario'];
    $dependentes = (int) ($dados['dependentes'] ?? 0);
    $tipo       = (string) $dados['tipo'];
    $aviso      = (string) $dados['aviso'];
    $feriasVencidas = max(0, (int) ($dados['ferias_vencidas'] ?? 0));

    $admissao = DateTime::createFromFormat('Y-m-d', $dados['admissao']);
    $demissao = DateTime::createFromFormat('Y-m-d', $dados['demissao']);

    $valorDia = $salario / 30;

    // 1) Saldo de salário (dias trabalhados no mês da rescisão)
    $diasMes = (int) $demissao->format('j');
    $saldo = $valorDia * $diasMes;

    // 2) Aviso prévio (30 dias + 3 por ano completo, limitado a 90)
    $anos = anosCompletos($admissao, $demissao);
    $diasAviso = min(90, 30 + (3 * $anos));
    $avisoValor = 0.0;
    if ($aviso === 'indenizado' && in_array($tipo, ['sem_justa', 'acordo'], true)) {
        $avisoValor = $valorDia * $diasAviso;
    }

    // 3) Férias vencidas (períodos completos não gozados) + 1/3
    $feriasVencValor = $feriasVencidas * $salario;
    $feriasVencTerco = $feriasVencValor / 3;

    // 4) Férias proporcionais + 1/3 (avos do período aquisitivo atual)
    $mesesTotais = mesesComFracao($admissao, $demissao);
    $avos = $mesesTotais % 12;
    $feriasProp = ($salario / 12) * $avos;
    $feriasPropTerco = $feriasProp / 3;

    // 5) 13º proporcional (meses com 15 dias ou mais no ano da rescisão)
    $inicioAno = DateTime::createFromFormat('Y-m-d', $demissao->format('Y') . '-01-01');
    $mesesAno = mesesComFracao($inicioAno, $demissao);
    $decimoProp = ($salario / 12) * $mesesAno;

    // 6) FGTS e multa rescisória (estimativa sobre os depósitos do contrato)
    $fgtsDeposito = (float) $config['fgts_aliquota'] * $salario * max(1, $mesesTotais);
    $multaPct = 0.0;
    if ($tipo === 'sem_justa') {
        $multaPct = (float) $config['fgts_multa_sem_justa_causa'];
    } elseif ($tipo === 'acordo') {
        $multaPct = (float) $config['fgts_multa_acordo'];
    }
    $multa = $fgtsDeposito * $multaPct;

    // Verbas indenizadas (isentas) x verbas tributáveis
    $feriasIndenizadas = $feriasVencValor + $feriasVencTerco + $feriasProp + $feriasPropTerco;

    $bruto = $saldo + $avisoValor + $feriasIndenizadas + $decimoProp + $multa;

    // Descontos: INSS/IRRF sobre saldo + aviso + 13º (estimativa)
    $tributavel = $saldo + $avisoValor + $decimoProp;
    $inss = inssEmpregado($tributavel, $config['inss_faixas']);
    $irrf = irrfMensal($tributavel, $inss, $dependentes, $config);

    // Aviso prévio não trabalhado em pedido de demissão é descontado.
    $descontoAviso = 0.0;
    if ($tipo === 'pedido' && $aviso !== 'trabalhado') {
        $descontoAviso = $valorDia * 30;
    }

    $liquido = $bruto - $inss - $irrf - $descontoAviso;

    return [
        'salario'            => $salario,
        'tipo'               => $tipo,
        'aviso'              => $aviso,
        'admissao'           => $admissao,
        'demissao'           => $demissao,
        'dias_mes'           => $diasMes,
        'anos'               => $anos,
        'meses_totais'       => $mesesTotais,
        'avos'               => $avos,
        'meses_ano'          => $mesesAno,
        'dias_aviso'         => $diasAviso,
        'saldo'              => $saldo,
        'aviso_valor'        => $avisoValor,
        'ferias_vencidas'    => $feriasVencValor + $feriasVencTerco,
        'ferias_venc_qtd'    => $feriasVencidas,
        'ferias_prop'        => $feriasProp + $feriasPropTerco,
        'ferias_prop_base'   => $feriasProp,
        'decimo_prop'        => $decimoProp,
        'fgts_deposito'      => $fgtsDeposito,
        'multa'              => $multa,
        'multa_pct'          => $multaPct * 100,
        'bruto'              => $bruto,
        'tributavel'         => $tributavel,
        'inss'               => $inss,
        'irrf'               => $irrf,
        'desconto_aviso'     => $descontoAviso,
        'total_descontos'    => $inss + $irrf + $descontoAviso,
        'liquido'            => $liquido,
    ];
}
