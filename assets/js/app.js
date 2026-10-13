/*
|--------------------------------------------------------------------------
| Calculadora de Horas Trabalhadas — interações do front-end
|--------------------------------------------------------------------------
*/
(function () {
    'use strict';

    /* ----- Preenche o salário a partir da profissão selecionada ----- */
    var selectProfissao = document.getElementById('profissao');
    var inputSalario = document.getElementById('salario_base');
    var categoriaHint = document.getElementById('categoriaHint');
    var customField = document.getElementById('profissaoCustomField');
    var customInput = document.getElementById('profissao_custom');
    var mapa = window.MAPA_PROFISSOES || {};

    function formataMoeda(valor) {
        return valor.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    if (selectProfissao && inputSalario) {
        selectProfissao.addEventListener('change', function () {
            var valor = selectProfissao.value;
            var dados = mapa[valor];
            var ehOutra = valor === '__outra__';

            if (customField) { customField.hidden = !ehOutra; }

            if (ehOutra) {
                inputSalario.value = '';
                if (categoriaHint) {
                    categoriaHint.textContent = 'Profissão personalizada: informe o salário abaixo.';
                }
                if (customInput) { customInput.focus(); }
            } else if (dados) {
                inputSalario.value = formataMoeda(dados.salario);
                if (categoriaHint) {
                    categoriaHint.textContent = 'Categoria: ' + dados.categoria +
                        ' — média de R$ ' + formataMoeda(dados.salario) + '.';
                }
            } else {
                inputSalario.value = '';
                if (categoriaHint) {
                    categoriaHint.textContent = 'A média salarial da categoria será preenchida automaticamente.';
                }
            }
        });
    }

    /* ----- Mostra/oculta o campo de horas no fim de semana ----- */
    var checkFds = document.getElementById('trabalha_fds');
    var fdsField = document.getElementById('fdsField');

    if (checkFds && fdsField) {
        checkFds.addEventListener('change', function () {
            fdsField.hidden = !checkFds.checked;
            if (checkFds.checked) {
                var input = document.getElementById('horas_fds');
                if (input) { input.focus(); }
            }
        });
    }

    /* ----- Barra de pesquisa do Google ----- */
    var searchForm = document.getElementById('searchForm');
    var searchInput = document.getElementById('searchInput');
    var csePanel = document.getElementById('csePanel');
    var cseClose = document.getElementById('cseClose');
    var cseId = window.GOOGLE_CSE_ID || '';

    function getCseElement() {
        if (window.google && google.search && google.search.cse && google.search.cse.element) {
            return google.search.cse.element.getElement('calculadora');
        }
        return null;
    }

    if (cseClose && csePanel) {
        cseClose.addEventListener('click', function () {
            csePanel.hidden = true;
        });
    }

    if (searchForm) {
        searchForm.addEventListener('submit', function (event) {
            var termo = (searchInput && searchInput.value || '').trim();
            if (termo === '') {
                event.preventDefault();
                if (searchInput) { searchInput.focus(); }
                return;
            }

            /* Se o Custom Search Engine estiver configurado e carregado,
               executa a busca dentro da página (sem recarregar, sem sair). */
            if (cseId) {
                var el = getCseElement();
                if (el && typeof el.execute === 'function') {
                    event.preventDefault();
                    if (csePanel) { csePanel.hidden = false; }
                    el.execute(termo);
                    return;
                }
            }

            /* Caso contrário, mantém o envio padrão (abre em nova aba). */
        });
    }
})();
