# Calculadora Trabalhista

Aplicação web em **PHP + HTML + CSS + JavaScript** com três calculadoras trabalhistas:

- **Horas trabalhadas** — valor da hora, horas extras e trabalho em finais de semana.
- **Férias** — valor das férias, 1/3 constitucional e abono pecuniário.
- **Rescisão** — saldo de salário, aviso prévio, férias, 13º e multa do FGTS.

Também inclui uma **barra de pesquisa integrada ao Google** para pesquisar sem sair da página.

## Funcionalidades

### Horas trabalhadas
- escolha da **profissão** (com média salarial por categoria) ou **profissão personalizada**;
- jornada (horas/dia × dias/semana);
- **horas extras** (adicional de 50%) e **finais de semana/feriados** (adicional de 100%);
- valor da hora normal, extra e de fim de semana, totais mensal e anual.

### Férias
- valor dos dias de férias + **1/3 constitucional**;
- opção de **abono pecuniário** (venda de 10 dias);
- descontos estimados de **INSS** e **IRRF** (tabelas 2026).

### Rescisão
- motivos: sem justa causa, justa causa, pedido de demissão, acordo (art. 484-A) e término de contrato;
- **saldo de salário**, **aviso prévio** (indenizado/trabalhado), **férias vencidas e proporcionais + 1/3**,
  **13º proporcional** e **multa do FGTS** (40% / 20%);
- descontos estimados de INSS, IRRF e aviso não trabalhado.

## Estrutura do projeto

```
calculadora/
├── index.php                 # Calculadora de horas trabalhadas
├── ferias.php                # Calculadora de férias
├── recisao.php               # Calculadora de rescisão
├── assets/
│   ├── css/style.css         # Estilos da interface
│   └── js/app.js             # Interações e busca no Google
└── includes/
    ├── header.php            # Cabeçalho + menu de navegação
    ├── footer.php            # Rodapé + scripts
    ├── config.php            # Configurações, tabelas de INSS/IRRF e FGTS
    ├── funcoes.php           # Validação e lógica de cálculo
    └── profissoes.php        # Tabela de médias salariais por categoria
```

## Como rodar

### Com XAMPP
Copie a pasta para `C:\xampp\htdocs\calculadora` e acesse
`http://localhost/calculadora`.

### Com o servidor embutido do PHP
```bash
php -S 127.0.0.1:8099 -t .
```
Depois abra `http://127.0.0.1:8099`.

## Barra de pesquisa do Google dentro da página

Por padrão, a pesquisa abre em uma **nova aba** do Google. Para exibir os resultados
**dentro da própria página**, crie um *Programmable Search Engine* gratuito em
<https://programmablesearchengine.google.com/> e informe o ID em
`includes/config.php`:

```php
'google_cse_id' => 'SEU_ID_DO_MOTOR_DE_BUSCA',
```

## Ajustando os valores

- **Adicionais de hora extra/fim de semana:** `adicional_hora_extra` e `adicional_fim_semana`.
- **Tabelas de INSS e IRRF, dependente, desconto simplificado e FGTS:** em `includes/config.php`
  (valores de referência de 2026).
- **Salários médios:** edite `includes/profissoes.php`.

## Aviso

Os valores são **estimativas** e não substituem o cálculo oficial do empregador, do
sindicato ou de um contador. Impostos e regras podem mudar ao longo do ano.

## Licença

MIT.

