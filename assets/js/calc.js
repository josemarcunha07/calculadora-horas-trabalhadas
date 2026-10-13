/*
|--------------------------------------------------------------------------
| Calculadora Trabalhista — lógica de cálculo (JavaScript)
|--------------------------------------------------------------------------
*/
(function (global) {
    'use strict';

    /* ----------------------- Formatação / utilidades ----------------------- */

    function formataMoeda(valor) {
        const v = Number(valor) || 0;
        return v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function formataNumero(valor, casas) {
        casas = casas === undefined ? 2 : casas;
        return (Number(valor) || 0).toLocaleString('pt-BR', {
            minimumFractionDigits: casas,
            maximumFractionDigits: casas,
        });
    }

    function formataHoras(horas) {
        return formataNumero(horas, 2) + ' h';
    }

    function formataData(valor) {
        if (!valor) return '—';
        const d = new Date(valor + 'T00:00:00');
        if (isNaN(d)) return '—';
        return d.toLocaleDateString('pt-BR');
    }

    /* Aceita "3.500,00", "3500", "3500.50" etc. */
    function parseNumero(valor) {
        if (valor === null || valor === undefined) return 0;
        let v = String(valor).trim().replace(/[.\s]/g, '').replace(',', '.');
        const n = parseFloat(v);
        return isNaN(n) ? 0 : n;
    }

    function parseData(valor) {
        if (!valor) return null;
        const partes = String(valor).split('-').map(Number);
        if (partes.length !== 3) return null;
        const d = new Date(partes[0], partes[1] - 1, partes[2]);
        return isNaN(d) ? null : d;
    }

    /* Diferença em anos/meses/dias (a <= b). */
    function diffYMD(a, b) {
        let y = b.getFullYear() - a.getFullYear();
        let m = b.getMonth() - a.getMonth();
        let d = b.getDate() - a.getDate();
        if (d < 0) {
            m -= 1;
            d += new Date(b.getFullYear(), b.getMonth(), 0).getDate();
        }
        if (m < 0) { y -= 1; m += 12; }
        return { y: y, m: m, d: d };
    }

    function anosCompletos(a, b) {
        return diffYMD(a, b).y;
    }

    function mesesComFracao(a, b) {
        const dif = diffYMD(a, b);
        return dif.y * 12 + dif.m + (dif.d >= 15 ? 1 : 0);
    }

    /* ------------------------------- INSS/IRRF ----------------------------- */

    function inssEmpregado(base) {
        if (base <= 0) return 0;
        let total = 0;
        let anterior = 0;
        for (const faixa of CONFIG.inssFaixas) {
            const teto = Math.min(base, faixa.ate);
            if (teto > anterior) {
                total += (teto - anterior) * faixa.aliquota;
                anterior = teto;
            }
            if (base <= faixa.ate) break;
        }
        return Math.round(total * 100) / 100;
    }

    function irrfMensal(rendimento, inss, dependentes) {
        dependentes = dependentes || 0;
        if (rendimento <= 0) return 0;

        const deducaoLegal = inss + dependentes * CONFIG.irrfDependente;
        const deducao = Math.max(deducaoLegal, CONFIG.irrfDescontoSimplificado);
        const base = Math.max(0, rendimento - deducao);

        let imposto = 0;
        for (const faixa of CONFIG.irrfFaixas) {
            if (faixa.ate === null || base <= faixa.ate) {
                imposto = base * faixa.aliquota - faixa.deduzir;
                break;
            }
        }
        imposto = Math.max(0, imposto);

        let reducao = 0;
        if (rendimento <= CONFIG.irrfReducaoIsencao) {
            reducao = imposto;
        } else if (rendimento <= CONFIG.irrfReducaoLimite) {
            reducao = Math.max(0, CONFIG.irrfReducaoConstante - CONFIG.irrfReducaoCoeficiente * rendimento);
        }

        return Math.round(Math.max(0, imposto - reducao) * 100) / 100;
    }

    /* IRRF do 13º: tributação exclusiva, sem desconto simplificado nem redução. */
    function irrfDecimo(rendimento, inss, dependentes) {
        dependentes = dependentes || 0;
        if (rendimento <= 0) return 0;
        const base = Math.max(0, rendimento - inss - dependentes * CONFIG.irrfDependente);

        let imposto = 0;
        for (const faixa of CONFIG.irrfFaixas) {
            if (faixa.ate === null || base <= faixa.ate) {
                imposto = base * faixa.aliquota - faixa.deduzir;
                break;
            }
        }
        return Math.round(Math.max(0, imposto) * 100) / 100;
    }

    /* ------------------------ Horas trabalhadas --------------------------- */

    function calcHoras(d) {
        const semanas = CONFIG.semanasPorMes;
        const salario = d.salario;
        const jornadaMensal = d.horasDia * d.diasSemana * semanas;
        const jornadaSemanal = d.horasDia * d.diasSemana;
        const valorHora = jornadaMensal > 0 ? salario / jornadaMensal : 0;

        const valorHoraExtra = valorHora * (1 + CONFIG.adicionalHoraExtra);
        const valorHoraFds = valorHora * (1 + CONFIG.adicionalFimSemana);

        const horasExtrasMes = (d.horasExtras || 0) * semanas;
        const horasFdsMes = (d.trabalhaFds ? (d.horasFds || 0) : 0) * semanas;

        const totalExtras = valorHoraExtra * horasExtrasMes;
        const totalFds = valorHoraFds * horasFdsMes;

        const totalMensal = salario + totalExtras + totalFds;
        const totalHorasMes = jornadaMensal + horasExtrasMes + horasFdsMes;
        const valorHoraEfetivo = totalHorasMes > 0 ? totalMensal / totalHorasMes : 0;

        return {
            salario: salario,
            jornadaSemanal: jornadaSemanal,
            jornadaMensal: jornadaMensal,
            valorHora: valorHora,
            valorHoraExtra: valorHoraExtra,
            valorHoraFds: valorHoraFds,
            horasExtrasMes: horasExtrasMes,
            horasFdsMes: horasFdsMes,
            totalExtras: totalExtras,
            totalFds: totalFds,
            totalHorasMes: totalHorasMes,
            valorHoraEfetivo: valorHoraEfetivo,
            totalMensal: totalMensal,
            totalAnual: totalMensal * 12,
        };
    }

    /* -------------------------------- Férias ------------------------------ */

    function calcFerias(d) {
        const salario = d.salario;
        const dependentes = d.dependentes || 0;
        const valorDia = salario / 30;

        const feriasBase = valorDia * d.dias;
        const terco = feriasBase / 3;

        const abonoDias = d.vender10 ? 10 : 0;
        const abono = valorDia * abonoDias;
        const abonoTerco = abono / 3;

        const bruto = feriasBase + terco + abono + abonoTerco;
        const inss = inssEmpregado(bruto);
        const irrf = irrfMensal(bruto, inss, dependentes);
        const liquido = bruto - inss - irrf;

        return {
            salario, valorDia, dias: d.dias, feriasBase, terco,
            abonoDias, abono, abonoTerco, bruto, inss, irrf, liquido, dependentes,
        };
    }

    /* ------------------------------ 13º salário --------------------------- */

    function calcDecimo(d) {
        const salario = d.salario;
        const dependentes = d.dependentes || 0;
        const avos = Math.min(12, Math.max(0, d.avos || 0));

        const bruto = (salario / 12) * avos;
        const inss = inssEmpregado(bruto);
        const irrf = irrfDecimo(bruto, inss, dependentes);

        const primeiraParcela = bruto / 2;
        const segundaParcela = bruto - primeiraParcela - inss - irrf;

        return {
            salario, avos, bruto, inss, irrf,
            primeiraParcela, segundaParcela,
            liquido: bruto - inss - irrf,
            dependentes,
        };
    }

    /* --------------------------------- FGTS ------------------------------- */

    function calcFgts(d) {
        const salario = d.salario;
        const meses = Math.max(0, d.meses || 0);
        const incluirDecimo = !!d.incluirDecimo;

        const depositoMensal = salario * CONFIG.fgtsAliquota;
        const depositoDecimo = incluirDecimo ? (salario / 12) * 12 * CONFIG.fgtsAliquota : 0; // 13º cheio no ano
        const totalPeriodo = depositoMensal * meses;
        const total = totalPeriodo + depositoDecimo;

        const multaSemJusta = total * CONFIG.fgtsMultaSemJustaCausa;
        const multaAcordo = total * CONFIG.fgtsMultaAcordo;

        return {
            salario, meses, depositoMensal, depositoDecimo,
            totalPeriodo, total, multaSemJusta, multaAcordo,
            incluirDecimo,
        };
    }

    /* ----------------------------- Insalubridade -------------------------- */

    function calcInsalubridade(d) {
        const pctGrau = CONFIG.insalubridadeGraus[d.grau] || 0;
        const base = d.baseTipo === 'salario' ? d.salario : CONFIG.salarioMinimo;
        const valorMensal = base * pctGrau;

        const periculosidade = d.periculosidade ? d.salario * CONFIG.periculosidade : 0;

        return {
            base, baseTipo: d.baseTipo, grau: d.grau, percentual: pctGrau,
            valorMensal, valorAnual: valorMensal * 12,
            periculosidade, periculosidadePct: CONFIG.periculosidade,
            totalMensal: valorMensal + periculosidade,
        };
    }

    /* ------------------------------- Rescisão ----------------------------- */

    function calcRescisao(d) {
        const salario = d.salario;
        const dependentes = d.dependentes || 0;
        const tipo = d.tipo;
        const aviso = d.aviso;
        const feriasVencidas = Math.max(0, d.feriasVencidas || 0);

        const admissao = d.admissao;
        const demissao = d.demissao;
        const valorDia = salario / 30;

        const diasMes = demissao.getDate();
        const saldo = valorDia * diasMes;

        const anos = anosCompletos(admissao, demissao);
        const diasAviso = Math.min(90, 30 + 3 * anos);
        let avisoValor = 0;
        if (aviso === 'indenizado' && (tipo === 'sem_justa' || tipo === 'acordo')) {
            avisoValor = valorDia * diasAviso;
        }

        const feriasVencValor = feriasVencidas * salario;
        const feriasVencTerco = feriasVencValor / 3;

        const mesesTotais = mesesComFracao(admissao, demissao);
        const avos = mesesTotais % 12;
        const feriasProp = (salario / 12) * avos;
        const feriasPropTerco = feriasProp / 3;

        const inicioAno = new Date(demissao.getFullYear(), 0, 1);
        const mesesAno = mesesComFracao(inicioAno, demissao);
        const decimoProp = (salario / 12) * mesesAno;

        const fgtsDeposito = CONFIG.fgtsAliquota * salario * Math.max(1, mesesTotais);
        let multaPct = 0;
        if (tipo === 'sem_justa') multaPct = CONFIG.fgtsMultaSemJustaCausa;
        else if (tipo === 'acordo') multaPct = CONFIG.fgtsMultaAcordo;
        const multa = fgtsDeposito * multaPct;

        const feriasIndenizadas = feriasVencValor + feriasVencTerco + feriasProp + feriasPropTerco;
        const bruto = saldo + avisoValor + feriasIndenizadas + decimoProp + multa;

        const tributavel = saldo + avisoValor + decimoProp;
        const inss = inssEmpregado(tributavel);
        const irrf = irrfMensal(tributavel, inss, dependentes);

        let descontoAviso = 0;
        if (tipo === 'pedido' && aviso !== 'trabalhado') {
            descontoAviso = valorDia * 30;
        }

        const liquido = bruto - inss - irrf - descontoAviso;

        return {
            salario, tipo, aviso, admissao, demissao,
            diasMes, anos, mesesTotais, avos, mesesAno, diasAviso,
            saldo, avisoValor,
            feriasVencidas: feriasVencValor + feriasVencTerco, feriasVencQtd: feriasVencidas,
            feriasProp: feriasProp + feriasPropTerco,
            decimoProp,
            fgtsDeposito, multa, multaPct: multaPct * 100,
            bruto, tributavel, inss, irrf,
            descontoAviso, totalDescontos: inss + irrf + descontoAviso,
            liquido, dependentes,
        };
    }

    global.Calc = {
        formataMoeda, formataNumero, formataHoras, formataData,
        parseNumero, parseData,
        inssEmpregado, irrfMensal, irrfDecimo,
        calcHoras, calcFerias, calcDecimo, calcFgts, calcInsalubridade, calcRescisao,
    };
})(window);
