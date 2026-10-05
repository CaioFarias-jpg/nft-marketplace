<?php
session_start();

const COUPONS = [
    'WELCOME10' => ['pct' => 10, 'exp' => '2099-12-31'],
    'GAMER20' => ['pct' => 20, 'exp' => '2099-12-31'],
    'OLD5' => ['pct' => 5, 'exp' => '2020-01-01']
];
const NETS = [
    'Ethereum' => 3000000,
    'Polygon' => 100000,
    'Arbitrum' => 800000
];
// taxa em gwei (1 ETH = 1.000.000.000 gwei)
@mkdir(__DIR__ . '/data');

function jl($f) {
    $p = __DIR__ . "/data/$f.json";
    return is_file($p) ? (json_decode(file_get_contents($p), true) ?: []) : [];
}

function js($f, $d) {
    file_put_contents(__DIR__ . "/data/$f.json", json_encode($d), LOCK_EX);
}

function h($s) {
    return htmlspecialchars((string) $s, ENT_QUOTES);
}

function eth($g) {
    return rtrim(rtrim(number_format($g / 1e9, 4, '.', ''), '0'), '.') . ' ETH';
}

function nfts() {
    static $n = [];
    if ($n) return $n;
    $c = ['Neon Arena', 'Pixel Beasts', 'Void Runners', 'Respawn Heroes'];
    $r = ['Comum', 'Raro', 'Épico', 'Lendário'];
    for ($i = 1; $i <= 24; $i++) $n[$i] = ['id' => $i, 'name' => $c[$i % 4] . ' #' . (100 + $i), 'col' => $c[$i % 4], 'rar' => $r[($i * 7) % 4], 'price' => ((($i * 37) % 90) + 10) * 5000000, 'stock' => 3 + ($i * 5) % 8];
    return $n;
}

function avail($id) {
    $s = jl('sold');
    return nfts()[$id]['stock'] - ($s[$id] ?? 0);
}

function me() {
    $u = jl('users');
    return isset($_SESSION['uid']) ? ($u[$_SESSION['uid']] ?? null) : null;
}

function cart() {
    return $_SESSION['cart'] ?? [];
}

function save_cart($c) {
    $_SESSION['cart'] = $c;
    if ($m = me()) {
        $u = jl('users');
        $u[$m['id']]['cart'] = $c;
        js('users', $u);
    }
}

function quote($net = 'Ethereum') {
    $s = 0;
    foreach (cart() as $i => $q) $s += nfts()[$i]['price'] * $q;
    $c = COUPONS[$_SESSION['coupon'] ?? ''] ?? null;
    $d = ($c && $c['exp'] >= date('Y-m-d')) ? intdiv($s * $c['pct'], 100) : 0;
    $f = $s ? NETS[$net] : 0;
    return ['sub' => $s, 'disc' => $d, 'fee' => $f, 'total' => $s - $d + $f];
}

function summary($t) {
    return "<dl class=sum><dt>Subtotal</dt><dd>" . eth($t['sub']) . "</dd><dt>Desconto</dt><dd>-" . eth($t['disc']) . "</dd><dt>Taxa de rede</dt><dd>" . eth($t['fee']) . "</dd><dt>Total</dt><dd><b>" . eth($t['total']) . "</b></dd></dl>";
}
if (!is_file(__DIR__ . '/data/users.json')) {
    $u = [];
    foreach (['ana', 'leo'] as $n) {
        $id = uniqid();
        $u[$id] = ['id' => $id, 'name' => ucfirst($n), 'email' => "$n@demo.com", 'pass' => password_hash('123456', PASSWORD_DEFAULT), 'fav' => [], 'cart' => []];
    }
    js('users', $u);
}

function top($t) {
    $n = array_sum(cart());
    $m = me();
    echo "<!doctype html><html lang=pt-br><meta charset=utf-8><meta name=viewport content='width=device-width,initial-scale=1'><title>" . h($t) . " - NFT Market</title><link rel=stylesheet href=assets/style.css><header><a class=logo href=index.php>NFT Market</a><nav><a href=index.php>Explorar</a><a href=cart.php>Carrinho ($n)</a>" . ($m ? "<span>" . h($m['name']) . "</span><a href='auth.php?logout=1'>Sair</a>" : "<a href=auth.php>Entrar</a>") . "</nav></header><main>";
}

function bottom() {
    echo "</main><script src=assets/app.js></script></html>";
}
