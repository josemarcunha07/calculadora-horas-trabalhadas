/*
|--------------------------------------------------------------------------
| Calculadora Trabalhista — interface, abas e busca
|--------------------------------------------------------------------------
*/
(function () {
    'use strict';

    const C = window.Calc;
    const $ = (sel, ctx) => (ctx || document).querySelector(sel);
    const $$ = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));

    $('#ano').textContent = new Date().getFullYear();

    /* ------------------------------- Abas -------------------------------- */

    const paineis = $$('.tabpanel');

    function ativaAba(nome) {
        const alvo = $('#tab-' + nome);
        if (!alvo) return;
        paineis.forEach((p) => p.classList.toggle('is-active', p === alvo));
        $$('.mainnav__link').forEach((b) => b.classList.toggle('is-active', b.dataset.tab === nome));
        if (history.replaceState) history.replaceState(null, '', '#' + nome);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    $$('[data-tab]').forEach((btn) => {
        btn.addEventListener('click', () => ativaAba(btn.dataset.tab));
    });

    if (location.hash) {
        const inicial = location.hash.replace('#', '');
        if ($('#tab-' + inicial)) ativaAba(inicial);
    }

    /* -------------------------- Helpers de UI ---------------------------- */

    function mostraErros(form, erros) {
        const box = form.closest('.card').querySelector('[data-erros]');
        if (!box) return;
        if (!erros || !erros.length) {
            box.hidden = true;
            box.innerHTML = '';
            return;
        }
        box.hidden = false;
        box.innerHTML = '<strong>Corrija os itens abaixo:</strong><ul>' +
            erros.map((e) => '<li>' + e + '</li>').join('') + '</ul>';
    }

    function resultadoDe(form) {
        return form.closest('.grid').querySelector('[data-result]');
    }

    function linhas(itens) {
        return '<dl class="details">' + itens.map((i) =>
            '<div class="details__item' + (i.total ? ' details__item--total' : '') + '">' +
            '<dt>' + i.k + '</dt><dd>' + i.v + '</dd></div>'
        ).join('') + '</dl>';
    }

    function destaque(rotulo, valor, sub) {
        return '<div class="highlight"><span>' + rotulo + '</span><strong>' + valor + '</strong>' +
            (sub ? '<small>' + sub + '</small>' : '') + '</div>';
    }

    function cabecalho(rotulo, valor, sub) {
        return '<div class="result-head"><div>' +
            '<span class="result-head__label">' + rotulo + '</span>' +
            '<strong class="result-head__value">' + valor + '</strong>' +
            (sub ? '<span class="result-head__sub">' + sub + '</span>' : '') +
            '</div></div>';
    }

    const NOTA = '<p class="disclaimer">Valores estimados. Não substitui o cálculo oficial do empregador ou contador.</p>';
    const m = C.formataMoeda;

    /* --------------------------- Profissões ------------------------------ */

    const selProfissao = $('#profissao');
    const customField = $('#profissaoCustomField');
    const salarioBase = $('#salario_base');
    const categoriaHint = $('#categoriaHint');

    selProfissao.innerHTML = '<option value="">Selecione a sua profissão...</option>' +
        Object.keys(window.PROFISSOES).map((cat) =>
            '<optgroup label="' + cat + '">' +
            window.PROFISSOES[cat].map((c) =>
                '<option value="' + c.cargo + '" data-categoria="' + cat + '" data-salario="' + c.salario + '">' +
                c.cargo + ' — ' + m(c.salario) + '</option>'
            ).join('') + '</optgroup>'
        ).join('') +
        '<option value="__outra__">Outra profissão (não está na lista)</option>';

    selProfissao.addEventListener('change', () => {
        const valor = selProfissao.value;
        const dados = window.MAPA_PROFISSOES[valor];
        const ehOutra = valor === '__outra__';
        customField.hidden = !ehOutra;

        if (ehOutra) {
            salarioBase.value = '';
            categoriaHint.textContent = 'Profissão personalizada: informe o salário abaixo.';
            $('#profissao_custom').focus();
        } else if (dados) {
            salarioBase.value = dados.salario.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            categoriaHint.textContent = 'Categoria: ' + dados.categoria + ' — média de ' + m(dados.salario) + '.';
        } else {
            salarioBase.value = '';
            categoriaHint.textContent = 'A média salarial da categoria será preenchida automaticamente.';
        }
    });

    /* ----------------------------- Toggles ------------------------------- */

    $('#trabalha_fds').addEventListener('change', (e) => {
        const campo = $('#fdsField');
        campo.hidden = !e.target.checked;
        if (e.target.checked) $('#horas_fds').focus();
    });

    // Data de desligamento padrão = hoje
    const hoje = new Date();
    $('#r_demissao').value = hoje.getFullYear() + '-' +
        String(hoje.getMonth() + 1).padStart(2, '0') + '-' +
        String(hoje.getDate()).padStart(2, '0');

    /* --------------------------- Formulários ----------------------------- */

    // Horas trabalhadas
    $('[data-calc="horas"]').addEventListener('submit', (e) => {
        e.preventDefault();
        const f = e.target;
        const profissao = f.profissao.value;
        const personalizada = f.profissao_custom.value.trim();
        const salario = C.parseNumero(f.salario.value);
        const horasDia = C.parseNumero(f.horas_dia.value);
        const diasSemana = C.parseNumero(f.dias_semana.value);
        const horasExtras = C.parseNumero(f.horas_extras.value);
        const trabalhaFds = f.trabalha_fds.checked;
        const horasFds = C.parseNumero(f.horas_fds.value);

        const erros = [];
        if (!profissao) erros.push('Selecione uma profissão.');
        else if (profissao === '__outra__' && !personalizada) erros.push('Informe o nome da sua profissão.');
        if (salario <= 0) erros.push('Informe um salário base válido (maior que zero).');
        if (horasDia <= 0) erros.push('Informe quantas horas normais você trabalha por dia.');
        if (horasDia > 24) erros.push('As horas por dia não podem passar de 24.');
        if (diasSemana < 1 || diasSemana > 7) erros.push('Os dias por semana devem ficar entre 1 e 7.');
        if (horasFds > 0 && !trabalhaFds) erros.push('Marque "trabalho em finais de semana" para contar essas horas.');
        if (erros.length) return mostraErros(f, erros);
        mostraErros(f, []);

        const r = C.calcHoras({ salario, horasDia, diasSemana, horasExtras, trabalhaFds, horasFds });
        const categoria = profissao === '__outra__' ? 'Profissão personalizada' : window.MAPA_PROFISSOES[profissao].categoria;
        const cargo = profissao === '__outra__' ? personalizada : profissao;

        resultadoDe(f).innerHTML =
            cabecalho('Categoria', categoria, cargo) +
            destaque('Total estimado por mês', m(r.totalMensal), m(r.totalAnual) + ' por ano') +
            linhas([
                { k: 'Valor da hora normal', v: m(r.valorHora) },
                { k: 'Valor da hora extra (+50%)', v: m(r.valorHoraExtra) },
                { k: 'Valor da hora em fim de semana (+100%)', v: m(r.valorHoraFds) },
                { k: 'Jornada mensal normal', v: C.formataHoras(r.jornadaMensal) },
                { k: 'Horas extras no mês', v: C.formataHoras(r.horasExtrasMes) },
                { k: 'Horas em finais de semana no mês', v: C.formataHoras(r.horasFdsMes) },
                { k: 'Ganho com horas extras', v: m(r.totalExtras) },
                { k: 'Ganho com finais de semana', v: m(r.totalFds) },
                { k: 'Salário base', v: m(r.salario) },
                { k: 'Valor médio por hora trabalhada', v: m(r.valorHoraEfetivo), total: true },
            ]) + NOTA;
    });

    // Férias
    $('[data-calc="ferias"]').addEventListener('submit', (e) => {
        e.preventDefault();
        const f = e.target;
        const salario = C.parseNumero(f.salario.value);
        const dias = C.parseNumero(f.dias.value);
        const dependentes = C.parseNumero(f.dependentes.value);
        const vender10 = f.vender10.checked;

        const erros = [];
        if (salario <= 0) erros.push('Informe um salário bruto válido (maior que zero).');
        if (dias < 1 || dias > 30) erros.push('Os dias de férias devem ficar entre 1 e 30.');
        if (erros.length) return mostraErros(f, erros);
        mostraErros(f, []);

        const r = C.calcFerias({ salario, dias, dependentes, vender10 });
        const itens = [
            { k: 'Valor por dia (salário ÷ 30)', v: m(r.valorDia) },
            { k: 'Férias (' + r.dias + ' dias)', v: m(r.feriasBase) },
            { k: '1/3 constitucional', v: m(r.terco) },
        ];
        if (r.abonoDias > 0) {
            itens.push({ k: 'Abono pecuniário (' + r.abonoDias + ' dias)', v: m(r.abono) });
            itens.push({ k: '1/3 sobre o abono', v: m(r.abonoTerco) });
        }
        itens.push({ k: 'Total bruto', v: m(r.bruto) });
        itens.push({ k: 'Desconto INSS', v: '- ' + m(r.inss) });
        itens.push({ k: 'Desconto IRRF', v: '- ' + m(r.irrf) });
        itens.push({ k: 'Líquido estimado', v: m(r.liquido), total: true });

        resultadoDe(f).innerHTML =
            destaque('Total bruto a receber', m(r.bruto), 'Líquido estimado: ' + m(r.liquido)) +
            linhas(itens) +
            '<p class="disclaimer">Cálculo estimado com base na CLT e nas tabelas de INSS/IRRF de 2026.</p>';
    });

    // 13º salário
    $('[data-calc="decimo"]').addEventListener('submit', (e) => {
        e.preventDefault();
        const f = e.target;
        const salario = C.parseNumero(f.salario.value);
        const avos = C.parseNumero(f.avos.value);
        const dependentes = C.parseNumero(f.dependentes.value);

        const erros = [];
        if (salario <= 0) erros.push('Informe um salário bruto válido (maior que zero).');
        if (avos < 0 || avos > 12) erros.push('Os meses trabalhados devem ficar entre 0 e 12.');
        if (erros.length) return mostraErros(f, erros);
        mostraErros(f, []);

        const r = C.calcDecimo({ salario, avos, dependentes });
        resultadoDe(f).innerHTML =
            destaque('13º bruto (' + r.avos + '/12)', m(r.bruto), 'Líquido estimado: ' + m(r.liquido)) +
            linhas([
                { k: 'Salário base', v: m(r.salario) },
                { k: '1ª parcela (50%, sem descontos)', v: m(r.primeiraParcela) },
                { k: 'Desconto INSS', v: '- ' + m(r.inss) },
                { k: 'Desconto IRRF', v: '- ' + m(r.irrf) },
                { k: '2ª parcela (com descontos)', v: m(r.segundaParcela) },
                { k: 'Líquido total', v: m(r.liquido), total: true },
            ]) +
            '<p class="disclaimer">INSS e IRRF calculados em separado (tributação exclusiva do 13º), tabelas 2026.</p>';
    });

    // FGTS
    $('[data-calc="fgts"]').addEventListener('submit', (e) => {
        e.preventDefault();
        const f = e.target;
        const salario = C.parseNumero(f.salario.value);
        const meses = C.parseNumero(f.meses.value);
        const incluirDecimo = f.incluirDecimo.checked;

        const erros = [];
        if (salario <= 0) erros.push('Informe um salário válido (maior que zero).');
        if (meses < 0) erros.push('Os meses não podem ser negativos.');
        if (erros.length) return mostraErros(f, erros);
        mostraErros(f, []);

        const r = C.calcFgts({ salario, meses, incluirDecimo });
        const itens = [
            { k: 'Depósito mensal (8%)', v: m(r.depositoMensal) },
            { k: 'Depósito sobre o período (' + r.meses + ' meses)', v: m(r.totalPeriodo) },
        ];
        if (r.incluirDecimo) itens.push({ k: 'Depósito sobre o 13º', v: m(r.depositoDecimo) });
        itens.push({ k: 'Saldo total estimado', v: m(r.total), total: true });
        itens.push({ k: 'Multa 40% (sem justa causa)', v: m(r.multaSemJusta) });
        itens.push({ k: 'Multa 20% (acordo)', v: m(r.multaAcordo) });

        resultadoDe(f).innerHTML =
            destaque('Saldo estimado do FGTS', m(r.total), 'Depósito de 8% ao mês') +
            linhas(itens) +
            '<p class="disclaimer">Estimativa de depósitos. Não considera correção monetária (Selic/TR) nem o extrato oficial do FGTS.</p>';
    });

    // Rescisão
    $('[data-calc="rescisao"]').addEventListener('submit', (e) => {
        e.preventDefault();
        const f = e.target;
        const salario = C.parseNumero(f.salario.value);
        const admissao = C.parseData(f.admissao.value);
        const demissao = C.parseData(f.demissao.value);
        const tipo = f.tipo.value;
        const aviso = f.aviso.value;
        const feriasVencidas = C.parseNumero(f.ferias_vencidas.value);
        const dependentes = C.parseNumero(f.dependentes.value);

        const erros = [];
        if (salario <= 0) erros.push('Informe um salário base válido (maior que zero).');
        if (!admissao || !demissao) erros.push('Informe as datas de admissão e desligamento.');
        else if (demissao < admissao) erros.push('A data de desligamento não pode ser anterior à admissão.');
        if (erros.length) return mostraErros(f, erros);
        mostraErros(f, []);

        const r = C.calcRescisao({ salario, admissao, demissao, tipo, aviso, feriasVencidas, dependentes });

        const itens = [
            { k: 'Saldo de salário (' + r.diasMes + ' dias)', v: m(r.saldo) },
        ];
        if (r.avisoValor > 0) itens.push({ k: 'Aviso prévio indenizado (' + r.diasAviso + ' dias)', v: m(r.avisoValor) });
        if (r.feriasVencidas > 0) itens.push({ k: 'Férias vencidas + 1/3 (' + r.feriasVencQtd + ' período(s))', v: m(r.feriasVencidas) });
        if (r.feriasProp > 0) itens.push({ k: 'Férias proporcionais + 1/3 (' + r.avos + ' avos)', v: m(r.feriasProp) });
        if (r.decimoProp > 0) itens.push({ k: '13º proporcional (' + r.mesesAno + ' avos)', v: m(r.decimoProp) });
        if (r.multa > 0) itens.push({ k: 'Multa do FGTS (' + Math.round(r.multaPct) + '%)', v: m(r.multa) });
        itens.push({ k: 'Total bruto', v: m(r.bruto) });
        itens.push({ k: 'Desconto INSS', v: '- ' + m(r.inss) });
        itens.push({ k: 'Desconto IRRF', v: '- ' + m(r.irrf) });
        if (r.descontoAviso > 0) itens.push({ k: 'Desconto de aviso não trabalhado', v: '- ' + m(r.descontoAviso) });
        itens.push({ k: 'Líquido estimado', v: m(r.liquido), total: true });

        resultadoDe(f).innerHTML =
            cabecalho('Período', C.formataData(f.admissao.value) + ' a ' + C.formataData(f.demissao.value),
                r.anos + ' ano(s) completo(s) · ' + r.mesesTotais + ' meses') +
            destaque('Total bruto estimado', m(r.bruto), 'Líquido estimado: ' + m(r.liquido)) +
            linhas(itens) +
            '<p class="disclaimer">Estimativa com base na CLT e nas tabelas 2026. A multa do FGTS usa depósitos aproximados (8% ao mês) e não substitui o extrato oficial.</p>';
    });

    // Insalubridade
    $('[data-calc="insalubridade"]').addEventListener('submit', (e) => {
        e.preventDefault();
        const f = e.target;
        const grau = f.grau.value;
        const baseTipo = f.baseTipo.value;
        const salario = C.parseNumero(f.salario.value);
        const periculosidade = f.periculosidade.checked;

        const erros = [];
        if ((baseTipo === 'salario' || periculosidade) && salario <= 0) {
            erros.push('Informe o salário base para calcular pela sua remuneração ou a periculosidade.');
        }
        if (erros.length) return mostraErros(f, erros);
        mostraErros(f, []);

        const r = C.calcInsalubridade({ grau, baseTipo, salario, periculosidade });
        const itens = [
            { k: 'Base de cálculo', v: m(r.base) + (r.baseTipo === 'salario' ? ' (seu salário)' : ' (salário mínimo)') },
            { k: 'Percentual do grau', v: C.formataNumero(r.percentual * 100, 0) + '%' },
            { k: 'Adicional de insalubridade', v: m(r.valorMensal) },
            { k: 'Valor anual (com 13º/reflexos não inclusos)', v: m(r.valorAnual) },
        ];
        if (r.periculosidade > 0) itens.push({ k: 'Adicional de periculosidade (30%)', v: m(r.periculosidade) });
        itens.push({ k: 'Total mensal', v: m(r.totalMensal), total: true });

        resultadoDe(f).innerHTML =
            destaque('Total mensal de adicionais', m(r.totalMensal), 'Insalubridade' + (r.periculosidade > 0 ? ' + periculosidade' : '')) +
            linhas(itens) +
            '<p class="disclaimer">A base padrão da insalubridade é o salário mínimo (definição do STF). Algumas categorias usam base própria por convenção.</p>';
    });

    /* --------------------------- Busca Google ---------------------------- */

    const searchForm = $('#searchForm');
    const searchInput = $('#searchInput');
    const cseId = window.CONFIG.googleCseId;

    if (cseId) {
        $('#cseHint').hidden = true;
        const slot = $('#cseSlot');
        slot.innerHTML = '<div class="cse-panel" id="csePanel" hidden>' +
            '<div class="cse-panel__header"><strong>Resultados da pesquisa</strong>' +
            '<button type="button" class="cse-panel__close" id="cseClose" aria-label="Fechar">&times;</button></div>' +
            '<div class="gcse-searchresults-only" data-gname="calculadora"></div></div>';
        const s = document.createElement('script');
        s.async = true;
        s.src = 'https://cse.google.com/cse.js?cx=' + encodeURIComponent(cseId);
        document.body.appendChild(s);

        $('#cseClose').addEventListener('click', () => { $('#csePanel').hidden = true; });
    }

    function getCseElement() {
        if (window.google && google.search && google.search.cse && google.search.cse.element) {
            return google.search.cse.element.getElement('calculadora');
        }
        return null;
    }

    searchForm.addEventListener('submit', (event) => {
        const termo = (searchInput.value || '').trim();
        if (!termo) {
            event.preventDefault();
            searchInput.focus();
            return;
        }
        if (cseId) {
            const el = getCseElement();
            if (el && typeof el.execute === 'function') {
                event.preventDefault();
                $('#csePanel').hidden = false;
                el.execute(termo);
            }
        }
    });
})();
