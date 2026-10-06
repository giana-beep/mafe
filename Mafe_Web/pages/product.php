<?php
$id=(int)($_GET['id']??0);$st=db()->prepare("SELECT * FROM products WHERE id=?");$st->bind_param("i",$id);$st->execute();$p=$st->get_result()->fetch_assoc();
if(!$p){echo '<div class="empty">Product not found.</div>';return;}
$ings=db()->query("SELECT i.* FROM ingredients i JOIN product_ingredients pi ON pi.ingredient_id=i.id WHERE pi.product_id=$id ORDER BY i.name");
$reviews=db()->query("SELECT r.*,u.name FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.product_id=$id ORDER BY r.id DESC");
$score=78;if(logged_in()){$u=$me;$score=match_score($p,$u);}
if($_SERVER['REQUEST_METHOD']==='POST' && logged_in() && isset($_POST['shelf'])){
 $sh=$_POST['shelf'];$st=db()->prepare("INSERT IGNORE INTO shelves(user_id,product_id,shelf) VALUES(?,?,?)");$st->bind_param("iis",$_SESSION['user_id'],$id,$sh);$st->execute();
}
?>
<div class="back"><a href="?page=products">← Back to discover</a></div>
<section class="detail">
<div class="detail-img"><img src="<?=e($p['image'])?>" alt=""></div>
<div class="detail-main"><span class="eyebrow"><?=e($p['brand'])?> · <?=e($p['category'])?></span><h1><?=e($p['name'])?></h1><p><?=e($p['description'])?></p>
<div class="detail-score"><div><b><?=$score?>%</b><small>personal match</small></div><div class="bar"><i style="width:<?=$score?>%"></i></div></div>
<div class="tags"><span>✓ <?=$p['fragrance_free']?'Fragrance free':'Contains fragrance'?></span><span>✓ <?=$p['alcohol_free']?'Alcohol free':'Contains alcohol'?></span><span>✓ <?=$p['vegan']?'Vegan':'Not marked vegan'?></span><span>✓ <?=$p['cruelty_free']?'Cruelty free':'Not marked cruelty free'?></span></div>
<div class="hero-btns"><a class="btn primary" href="?page=compare&a=<?=$id?>">Compare</a><?php if(logged_in()):?><form method="post" class="inline"><select name="shelf"><option>Liked</option><option>Owned</option><option>Wishlist</option><option>Disliked</option></select><button class="btn soft" name="shelf">+ Shelf</button></form><?php endif;?></div>
</div></section>
<section class="two-col"><div class="panel"><div class="section-head"><h2>What's inside?</h2><span><?= $ings->num_rows ?> mapped ingredients</span></div><div class="ingredient-list"><?php while($i=$ings->fetch_assoc()):?><div><span class="ing-dot"></span><b><?=e($i['name'])?></b><small><?=e($i['function_text'])?></small><em class="<?=$i['rating']==='Caution'?'warn':''?>"><?=e($i['rating'])?></em></div><?php endwhile;?></div></div>
<div class="panel"><h2>Ingredients in formula</h2><p><?=e($p['ingredients_text'])?></p><a class="btn soft" href="?page=ingredients">Explore dictionary →</a><h3>Find similar</h3><p>Products with overlapping ingredient profiles can be explored in Compare.</p><a href="?page=products&q=<?=urlencode($p['category'])?>">Browse <?=e($p['category'])?>s →</a></div></section>
<section class="panel"><div class="section-head"><h2>Community reviews</h2><span><?= $reviews->num_rows ?> reviews</span></div>
<?php if(logged_in()):?><form class="review-form" method="post" action="actions.php"><input type="hidden" name="action" value="review"><input type="hidden" name="product_id" value="<?=$id?>"><select name="rating"><option>5</option><option>4</option><option>3</option><option>2</option><option>1</option></select><input name="body" required placeholder="What did you think about this product?"><button class="btn primary">Post</button></form><?php endif;?>
<?php while($r=$reviews->fetch_assoc()):?><div class="review"><b><?=e($r['name'])?></b><span> <?=str_repeat('★',$r['rating'])?></span><p><?=e($r['body'])?></p></div><?php endwhile;?></section>