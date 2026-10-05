<?php
require 'lib.php';

$n = nfts()[(int)($_GET['id'] ?? 0)] ?? null;
top($n['name'] ?? 'NFT não encontrado');
if (!$n) {
    echo '<p class=empty>NFT não encontrado. <a href=index.php>Voltar ao catálogo</a></p>';
    bottom();
    exit;
}
$a = avail($n['id']);
$f = in_array($n['id'], me()['fav'] ?? []);
?>
<div class=detail>
    <img src="img.php?id=<?= $n['id'] ?>" alt="Arte de <?= h($n['name']) ?>">
    <div>
        <h1><?= h($n['name']) ?></h1>
        <p><?= h($n['col']) ?>, raridade <?= $n['rar'] ?></p>
        <p class=price><?= eth($n['price']) ?></p>
        <p><?= $a > 0 ? "$a de {$n['stock']} edições disponíveis" : 'Esgotado' ?></p>

        <button class="fav <?= $f ? 'on' : '' ?>" data-id="<?= $n['id'] ?>" aria-label="Favoritar">♥ Favoritar</button>

        <?php if ($a > 0): ?>
            <form method=post action=cart.php>
                <input type=hidden name=a value=add>
                <input type=hidden name=id value="<?= $n['id'] ?>">
                <label>Quantidade <input type=number name=q value=1 min=1 max="<?= $a ?>"></label>
                <button>Adicionar ao carrinho</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php bottom();
