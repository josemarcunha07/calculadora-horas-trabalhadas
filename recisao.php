<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/funcoes.php';

$config = require __DIR__ . '/includes/config.php';

$tipos = [
    'sem_justa' => 'Dispensa sem justa causa',
    'com_justa' => 'Dispensa por justa causa',
    'pedido'    => 'Pedido de demissão',
    'acordo'    => 'Acordo entre as partes (art. 484-A)',
    'termino'   => 'Término de contrato (prazo determinado)',
];

$form = [
    'salario'         => '',
    'admissao'        => '',
    'demissao'        => date('Y-m-d'),
    'tipo'            => 'sem_justa',
    'aviso'           => 'indenizado',
    'ferias_vencidas' => '0',
    'dependentes'     => '0',
];

$erros     = [];
$resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['salario']         = (string) ($_POST['salario'] ?? '');
    $form['admissao']        = (string) ($_POST['admissao'] ?? '');
    $form['demissao']        = (string) ($_POST['demissao'] ?? '');
    $form['tipo']            = (string) ($_POST['tipo'] ?? 'sem_justa');
    $form['aviso']           = (string) ($_POST['aviso'] ?? 'indenizado');
    $form['ferias_vencidas'] = (string) ($_POST['ferias_vencidas'] ?? '0');
    $form['dependentes']     = (string) ($_POST['dependentes'] ?? '0');

    $dados = [
        'salario'         => valorNumerico($form['salario']),
        'admissao'        => $form['admissao'],
        'demissao'        => $form['demissao'],
        'tipo'            => array_key_exists($form['tipo'], $tipos) ? $form['tipo'] : 'sem_justa',
        'aviso'           => in_array($form['aviso'], ['indenizado', 'trabalhado', 'nao'], true) ? $form['aviso'] : 'indenizado',
        'ferias_vencidas' => (int) valorNumerico($form['ferias_vencidas']),
        'dependentes'     => (int) valorNumerico($form['dependentes']),
    ];

    if ($dados['salario'] <= 0) {
        $erros[] = 'Informe um salário base válido (maior que zero).';
    }
    if (empty($dados['admissao']) || empty($dados['demissao'])) {
        $erros[] = 'Informe as datas de admissão e de desligamento.';
    } else {
        $adm = DateTime::createFromFormat('Y-m-d', $dados['admissao']);
        $dem = DateTime::createFromFormat('Y-m-d', $dados['demissao']);
        if (!$adm || !$dem) {
            $erros[] = 'Datas inválidas.';
        } elseif ($dem < $adm) {
            $erros[] = 'A data de desligamento não pode ser anterior à admissão.';
        }
    }

    if (empty($erros)) {
        $resultado = calcRescisao($dados, $config);
    }
}

