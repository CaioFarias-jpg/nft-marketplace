<?php
require 'lib.php';

header('Content-Type: application/json');
$m = me();
if (!$m) {
    http_response_code(401);
    exit('{"error":"login"}');
}
$id = (int)($_POST['id'] ?? 0);
if (!isset(nfts()[$id])) {
    http_response_code(404);
    exit('{}');
}
$u = jl('users');
$f = $u[$m['id']]['fav'];
$on = !in_array($id, $f);
$u[$m['id']]['fav'] = $on ? array_values(array_merge($f, [$id])) : array_values(array_diff($f, [$id]));
js('users', $u);
echo json_encode(['fav' => $on]);
