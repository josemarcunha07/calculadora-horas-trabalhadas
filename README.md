# Calculadora de Horas Trabalhadas

Aplicação web em **PHP + HTML + CSS + JavaScript** que estima quanto uma pessoa recebe
por hora, levando em conta:

- a **média salarial da categoria** profissional escolhida;
- a **jornada** (horas por dia e dias por semana);
- as **horas extras** em dias úteis (adicional de 50%);
- o trabalho em **finais de semana / feriados** (adicional de 100%).

Também inclui uma **barra de pesquisa integrada ao Google** para o usuário pesquisar
sem sair da página.

## Demonstração do cálculo

1. Jornada mensal = `horas/dia × dias/semana × (52 / 12)`
2. Valor da hora normal = `salário base ÷ jornada mensal`
3. Hora extra = `valor da hora × 1,50`
4. Hora em fim de semana = `valor da hora × 2,00`
5. Total mensal = `salário base + extras + finais de semana`

## Estrutura do projeto

```
calculadora/
├── index.php                 # Página principal (formulário + resultado + busca)
├── assets/
│   ├── css/style.css         # Estilos da interface
│   └── js/app.js             # Interações e busca no Google
└── includes/
    ├── config.php            # Configurações (adicionais, ID do Google CSE)
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

- **Adicionais legais:** altere `adicional_hora_extra` e `adicional_fim_semana`
  em `includes/config.php`.
- **Salários médios:** edite `includes/profissoes.php`. Os valores são referências
  aproximadas de mercado e podem ser ajustados pelo usuário no próprio formulário.

## Aviso

Os valores são **estimativas**. Impostos e descontos (INSS, IRRF e outros) não são
considerados.

## Licença

MIT.
