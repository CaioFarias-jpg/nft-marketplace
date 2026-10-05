<?php
require 'lib.php';

$m = me();
if (!$m) {
    header('Location: auth.php?next=checkout.php');
    exit;
}
$orders = jl('orders');
$err = '';
if (isset($_GET['order'])) {
    $o = $orders[$_GET['order']] ?? null;
    top('Pedido');
    if (!$o || $o['uid'] != $m['id']) {
        http_response_code(404);
        echo '<p class=empty>Pedido não encontrado.</p>';
        bottom();
        exit;
    }
    if ($o['status'] == 'confirmado') {
        echo "<h1>Pedido confirmado</h1><p>Transação: <code>{$o['tx']}</code> em {$o['date']}, rede {$o['net']}</p><ul>";
        foreach ($o['items'] as $it) echo '<li>' . h($it['name']) . ' x' . $it['q'] . ' - ' . eth($it['price'] * $it['q']) . '</li>';
        echo '</ul>' . summary($o['q']) . '<a class=btn href=index.php>Continuar explorando</a>';
    }
    else echo '<h1>Pagamento recusado</h1><p class=err role=alert>A carteira recusou a transação. Seus itens continuam no carrinho.</p><a class=btn href=checkout.php>Tentar novamente</a>';
    bottom();
    exit;
}
if (!cart()) {
    header('Location: cart.php');
    exit;
}

function qhash($qt) {
    return md5(json_encode([cart(), $qt, array_map('avail', array_keys(cart()))]));
}
$step = $_POST['step'] ?? 'form';
$d = ['name' => $m['name'], 'email' => $m['email'], 'wallet' => '', 'net' => 'Ethereum'];
if ($step != 'form') {
    foreach ($d as $k => $_) $d[$k] = trim($_POST[$k] ?? '');
    if (!$d['name'] || !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $err = 'Informe nome e e-mail válidos.';
    elseif (!preg_match('/^0x[a-fA-F0-9]{6,40}$/', $d['wallet'])) $err = 'Endereço de carteira inválido. Exemplo: 0x1234abcd.';
    elseif (!isset(NETS[$d['net']])) $err = 'Escolha uma rede.';
    if ($err) $step = 'form';
}
$qt = quote($d['net']);
if ($step == 'confirm') {
    $key = $_POST['key'] ?? '';
    foreach ($orders as $o) if ($o['key'] === $key && $o['uid'] == $m['id']) {
        header("Location: checkout.php?order={$o['id']}");
        exit;
    }
    // idempotência
    if ($key === '' || $key !== ($_SESSION['key'] ?? '') || ($_POST['hash'] ?? '') !== qhash($qt)) {
        $err = 'Preço ou disponibilidade mudou. Revise o pedido e confirme de novo.';
        $step = 'review';
    }
    else {
        $id = bin2hex(random_bytes(4));
        $items = [];
        foreach (cart() as $i => $q) $items[] = ['name' => nfts()[$i]['name'], 'q' => $q, 'price' => nfts()[$i]['price']];
        $ok = strtolower(substr($d['wallet'], -4)) !== 'dead';
        $orders[$id] = [
            'id' => $id,
            'key' => $key,
            'uid' => $m['id'],
            'status' => $ok ? 'confirmado' : 'recusado',
            'items' => $items,
            'q' => $qt,
            'net' => $d['net'],
            'wallet' => $d['wallet'],
            'tx' => '0x' . substr(hash('sha256', $id), 0, 40),
            'date' => date('d/m/Y H:i')
        ];
        js('orders', $orders);
        if ($ok) {
            $s = jl('sold');
            foreach (cart() as $i => $q) $s[$i] = ($s[$i] ?? 0) + $q;
            js('sold', $s);
            save_cart([]);
            unset($_SESSION['coupon']);
        }
        unset($_SESSION['key']);
        header("Location: checkout.php?order=$id");
        exit;
    }
}
top('Pagamento');
echo '<h1>Pagamento</h1>';
if ($err) echo "<p class=err role=alert>$err</p>";
if ($step == 'review') {
    $_SESSION['key'] = bin2hex(random_bytes(8));
    $qt = quote($d['net']);
    echo '<h2>Revise seu pedido</h2><ul>';
    foreach (cart() as $i => $q) echo '<li>' . h(nfts()[$i]['name']) . " x$q</li>";
    echo '</ul><p>' . h($d['name']) . ', ' . h($d['email']) . '<br>Carteira ' . h($d['wallet']) . ' na rede ' . h($d['net']) . '</p>' . summary($qt) . '<form method=post data-once><input type=hidden name=step value=confirm>';
    foreach ($d as $k => $v) echo "<input type=hidden name=$k value=\"" . h($v) . "\">";
    echo '<input type=hidden name=key value="' . $_SESSION['key'] . '"><input type=hidden name=hash value="' . qhash($qt) . '"><button>Confirmar compra</button></form>';
}
else {
    echo '<form method=post class=stack><input type=hidden name=step value=review>
<label>Nome <input name=name value="' . h($d['name']) . '" required></label><label>E-mail <input type=email name=email value="' . h($d['email']) . '" required></label>
<label>Endereço da carteira <input name=wallet value="' . h($d['wallet']) . '" placeholder="0x1234abcd" required></label><label>Rede <select name=net>';
    foreach (NETS as $k => $f) echo "<option value=$k " . ($d['net'] == $k ? 'selected' : '') . ">$k (taxa " . eth($f) . ')</option>';
    echo '</select></label>' . summary(quote($d['net'])) . '<button>Revisar pedido</button></form>';
}
bottom();
