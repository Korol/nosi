<?php
$images_path = 'uploads/shop/products/thumbs4/';
?>
<!-- SLICK SLIDER -->
<div class="row">
    <div class="col-lg-12">
        <!-- Slider 1200x118 -->
        <div class="slick-slider">
        <?php if(!empty($products)): ?>
            <?php foreach($products as $product): ?>
            <div>
                <a href="<?=base_url($product['url']); ?>" title="<?=htmlspecialchars($product['title']); ?>">
                    <img src="<?=HTTP_HOST . $images_path . $product['file_name']; ?>" alt="<?=htmlspecialchars($product['title']); ?>">
                </a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
    </div>
</div>
<!-- /SLICK SLIDER -->