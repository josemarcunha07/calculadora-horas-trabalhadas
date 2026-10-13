/*
|--------------------------------------------------------------------------
| Calculadora Trabalhista — dados e configurações
|--------------------------------------------------------------------------
| Valores de referência 2026 (INSS, IRRF, FGTS, insalubridade).
|--------------------------------------------------------------------------
*/

const CONFIG = {
    // Jornada
    adicionalHoraExtra: 0.50,   // hora extra em dia útil (CLT)
    adicionalFimSemana: 1.00,   // hora extra em fim de semana/feriado (CLT)
    semanasPorMes: 52 / 12,

    // INSS (empregado) — portaria 2026
    salarioMinimo: 1621.00,
    inssFaixas: [
        { ate: 1621.00, aliquota: 0.075 },
        { ate: 2902.84, aliquota: 0.09 },
        { ate: 4354.27, aliquota: 0.12 },
        { ate: 8475.55, aliquota: 0.14 },
    ],

    // IRRF — tabela mensal 2026
    irrfFaixas: [
        { ate: 2428.80, aliquota: 0.000, deduzir: 0.00 },
        { ate: 2826.65, aliquota: 0.075, deduzir: 182.16 },
        { ate: 3751.05, aliquota: 0.150, deduzir: 394.16 },
        { ate: 4664.68, aliquota: 0.225, deduzir: 675.49 },
        { ate: null,    aliquota: 0.275, deduzir: 908.73 },
    ],
    irrfDescontoSimplificado: 607.20,
    irrfDependente: 189.59,
    irrfReducaoIsencao: 5000.00,
    irrfReducaoLimite: 7350.00,
    irrfReducaoConstante: 978.62,
    irrfReducaoCoeficiente: 0.133145,

    // FGTS
    fgtsAliquota: 0.08,
    fgtsMultaSemJustaCausa: 0.40,
    fgtsMultaAcordo: 0.20,

    // Insalubridade / periculosidade
    insalubridadeGraus: {
        minimo: 0.10,
        medio: 0.20,
        maximo: 0.40,
    },
    periculosidade: 0.30,

    // Pesquisa Google (opcional): informe um Programmable Search Engine ID
    // para exibir resultados dentro da página. Vazio = abre em nova aba.
    googleCseId: '',
};

const PROFISSOES = {
    'Tecnologia da Informação': [
        { cargo: 'Desenvolvedor(a) de Software', salario: 6500.00 },
        { cargo: 'Analista de Dados', salario: 7200.00 },
        { cargo: 'Analista de Suporte Técnico', salario: 3200.00 },
        { cargo: 'Designer UX/UI', salario: 5500.00 },
        { cargo: 'Analista de Segurança da Informação', salario: 8500.00 },
    ],
    'Saúde': [
        { cargo: 'Médico(a) Clínico Geral', salario: 15000.00 },
        { cargo: 'Enfermeiro(a)', salario: 4500.00 },
        { cargo: 'Técnico(a) de Enfermagem', salario: 2600.00 },
        { cargo: 'Dentista', salario: 9000.00 },
        { cargo: 'Farmacêutico(a)', salario: 4800.00 },
    ],
    'Educação': [
        { cargo: 'Professor(a) do Ensino Fundamental', salario: 3800.00 },
        { cargo: 'Professor(a) do Ensino Médio', salario: 4500.00 },
        { cargo: 'Professor(a) Universitário(a)', salario: 9500.00 },
        { cargo: 'Auxiliar de Educação Infantil', salario: 2200.00 },
    ],
    'Construção Civil': [
        { cargo: 'Engenheiro(a) Civil', salario: 9000.00 },
        { cargo: 'Pedreiro(a)', salario: 2600.00 },
        { cargo: 'Servente de Obras', salario: 1800.00 },
        { cargo: 'Mestre de Obras', salario: 3500.00 },
    ],
    'Comércio e Vendas': [
        { cargo: 'Vendedor(a)', salario: 2500.00 },
        { cargo: 'Gerente de Loja', salario: 4000.00 },
        { cargo: 'Operador(a) de Caixa', salario: 1800.00 },
        { cargo: 'Promotor(a) de Vendas', salario: 2200.00 },
    ],
    'Indústria': [
        { cargo: 'Engenheiro(a) de Produção', salario: 8500.00 },
        { cargo: 'Mecânico(a) Industrial', salario: 3200.00 },
        { cargo: 'Operador(a) de Máquinas', salario: 2400.00 },
        { cargo: 'Soldador(a)', salario: 2800.00 },
    ],
    'Serviços': [
        { cargo: 'Motorista', salario: 2500.00 },
        { cargo: 'Cozinheiro(a)', salario: 2200.00 },
        { cargo: 'Recepcionista', salario: 1900.00 },
        { cargo: 'Auxiliar de Limpeza', salario: 1600.00 },
        { cargo: 'Garçom/Garçonete', salario: 1800.00 },
    ],
    'Administração e Financeiro': [
        { cargo: 'Auxiliar Administrativo', salario: 2100.00 },
        { cargo: 'Contador(a)', salario: 4500.00 },
        { cargo: 'Analista Financeiro', salario: 5200.00 },
        { cargo: 'Analista de RH', salario: 3800.00 },
    ],
    'Segurança': [
        { cargo: 'Vigilante', salario: 2200.00 },
        { cargo: 'Bombeiro(a) Civil', salario: 2500.00 },
        { cargo: 'Porteiro(a)', salario: 1700.00 },
    ],
    'Entregas e Aplicativos': [
        { cargo: 'Motorista de Aplicativo', salario: 3000.00 },
        { cargo: 'Entregador(a) / Motoboy', salario: 2200.00 },
        { cargo: 'Ciclista de Delivery', salario: 1800.00 },
    ],
};

// Mapa achatado cargo => { categoria, salario }
const MAPA_PROFISSOES = {};
for (const categoria in PROFISSOES) {
    for (const item of PROFISSOES[categoria]) {
        MAPA_PROFISSOES[item.cargo] = { categoria, salario: item.salario };
    }
}
