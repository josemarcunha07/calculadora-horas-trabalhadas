<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/funcoes.php';

$config = require __DIR__ . '/includes/config.php';

$form = [
    'salario'     => '',
    'dias'        => '30',
    'vender10'    => false,
    'dependentes' => '0',
];

$erros     = [];
$resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['salario']     = (string) ($_POST['salario'] ?? '');
    $form['dias']        = (string) ($_POST['dias'] ?? '30');
    $form['vender10']    = !empty($_POST['vender10']);
    $form['dependentes'] = (string) ($_POST['dependentes'] ?? '0');

    $dados = [
        'salario'     => valorNumerico($form['salario']),
        'dias'        => valorNumerico($form['dias']),
        'vender10'    => $form['vender10'],
        'dependentes' => (int) valorNumerico($form['dependentes']),
    ];

    if ($dados['salario'] <= 0) {
        $erros[] = 'Informe um salário bruto válido (maior que zero).';
    }
    if ($dados['dias'] < 1 || $dados['dias'] > 30) {
        $erros[] = 'Os dias de férias devem ficar entre 1 e 30.';
    }
    if ($dados['dependentes'] < 0) {
        $erros[] = 'O número de dependentes não pode ser negativo.';
    }

    if (empty($erros)) {
        $resultado = calcFerias($dados, $config);
    }
}

$paginaAtiva  = 'ferias';
$tituloPagina = 'Calculadora de Férias · ' . $config['app_name'];
require __DIR__ . '/includes/header.php';
?>

    <section class="hero">
        <h1>Calculadora de Férias</h1>
        <p>Veja quanto você tem a receber pelas férias: o valor dos dias descansados, o
           adicional de 1/3 constitucional e, se quiser, o abono pecuniário (venda de 10 dias).</p>
    </section>

    <div class="grid">

        <section class="card">
            <h2 class="card__title">Dados das férias</h2>

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
                    <label for="salario">Salário bruto mensal (R$)</label>
                    <input type="text" inputmode="decimal" name="salario" id="salario"
                           value="<?= htmlspecialchars($form['salario']) ?>"
                           placeholder="Ex.: 3.500,00" required>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="dias">Dias de férias</label>
                        <input type="number" step="1" min="1" max="30" name="dias" id="dias"
                               value="<?= htmlspecialchars($form['dias']) ?>" required>
                        <small class="field__hint">Normalmente 30 dias.</small>
                    </div>
                    <div class="field">
                        <label for="dependentes">Dependentes (IRRF)</label>
                        <input type="number" step="1" min="0" name="dependentes" id="dependentes"
                               value="<?= htmlspecialchars($form['dependentes']) ?>">
                        <small class="field__hint"><?= formataMoeda((float) $config['irrf_dependente']) ?> por dependente.</small>
                    </div>
                </div>

                <label class="toggle-card">
                    <input type="checkbox" name="vender10" id="vender10" value="1"
                           <?= $form['vender10'] ? 'checked' : '' ?>>
                    <span class="switch__track"></span>
                    <span class="toggle-card__text">
                        <strong>Vender 10 dias (abono pecuniário)</strong>
                        <small>Você descansa menos e recebe o valor dos dias vendidos + 1/3.</small>
                    </span>
                </label>

                <button type="submit" class="btn btn--primary">Calcular férias</button>
            </form>
        </section>

        <section class="card card--result">
            <h2 class="card__title">Resultado</h2>

            <?php if ($resultado === null): ?>
                <div class="empty">
                    <span class="empty__icon">&#127958;</span>
                    <p>Informe o salário e os dias para ver o valor das férias.</p>
                </div>
            <?php else: ?>
                <div class="highlight">
                    <span>Total bruto a receber</span>
                    <strong><?= formataMoeda($resultado['bruto']) ?></strong>
                    <small>Líquido estimado: <?= formataMoeda($resultado['liquido']) ?></small>
                </div>

                <dl class="details">
                    <div class="details__item">
                        <dt>Valor por dia (salário ÷ 30)</dt>
                        <dd><?= formataMoeda($resultado['valor_dia']) ?></dd>
                    </div>
                    <div class="details__item">
                        <dt>Férias (<?= number_format($resultado['dias'], 0) ?> dias)</dt>
                        <dd><?= formataMoeda($resultado['ferias_base']) ?></dd>
                    </div>
                    <div class="details__item">
                        <dt>1/3 constitucional</dt>
                        <dd><?= formataMoeda($resultado['terco']) ?></dd>
                    </div>
                    <?php if ($resultado['abono_dias'] > 0): ?>
                    <div class="details__item">
                        <dt>Abono pecuniário (<?= (int) $resultado['abono_dias'] ?> dias)</dt>
                        <dd><?= formataMoeda($resultado['abono']) ?></dd>
                    </div>
                    <div class="details__item">
                        <dt>1/3 sobre o abono</dt>
                        <dd><?= formataMoeda($resultado['abono_terco']) ?></dd>
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
                        <dt>Desconto IRRF<?= $resultado['dependentes'] > 0 ? ' (' . $resultado['dependentes'] . ' dependente' . ($resultado['dependentes'] > 1 ? 's' : '') . ')' : '' ?></dt>
                        <dd>- <?= formataMoeda($resultado['irrf']) ?></dd>
                    </div>
                    <div class="details__item details__item--total">
                        <dt>Líquido estimado</dt>
                        <dd><?= formataMoeda($resultado['liquido']) ?></dd>
                    </div>
                </dl>

                <p class="disclaimer">
                    Valores estimados com base na CLT e nas tabelas de INSS e IRRF de 2026.
                    O abono pecuniário é isento de INSS e IRRF sobre a parte indenizada, mas aqui
                    aplicamos a tributação de forma simplificada.
                </p>
            <?php endif; ?>
        </section>

    </div>

<?php require __DIR__ . '/includes/footer.php'; ?>
