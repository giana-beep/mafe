<?php
require_once "config.php"; require_login();
$id=(int)($_POST['id']??0);
$brand=trim($_POST['brand']);$name=trim($_POST['name']);$cat=trim($_POST['category']);$price=(float)($_POST['price']??0);
$image=trim($_POST['image']);$desc=trim($_POST['description']);$it=trim($_POST['ingredients_text']);$skins=trim($_POST['skin_types']);$conc=trim($_POST['concerns']);
$ff=isset($_POST['fragrance_free'])?1:0;$af=isset($_POST['alcohol_free'])?1:0;$veg=isset($_POST['vegan'])?1:0;
if($id){$st=db()->prepare("UPDATE products SET brand=?,name=?,category=?,price=?,image=?,description=?,ingredients_text=?,skin_types=?,concerns=?,fragrance_free=?,alcohol_free=?,vegan=? WHERE id=?");$st->bind_param("sssdsssssiiii",$brand,$name,$cat,$price,$image,$desc,$it,$skins,$conc,$ff,$af,$veg,$id);}
else{$st=db()->prepare("INSERT INTO products(brand,name,category,price,image,description,ingredients_text,skin_types,concerns,fragrance_free,alcohol_free,vegan) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");$st->bind_param("sssdsssssiii",$brand,$name,$cat,$price,$image,$desc,$it,$skins,$conc,$ff,$af,$veg);}
$st->execute();header("Location:?page=admin");exit;
?>