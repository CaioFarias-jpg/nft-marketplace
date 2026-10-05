<?php
require 'lib.php';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
$reg = ($_GET['mode'] ?? '') == 'register';
$next = preg_match('/^[a-z]+\.php(\?[\w=&%]*)?$/', $_GET['next'] ?? '') ? $_GET['next'] : 'index.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $e = strtolower(trim($_POST['email'] ?? ''));
    $p = $_POST['pass'] ?? '';
    $u = jl('users');
    $f = null;
    foreach ($u as $x) if ($x['email'] == $e) $f = $x;
    if ($reg) {
        $nm = trim($_POST['name'] ?? '');
        if (!$nm || !filter_var($e, FILTER_VALIDATE_EMAIL) || strlen($p) < 6) $err = 'Preencha nome, e-mail válido e senha com 6 caracteres ou mais.';
        elseif ($f) $err = 'Este e-mail já está cadastrado. Entre na sua conta.';
        else {
            $id = uniqid();
            $u[$id] = ['id' => $id, 'name' => $nm, 'email' => $e, 'pass' => password_hash($p, PASSWORD_DEFAULT), 'fav' => [], 'cart' => []];
            js('users', $u);
            $f = $u[$id];
        }
    }
    elseif (!$f || !password_verify($p, $f['pass'])) $err = 'E-mail ou senha incorretos.';
    if (!$err) {
        $c = $f['cart'];
        foreach (cart() as $i => $q) $c[$i] = min(($c[$i] ?? 0) + $q, avail($i));
        session_regenerate_id(true);
        $_SESSION['uid'] = $f['id'];
        save_cart(array_filter($c));
        header("Location: $next");
        exit;
    }
}
top($reg ? 'Cadastro' : 'Entrar');
?>
<h1><?= $reg ? 'Criar conta' : 'Entrar' ?></h1>

<?php if ($err) echo "<p class=err role=alert>$err</p>"; ?>

<form method=post class=stack>
    <?php if ($reg) echo '<label>Nome <input name=name required></label>'; ?>
    <label>E-mail <input type=email name=email required></label>
    <label>Senha <input type=password name=pass required></label>
    <button><?= $reg ? 'Criar conta' : 'Entrar' ?></button>
</form>

<p><?= $reg ? '<a href="auth.php">Já tenho conta</a>' : '<a href="auth.php?mode=register">Criar conta</a>' ?> | Teste: ana@demo.com / 123456</p>

<?php bottom();
