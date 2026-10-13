<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/funcoes.php';

$config     = require __DIR__ . '/includes/config.php';
$profissoes = require __DIR__ . '/includes/profissoes.php';

/*
 * Mapa achatado (cargo => dados) usado para preencher o salário via JS.
 */
$mapaProfissoes = [];
foreach ($profissoes as $categoria => $cargos) {
    foreach ($cargos as $cargo) {
        $mapaProfissoes[$cargo['cargo']] = [
            'categoria' => $categoria,
            'salario'   => $cargo['salario'],
        ];
    }
}

$form = [
    'profissao'        => '',
    'profissao_custom' => '',
    'salario_base'     => '',
    'horas_dia'        => '8',
    'dias_semana'      => '5',
    'horas_extras'     => '0',
    'trabalha_fds'     => false,
    'horas_fds'        => '0',
];

$erros        = [];
$resultado    = null;
$categoriaSel = '';
$cargoSel     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['profissao']        = trim((string) ($_POST['profissao'] ?? ''));
    $form['profissao_custom'] = trim((string) ($_POST['profissao_custom'] ?? ''));
    $form['salario_base']     = (string) ($_POST['salario_base'] ?? '');
    $form['horas_dia']        = (string) ($_POST['horas_dia'] ?? '');
    $form['dias_semana']      = (string) ($_POST['dias_semana'] ?? '');
    $form['horas_extras']     = (string) ($_POST['horas_extras'] ?? '0');
    $form['trabalha_fds']     = !empty($_POST['trabalha_fds']);
    $form['horas_fds']        = (string) ($_POST['horas_fds'] ?? '0');

    $dados = [
        'profissao'        => $form['profissao'],
        'profissao_custom' => $form['profissao_custom'],
        'salario_base'     => valorNumerico($form['salario_base']),
        'horas_dia'        => valorNumerico($form['horas_dia']),
        'dias_semana'      => valorNumerico($form['dias_semana']),
        'horas_extras'     => valorNumerico($form['horas_extras']),
        'trabalha_fds'     => $form['trabalha_fds'],
        'horas_fds'        => valorNumerico($form['horas_fds']),
    ];

    $erros = validaEntrada($dados);

    if ($form['profissao'] === '__outra__') {
        $categoriaSel = 'Profissão personalizada';
        $cargoSel     = $form['profissao_custom'];
    } elseif (isset($mapaProfissoes[$form['profissao']])) {
        $categoriaSel = $mapaProfissoes[$form['profissao']]['categoria'];
        $cargoSel     = $form['profissao'];
    }

    if (empty($erros)) {
        $resultado = calcularRemuneracao($dados, $config);
    }
}

