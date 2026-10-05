<?php require 'lib.php';$c=cart();
if($_SERVER['REQUEST_METHOD']=='POST'){$a=$_POST['a']??'';$id=(int)($_POST['id']??0);
 if(in_array($a,['add','set'])&&isset(nfts()[$id])){$q=(int)($_POST['q']??1);if($a=='add')$q+=$c[$id]??0;$q=min($q,avail($id));if($q>0)$c[$id]=$q;else unset($c[$id]);save_cart($c);}
 if($a=='del'){unset($c[$id]);save_cart($c);}
 if($a=='coupon'){$k=strtoupper(trim($_POST['code']??''));
  if(!isset(COUPONS[$k]))$_SESSION['err']='Cupom inválido. Confira o código.';elseif(COUPONS[$k]['exp']<date('Y-m-d'))$_SESSION['err']='Cupom expirado.';else $_SESSION['coupon']=$k;}
 if($a=='nocoupon')unset($_SESSION['coupon']);
 header('Location: cart.php');exit;}
$err=$_SESSION['err']??'';unset($_SESSION['err']);top('Carrinho');?>
<h1>Carrinho</h1>
<?php if(!$c)echo '<p class=empty>Seu carrinho está vazio. <a href=index.php>Explorar NFTs</a></p>';else:?>
<table><tr><th>NFT<th>Preço<th>Qtd<th><span class=sr>Remover</span></tr>
<?php foreach($c as $i=>$q):$n=nfts()[$i];?><tr><td><img class=thumb src="img.php?id=<?=$i?>" alt=""> <a href="nft.php?id=<?=$i?>"><?=h($n['name'])?></a>
<td><?=eth($n['price'])?>
<td><form method=post><input type=hidden name=a value=set><input type=hidden name=id value="<?=$i?>"><input class=qty type=number name=q value="<?=$q?>" min=0 max="<?=avail($i)?>" aria-label="Quantidade de <?=h($n['name'])?>"></form>
<td><form method=post><input type=hidden name=a value=del><input type=hidden name=id value="<?=$i?>"><button>Remover</button></form></tr><?php endforeach;?></table>
<form method=post class=coupon><input type=hidden name=a value=coupon><label>Cupom <input name=code placeholder="WELCOME10"></label><button>Aplicar</button>
<?php if(isset($_SESSION['coupon']))echo '<span>Cupom '.h($_SESSION['coupon']).' ativo</span> <button name=a value=nocoupon>Remover cupom</button>';?></form>
<?php if($err)echo "<p class=err role=alert>$err</p>";echo summary(quote());?>
<a class=btn href=checkout.php>Ir para o pagamento</a><?php endif;bottom();
