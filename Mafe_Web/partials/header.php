<?php
$me=null; if(logged_in()) $me=db()->query("SELECT * FROM users WHERE id=".user_id())->fetch_assoc();
?>
<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Mafe — Smart Skincare</title><link rel="stylesheet" href="assets/style.css">
</head><body>
<header class="topbar"><a class="logo" href="?page=home"><span>✦</span> Mafe</a>
<nav>
<a href="?page=products">Discover</a><a href="?page=ingredients">Ingredients</a><a href="?page=compare">Compare</a><a href="?page=routines">Routine</a><a href="?page=shelves">Shelves</a><a href="?page=community">Community</a>
</nav>
<div class="top-actions"><a class="scan" href="?page=scanner">⌕ Scan</a>
<?php if(logged_in()): ?><a class="avatar" href="?page=profile"><?=e(strtoupper(substr($me['name'],0,1)))?></a><?php else: ?><a class="login" href="?page=login">Log in</a><?php endif; ?></div>
</header><main class="container">