$paginaAtiva  = 'recisao';
$tituloPagina = 'Calculadora de Rescisão · ' . $config['app_name'];
require __DIR__ . '/includes/header.php';
?>

    <section class="hero">
        <h1>Calculadora de Rescisão</h1>
        <p>Estime as principais verbas rescisórias: saldo de salário, aviso prévio,
           férias vencidas e proporcionais, 13º proporcional e a multa do FGTS.</p>
    </section>

    <div class="grid">

        <section class="card">
            <h2 class="card__title">Dados do contrato</h2>

            <?php if (!empty($erros)): ?>
                <div class="alert alert--error">
                    <strong>Corrija os itens abaixo:</strong>
                    <ul>
                        <?php foreach ($erros as $erro): ?>
                            <li><?= htmlspecialchars($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate>

                <div class="field">
                    <label for="salario">Salário base mensal (R$)</label>
                    <input type="text" inputmode="decimal" name="salario" id="salario"
                           value="<?= htmlspecialchars($form['salario']) ?>"
                           placeholder="Ex.: 3.500,00" required>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="admissao">Data de admissão</label>
                        <input type="date" name="admissao" id="admissao"
                               value="<?= htmlspecialchars($form['admissao']) ?>" required>
                    </div>
                    <div class="field">
                        <label for="demissao">Data de desligamento</label>
                        <input type="date" name="demissao" id="demissao"
                               value="<?= htmlspecialchars($form['demissao']) ?>" required>
                    </div>
                </div>

                <div class="field">
                    <label for="tipo">Motivo da rescisão</label>
                    <select name="tipo" id="tipo">
                        <?php foreach ($tipos as $valor => $rotulo): ?>
                            <option value="<?= $valor ?>" <?= $form['tipo'] === $valor ? 'selected' : '' ?>>
                                <?= htmlspecialchars($rotulo) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="aviso">Aviso prévio</label>
                    <select name="aviso" id="aviso">
                        <option value="indenizado" <?= $form['aviso'] === 'indenizado' ? 'selected' : '' ?>>Indenizado (não trabalhado)</option>
                        <option value="trabalhado" <?= $form['aviso'] === 'trabalhado' ? 'selected' : '' ?>>Trabalhado</option>
                        <option value="nao" <?= $form['aviso'] === 'nao' ? 'selected' : '' ?>>Não se aplica</option>
                    </select>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="ferias_vencidas">Férias vencidas (períodos)</label>
                        <input type="number" step="1" min="0" name="ferias_vencidas" id="ferias_vencidas"
                               value="<?= htmlspecialchars($form['ferias_vencidas']) ?>">
                        <small class="field__hint">Períodos de 12 meses não gozados.</small>
                    </div>
                    <div class="field">
                        <label for="dependentes">Dependentes (IRRF)</label>
                        <input type="number" step="1" min="0" name="dependentes" id="dependentes"
                               value="<?= htmlspecialchars($form['dependentes']) ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn--primary">Calcular rescisão</button>
            </form>
        </section>

        <section class="card card--result">
            <h2 class="card__title">Resultado</h2>

            <?php if ($resultado === null): ?>
                <div class="empty">
                    <span class="empty__icon">&#128188;</span>
                    <p>Preencha os dados do contrato para estimar as verbas rescisórias.</p>
                </div>
            <?php else: ?>
                <div class="result-head">
                    <div>
                        <span class="result-head__label">Período</span>
                        <strong class="result-head__value">
                            <?= formataData($form['admissao']) ?> a <?= formataData($form['demissao']) ?>
                        </strong>
                        <span class="result-head__sub">
                            <?= $resultado['anos'] ?> ano(s) completo(s) · <?= $resultado['meses_totais'] ?> meses
                        </span>
                    </div>
                </div>

                <div class="highlight">
                    <span>Total bruto estimado</span>
                    <strong><?= formataMoeda($resultado['bruto']) ?></strong>
                    <small>Líquido estimado: <?= formataMoeda($resultado['liquido']) ?></small>
                </div>

                <dl class="details">
                    <div class="details__item">
                        <dt>Saldo de salário (<?= (int) $resultado['dias_mes'] ?> dias)</dt>
                        <dd><?= formataMoeda($resultado['saldo']) ?></dd>
                    </div>
                    <?php if ($resultado['aviso_valor'] > 0): ?>
                    <div class="details__item">
                        <dt>Aviso prévio indenizado (<?= (int) $resultado['dias_aviso'] ?> dias)</dt>
                        <dd><?= formataMoeda($resultado['aviso_valor']) ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if ($resultado['ferias_vencidas'] > 0): ?>
                    <div class="details__item">
                        <dt>Férias vencidas + 1/3 (<?= (int) $resultado['ferias_venc_qtd'] ?> período(s))</dt>
                        <dd><?= formataMoeda($resultado['ferias_vencidas']) ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if ($resultado['ferias_prop'] > 0): ?>
                    <div class="details__item">
                        <dt>Férias proporcionais + 1/3 (<?= (int) $resultado['avos'] ?> avos)</dt>
                        <dd><?= formataMoeda($resultado['ferias_prop']) ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if ($resultado['decimo_prop'] > 0): ?>
                    <div class="details__item">
                        <dt>13º proporcional (<?= (int) $resultado['meses_ano'] ?> avos)</dt>
                        <dd><?= formataMoeda($resultado['decimo_prop']) ?></dd>
                    </div>
                    <?php endif; ?>
                    <?php if ($resultado['multa'] > 0): ?>
                    <div class="details__item">
                        <dt>Multa do FGTS (<?= number_format($resultado['multa_pct'], 0) ?>%)</dt>
                        <dd><?= formataMoeda($resultado['multa']) ?></dd>
                    </div>
                    <?php endif; ?>
                    <div class="details__item">
                        <dt>Total bruto</dt>
                        <dd><?= formataMoeda($resultado['bruto']) ?></dd>
                    </div>
                    <div class="details__item">
                        <dt>Desconto INSS</dt>
                        <dd>- <?= formataMoeda($resultado['inss']) ?></dd>
                    </div>
                    <div class="details__item">
                        <dt>Desconto IRRF</dt>
                        <dd>- <?= formataMoeda($resultado['irrf']) ?></dd>
                    </div>
                    <?php if ($resultado['desconto_aviso'] > 0): ?>
                    <div class="details__item">
                        <dt>Desconto de aviso prévio não trabalhado</dt>
                        <dd>- <?= formataMoeda($resultado['desconto_aviso']) ?></dd>
                    </div>
                    <?php endif; ?>
                    <div class="details__item details__item--total">
                        <dt>Líquido estimado</dt>
                        <dd><?= formataMoeda($resultado['liquido']) ?></dd>
                    </div>
                </dl>

                <p class="disclaimer">
                    Estimativa com base na CLT e nas tabelas de INSS/IRRF de 2026.
                    A multa do FGTS usa uma estimativa de depósitos (<?= number_format((float) $config['fgts_aliquota'] * 100, 0) ?>% ao mês)
                    e não substitui o extrato oficial do FGTS. Saldo de FGTS a sacar não está incluso no total.
                </p>
            <?php endif; ?>
        </section>

    </div>

<?php require __DIR__ . '/includes/footer.php'; ?>
