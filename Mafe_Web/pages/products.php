<?php
$q=trim($_GET['q']??''); $cat=$_GET['cat']??''; $skin=$_GET['skin']??''; $ff=isset($_GET['ff']);
$where=[];$params=[];$types='';
if($q){$where[]="(brand LIKE ? OR name LIKE ? OR ingredients_text LIKE ?)";$x="%$q%";$params=[$x,$x,$x];$types.="sss";}
if($cat){$where[]="category=?";$params[]=$cat;$types.="s";}
if($skin){$where[]="skin_types LIKE ?";$params[]="%$skin%";$types.="s";}
if($ff)$where[]="fragrance_free=1";
$sql="SELECT * FROM products".($where?" WHERE ".implode(" AND ",$where):"")." ORDER BY id DESC";
$st=db()->prepare($sql); if($params)$st->bind_param($types,...$params); $st->execute();$rs=$st->get_result();
?>
<section class="page-title"><span class="eyebrow">DISCOVER</span><h1>Find your next product.</h1><p>Search by brand, product or ingredient and filter by your needs.</p></section>
<form class="filterbar"><input name="q" value="<?=e($q)?>" placeholder="Search products, brands, ingredients…"><select name="cat"><option value="">All categories</option><?php foreach(['Cleanser','Serum','Moisturizer','Sunscreen','Toner','Essence','Ampoule','Exfoliant','Treatment'] as $c):?><option <?=$cat===$c?'selected':''?>><?=$c?></option><?php endforeach;?></select><select name="skin"><option value="">All skin types</option><?php foreach(['Normal','Dry','Oily','Combination','Sensitive'] as $s):?><option <?=$skin===$s?'selected':''?>><?=$s?></option><?php endforeach;?></select><label><input type="checkbox" name="ff" <?=$ff?'checked':''?>> Fragrance-free</label><button class="btn primary">Filter</button></form>
<div class="product-grid"><?php while($p=$rs->fetch_assoc()):include "partials/product_card.php";endwhile;?></div>
<?php if($rs->num_rows===0):?><div class="empty">No products found. Try another search.</div><?php endif;?>