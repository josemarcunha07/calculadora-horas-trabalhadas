<?php

/*
|--------------------------------------------------------------------------
| Rodapé e scripts compartilhados entre as páginas
|--------------------------------------------------------------------------
*/

$cseId    = $cseId ?? trim((string) $config['google_cse_id']);
$cseAtivo = $cseId !== '';
?>
</main>

<footer class="footer">
    <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($config['app_name']) ?> &middot; Feito com PHP, HTML, CSS e JavaScript.</p>
    <p class="footer__note">Valores de referência (2026). Não substitui o cálculo oficial do seu empregador ou contador.</p>
</footer>

<script>
    window.MAPA_PROFISSOES = <?= json_encode($mapaProfissoes ?? [], JSON_UNESCAPED_UNICODE) ?>;
    window.GOOGLE_CSE_ID = <?= json_encode($cseId) ?>;
</script>
<script src="assets/js/app.js"></script>

<?php if ($cseAtivo): ?>
<script async src="https://cse.google.com/cse.js?cx=<?= urlencode($cseId) ?>"></script>
<?php endif; ?>

</body>
</html>
