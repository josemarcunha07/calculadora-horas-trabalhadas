# Calculadora Trabalhista

Aplicação web **estática** (HTML + CSS + JavaScript, sem backend) com seis calculadoras
trabalhistas e uma barra de pesquisa integrada ao Google:

- **Horas trabalhadas** — valor da hora, horas extras (50%) e finais de semana (100%).
- **Férias** — valor das férias, 1/3 constitucional e abono pecuniário (venda de 10 dias).
- **13º salário** — valor proporcional (avos) com parcelas e descontos.
- **FGTS** — depósito mensal de 8%, saldo estimado e multas rescisórias (40% / 20%).
- **Rescisão** — saldo de salário, aviso prévio, férias, 13º e multa do FGTS.
- **Insalubridade** — adicional por grau (10% / 20% / 40%) e periculosidade (30%).

> Valores de referência **2026** para INSS, IRRF (incluindo a redução da Lei 15.270/2025),
> FGTS e insalubridade. São estimativas e não substituem o cálculo oficial.

## Como rodar

É um site estático. Basta **abrir o `index.html`** no navegador — ou servir a pasta com
qualquer servidor estático:

```bash
# Python
python -m http.server 8099

# PHP (também serve arquivos estáticos)
php -S 127.0.0.1:8099 -t .
```

Depois acesse `http://127.0.0.1:8099`.

## GitHub Pages

O projeto é publicado automaticamente pelo **GitHub Pages** a partir da branch `main`
(pasta raiz). Endereço: <https://josemarcunha07.github.io/calculadora-horas-trabalhadas/>

## Estrutura do projeto

```
calculadora/
├── index.html                # Página única com as abas e formulários
├── assets/
│   ├── css/style.css         # Estilos da interface
│   └── js/
│       ├── data.js           # Configurações (tabelas) e lista de profissões
│       ├── calc.js           # Lógica de cálculo de todas as calculadoras
│       └── app.js            # Abas, formulários, resultados e busca
└── README.md
```

## Barra de pesquisa do Google dentro da página

Por padrão a pesquisa abre em uma **nova aba**. Para exibir os resultados **dentro da
própria página**, crie um *Programmable Search Engine* gratuito em
<https://programmablesearchengine.google.com/> e informe o ID em `assets/js/data.js`:

```js
googleCseId: 'SEU_ID_DO_MOTOR_DE_BUSCA',
```

## Ajustando os valores

Tudo fica em `assets/js/data.js`:

- **Adicionais de hora extra/fim de semana:** `adicionalHoraExtra`, `adicionalFimSemana`.
- **INSS, IRRF, dependente, desconto simplificado, FGTS e insalubridade:** objeto `CONFIG`.
- **Lista de profissões e médias salariais:** objeto `PROFISSOES`.

## Aviso

Os valores são **estimativas**. Impostos, regras e tabelas podem mudar ao longo do ano.
Não substitui o cálculo do empregador, do sindicato ou de um contador.

## Licença

MIT.
