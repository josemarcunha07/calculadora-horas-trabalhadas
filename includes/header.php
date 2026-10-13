<?php

/*
|--------------------------------------------------------------------------
| Cabeçalho e menu compartilhados entre as páginas
|--------------------------------------------------------------------------
| Variáveis esperadas:
|   $config      - configurações do projeto
|   $paginaAtiva - 'horas' | 'ferias' | 'recisao'
|   $tituloPagina (opcional)
|--------------------------------------------------------------------------
*/

$paginaAtiva  = $paginaAtiva ?? 'horas';
$tituloPagina = $tituloPagina ?? $config['app_name'];

$cseId    = trim((string) $config['google_cse_id']);
$cseAtivo = $cseId !== '';

$navItens = [
    'horas'   => ['index.php',   'Horas trabalhadas', '&#9201;'],
    'ferias'  => ['ferias.php',  'Férias',            '&#127958;'],
    'recisao' => ['recisao.php', 'Rescisão',          '&#128188;'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina) ?></title>
    <meta name="description" content="Calcule horas trabalhadas, férias e rescisão com valores de referência da CLT 2026.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="topbar">
    <div class="topbar__inner">
        <a class="brand" href="index.php">
            <span class="brand__icon">&#9201;</span>
            <span class="brand__text">Calculadora<span>.trabalhista</span></span>
        </a>

        <form class="searchbar" id="searchForm" role="search"
              action="https://www.google.com/search" method="GET" target="_blank">
            <span class="searchbar__icon">&#128269;</span>
            <input type="search" name="q" id="searchInput" class="searchbar__input"
                   placeholder="Pesquise no Google sem sair da página..."
                   aria-label="Pesquisar no Google" autocomplete="off">
            <button type="submit" class="searchbar__btn">Pesquisar</button>
        </form>
    </div>

    <nav class="mainnav" aria-label="Navegação principal">
        <div class="mainnav__inner">
            <?php foreach ($navItens as $chave => $item): ?>
                <a class="mainnav__link <?= $paginaAtiva === $chave ? 'is-active' : '' ?>"
                   href="<?= $item[0] ?>">
                    <span class="mainnav__icon"><?= $item[2] ?></span><?= $item[1] ?>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>

    <?php if ($cseAtivo): ?>
    <div class="cse-panel" id="csePanel" hidden>
        <div class="cse-panel__header">
            <strong>Resultados da pesquisa</strong>
            <button type="button" class="cse-panel__close" id="cseClose" aria-label="Fechar">&times;</button>
        </div>
        <div class="gcse-searchresults-only" data-gname="calculadora" data-queryParameterName="q"></div>
    </div>
    <?php else: ?>
    <div class="cse-hint">
        Dica: configure um <em>Programmable Search Engine</em> em
        <code>includes/config.php</code> para ver os resultados do Google aqui dentro da página.
        Enquanto isso, a pesquisa abre em uma nova aba.
    </div>
    <?php endif; ?>
</header>

<main class="container">
