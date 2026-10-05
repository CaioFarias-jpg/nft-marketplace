<?php require 'lib.php';
$q=trim($_GET['q']??'');$col=$_GET['col']??'';$rar=$_GET['rar']??'';$sort=$_GET['sort']??'';$pg=max(1,(int)($_GET['page']??1));
$l=array_filter(nfts(),fn($n)=>($q===''||stripos($n['name'],$q)!==false)&&($col===''||$n['col']===$col)&&($rar===''||$n['rar']===$rar));
if($sort=='asc')usort($l,fn($a,$b)=>$a['price']<=>$b['price']);elseif($sort=='desc')usort($l,fn($a,$b)=>$b['price']<=>$a['price']);
$tot=max(1,(int)ceil(count($l)/8));$pg=min($pg,$tot);$l=array_slice($l,($pg-1)*8,8);$fav=me()['fav']??[];
top('Explorar');?>
<h1>Explorar NFTs</h1>
<form class=filters method=get>
<input type=search name=q value="<?=h($q)?>" placeholder="Buscar NFT" aria-label="Buscar NFT">
<select name=col aria-label="Coleção"><option value="">Todas as coleções</option><?php foreach(['Neon Arena','Pixel Beasts','Void Runners','Respawn Heroes'] as $x)echo "<option ".($col==$x?'selected':'').">$x</option>";?></select>
<select name=rar aria-label="Raridade"><option value="">Todas as raridades</option><?php foreach(['Comum','Raro','Épico','Lendário'] as $x)echo "<option ".($rar==$x?'selected':'').">$x</option>";?></select>
<select name=sort aria-label="Ordenar"><option value="">Ordem padrão</option><option value=asc <?=$sort=='asc'?'selected':''?>>Menor preço</option><option value=desc <?=$sort=='desc'?'selected':''?>>Maior preço</option></select>
<button>Filtrar</button></form>
<?php if(!$l)echo '<p class=empty>Nenhum NFT encontrado. Tente limpar os filtros.</p>';?>
<div class=grid><?php foreach($l as $n):?>
<article class=card><a href="nft.php?id=<?=$n['id']?>"><img src="img.php?id=<?=$n['id']?>" alt="Arte de <?=h($n['name'])?>"></a>
<div class=info><h3><?=h($n['name'])?></h3><small><?=h($n['col'])?>, <?=$n['rar']?></small><b><?=eth($n['price'])?></b>
<button class="fav <?=in_array($n['id'],$fav)?'on':''?>" data-id="<?=$n['id']?>" aria-label="Favoritar <?=h($n['name'])?>">♥</button></div></article>
<?php endforeach;?></div>
<nav class=pages aria-label="Páginas"><?php for($p=1;$p<=$tot;$p++)echo '<a '.($p==$pg?'aria-current=page ':'').'href="?'.h(http_build_query(array_merge($_GET,['page'=>$p]))).'">'.$p.'</a>';?></nav>
<?php bottom();
