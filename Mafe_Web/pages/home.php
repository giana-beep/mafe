<?php
$featured=db()->query("SELECT * FROM products ORDER BY id DESC LIMIT 6");
?>
<section class="hero">
<div><div class="eyebrow">YOUR SKIN, SORTED ✦</div><h1>Stop guessing.<br><em>Understand</em> your skincare.</h1>
<p>Check ingredients, discover products, build routines, compare formulas, and keep your skincare journey organized.</p>
<div class="hero-btns"><a class="btn primary" href="?page=products">Explore products →</a><a class="btn soft" href="?page=scanner">Analyze ingredients</a></div></div>
<div class="hero-card"><div class="orb">✦</div><div class="score-big">92</div><div class="score-label">MATCH SCORE</div><div class="mini-tags"><span>Barrier friendly</span><span>Hydrating</span><span>Fragrance free</span></div></div>
</section>
<section class="quick-grid">
<a href="?page=scanner" class="quick"><b>📷 Scan / Analyze</b><small>Paste INCI or scan a barcode</small></a>
<a href="?page=compare" class="quick"><b>⚖ Compare</b><small>Compare up to 4 products</small></a>
<a href="?page=routines" class="quick"><b>☀ Build Routine</b><small>Track AM & PM steps</small></a>
<a href="?page=ingredients" class="quick"><b>🧪 Ingredient Dictionary</b><small>Understand what ingredients do</small></a>
</section>
<section><div class="section-head"><div><span class="eyebrow">DISCOVER</span><h2>Products worth exploring</h2></div><a href="?page=products">See all →</a></div>
<div class="product-grid"><?php while($p=$featured->fetch_assoc()): include "partials/product_card.php"; endwhile; ?></div></section>
<section class="callout"><div><span class="eyebrow">PERSONALIZATION</span><h2>Get your own match score</h2><p>Tell Mafe your skin type and concerns, then products are scored against your profile.</p></div><a class="btn primary" href="<?=logged_in()?'?page=profile':'?page=register'?>">Set up profile</a></section>