$paginaAtiva  = 'horas';
$tituloPagina = $config['app_name'];
require __DIR__ . '/includes/header.php';
?>

    <section class="hero">
        <h1>Quanto vale a sua hora?</h1>
        <p>Informe sua profissão e sua jornada. Calculamos a média salarial da categoria,
           o valor da hora normal, as horas extras e o trabalho em finais de semana.</p>
    </section>

    <div class="grid">

        <section class="card">
            <h2 class="card__title">Dados da jornada</h2>

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

            <form method="POST" action="" id="calcForm" novalidate>

                <div class="field">
                    <label for="profissao">Profissão / Cargo</label>
                    <select name="profissao" id="profissao" required>
                        <option value="">Selecione a sua profissão...</option>
                        <?php foreach ($profissoes as $categoria => $cargos): ?>
                            <optgroup label="<?= htmlspecialchars($categoria) ?>">
                                <?php foreach ($cargos as $cargo): ?>
                                    <option value="<?= htmlspecialchars($cargo['cargo']) ?>"
                                            data-categoria="<?= htmlspecialchars($categoria) ?>"
                                            data-salario="<?= htmlspecialchars((string) $cargo['salario']) ?>"
                                            <?= $form['profissao'] === $cargo['cargo'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cargo['cargo']) ?>
                                        — <?= formataMoeda($cargo['salario']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                        <option value="__outra__" <?= $form['profissao'] === '__outra__' ? 'selected' : '' ?>>
                            Outra profissão (não está na lista)
                        </option>
                    </select>
                    <small class="field__hint" id="categoriaHint">A média salarial da categoria será preenchida automaticamente.</small>
                </div>

                <div class="field" id="profissaoCustomField" <?= $form['profissao'] === '__outra__' ? '' : 'hidden' ?>>
                    <label for="profissao_custom">Qual é a sua profissão?</label>
                    <input type="text" name="profissao_custom" id="profissao_custom"
                           value="<?= htmlspecialchars($form['profissao_custom']) ?>"
                           placeholder="Ex.: Tatuador(a), Piloto de drone, Barbeiro(a)...">
                    <small class="field__hint">Não temos uma média para essa profissão — informe o salário abaixo.</small>
                </div>

                <div class="field">
                    <label for="salario_base">Salário base mensal (R$)</label>
                    <input type="text" inputmode="decimal" name="salario_base" id="salario_base"
                           value="<?= htmlspecialchars($form['salario_base']) ?>"
                           placeholder="Ex.: 3.500,00" required>
                    <small class="field__hint">Pode ajustar para o seu salário real, se preferir.</small>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="horas_dia">Horas normais por dia</label>
                        <input type="number" step="0.25" min="0.5" max="24" name="horas_dia" id="horas_dia"
                               value="<?= htmlspecialchars($form['horas_dia']) ?>" required>
                    </div>
                    <div class="field">
                        <label for="dias_semana">Dias por semana</label>
                        <input type="number" step="1" min="1" max="7" name="dias_semana" id="dias_semana"
                               value="<?= htmlspecialchars($form['dias_semana']) ?>" required>
                    </div>
                </div>

                <div class="field">
                    <label for="horas_extras">Horas extras por semana</label>
                    <input type="number" step="0.5" min="0" name="horas_extras" id="horas_extras"
                           value="<?= htmlspecialchars($form['horas_extras']) ?>">
                    <small class="field__hint">Adicional de <?= number_format($config['adicional_hora_extra'] * 100, 0) ?>%.</small>
                </div>

                <label class="toggle-card">
                    <input type="checkbox" name="trabalha_fds" id="trabalha_fds" value="1"
                           <?= $form['trabalha_fds'] ? 'checked' : '' ?>>
                    <span class="switch__track"></span>
                    <span class="toggle-card__text">
                        <strong>Trabalho em finais de semana</strong>
                        <small>Inclui horas de sábado, domingo e feriados (adicional de
                            <?= number_format($config['adicional_fim_semana'] * 100, 0) ?>%).</small>
                    </span>
                </label>

                <div class="field" id="fdsField" <?= $form['trabalha_fds'] ? '' : 'hidden' ?>>
                    <label for="horas_fds">Horas em finais de semana por semana</label>
                    <input type="number" step="0.5" min="0" name="horas_fds" id="horas_fds"
                           value="<?= htmlspecialchars($form['horas_fds']) ?>">
                    <small class="field__hint">Adicional de <?= number_format($config['adicional_fim_semana'] * 100, 0) ?>%.</small>
                </div>

                <button type="submit" class="btn btn--primary">Calcular remuneração</button>
            </form>
        </section>

        <section class="card card--result" id="resultado">
            <h2 class="card__title">Resultado</h2>

            <?php if ($resultado === null): ?>
                <div class="empty">
                    <span class="empty__icon">&#128202;</span>
                    <p>Preencha os dados ao lado e clique em <strong>Calcular remuneração</strong>
                       para ver o detalhamento.</p>
                </div>
            <?php else: ?>
                <div class="result-head">
                    <div>
                        <span class="result-head__label">Categoria</span>
                        <strong class="result-head__value"><?= htmlspecialchars($categoriaSel) ?></strong>
                        <span class="result-head__sub"><?= htmlspecialchars($cargoSel) ?></span>
                    </div>
                </div>

                <div class="highlight">
                    <span>Total estimado por mês</span>
                    <strong><?= formataMoeda($resultado['total_mensal']) ?></strong>
                    <small><?= formataMoeda($resultado['total_anual']) ?> por ano</small>
                </div>

                <dl class="details">
                    <div class="details__item">
                        <dt>Valor da hora normal</dt>
                        <dd><?= formataMoeda($resultado['valor_hora']) ?></dd>
                    </div>
                    <div class="details__item">
                        <dt>Valor da hora extra (+<?= number_format($resultado['adicional_extra_pct'], 0) ?>%)</dt>
                        <dd><?= formataMoeda($resultado['valor_hora_extra']) ?></dd>
                    </div>
                    <div class="details__item">
                        <dt>Valor da hora em fim de semana (+<?= number_format($resultado['adicional_fds_pct'], 0) ?>%)</dt>
                        <dd><?= formataMoeda($resultado['valor_hora_fds']) ?></dd>
                    </div>
                    <div class="details__item">
                        <dt>Jornada mensal normal</dt>
                        <dd><?= formataHoras($resultado['jornada_mensal']) ?></dd>
                    </div>
                    <div class="details__item">
                        <dt>Horas extras no mês</dt>
                        <dd><?= formataHoras($resultado['horas_extras_mes']) ?></dd>
                    </div>
                    <div class="details__item">
                        <dt>Horas em finais de semana no mês</dt>
                        <dd><?= formataHoras($resultado['horas_fds_mes']) ?></dd>
                    </div>
                    <div class="details__item">
                        <dt>Ganho com horas extras</dt>
                        <dd><?= formataMoeda($resultado['total_extras']) ?></dd>
                    </div>
                    <div class="details__item">
                        <dt>Ganho com finais de semana</dt>
                        <dd><?= formataMoeda($resultado['total_fds']) ?></dd>
                    </div>
                    <div class="details__item">
                        <dt>Salário base</dt>
                        <dd><?= formataMoeda($resultado['salario_base']) ?></dd>
                    </div>
                    <div class="details__item">
                        <dt>Total de horas no mês</dt>
                        <dd><?= formataHoras($resultado['total_horas_mes']) ?></dd>
                    </div>
                    <div class="details__item details__item--total">
                        <dt>Valor médio por hora trabalhada</dt>
                        <dd><?= formataMoeda($resultado['valor_hora_efetivo']) ?></dd>
                    </div>
                </dl>

                <p class="disclaimer">
                    Valores estimados com base na média da categoria e nos adicionais de
                    <?= number_format($resultado['adicional_extra_pct'], 0) ?>% (hora extra) e
                    <?= number_format($resultado['adicional_fds_pct'], 0) ?>% (fim de semana).
                    Descontos de INSS, IRRF e demais encargos não estão incluídos.
                </p>
            <?php endif; ?>
        </section>

    </div>

    <section class="info">
        <article>
            <h3>Como o cálculo é feito</h3>
            <p>A jornada mensal é obtida multiplicando horas por dia, dias por semana e as
               <?= number_format((float) $config['semanas_por_mes'], 2) ?> semanas médias de um mês.
               A partir dela encontramos o valor da hora normal e aplicamos os adicionais
               sobre as horas extras e o trabalho em finais de semana.</p>
        </article>
        <article>
            <h3>Adicionais legais (CLT)</h3>
            <p>A jornada extra em dias úteis tem acréscimo mínimo de <?= number_format($config['adicional_hora_extra'] * 100, 0) ?>%.
               Já o trabalho em finais de semana e feriados costuma ter adicional de
               <?= number_format($config['adicional_fim_semana'] * 100, 0) ?>%.</p>
        </article>
        <article>
            <h3>Sobre as médias salariais</h3>
            <p>Os valores são referências aproximadas de mercado (IBGE, CAGED, sindicatos).
               Cada empresa e região pode praticar números diferentes, então ajuste o salário
               base para a sua realidade.</p>
        </article>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
