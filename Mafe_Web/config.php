<?php
// Mafe - zero terminal setup
// XAMPP default: MySQL root without password.
session_start();

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mafe_web');

function db_server() {
    static $db = null;
    if ($db === null) {
        $db = @new mysqli(DB_HOST, DB_USER, DB_PASS);
        if ($db->connect_errno) {
            die("MySQL belum menyala. Buka XAMPP lalu nyalakan Apache dan MySQL.");
        }
        $db->set_charset('utf8mb4');
        @$db->query("CREATE DATABASE IF NOT EXISTS `".DB_NAME."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    return $db;
}
function db() {
    static $db = null;

    if ($db === null) {

        // Connect to MySQL WITHOUT selecting a database first
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS);

        if ($db->connect_errno) {
            die("MySQL belum menyala. Buka XAMPP lalu nyalakan Apache dan MySQL.");
        }

        $db->set_charset('utf8mb4');

        // Create database automatically if it does not exist
        $sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci";

        if (!$db->query($sql)) {
            die("Database tidak bisa dibuat: " . $db->error);
        }

        // Select the newly created database
        if (!$db->select_db(DB_NAME)) {
            die("Database tidak bisa dipilih: " . $db->error);
        }
    }

    return $db;
}
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function user_id(){ return (int)($_SESSION['user_id'] ?? 0); }
function logged_in(){ return user_id() > 0; }
function require_login(){ if(!logged_in()){ header("Location: ?page=login"); exit; } }


function match_score($p,$u){
    $s=70;
    $skin=strtolower($u['skin_type']??'Normal'); $types=strtolower($p['skin_types']??'');
    if(strpos($types,$skin)!==false) $s+=15;
    $conc=strtolower($u['concerns']??''); $pc=strtolower($p['concerns']??'');
    if($conc){foreach(preg_split('/[,;]+/',$conc) as $c){$c=trim($c);if($c && strpos($pc,$c)!==false)$s+=5;}}
    if(($u['skin_type']??'')==='Sensitive' && !$p['fragrance_free']) $s-=8;
    return max(1,min(99,$s));
}

function init_db(){
    $d = db();
    $sql = [];
    $sql[] = "CREATE TABLE IF NOT EXISTS users(
        id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL,
        email VARCHAR(160) UNIQUE NOT NULL, password VARCHAR(255) NOT NULL,
        skin_type VARCHAR(50) DEFAULT 'Normal', concerns VARCHAR(255) DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    $sql[] = "CREATE TABLE IF NOT EXISTS ingredients(
        id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(120) UNIQUE NOT NULL,
        function_text VARCHAR(255) DEFAULT '', benefits TEXT, concerns TEXT,
        rating VARCHAR(30) DEFAULT 'Good', fragrance TINYINT DEFAULT 0,
        alcohol TINYINT DEFAULT 0, vegan TINYINT DEFAULT 1,
        fungal_safe TINYINT DEFAULT 1
    ) ENGINE=InnoDB";
    $sql[] = "CREATE TABLE IF NOT EXISTS products(
        id INT AUTO_INCREMENT PRIMARY KEY, brand VARCHAR(120) NOT NULL,
        name VARCHAR(180) NOT NULL, category VARCHAR(80) DEFAULT 'Other',
        price DECIMAL(12,2) DEFAULT 0, image VARCHAR(255) DEFAULT '',
        description TEXT, ingredients_text LONGTEXT, skin_types VARCHAR(255) DEFAULT 'Normal',
        concerns VARCHAR(255) DEFAULT '', fragrance_free TINYINT DEFAULT 0,
        alcohol_free TINYINT DEFAULT 0, vegan TINYINT DEFAULT 0,
        cruelty_free TINYINT DEFAULT 0, acne_safe TINYINT DEFAULT 0,
        barcode VARCHAR(80) DEFAULT '', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    $sql[] = "CREATE TABLE IF NOT EXISTS product_ingredients(
        product_id INT NOT NULL, ingredient_id INT NOT NULL,
        PRIMARY KEY(product_id,ingredient_id)
    ) ENGINE=InnoDB";
    $sql[] = "CREATE TABLE IF NOT EXISTS shelves(
        id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL,
        product_id INT NOT NULL, shelf VARCHAR(40) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY ux_shelf(user_id,product_id,shelf)
    ) ENGINE=InnoDB";
    $sql[] = "CREATE TABLE IF NOT EXISTS routines(
        id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL,
        name VARCHAR(120) NOT NULL, time_of_day VARCHAR(20) DEFAULT 'Morning',
        days VARCHAR(100) DEFAULT 'Every day', description TEXT DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    $sql[] = "CREATE TABLE IF NOT EXISTS routine_products(
        routine_id INT NOT NULL, product_id INT NOT NULL, step_no INT DEFAULT 1,
        PRIMARY KEY(routine_id,product_id)
    ) ENGINE=InnoDB";
    $sql[] = "CREATE TABLE IF NOT EXISTS routine_logs(
        id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL,
        routine_id INT NOT NULL, log_date DATE NOT NULL, note TEXT DEFAULT '',
        UNIQUE KEY ux_log(user_id,routine_id,log_date)
    ) ENGINE=InnoDB";
    $sql[] = "CREATE TABLE IF NOT EXISTS reviews(
        id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, product_id INT NOT NULL,
        rating TINYINT NOT NULL, body TEXT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    $sql[] = "CREATE TABLE IF NOT EXISTS posts(
        id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL,
        title VARCHAR(180) NOT NULL, body TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    $sql[] = "CREATE TABLE IF NOT EXISTS scan_history(
        id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL,
        product_id INT DEFAULT NULL, raw_text LONGTEXT DEFAULT '', scanned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";
    foreach($sql as $q) @$d->query($q);

    $cnt = $d->query("SELECT COUNT(*) c FROM ingredients")->fetch_assoc()['c'];
    if((int)$cnt===0){
        $ings = [
            ['Niacinamide','Brightening + barrier support','Helps support the skin barrier and improve uneven-looking tone','May be irritating for some users at high concentrations','Good',0,0],
            ['Hyaluronic Acid','Humectant','Helps attract water to the skin for hydration','Usually well tolerated','Good',0,0],
            ['Salicylic Acid','BHA exfoliant','Helps exfoliate inside pores and support acne-prone skin','Can be drying or irritating for some users','Good',0,0],
            ['Ceramide NP','Barrier lipid','Supports the skin barrier and helps reduce moisture loss','Rare sensitivity is possible','Good',0,0],
            ['Glycerin','Humectant','Helps keep skin hydrated','Rare sensitivity','Good',0,0],
            ['Panthenol','Soothing humectant','Supports hydration and soothing','Rare sensitivity','Good',0,0],
            ['Vitamin C','Antioxidant','Helps brighten the appearance of skin and provides antioxidant support','Can sting or irritate sensitive skin','Good',0,0],
            ['Retinol','Retinoid','Supports skin renewal and the appearance of fine lines','Can cause irritation and increased sensitivity','Caution',0,0],
            ['Fragrance','Fragrance','Adds scent','May irritate fragrance-sensitive users','Caution',1,0],
            ['Alcohol Denat.','Solvent','Helps formula texture and evaporation','Can be drying for some skin types','Caution',0,1],
            ['Centella Asiatica','Soothing botanical','Commonly used to soothe and support the skin barrier','Rare sensitivity','Good',0,0],
            ['Azelaic Acid','Multifunctional acid','Can help with uneven tone and blemish-prone skin','May sting or irritate initially','Good',0,0],
        ];
        $st=$d->prepare("INSERT INTO ingredients(name,function_text,benefits,concerns,rating,fragrance,alcohol) VALUES(?,?,?,?,?,?,?)");
        foreach($ings as $x){$st->bind_param("ssssssi",$x[0],$x[1],$x[2],$x[3],$x[4],$x[5],$x[6]);$st->execute();}
    }
    $pc = $d->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'];
    if((int)$pc===0){
        $products = [
        ['CeraVe','Hydrating Facial Cleanser','Cleanser',145000,'https://images.unsplash.com/photo-1556228578-8c89e6adf883?auto=format&fit=crop&w=700&q=80','Gentle cleanser focused on hydration and barrier support.','Ceramides, Glycerin, Hyaluronic Acid','Normal,Dry,Sensitive','Dryness,Redness',1,1,0,1,1],
        ['The Ordinary','Niacinamide 10% + Zinc 1%','Serum',115000,'https://images.unsplash.com/photo-1608248597279-f99d160bfcbc?auto=format&fit=crop&w=700&q=80','Lightweight serum aimed at uneven tone and blemish-prone skin.','Niacinamide','Oily,Combination,Normal','Pores,Acne,Dullness',1,1,1,1,1],
        ['COSRX','Advanced Snail 96 Mucin Power Essence','Essence',210000,'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=700&q=80','Hydrating essence with a soothing, cushiony finish.','Snail Mucin, Glycerin, Panthenol','Normal,Dry,Combination,Sensitive','Dryness,Texture,Redness',1,1,0,1,1],
        ['La Roche-Posay','Effaclar Medicated Gel Cleanser','Cleanser',225000,'https://images.unsplash.com/photo-1631729371254-42c2892f0e6e?auto=format&fit=crop&w=700&q=80','BHA cleanser for oily and blemish-prone routines.','Salicylic Acid, Glycerin','Oily,Combination','Acne,Pores',1,1,0,1,1],
        ['Beauty of Joseon','Relief Sun SPF50+','Sunscreen',190000,'https://images.unsplash.com/photo-1556229010-6c3f2c9ca5f8?auto=format&fit=crop&w=700&q=80','Daily sunscreen designed for a comfortable finish.','Rice Extract, Panthenol','Normal,Dry,Combination,Sensitive','Dullness,Redness',1,1,1,1,1],
        ['Paula’s Choice','2% BHA Liquid Exfoliant','Exfoliant',520000,'https://images.unsplash.com/photo-1612817288484-6f916006741a?auto=format&fit=crop&w=700&q=80','Leave-on BHA exfoliant for pores and blemish-prone skin.','Salicylic Acid, Green Tea','Oily,Combination,Normal','Acne,Pores,Texture',1,1,0,1,1],
        ['The Inkey List','Retinol Serum','Serum',210000,'https://images.unsplash.com/photo-1571781926291-c477ebfd024b?auto=format&fit=crop&w=700&q=80','Beginner-oriented retinol serum.','Retinol, Squalane','Normal,Dry,Combination','Texture,Fine Lines,Dullness',1,1,1,1,1],
        ['SKIN1004','Madagascar Centella Ampoule','Ampoule',245000,'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=700&q=80','Minimal-feel soothing ampoule.','Centella Asiatica, Glycerin','Sensitive,Normal,Dry','Redness,Dryness',1,1,1,1,1],
        ['The Ordinary','Azelaic Acid Suspension 10%','Treatment',165000,'https://images.unsplash.com/photo-1598440947619-2c35fc9aa908?auto=format&fit=crop&w=700&q=80','Creamy treatment targeting uneven-looking tone and blemishes.','Azelaic Acid, Glycerin','Normal,Oily,Combination','Acne,Dullness,Redness',1,1,1,1,1],
        ['Simple','Hydrating Light Moisturiser','Moisturizer',85000,'https://images.unsplash.com/photo-1620917670395-3c6b8b1f3b1d?auto=format&fit=crop&w=700&q=80','Simple lightweight moisturizer for everyday hydration.','Glycerin, Panthenol','Normal,Oily,Combination','Dryness',1,1,1,1,1],
        ['Some By Mi','AHA BHA PHA 30 Days Miracle Toner','Toner',175000,'https://images.unsplash.com/photo-1611930022073-b7a4ba5fcccd?auto=format&fit=crop&w=700&q=80','Exfoliating toner for texture and blemish-prone skin.','Salicylic Acid, Panthenol','Oily,Combination','Acne,Pores,Texture',1,1,1,1,1],
        ['Beauty of Joseon','Glow Serum Propolis + Niacinamide','Serum',180000,'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=700&q=80','Soothing brightening serum.','Niacinamide, Glycerin','Normal,Dry,Combination','Dullness,Redness,Pores',1,1,1,1,1],
        ];
        $st=$d->prepare("INSERT INTO products(brand,name,category,price,image,description,ingredients_text,skin_types,concerns,fragrance_free,alcohol_free,vegan,cruelty_free,acne_safe) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        foreach($products as $x){
            $st->bind_param("sssdsssssiiiii",...$x); $st->execute();
        }
    }
    # map ingredients from comma-separated product text
    $rows=$d->query("SELECT id,ingredients_text FROM products");
    $ins=$d->prepare("INSERT IGNORE INTO product_ingredients(product_id,ingredient_id) SELECT ?,id FROM ingredients WHERE LOWER(?) LIKE CONCAT('%',LOWER(name),'%')");
    while($r=$rows->fetch_assoc()){ $pid=(int)$r['id']; $txt=$r['ingredients_text']; $ins->bind_param("is",$pid,$txt); $ins->execute(); }
}
init_db();
?>
