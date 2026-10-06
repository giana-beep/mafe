<?php
$score=75;
if(logged_in()){
 $u=$me ?? db()->query("SELECT * FROM users WHERE id=".user_id())->fetch_assoc();
 $score=match_score($p,$u);
}
?>
<a class="product-card" href="?page=product&id=<?=$p['id']?>">
<div class="product-img"><?php if($p['image']): ?><img src="<?=e($p['image'])?>" alt=""><?php else: ?><span>🧴</span><?php endif; ?><span class="score-pill"><?=$score?>%</span></div>
<div class="product-info"><small><?=e($p['brand'])?></small><h3><?=e($p['name'])?></h3><p><?=e($p['category'])?> · Rp <?=number_format($p['price'],0,',','.')?></p></div>
</a>
